"""Serve the unchanged HTML layouts for local visual comparisons only."""
from http.server import SimpleHTTPRequestHandler,ThreadingHTTPServer
from pathlib import Path
import re
root=Path(__file__).resolve().parents[1]
class Handler(SimpleHTTPRequestHandler):
 def translate_path(self,path):
  path=path.split('?')[0]
  if path.startswith('/assets/'):return str(root/'public'/path.lstrip('/'))
  return str(root/path.lstrip('/'))
 def do_GET(self):
  if self.path=='/reference-fonts.css':
   css=''
   for package,filename in [('@fontsource-variable/manrope','index.css'),('@fontsource/prosto-one','400.css')]:
    text=(root/'node_modules'/package/filename).read_text().replace('Manrope Variable','Manrope')
    css+=text.replace('./files/','/node_modules/'+package+'/files/')
   self.send_response(200);self.send_header('Content-Type','text/css');self.end_headers();self.wfile.write(css.encode());return
  if self.path.endswith('.html'):
   f=root/self.path.lstrip('/')
   if f.is_file():
    html=f.read_text();html=re.sub(r'<link href="https://fonts.googleapis.com/[^>]+>', '<link href="/reference-fonts.css" rel="stylesheet">',html,flags=re.S)
    html=html.replace('https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css','/node_modules/swiper/swiper-bundle.min.css').replace('https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js','/node_modules/swiper/swiper-bundle.min.js').replace('https://unpkg.com/lenis@1.3.8/dist/lenis.css','/node_modules/lenis/dist/lenis.css').replace('https://unpkg.com/lenis@1.3.8/dist/lenis.min.js','/node_modules/lenis/dist/lenis.min.js')
    html=re.sub(r'<script src="https://code.jquery.com/[^>]+></script>','',html)
    self.send_response(200);self.send_header('Content-Type','text/html');self.end_headers();self.wfile.write(html.encode());return
  super().do_GET()
ThreadingHTTPServer(('127.0.0.1',8001),Handler).serve_forever()
