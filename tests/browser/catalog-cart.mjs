import { chromium, expect } from "@playwright/test";
import fs from "node:fs";

const browser = await chromium.launch({
    executablePath:
        process.env.CHROMIUM_PATH ||
        (fs.existsSync("/usr/bin/chromium")
            ? "/usr/bin/chromium"
            : chromium.executablePath()),
    args: ["--no-sandbox"],
    headless: true,
});
const base = process.env.APP_TEST_URL || "http://127.0.0.1:8000";
fs.mkdirSync("/tmp/kamali-ui", { recursive: true });

// Verify painted contrast rather than a particular CSS implementation.
async function expectReadable(locator, paint = "color") {
    const ratio = await locator.evaluate((element, property) => {
        const rgb = getComputedStyle(element)
            [property].match(/[\d.]+/g)
            ?.slice(0, 3)
            .map(Number);
        if (!rgb) return 0;
        const luminance = rgb
            .map((v) => v / 255)
            .map((v) =>
                v <= 0.04045 ? v / 12.92 : ((v + 0.055) / 1.055) ** 2.4,
            )
            .reduce((n, v, i) => n + v * [0.2126, 0.7152, 0.0722][i], 0);
        // The cart and search controls are painted on white or near-white panels.
        return 1.05 / (luminance + 0.05);
    }, paint);
    expect(
        ratio,
        `${paint} must remain readable on the light panel`,
    ).toBeGreaterThanOrEqual(4.5);
}

try {
    for (const width of [1440, 2048, 375]) {
        const context = await browser.newContext({
            viewport: { width, height: width === 375 ? 812 : 1000 },
        });
        await context.addInitScript(() =>
            localStorage.setItem("kamali.cookies", "declined"),
        );
        const page = await context.newPage();
        const errors = [];
        page.on("pageerror", (error) => errors.push(error.message));

        await page.goto(base + "/catalog?q=Sultan");
        await expect(page.locator(".catalog-page__item")).toHaveCount(1);
        await expect(page.locator(".store-pagination")).toHaveCount(0);
        await page.screenshot({
            path: `/tmp/kamali-ui/catalog-${width}.png`,
            fullPage: true,
        });

        const card = page.locator(".catalog-page__item");
        await card.hover();
        await card
            .locator(
                width === 375
                    ? ".catalog-page__item__btn_mobile"
                    : ".catalog-page__item__btn",
            )
            .click();
        const catalogueCart = page.locator(".popup-cart.active");
        await expect(
            catalogueCart.locator(".popup-cart__product__count .value"),
        ).toHaveText("1");
        await catalogueCart
            .getByRole("button", { name: "Удалить товар" })
            .click();
        await expect(catalogueCart.locator(".popup-cart__empty")).toBeVisible();
        await catalogueCart
            .getByRole("button", { name: "Закрыть корзину" })
            .click();
        await expect(catalogueCart).toHaveCount(0);

        // A physical click on the photo must work even while its hover overlay is on top.
        const picture = page.locator(".catalog-page__item__img");
        await picture.scrollIntoViewIfNeeded();
        const box = await picture.boundingBox();
        await page.mouse.click(box.x + box.width / 2, box.y + box.height / 2);
        await page.waitForURL("**/products/sultan");
        await expect(page.locator(".product-hero__title")).toContainText(
            "Sultan",
        );
        await expect(page.locator(".product-hero__main")).toBeVisible();
        await page.screenshot({
            path: `/tmp/kamali-ui/product-${width}.png`,
            fullPage: true,
        });

        await page.locator(".product-hero__add-basket").click();
        const cart = page.locator(".popup-cart.active");
        await expect(cart).toBeVisible();
        await expect(cart.locator(".popup-cart__product")).toHaveCount(1);
        await expect(
            cart.locator(".popup-cart__product__count .value"),
        ).toHaveText("1");
        await expectReadable(
            cart.locator(".popup-cart__product__count .value"),
        );
        for (const control of [
            ".popup-cart__close",
            ".popup-cart__product__delete",
            ".popup-cart__product__minus",
            ".popup-cart__product__plus",
        ]) {
            await expect(cart.locator(`${control} svg`)).toBeVisible();
            await expectReadable(cart.locator(`${control} svg path`), "stroke");
        }
        await expect(
            cart.locator(".popup-cart__product__add-wishlist svg"),
        ).toBeVisible();
        await cart
            .getByRole("button", { name: "Увеличить количество" })
            .click();
        await expect(
            cart.locator(".popup-cart__product__count .value"),
        ).toHaveText("2");
        await cart
            .getByRole("button", { name: "Уменьшить количество" })
            .click();
        await expect(
            cart.locator(".popup-cart__product__count .value"),
        ).toHaveText("1");
        const submit = cart.getByRole("link", {
            name: "Оформить заказ",
            exact: true,
        });
        await expect(submit).toBeInViewport();
        const submitWidth = await submit.evaluate(
            (e) => e.getBoundingClientRect().width,
        );
        const resultWidth = await cart
            .locator(".popup-cart__product__result")
            .evaluate((e) => e.getBoundingClientRect().width);
        expect(Math.abs(submitWidth - resultWidth)).toBeLessThanOrEqual(1);
        await page.screenshot({ path: `/tmp/kamali-ui/cart-${width}.png` });

        await cart.getByRole("button", { name: "Удалить товар" }).click();
        await expect(cart.locator(".popup-cart__empty")).toBeVisible();
        await cart.getByRole("button", { name: "Закрыть корзину" }).click();
        await expect(cart).toHaveCount(0);
        await page.getByRole("button", { name: "Поиск", exact: true }).click();
        const search = page.locator(".search-popup.active");
        await expect(search).toBeVisible();
        await expectReadable(
            search.locator(".search-popup__close svg path"),
            "stroke",
        );
        await search.getByRole("button", { name: "Закрыть поиск" }).click();
        await expect(search).toHaveCount(0);
        expect(
            await page.evaluate(() => document.documentElement.scrollWidth),
        ).toBeLessThanOrEqual(width + 1);
        expect(errors).toEqual([]);
        await context.close();
    }
    console.log(
        "PASS: catalogue image navigation, product layout, SVG visibility and contrast, cart actions and totals at 1440, 2048 and 375 pixels",
    );
} finally {
    await browser.close();
}
