// Import unbound labels and image/link attributes into the CMS seed data.
// Existing bindings are preserved. Review generated changes before committing.
import fs from 'node:fs';
import crypto from 'node:crypto';
import {parse} from '@vue/compiler-dom';
const entries=JSON.parse(fs.readFileSync('database/seeders/data/content.json'));
const defaults=JSON.parse(fs.readFileSync('resources/js/content-defaults.json'));
const hash=s=>crypto.createHash('sha1').update(s).digest('hex').slice(0,12);
function add(value,type='text',context=''){
 const key='shared.'+hash(type+value+context);
 if(!(key in defaults)){entries.push({key,group:'shared',label:(context?context.split('/').at(-1)+' — ':'')+value.slice(0,100),type,value});defaults[key]=value;}
 return key;
}
const files=['Pages/Auth.vue','Pages/Checkout.vue','Pages/Catalog.vue','Pages/Account.vue','Components/CartPanel.vue','Components/SearchPanel.vue','Components/ProductCard.vue','Components/SiteHeader.vue','Components/SiteFooter.vue','Components/PromoBar.vue','Components/CookieNotice.vue',...fs.readdirSync('resources/js/Layouts').map(f=>'Layouts/'+f)];
for(const file of files){
 const path='resources/js/'+file;let source=fs.readFileSync(path,'utf8');const start=source.indexOf('<template>')+10;const end=source.lastIndexOf('</template>');let template=source.slice(start,end);const ast=parse(template);const replacements=[];
 function walk(n){
  if(n.type===2&&n.content.trim()){
   const value=n.content.trim();const key=add(value);replacements.push({start:n.loc.start.offset,end:n.loc.end.offset,value:n.loc.source.replace(n.loc.source.trim(),`{{ text('${key}') }}`)});
  }
  if(n.type===1){for(const attr of n.props??[]){
   // Each image or link has its own CMS field, even when the original placeholders coincide.
   if(attr.type===6&&['src','poster','href','placeholder','title','alt'].includes(attr.name)&&attr.value?.content){
    const value=attr.value.content;const type=['src','poster'].includes(attr.name)?'image':attr.name==='href'?'link':'text';
    const key=add(value,type,file+':'+attr.loc.start.offset+':'+attr.name);
    replacements.push({start:attr.loc.start.offset,end:attr.loc.end.offset,value:`:${attr.name}="text('${key}')"`});
   }else if(attr.type===7&&attr.name==='bind'&&['href','src','poster'].includes(attr.arg?.content)){
    const match=attr.exp?.content.match(/^text\('([^']+)'\)$/);if(!match)continue;
    const old=entries.find(e=>e.key===match[1]);if(!old)continue;
    // Already individualized entries have a contextual label containing the file name.
    if(old.label.includes(file.split('/').at(-1)+':'))continue;
    const key=add(old.value,old.type,file+':'+attr.loc.start.offset+':'+attr.arg.content);
    replacements.push({start:attr.exp.loc.start.offset,end:attr.exp.loc.end.offset,value:`text('${key}')`});
   }
  }}
  for(const c of n.children??[])walk(c);
 }
 walk(ast);for(const r of replacements.sort((a,b)=>b.start-a.start))template=template.slice(0,r.start)+r.value+template.slice(r.end);
 source=source.slice(0,start)+template+source.slice(end);
 if(!source.includes('useContent'))source=source.replace('<script setup>',"<script setup>\nimport { useContent } from '../composables/content';\nconst { text }=useContent();");
 fs.writeFileSync(path,source);
}
const unique=[...new Map(entries.map(e=>[e.key,e])).values()];
fs.writeFileSync('database/seeders/data/content.json',JSON.stringify(unique,null,2)+'\n');
fs.writeFileSync('resources/js/content-defaults.json',JSON.stringify(defaults)+'\n');
console.log('Registered content entries:',unique.length);
