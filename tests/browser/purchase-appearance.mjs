import { chromium, expect } from "@playwright/test";
import fs from "node:fs";
import path from "node:path";
import { fileURLToPath } from "node:url";

const root = fileURLToPath(new URL("../../", import.meta.url));
const base = process.env.APP_TEST_URL || "http://127.0.0.1:8000";
const output = "/tmp/kamali-purchase-appearance";
fs.mkdirSync(output, { recursive: true });

// Render the supplied HTML/CSS as the reference, with the same fonts hosted locally.
const fonts = [
    ["@fontsource-variable/manrope", "index.css"],
    ["@fontsource/prosto-one", "400.css"],
]
    .map(([name, file]) => {
        const folder = path.join(root, "node_modules", name);
        return fs
            .readFileSync(path.join(folder, file), "utf8")
            .replaceAll("Manrope Variable", "Manrope")
            .replace(
                /url\((\.\/files\/[^)]+)\)/g,
                (_, file) =>
                    `url(data:font/woff2;base64,${fs.readFileSync(path.join(folder, file)).toString("base64")})`,
            );
    })
    .join("\n");

function original(file) {
    return fs
        .readFileSync(path.join(root, file), "utf8")
        .replace(/<script\b[^>]*>[\s\S]*?<\/script>/g, "")
        .replace(/<link\b[^>]*https:\/\/[^>]*>/g, "")
        .replace(
            "<head>",
            `<head><base href="${base}/"><style>${fonts}</style>`,
        )
        .replace(
            /<!--\s*(<a\b[^>]*class="product-hero__add-basket"[\s\S]*?<\/a>)\s*-->/,
            "$1",
        );
}

const browser = await chromium.launch({
    executablePath:
        process.env.CHROMIUM_PATH ||
        (fs.existsSync("/usr/bin/chromium")
            ? "/usr/bin/chromium"
            : chromium.executablePath()),
    headless: true,
    args: ["--no-sandbox"],
});

async function ready(page) {
    await page.evaluate(async () => {
        await document.fonts.ready;
        await Promise.all(
            Array.from(document.images).map((i) => i.decode().catch(() => {})),
        );
    });
    await page.addStyleTag({
        content:
            "*,*::before,*::after{animation:none!important;transition:none!important}.cookie-popup,.lk-message{display:none!important}",
    });
}

async function appearance(locator, pseudo = null) {
    return locator.evaluate((element, pseudo) => {
        const style = getComputedStyle(element, pseudo);
        const properties = [
            "fontSize",
            "fontWeight",
            "lineHeight",
            "letterSpacing",
            "textTransform",
            "color",
            "backgroundColor",
            "backdropFilter",
            "borderTopWidth",
            "borderTopColor",
            "borderRadius",
            "paddingTop",
            "paddingBottom",
            "paddingLeft",
            "paddingRight",
        ];
        const result = Object.fromEntries(properties.map((p) => [p, style[p]]));
        if (!pseudo) {
            const box = element.getBoundingClientRect();
            result.width = box.width;
            result.height = box.height;
        } else {
            result.top = style.top;
        }
        return result;
    }, pseudo);
}

async function compare(actual, reference, name, pseudo = null) {
    const a = await appearance(actual, pseudo);
    const b = await appearance(reference, pseudo);
    for (const key of ["width", "height"]) {
        if (a[key] !== undefined) {
            expect(
                Math.abs(a[key] - b[key]),
                `${name}: ${key}`,
            ).toBeLessThanOrEqual(1);
            delete a[key];
            delete b[key];
        }
    }
    expect(a, `${name}: supplied purchase styles`).toEqual(b);
}

try {
    for (const width of [1440, 2048, 375]) {
        const context = await browser.newContext({
            viewport: { width, height: 1000 },
        });
        await context.addInitScript(() =>
            localStorage.setItem("kamali.cookies", "declined"),
        );
        const page = await context.newPage();
        const reference = await context.newPage();
        await page.goto(base + "/catalog?q=Vanilla");
        await reference.setContent(original("catalog.html"));
        const data = await page
            .locator(".catalog-page__item")
            .first()
            .evaluate((card) => ({
                name: card.querySelector(".catalog-page__item__name")
                    .textContent,
                description: card.querySelector(".catalog-page__item__text")
                    .textContent,
                price: card.querySelector(".catalog-page__item__price")
                    .textContent,
                label: card.querySelector(".catalog-page__item__btn")
                    .textContent,
                image: card.querySelector(".catalog-page__item__img img").src,
            }));
        await reference
            .locator(".catalog-page__main")
            .evaluate((grid, data) => {
                const card = grid.querySelector(".catalog-page__item");
                grid.replaceChildren(card);
                card.querySelector(".catalog-page__item__name").textContent =
                    data.name;
                card.querySelector(".catalog-page__item__text").textContent =
                    data.description;
                card.querySelector(".catalog-page__item__price").textContent =
                    data.price;
                card.querySelector(".catalog-page__item__img img").src =
                    data.image;
                card.querySelectorAll(
                    ".catalog-page__item__btn,.catalog-page__item__btn_mobile",
                ).forEach((button) => (button.textContent = data.label));
            }, data);
        await ready(page);
        await ready(reference);
        const selector =
            width <= 768
                ? ".catalog-page__item__btn_mobile"
                : ".catalog-page__item__btn";
        const actual = page.locator(selector).first();
        const expected = reference.locator(selector).first();
        if (width > 768) {
            await page.locator(".catalog-page__item").first().hover();
            await reference.locator(".catalog-page__item").first().hover();
            await expect(
                page.locator(".catalog-page__item__hover img").first(),
            ).toHaveAttribute("src", "/assets/images/product-hover.png");
            await compare(actual, expected, `catalogue ${width}`);
            await compare(
                actual,
                expected,
                `catalogue background ${width}`,
                "::before",
            );
            await actual.screenshot({
                path: `${output}/catalogue-${width}-original-state-vue.png`,
            });
            await expected.screenshot({
                path: `${output}/catalogue-${width}-original-state-html.png`,
            });
            await actual.hover();
            await expected.hover();
            await compare(actual, expected, `catalogue button hover ${width}`);
            await compare(
                actual,
                expected,
                `catalogue white fill ${width}`,
                "::before",
            );
        } else {
            await compare(actual, expected, `catalogue mobile ${width}`);
        }
        await actual.screenshot({
            path: `${output}/catalogue-${width}-vue.png`,
        });
        await expected.screenshot({
            path: `${output}/catalogue-${width}-html.png`,
        });

        await page.goto(base + "/products/blanche");
        await reference.setContent(original("product-page.html"));
        await reference
            .locator(".product-hero__count")
            .evaluate((element) => element.remove());
        const product = page.locator(".product-hero__add-basket");
        const sourceProduct = reference.locator(".product-hero__add-basket");
        await sourceProduct.evaluate(
            (button, label) => (button.textContent = label),
            await product.textContent(),
        );
        await ready(page);
        await ready(reference);
        await page.mouse.move(0, 0);
        await reference.mouse.move(0, 0);
        await compare(product, sourceProduct, `product ${width}`);
        await product.screenshot({
            path: `${output}/product-${width}-vue.png`,
        });
        await sourceProduct.screenshot({
            path: `${output}/product-${width}-html.png`,
        });
        await product.hover();
        await sourceProduct.hover();
        await compare(product, sourceProduct, `product hover ${width}`);
        await context.close();
    }
    console.log(
        "PASS: purchase typography, dimensions, backgrounds and hover states match the supplied HTML at 1440, 2048 and 375 pixels",
    );
} finally {
    await browser.close();
}
