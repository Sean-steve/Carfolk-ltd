import type {DemoVehicle} from '../data/showroom';
export type SortBy = 'recent'|'price-asc'|'price-desc'|'year-desc'|'mileage-asc';
export type Filters = {q:string;make:string;model:string;body:string;fuel:string;transmission:string;condition:string;minPrice:string;maxPrice:string;minYear:string;maxYear:string;sort:SortBy};
export const EMPTY_FILTERS:Filters={q:'',make:'',model:'',body:'',fuel:'',transmission:'',condition:'',minPrice:'',maxPrice:'',minYear:'',maxYear:'',sort:'recent'};
const filterKeys = Object.keys(EMPTY_FILTERS) as (keyof Filters)[];
export function filtersFromQuery(search:string):Filters{
 const params=new URLSearchParams(search.startsWith('?')?search.slice(1):search);
 const f={...EMPTY_FILTERS};
 for(const key of filterKeys){const val=params.get(key);if(val!==null)(f as Record<string,string>)[key]=val;}
 if(!['recent','price-asc','price-desc','year-desc','mileage-asc'].includes(f.sort)) f.sort='recent';
 return f;
}
export function filtersToQuery(filters:Filters):string{
 const params=new URLSearchParams();
 for(const key of filterKeys){if(filters[key] && filters[key]!==EMPTY_FILTERS[key])params.set(key,filters[key]);}
 return params.toString();
}
export function filterAndSort(cars:DemoVehicle[],f:Filters):DemoVehicle[]{
 const n=(s:string)=>s===''?null:Number(s);
 const minPrice=n(f.minPrice),maxPrice=n(f.maxPrice),minYear=n(f.minYear),maxYear=n(f.maxYear);
 const query=f.q.trim().toLowerCase();
 const result=cars.filter(v=>{
  if(query&&!([v.make,v.model,v.variant,v.body,v.fuel,v.summary,String(v.year)].join(' ').toLowerCase().includes(query)))return false;
  if(f.make&&v.make!==f.make)return false;
  if(f.model&&v.model!==f.model)return false;
  if(f.body&&v.body!==f.body)return false;
  if(f.fuel&&v.fuel!==f.fuel)return false;
  if(f.transmission&&v.transmission!==f.transmission)return false;
  if(f.condition&&v.condition!==f.condition)return false;
  if(minPrice!==null&&Number.isFinite(minPrice)&&v.priceKes<minPrice)return false;
  if(maxPrice!==null&&Number.isFinite(maxPrice)&&v.priceKes>maxPrice)return false;
  if(minYear!==null&&Number.isFinite(minYear)&&v.year<minYear)return false;
  if(maxYear!==null&&Number.isFinite(maxYear)&&v.year>maxYear)return false;
  return true;
 });
 return result.sort((a,b)=>{
  switch(f.sort){
   case 'price-asc':return a.priceKes-b.priceKes;
   case 'price-desc':return b.priceKes-a.priceKes;
   case 'year-desc':return b.year-a.year||a.make.localeCompare(b.make);
   case 'mileage-asc':return a.mileageKm-b.mileageKm;
   default:return DEMO_ORDER.indexOf(a.id)-DEMO_ORDER.indexOf(b.id);
  }
 });
}
const DEMO_ORDER=['prado','rav4','crv','cx5','forester','harrier','demio','fit'];
export function options<T extends string|number>(values:T[]):T[]{return [...new Set(values)].sort((a,b)=>String(a).localeCompare(String(b)));}
