import { chromium, expect } from "@playwright/test";
import fs from "node:fs";
const fixture = JSON.parse(
    fs.readFileSync(
        process.env.ACCESS_TEST_FIXTURE_FILE ||
            "/tmp/kamali-access-browser.json",
        "utf8",
    ),
);
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
fs.mkdirSync("/tmp/kamali-admin-access", { recursive: true });
const ownerContext = await browser.newContext({
    viewport: { width: 1440, height: 1000 },
});
const viewerContext = await browser.newContext({
    viewport: { width: 1440, height: 1000 },
});
for (const context of [ownerContext, viewerContext])
    await context.addInitScript(() =>
        localStorage.setItem("kamali.cookies", "declined"),
    );
const owner = await ownerContext.newPage(),
    viewer = await viewerContext.newPage();
const errors = [];
for (const page of [owner, viewer])
    page.on("pageerror", (error) => errors.push(error.message));
const field = (page, name) => page.locator(`[id="form.${name}"]`);
async function login(page, email, password) {
    await page.goto(base + "/admin/login");
    await page.locator("input[type=email]").fill(email);
    await page.locator("input[type=password]").fill(password);
    await page.getByRole("button", { name: /^(Войти|Sign in)$/ }).click();
    await page.waitForURL((url) => !url.pathname.endsWith("/login"));
}
async function permission(page, section, action, value = true) {
    const input = page.locator(`input[type=checkbox][value="${action}"]`);
    const actual = input.locator(
        `xpath=self::*[@*[starts-with(name(), 'wire:model')]='data.permissions.${section}']`,
    );
    await actual.setChecked(value);
}
try {
    await login(owner, fixture.owner_email, fixture.owner_password);
    await owner.goto(base + "/admin/roles/create");
    const roleName = "Просмотр заказов " + fixture.suffix;
    await field(owner, "name").fill(roleName);
    await permission(owner, "orders", "view");
    await owner.getByRole("button", { name: /^(Создать|Create)$/ }).click();
    await owner.waitForURL(
        (url) =>
            url.pathname.startsWith("/admin/roles") &&
            !url.pathname.endsWith("/create"),
    );
    if (!owner.url().endsWith("/edit")) {
        await owner
            .locator("tr")
            .filter({ hasText: roleName })
            .getByRole("link", { name: /Редактировать|Edit/ })
            .click();
    }
    const roleEdit = owner.url();
    await owner.screenshot({
        path: "/tmp/kamali-admin-access/role.png",
        fullPage: true,
    });
    await owner.goto(base + "/admin/staff/create");
    await field(owner, "name").fill("Сотрудник " + fixture.suffix);
    await field(owner, "email").fill(fixture.staff_email);
    await field(owner, "password").fill(fixture.staff_password);
    await field(owner, "admin_role_id").selectOption({ label: roleName });
    await owner.getByRole("button", { name: /^(Создать|Create)$/ }).click();
    await owner.waitForURL(/\/admin\/staff\/\d+\/edit$/);
    const staffEdit = owner.url();
    await owner.goto(base + "/admin/users");
    await expect(owner.locator("tbody")).not.toContainText(fixture.staff_email);
    await expect(owner.locator("tbody")).toContainText(fixture.owner_email);
    console.log(
        "PASS: owner creates a role and a separate staff account; the staff account is absent from customers",
    );

    await login(viewer, fixture.staff_email, fixture.staff_password);
    const nav = viewer.locator(".fi-sidebar");
    await expect(
        nav.getByRole("link", { name: "Заказы", exact: true }),
    ).toBeVisible();
    await expect(
        nav.getByRole("link", { name: "Товары", exact: true }),
    ).toHaveCount(0);
    await expect(
        nav.getByRole("link", { name: "Сотрудники", exact: true }),
    ).toHaveCount(0);
    await viewer.goto(base + "/admin/orders");
    await viewer.screenshot({
        path: "/tmp/kamali-admin-access/restricted.png",
        fullPage: true,
    });
    for (const route of ["/admin/products", "/admin/staff", "/admin/roles"])
        expect((await viewer.goto(base + route)).status()).toBe(403);
    console.log(
        "PASS: limited staff sees only allowed sections and receives 403 on direct forbidden URLs",
    );

    await owner.goto(roleEdit);
    await permission(owner, "orders", "view", false);
    await owner
        .getByRole("button", {
            name: /^(Сохранить|Сохранить изменения|Save changes)$/,
        })
        .click();
    await expect(
        owner.getByRole("heading", { name: /Сохранено|Saved/ }).first(),
    ).toBeVisible();
    expect((await viewer.goto(base + "/admin/orders")).status()).toBe(403);
    await owner.goto(staffEdit);
    await owner
        .getByRole("switch", { name: "Доступ в админку включён", exact: true })
        .uncheck();
    await owner
        .getByRole("button", {
            name: /^(Сохранить|Сохранить изменения|Save changes)$/,
        })
        .click();
    await expect(
        owner.getByRole("heading", { name: /Сохранено|Saved/ }).first(),
    ).toBeVisible();
    expect((await viewer.goto(base + "/admin")).status()).toBe(403);
    console.log(
        "PASS: changing a role and blocking an account take effect for an existing staff session",
    );

    await owner.goto(base + "/login");
    await owner
        .getByRole("textbox", { name: "Электронная почта", exact: true })
        .fill(fixture.owner_email);
    await owner
        .getByRole("textbox", { name: "Пароль", exact: true })
        .fill(fixture.customer_password);
    await owner
        .getByRole("button", { name: /Войти в личный кабинет/i })
        .click();
    await owner.waitForURL("**/account");
    await owner
        .getByRole("button", { name: "Контактная информация", exact: true })
        .click();
    await expect(
        owner.getByRole("textbox", { name: "Имя", exact: true }),
    ).toHaveValue("Покупатель " + fixture.suffix);
    await owner.goto(base + "/admin/staff");
    await expect(
        owner.getByRole("heading", { name: "Сотрудники", exact: true }),
    ).toBeVisible();
    expect(errors).toEqual([]);
    console.log(
        "PASS: customer and staff sessions use independent identities even with the same email",
    );
} catch (error) {
    await owner.screenshot({
        path: "/tmp/kamali-admin-access/failure-owner.png",
        fullPage: true,
    });
    await viewer.screenshot({
        path: "/tmp/kamali-admin-access/failure-staff.png",
        fullPage: true,
    });
    console.error(
        "Validation:",
        await owner.locator(".fi-fo-field-wrp-error-message").allTextContents(),
    );
    throw error;
} finally {
    await browser.close();
}
