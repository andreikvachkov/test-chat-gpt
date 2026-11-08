import {chromium,expect} from '@playwright/test';
import fs from 'node:fs';
const browser=await chromium.launch({executablePath:process.env.CHROMIUM_PATH||(fs.existsSync('/usr/bin/chromium')?'/usr/bin/chromium':chromium.executablePath()),headless:true,args:['--no-sandbox']});
fs.mkdirSync('/tmp/kamali-visual',{recursive:true});
const results=[];
try{
 for(const width of [1440,375]){
  for(const slug of ['index','about','history','contact','blog','article','document','document2','gift']){
   const geometries=[];
   for(const [kind,base,path] of [['original','http://127.0.0.1:8001',slug+'.html'],['vue','http://127.0.0.1:8000',slug==='index'?'':slug]]){
    const page=await browser.newPage({viewport:{width,height:900}});
    await page.goto(base+'/'+path,{waitUntil:'networkidle'});await page.evaluate(async()=>{await document.fonts.ready;await Promise.all(Array.from(document.images).map(i=>i.decode().catch(()=>{})));document.querySelectorAll('.swiper').forEach(e=>{e.swiper?.update();if(e.swiper?.params.autoHeight)e.swiper.updateAutoHeight(0);});});
    await page.addStyleTag({content:'.cookie-popup,.lk-message{display:none!important}*{animation:none!important;transition:none!important}'});
    await page.screenshot({path:`/tmp/kamali-visual/${slug}-${width}-${kind}.png`,fullPage:true});
    geometries.push(await page.locator('section,footer').evaluateAll(els=>els.map(el=>({class:el.className,y:el.getBoundingClientRect().y,height:el.getBoundingClientRect().height,width:el.getBoundingClientRect().width}))));
    await page.close();
   }
   const [a,b]=geometries;const differences=a.filter((r,i)=>!b[i]||Math.abs(r.y-b[i].y)>2||Math.abs(r.height-b[i].height)>2||Math.abs(r.width-b[i].width)>2);
   results.push({slug,width,sections:a.length,vueSections:b.length,differences});
  }
 }
 fs.writeFileSync('/tmp/kamali-visual/geometry.json',JSON.stringify(results,null,2));
 const mismatches=results.filter(r=>r.differences.length||r.sections!==r.vueSections);console.log(JSON.stringify(mismatches,null,2));expect(mismatches, 'marketing section geometry').toEqual([]);
}finally{await browser.close();}
