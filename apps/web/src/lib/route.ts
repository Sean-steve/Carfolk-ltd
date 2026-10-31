export type Route={name:'home'|'cars'|'detail'|'compare'|'finance'|'about'|'contact'|'not-found';slug?:string;query:string};
export function currentRoute():Route{
 const hash=decodeURIComponent(window.location.hash);
 const raw=hash.startsWith('#/')?hash.slice(2):'';
 const [path,query='']=raw.split('?');
 if(!path||path==='home')return {name:'home',query};
 if(path==='cars')return {name:'cars',query};
 if(path.startsWith('cars/')&&path.length>5)return {name:'detail',slug:path.slice(5),query};
 if(path==='compare')return {name:'compare',query};
 if(path==='finance')return {name:'finance',query};
 if(path==='about')return {name:'about',query};
 if(path==='contact')return {name:'contact',query};
 return {name:'not-found',query};
}
export const toCar=(slug:string)=>'#/cars/'+encodeURIComponent(slug);
