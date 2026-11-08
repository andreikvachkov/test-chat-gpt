"""One-time layout import. Requires beautifulsoup4. Does not change original HTML.
Generated Vue files and seed JSON are committed; do not rerun after editing them.
"""
from pathlib import Path
from bs4 import BeautifulSoup, NavigableString, Comment
import hashlib,json,re
ROOT=Path(__file__).resolve().parents[1]
entries={}
def read(name):return BeautifulSoup((ROOT/(name+'.html')).read_text(),'html.parser')
def key(group,value,kind):
 k=group+'.'+hashlib.sha1((kind+value).encode()).hexdigest()[:12]
 entries[k]={'key':k,'group':group,'type':kind,'label':value[:150],'value':value}
 return k
def convert(node,group):
 for el in list(node.find_all(['script','style'])):el.decompose()
 for el in list(node.find_all(string=lambda x:isinstance(x,Comment))):el.extract()
 for el in node.find_all(True):
  for attr in list(el.attrs):
   if attr.startswith('on'):del el[attr]
  for attr in ['src','poster','href','alt','title','placeholder']:
   v=el.get(attr)
   if not v:continue
   if v.startswith('assets/'):v='/'+v
   if attr=='href' and (v.startswith('javascript:') or v=='#'):v='/catalog'
   if attr=='href' and v.endswith('.html'):v={'/index.html':'/','index.html':'/','catalog.html':'/catalog','product-page.html':'/catalog','order.html':'/checkout','lk-login.html':'/login'}.get(v,'/'+v.lstrip('/'))
   kind={'src':'image','poster':'image','href':'link'}.get(attr,'text')
   k=key(group,v,kind);del el[attr];el[':'+attr]="text('"+k+"')"
 for s in list(node.find_all(string=True)):
  if not isinstance(s,NavigableString) or isinstance(s,Comment) or s.parent.name in ('svg','path','script','style'):continue
  v=str(s);content=v.strip()
  if not content:continue
  k=key(group,content,'text');s.replace_with(v[:len(v)-len(v.lstrip())]+"{{ text('"+k+"') }}"+v[len(v.rstrip()):])
 return str(node)
def component(path,markup,group):
 # Use a fragment root so the original layout does not gain extra boxes.
 soup=BeautifulSoup(markup,'html.parser');markup=convert(soup,group)
 p=ROOT/'resources/js'/path;p.parent.mkdir(parents=True,exist_ok=True)
 p.write_text('<script setup>\nimport { useContent } from \'../composables/content\';\nconst { text } = useContent();\n</script>\n<template>\n'+markup+'\n</template>\n')

soup=read('index')
# Give previously empty navigation anchors real destinations before binding them.
links={'Каталог':'/catalog','О бренде':'/about','История бренда':'/history','Блог':'/blog','Контакты':'/contact','Покупателям':'/document','Политика конфиденциальности':'/document2','Публичная оферта':'/document','Подарочные карты':'/gift'}
def navigation(soup):
 for a in soup.select('a'):
  text=' '.join(a.get_text(' ',strip=True).split());cls=' '.join(a.get('class',[]))
  if not a.get('href'):
   a['href']=links.get(text,'/catalog')
   if any(x in cls for x in ['logo','logotype']):a['href']='/'
   if 'header__lk' in cls:a['href']='/account'
   if 'wishlist' in cls:a['href']='/favourites'
  if 'header__basket' in cls:a['href']='/checkout'
  if 'header__search' in cls:a['href']='/catalog'
  if 'blog' in cls and ('item' in cls or 'card' in cls):a['href']='/article'
navigation(soup)
component('Components/PromoBar.vue',str(soup.select_one('.running_line')),'shared')
component('Components/SiteHeader.vue',str(soup.header),'shared')
component('Components/SiteFooter.vue',str(soup.footer),'shared')
component('Components/CookieNotice.vue',str(soup.select_one('.cookie-popup')),'shared')
marketing=['index','about','history','contact','blog','article','document','document2','document-gift-card','gift','404']
for name in marketing:
 s=read(name);navigation(s)
 wrap=s.select_one('.wrap');parts=[]
 for node in wrap.contents:
  if getattr(node,'name',None) in ('header','footer'):continue
  parts.append(str(node))
 component('Layouts/'+name+'.vue',''.join(parts),name)
 p=ROOT/'resources/js/Layouts'/f'{name}.vue';p.write_text(p.read_text().replace("'../composables/","'../composables/"))
# Import the original product hero and informational sections, keeping its geometry.
s=read('product-page');navigation(s)
hero=s.select_one('.product-hero__title');hero.clear();hero.append('{{ product.name }}')
for selector,value in [('.product-hero__text','product.description'),('.product-hero__art span','selected?.sku')]:
 el=s.select_one(selector)
 if el:el.clear();el.append('{{ '+value+' }}')
variants=s.select_one('.product-hero__variants');variants.clear();variants.append(BeautifulSoup('<div class="radio-item" v-for="variant in product.variants" :key="variant.id"><label><input class="radio-input" type="radio" v-model="variantId" :value="variant.id"><span>{{ variant.name }}</span></label></div>','html.parser'))
# Find and replace the original purchase controls, not unrelated content.
for el in s.select('.product-hero__buttons'):el.clear();el.append(BeautifulSoup('<ProductPurchase :product="product" v-model="variantId" />','html.parser'))
# Gallery remains the original slider layout, with actual product images.
for selector in ['.product-hero__main-slider .swiper-wrapper','.product-hero__thumbs-slider .swiper-wrapper']:
 el=s.select_one(selector)
 if el:el.clear();el.append(BeautifulSoup('<div class="swiper-slide" v-for="image in gallery" :key="image"><img :src="image" :alt="product.name"></div>','html.parser'))
# Vue expressions must not become CMS text.
wrap=s.select_one('.wrap');parts=''.join(str(n) for n in wrap.contents if getattr(n,'name',None) not in ('header','footer'))
markers={}
def protect(m):
 k='KAMALIBIND'+str(len(markers))+'END';markers[k]=m.group();return k
parts=re.sub(r'\{\{.*?\}\}',protect,parts)
converted=convert(BeautifulSoup(parts,'html.parser'),'product-page')
for k,v in markers.items():
 # Remove the temporary content entry, restoring the Vue binding directly.
 for ek,entry in list(entries.items()):
  if entry['value']==k:converted=converted.replace("{{ text('"+ek+"') }}",v);del entries[ek]
converted=converted.replace('<productpurchase','<ProductPurchase').replace('</productpurchase>','</ProductPurchase>')
(ROOT/'resources/js/Layouts/product-page.vue').write_text('''<script setup>
import { computed, ref } from 'vue';
import { usePage } from '@inertiajs/vue3';
import { useContent } from '../composables/content';
import ProductPurchase from '../Components/ProductPurchase.vue';
const { text } = useContent();
const product = usePage().props.product;
const variantId = ref(product.variants[0]?.id);
const selected = computed(()=>product.variants.find(v=>v.id===variantId.value));
const gallery = computed(()=>product.gallery?.length ? product.gallery : [product.image].filter(Boolean));
</script><template>'''+converted+'</template>\n')
(ROOT/'database/seeders/data/content.json').write_text(json.dumps(list(entries.values()),ensure_ascii=False,indent=2)+'\n')
# Share the same content defaults in development without a database round trip per component.
(ROOT/'resources/js/content-defaults.json').write_text(json.dumps({k:v['value'] for k,v in entries.items()},ensure_ascii=False)+'\n')
print('Imported',len(entries),'content entries')
