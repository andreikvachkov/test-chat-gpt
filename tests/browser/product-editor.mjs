import { chromium, expect } from "@playwright/test";
import fs from "node:fs";
import { fileURLToPath } from "node:url";

const fixture = JSON.parse(
    fs.readFileSync(
        process.env.ADMIN_TEST_FIXTURE_FILE ||
            "/tmp/kamali-editor-browser.json",
        "utf8",
    ),
);
fixture.suffix += "-" + Date.now().toString(36);
const base = process.env.APP_TEST_URL || "http://127.0.0.1:8000";
const browser = await chromium.launch({
    executablePath:
        process.env.CHROMIUM_PATH ||
        (fs.existsSync("/usr/bin/chromium")
            ? "/usr/bin/chromium"
            : chromium.executablePath()),
    headless: true,
    args: ["--no-sandbox"],
});
const context = await browser.newContext({
    viewport: { width: 1440, height: 1000 },
});
await context.addInitScript(() =>
    localStorage.setItem("kamali.cookies", "declined"),
);
const page = await context.newPage();
const errors = [];
page.on("pageerror", (e) => errors.push(e.message));
fs.mkdirSync("/tmp/kamali-product-editor", { recursive: true });
const field = (name) => page.locator(`[id="form.${name}"]`);

async function basic(kind) {
    await page.goto(base + "/admin/products/create");
    await field("name").fill(`${kind} ${fixture.suffix}`);
    await field("slug").fill(`${kind.toLowerCase()}-${fixture.suffix}`);
    await field("short_description").fill("Краткое описание покупателю");
    await field("sku").fill(`${kind}-${fixture.suffix}`);
    await field("article").fill(`ART-${kind}-${fixture.suffix}`);
    await field("status").selectOption("published");
    for (const item of [...fixture.categories, ...fixture.collections])
        await page
            .getByRole("checkbox", { name: item.name, exact: true })
            .check();
}

try {
    await page.goto(base + "/admin/login");
    await page.locator("input[type=email]").fill(fixture.email);
    await page.locator("input[type=password]").fill(fixture.password);
    await page.getByRole("button", { name: /^(Sign in|Войти)$/ }).click();
    await page.waitForURL((url) => !url.pathname.endsWith("/login"));
    await page.goto(base + "/admin/products");
    await page
        .getByRole("link", { name: /^(New product|Создать товар|Создать)$/i })
        .click();
    await page.waitForURL("**/admin/products/create");
    console.log("PASS: products use a dedicated create page");

    await basic("Simple");
    await page
        .locator("[contenteditable=true]")
        .first()
        .fill("Полное описание товара из редактора");
    await field("simple.price").fill("620.50");
    await field("simple.stock").fill("5");
    await field("simple.name").fill("250 мл");
    const uploads = page.locator(".fi-fo-file-upload input[type=file]");
    await uploads
        .nth(0)
        .setInputFiles(
            fileURLToPath(
                new URL(
                    "../../public/assets/images/catalog-product.png",
                    import.meta.url,
                ),
            ),
        );
    await uploads
        .nth(1)
        .setInputFiles([
            fileURLToPath(
                new URL(
                    "../../public/assets/images/catalog-product2.png",
                    import.meta.url,
                ),
            ),
            fileURLToPath(
                new URL(
                    "../../public/assets/images/product-page-slider1.png",
                    import.meta.url,
                ),
            ),
        ]);
    await expect(
        page.locator('[data-filepond-item-state="processing-complete"]'),
    ).toHaveCount(3, { timeout: 30000 });
    await page.getByRole("button", { name: /^(Create|Создать)$/ }).click();
    await page.waitForURL(/\/admin\/products\/\d+\/edit$/);
    const simpleEdit = page.url();
    await expect(field("sku")).toHaveValue(`Simple-${fixture.suffix}`);
    await expect(page.locator(".filepond--item")).toHaveCount(3);
    await page.screenshot({
        path: "/tmp/kamali-product-editor/simple.png",
        fullPage: true,
    });
    console.log(
        "PASS: simple product saves two categories, two collections, separate descriptions, article and three uploaded images",
    );

    const shop = await context.newPage();
    await shop.goto(base + `/products/simple-${fixture.suffix}`);
    await expect(shop.locator(".product-hero__text").first()).toHaveText(
        "Краткое описание покупателю",
    );
    await expect(shop.locator(".product-hero__art")).toContainText(
        `ART-Simple-${fixture.suffix}`,
    );
    await expect(
        shop.locator('[data-tab="product-tab-1"].product-tab-section__text'),
    ).toContainText("Полное описание товара из редактора");
    await expect(
        shop.locator(".product-hero__main-slider .swiper-slide"),
    ).toHaveCount(3);
    await shop.close();

    await basic("Variable");
    await field("type").selectOption("variable");
    await page.getByRole("tab", { name: "Атрибуты", exact: true }).click();
    for (let i = 0; i < fixture.attributes.length; i++) {
        await page
            .getByRole("button", { name: "Добавить атрибут", exact: true })
            .click();
        const rows = page
            .locator(".fi-fo-repeater-item")
            .filter({ has: page.locator("select") });
        const row = rows.nth(i);
        await row
            .locator("select")
            .selectOption(String(fixture.attributes[i].id));
        for (const value of fixture.attributes[i].values)
            await row
                .getByRole("checkbox", { name: value.name, exact: true })
                .check();
        await row
            .getByRole("switch", {
                name: "Использовать для вариаций",
                exact: true,
            })
            .check();
    }
    await page.getByRole("tab", { name: "Вариации", exact: true }).click();
    await page
        .getByRole("button", {
            name: "Сформировать вариации из атрибутов",
            exact: true,
        })
        .click();
    const prices = page.locator('input[id$=".price"]');
    await expect(prices).toHaveCount(4);
    for (let i = 0; i < 4; i++) {
        await prices.nth(i).fill(String(700 + i));
        await page
            .locator('input[id$=".stock"]')
            .nth(i)
            .fill(String(3 + i));
    }
    await page.getByRole("button", { name: /^(Create|Создать)$/ }).click();
    await page.waitForURL(/\/admin\/products\/\d+\/edit$/);
    await page.getByRole("tab", { name: "Вариации", exact: true }).click();
    await expect(
        page.locator('input[id*=".variants."][id$=".sku"]'),
    ).toHaveCount(4);
    const skus = await page
        .locator('input[id*=".variants."][id$=".sku"]')
        .evaluateAll((els) => els.map((e) => e.value));
    await page
        .getByRole("button", {
            name: "Сформировать вариации из атрибутов",
            exact: true,
        })
        .click();
    await expect(page.locator('input[id$=".price"]').nth(3)).toHaveValue(
        "703.00",
    );
    expect(
        await page
            .locator('input[id*=".variants."][id$=".sku"]')
            .evaluateAll((els) => els.map((e) => e.value)),
    ).toEqual(skus);
    await page.screenshot({
        path: "/tmp/kamali-product-editor/variable.png",
        fullPage: true,
    });
    console.log(
        "PASS: attribute checkboxes generate four variations and regeneration preserves prices and SKUs",
    );

    await page.goto(base + `/products/variable-${fixture.suffix}`);
    await page
        .locator(".product-hero__size label")
        .filter({ hasText: /^250 мл$/ })
        .click();
    await expect(page.getByLabel("250 мл", { exact: true })).toBeChecked();
    await page
        .locator(".product-hero__size label")
        .filter({ hasText: /^Vanilla$/ })
        .click();
    await expect(page.getByLabel("Vanilla", { exact: true })).toBeChecked();
    await expect(page.locator(".product-hero__add-basket")).toContainText(
        "703",
    );
    await page.locator(".product-hero__add-basket").click();
    await expect(
        page.locator(".popup-cart.active .popup-cart__product__text"),
    ).toContainText("250 мл / Vanilla");
    await page.locator(".popup-cart__close").click();
    await page.goto(simpleEdit);
    await page
        .getByRole("button", { name: "Медиатека", exact: true })
        .first()
        .click();
    await page
        .locator('input[type=radio][value="/assets/images/product.jpg"]')
        .check();
    await page
        .getByRole("button", { name: "Выбрать", exact: true })
        .last()
        .click();
    await page
        .getByRole("button", {
            name: /^(Save changes|Сохранить изменения|Сохранить)$/,
        })
        .first()
        .click();
    await expect(
        page.getByText(/Saved|Сохранено/, { exact: true }).first(),
    ).toBeVisible();
    expect(errors).toEqual([]);
    console.log(
        "PASS: variation selection adds the correct price to the cart; the media library selects images with thumbnails",
    );
} catch (error) {
    await page.screenshot({
        path: "/tmp/kamali-product-editor/failure.png",
        fullPage: true,
    });
    console.error(
        "Validation:",
        await page.locator(".fi-fo-field-wrp-error-message").allTextContents(),
    );
    throw error;
} finally {
    await browser.close();
}
