import type {SortBy} from './types';
export type Filters={
 q:string;make:string;model:string;body:string;fuel:string;transmission:string;
 condition:string;minPrice:string;maxPrice:string;minYear:string;maxYear:string;sort:SortBy;
};
export const EMPTY_FILTERS:Filters={q:'',make:'',model:'',body:'',fuel:'',transmission:'',condition:'',minPrice:'',maxPrice:'',minYear:'',maxYear:'',sort:'recent'};
const keys=Object.keys(EMPTY_FILTERS) as (keyof Filters)[];
export function filtersFromQuery(raw:string):Filters {
 const p=new URLSearchParams(raw.startsWith('?')?raw.slice(1):raw);
 const out={...EMPTY_FILTERS};
 for(const key of keys){const value=p.get(key);if(value!==null)(out as Record<string,string>)[key]=value;}
 if(!['recent','price-asc','price-desc','year-desc','mileage-asc'].includes(out.sort))out.sort='recent';
 return out;
}
export function filtersToQuery(f:Filters):string {
 const query=new URLSearchParams();
 for(const key of keys)if(f[key] && f[key]!==EMPTY_FILTERS[key])query.set(key,f[key]);
 return query.toString();
}
export function filtersToApi(f:Filters,page=1):Record<string,string|number>{
 const out:Record<string,string|number>={page,sort:f.sort};
 const mapping:Partial<Record<keyof Filters,string>>={
  q:'q',make:'make',model:'model',body:'body_type',fuel:'fuel_type',
  transmission:'transmission',condition:'condition',minPrice:'price_min',
  maxPrice:'price_max',minYear:'year_min',maxYear:'year_max',
 };
 for(const [from,to] of Object.entries(mapping) as [keyof Filters,string][])
   if(f[from])out[to]=f[from];
 return out;
}
export const options=(values:string[])=>[...new Set(values)].sort((a,b)=>a.localeCompare(b));
