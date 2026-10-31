/**
 * Carfolk public API. The application never falls back to synthetic inventory or
 * silently claims success when the Laravel server is unreachable.
 *
 * The GitHub Pages build is inert without an explicitly configured HTTPS API.
 * Local development and same-origin production default to /api/v1.
 */
export type Vehicle = {
 id:string;slug:string;make:string;model:string;trim:string|null;year:number;
 price_kes:number;mileage_km:number|null;engine_cc:number|null;
 fuel_type:string|null;transmission:string|null;drivetrain:string|null;
 body_type:string|null;condition:string|null;colour:string|null;location:string|null;
 description:string|null;features:string[];status:'available'|'reserved'|'sold';
 is_featured:boolean;images:{id:string;url:string;alt_text:string|null;is_primary:boolean;sort_order:number}[];
};
export type Site = {
 name:string;tagline:string;phone:string|null;whatsapp:string|null;email:string|null;
 address:string|null;opening_hours:string|null;about:string|null;
 leads_enabled:boolean;privacy_url:string|null;
};
export type CatalogueOptions = {
 makes:string[];models_by_make:Record<string,string[]>;bodies:string[];
 fuel_types:string[];transmissions:string[];conditions:string[];
};
export type PageMeta={current_page:number;last_page:number;per_page:number;total:number};
export type CataloguePageResponse={data:Vehicle[];meta:PageMeta};
export type EnquiryPayload={
 vehicle_id?:string;name:string;phone:string;email?:string;message:string;
 consent:true;preferred_date?:string;preferred_time?:string;
};
export type LeadResponse={id:string;status:'new'|'requested';message:string};
export class ApiError extends Error {
 constructor(message:string,public readonly status:number,public readonly fieldErrors:Record<string,string[]>={}){super(message);this.name='ApiError'}
}
const override=String(import.meta.env.VITE_CARFOLK_API_BASE??'').trim().replace(/\/$/,'');
const isPagesBuild=import.meta.env.BASE_URL.startsWith('/Carfolk-ltd/');
function validConfiguredBase(value:string):boolean {
 if(!value)return false;
 if(value.startsWith('/'))return value.startsWith('/api/')&&!value.startsWith('//');
 try {const url=new URL(value);return url.protocol==='https:' || (url.protocol==='http:'&&['localhost','127.0.0.1'].includes(url.hostname));}catch{return false}
}
export function isApiConfigured():boolean {return override?validConfiguredBase(override):!isPagesBuild;}
function root():string{return override||'/api/v1';}
async function send<T>(path:string,options:RequestInit={},signal?:AbortSignal):Promise<T>{
 if(!isApiConfigured())throw new ApiError('Carfolk live API is not configured for this preview.',0);
 let response:Response;
 try{
  response=await fetch(root()+path,{
   ...options,credentials:'omit',signal,headers:{Accept:'application/json',...(options.body?{'Content-Type':'application/json'}:{}),...options.headers},
  });
 }catch(e){
  if(e instanceof DOMException&&e.name==='AbortError')throw e;
  throw new ApiError('Unable to connect to the Carfolk dealership server.',0);
 }
 let body:unknown;
 try{body=await response.json()}catch{throw new ApiError('Unexpected dealership server response.',response.status)}
 if(!response.ok){
  const value=body as {message?:string;errors?:Record<string,string[]>};
  throw new ApiError(
   response.status===429?'Too many requests. Please try again later.':
   response.status===503?'The dealership service is not currently available.':
   response.status===404?'This vehicle is no longer available.':
   response.status===422?'Please correct the highlighted information.':
   'The dealership server could not complete your request.',
   response.status,response.status===422?value?.errors??{}:{},
  );
 }
 return body as T;
}
const params=(values:Record<string,string|number|boolean|undefined>):string=>{
 const q=new URLSearchParams();
 for(const [key,value] of Object.entries(values)) if(value!==undefined&&value!==''&&value!==false)q.set(key,String(value));
 const s=q.toString();return s?'?'+s:'';
};
export function listVehicles(filters:Record<string,string|number|boolean|undefined>={},signal?:AbortSignal):Promise<CataloguePageResponse>{
 return send<CataloguePageResponse>('/vehicles'+params(filters),{},signal);
}
export async function getVehicle(slug:string,signal?:AbortSignal):Promise<Vehicle>{
 const body=await send<{data:Vehicle}>('/vehicles/'+encodeURIComponent(slug),{},signal);
 return body.data;
}
export async function getVehiclesByIds(ids:string[],signal?:AbortSignal):Promise<Vehicle[]>{
 if(ids.length===0)return [];
 const query=new URLSearchParams();ids.slice(0,3).forEach(id=>query.append('ids[]',id));
 const body=await send<{data:Vehicle[]}>('/vehicles/compare?'+query.toString(),{},signal);
 return body.data;
}
export async function getOptions(signal?:AbortSignal):Promise<CatalogueOptions>{
 const body=await send<{data:CatalogueOptions}>('/vehicles/options',{},signal);
 return body.data;
}
export async function getSite(signal?:AbortSignal):Promise<Site>{
 const body=await send<{data:Site}>('/site',{},signal);
 return body.data;
}
export async function submitEnquiry(kind:'general'|'vehicle'|'test-drive',payload:EnquiryPayload):Promise<LeadResponse>{
 const endpoint=kind==='test-drive'?'/test-drives':'/enquiries';
 const body=await send<{data:LeadResponse}>(endpoint,{method:'POST',body:JSON.stringify(payload)});
 return body.data;
}
export const kes=(value:number)=>'KES '+new Intl.NumberFormat('en-KE',{maximumFractionDigits:0}).format(value);
export const vehicleName=(car:Vehicle)=>[car.make,car.model,car.trim].filter(Boolean).join(' ');
