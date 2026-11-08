import { chromium, expect } from "@playwright/test";
import fs from "node:fs";
const fixture = JSON.parse(
    fs.readFileSync(
        process.env.ADMIN_TEST_FIXTURE_FILE ||
            "/tmp/kamali-editor-browser.json",
        "utf8",
    ),
);
const base = process.env.APP_TEST_URL || "http://127.0.0.1:8000";
const browser = await chromium.launch({
    executablePath: process.env.CHROMIUM_PATH || "/usr/bin/chromium",
    headless: true,
    args: ["--no-sandbox"],
});
const context = await browser.newContext({
    viewport: { width: 1440, height: 1000 },
    colorScheme: "dark",
});
await context.addInitScript(() => {
    localStorage.setItem("theme", "dark");
    localStorage.setItem("kamali.cookies", "declined");
});
const page = await context.newPage();
const errors = [];
page.on("pageerror", (error) => errors.push(error.message));
fs.mkdirSync("/tmp/kamali-woo-admin", { recursive: true });
try {
    await page.goto(base + "/admin/login");
    await page.locator("input[type=email]").fill(fixture.email);
    await page.locator("input[type=password]").fill(fixture.password);
    await page.getByRole("button", { name: /^(Войти|Sign in)$/ }).click();
    await page.waitForURL((url) => !url.pathname.endsWith("/login"));
    await page.goto(base + "/admin/products");
    expect(
        (await page.locator("thead th").allTextContents())
            .map((value) => value.trim())
            .filter(Boolean),
    ).toEqual(["Название", "Статус", "Дата публикации"]);
    await page.screenshot({
        path: "/tmp/kamali-woo-admin/products.png",
        fullPage: true,
    });
    await page.goto(base + "/admin/categories");
    await page.locator("input[type=search]").first().fill(fixture.suffix);
    const rows = page.locator("tbody tr");
    await expect(rows).toHaveCount(2);
    await expect(rows.nth(0)).toContainText(fixture.categories[0].name);
    await expect(rows.nth(1)).toContainText("— " + fixture.categories[1].name);
    await page.screenshot({
        path: "/tmp/kamali-woo-admin/categories.png",
        fullPage: true,
    });
    await page.goto(base + "/admin/products/create");
    await expect(page.locator('[id="form.type"]')).toHaveValue("simple");
    await expect(page.locator('[id="form.type"] option')).toHaveCount(2);
    expect(
        (await page.getByRole("tab").allTextContents()).map((value) =>
            value.trim(),
        ),
    ).toEqual(
        expect.arrayContaining([
            "Основные",
            "Запасы",
            "Доставка",
            "Атрибуты",
            "Дополнительно",
        ]),
    );
    const parent = await page
        .getByRole("checkbox", {
            name: fixture.categories[0].name,
            exact: true,
        })
        .boundingBox();
    const child = await page
        .getByRole("checkbox", {
            name: fixture.categories[1].name,
            exact: true,
        })
        .boundingBox();
    expect(child.x - parent.x).toBeGreaterThanOrEqual(20);
    const tabs = page.locator("[role=tablist]");
    await expect(tabs).toHaveAttribute("aria-orientation", "vertical");
    await page.screenshot({
        path: "/tmp/kamali-woo-admin/product-data.png",
        fullPage: true,
    });
    console.log(
        "PASS: three product columns, grouped category hierarchy, indented checkboxes, default simple type and vertical product tabs",
    );

    await page.goto(base + "/admin/media-assets");
    await expect(page.locator(".kamali-media-card").first()).toBeVisible();
    await page
        .locator(".kamali-media-card")
        .filter({ has: page.locator("img") })
        .first()
        .click();
    const dialog = page.getByRole("dialog");
    await expect(dialog.locator(".kamali-media-preview img")).toBeVisible();
    const title = "WP медиа " + Date.now();
    await dialog.getByRole("textbox", { name: /^Название/ }).fill(title);
    await dialog
        .getByRole("textbox", { name: "Альтернативный текст", exact: true })
        .fill("Превью в медиатеке");
    await page.screenshot({
        path: "/tmp/kamali-woo-admin/media-details.png",
        fullPage: true,
    });
    await dialog
        .getByRole("button", {
            name: /^(Сохранить изменения|Сохранить|Save changes)$/,
        })
        .click();
    await expect(dialog).toHaveCount(0);
    await page.locator("input[type=search]").first().fill(title);
    await expect(page.locator(".kamali-media-card")).toHaveCount(1);
    await page.locator("input[type=search]").first().fill("");
    await page
        .getByRole("button", { name: "Добавить медиафайлы", exact: true })
        .click();
    const uploadDialog = page.getByRole("dialog");
    const name = "wp-upload-" + Date.now() + ".png";
    await uploadDialog.locator("input[type=file]").setInputFiles([
        {
            name,
            mimeType: "image/png",
            buffer: fs.readFileSync("public/assets/images/catalog-product.png"),
        },
        {
            name: "second-" + name,
            mimeType: "image/png",
            buffer: fs.readFileSync(
                "public/assets/images/catalog-product2.png",
            ),
        },
    ]);
    await expect(
        uploadDialog.locator(
            '[data-filepond-item-state="processing-complete"]',
        ),
    ).toHaveCount(2, { timeout: 30000 });
    await uploadDialog
        .getByRole("button", { name: "Добавить", exact: true })
        .click();
    await expect(uploadDialog).toHaveCount(0);
    await page.locator("input[type=search]").first().fill(name);
    await expect(page.locator(".kamali-media-card")).toHaveCount(2);
    await expect(page.locator(".kamali-media-card img").first()).toBeVisible();
    await page.screenshot({
        path: "/tmp/kamali-woo-admin/media-grid.png",
        fullPage: true,
    });
    expect(errors).toEqual([]);
    console.log(
        "PASS: media grid opens a large preview, saves metadata, supports search and uploads multiple files",
    );
} catch (error) {
    await page.screenshot({
        path: "/tmp/kamali-woo-admin/failure.png",
        fullPage: true,
    });
    throw error;
} finally {
    await browser.close();
}
