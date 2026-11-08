import { chromium, expect } from '@playwright/test';
import fs from 'node:fs';
const browser=await chromium.launch({executablePath:process.env.CHROMIUM_PATH||(fs.existsSync('/usr/bin/chromium')?'/usr/bin/chromium':chromium.executablePath()),headless:true,args:['--no-sandbox']});
const context=await browser.newContext({viewport:{width:1440,height:1000}});
await context.addInitScript(()=>localStorage.setItem('kamali.cookies','declined'));
const page=await context.newPage();const errors=[];page.on('pageerror',e=>errors.push(e.message));
const base=process.env.APP_TEST_URL||'http://127.0.0.1:8000';
try{
 for(const path of ['/','/about','/history','/blog','/article','/contact','/document','/gift','/catalog','/products/sultan','/login']){
  const response=await page.goto(base+path);expect(response.status(),path).toBe(200);await page.locator('header').waitFor();await page.waitForTimeout(150);
 }
 await page.goto(base+'/register');const email=`browser-${Date.now()}@example.test`;
 await page.getByRole('textbox',{name:'Ваше имя',exact:true}).fill('Проверка браузера');await page.getByRole('textbox',{name:'Электронная почта',exact:true}).fill(email);
 await page.getByRole('textbox',{name:'Пароль',exact:true}).fill('BrowserPassword123');await page.getByRole('textbox',{name:'Повторите пароль',exact:true}).fill('BrowserPassword123');await page.getByRole('checkbox').check();
 await page.getByRole('button',{name:'Зарегистрироваться',exact:true}).click();await page.waitForURL('**/account');
 await page.goto(base+'/products/sultan');await page.locator('.product-hero__add-basket').click();await expect(page.locator('.popup-cart.active')).toBeVisible();await expect(page.locator('.popup-cart__product')).toHaveCount(1);
 await page.locator('.popup-cart__product__add-wishlist').click();await expect(page.locator('.popup-cart__product__add-wishlist')).toHaveAttribute('aria-pressed','true');
 await page.getByRole('link',{name:'Оформить заказ',exact:true}).click();await page.waitForURL('**/checkout');
 await page.getByLabel('Телефон',{exact:true}).fill('+79991234567');await page.getByLabel('Город, адрес или желаемый пункт СДЭК').fill('Казань, пункт СДЭК');await page.getByRole('checkbox').check();
 await page.getByRole('button',{name:'Создать заказ',exact:true}).click();await page.waitForURL('**/account/orders/*');await expect(page.getByText('Оплата не начата',{exact:false})).toBeVisible();
 for(let i=0;i<3;i++){await page.locator('header a').filter({hasText:/каталог/i}).first().click();await page.waitForURL('**/catalog');await page.locator('header a').filter({hasText:/о бренде/i}).first().click();await page.waitForURL('**/about');}
 await page.screenshot({path:'/tmp/kamali-desktop.png',fullPage:true});
 await page.setViewportSize({width:375,height:812});await page.goto(base+'/');await page.evaluate(()=>document.fonts.ready);await page.screenshot({path:'/tmp/kamali-mobile.png',fullPage:true});
 expect(await page.evaluate(()=>document.documentElement.scrollWidth)).toBeLessThanOrEqual(376);
 await page.getByRole('button',{name:'Меню',exact:true}).click();
 await expect(page.locator('header nav')).toBeVisible();
 await page.locator('header nav a').filter({hasText:/каталог/i}).first().click();await page.waitForURL('**/catalog');
 await page.getByRole('button',{name:'Поиск',exact:true}).filter({visible:true}).click();
 await page.getByRole('textbox',{name:'Поиск товаров'}).fill('Sultan');await page.getByRole('button',{name:'Найти',exact:true}).last().click();
 await expect(page.locator('.catalog-page__item')).toHaveCount(1);
 expect(errors,'browser JavaScript errors').toEqual([]);
 console.log('PASS: pages, registration, cart, checkout, Inertia navigation and mobile overflow');
}finally{await browser.close();}
