import {defineConfig} from 'vite';
import react from '@vitejs/plugin-react';
// Search indexing requires explicit launch authorization at build time;
// the production Nginx noindex header and Laravel SEO gate are additional controls.
export default defineConfig(({mode})=>{
 const indexApproved=mode==='production'&&process.env.CARFOLK_SEO_INDEX_APPROVED==='I_APPROVE_CARFOLK_INDEXING';
 return {
  plugins:[
   react(),
   {name:'carfolk-robots-launch-gate',transformIndexHtml(source:string){
     return source.replace('__CARFOLK_ROBOTS__',indexApproved?'index,follow':'noindex,nofollow');
   }},
  ],
  base:mode==='pages'?'/Carfolk-ltd/':'/',
  server:{port:5173,proxy:{
   '/api':{target:'http://127.0.0.1:8000',changeOrigin:true},
   '/storage':{target:'http://127.0.0.1:8000',changeOrigin:true},
  }},
 };
});
