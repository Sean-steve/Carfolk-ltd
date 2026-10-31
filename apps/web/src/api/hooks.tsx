import {createContext,useContext,useEffect,useState,type ReactNode} from 'react';
import {getOptions,getSite,getVehicle,listVehicles,isApiConfigured,
 type CatalogueOptions,type CataloguePageResponse,type Site,type Vehicle} from './client';
type Load<T>={data:T|null;loading:boolean;error:string|null;ready:boolean};
export function useRemote<T>(loader:(signal:AbortSignal)=>Promise<T>,key:string):Load<T>{
 const ready=isApiConfigured();
 const [value,setValue]=useState<Load<T>>({data:null,loading:ready,error:null,ready});
 useEffect(()=>{
  if(!ready){setValue({data:null,loading:false,error:null,ready:false});return}
  const controller=new AbortController();
  setValue({data:null,loading:true,error:null,ready:true});
  loader(controller.signal).then(data=>{if(!controller.signal.aborted)setValue({data,loading:false,error:null,ready:true})})
    .catch(error=>{if(!controller.signal.aborted)setValue({data:null,loading:false,error:error instanceof Error?error.message:'Request failed',ready:true})});
  return ()=>controller.abort();
 // caller supplies a key for all parameters; only restart on key changes.
 // eslint-disable-next-line react-hooks/exhaustive-deps
 },[key,ready]);
 return value;
}
export function useVehicles(params:Record<string,string|number|boolean|undefined>={}):Load<CataloguePageResponse>{
 const key=JSON.stringify(params);
 return useRemote(signal=>listVehicles(params,signal),key);
}
export function useVehicle(slug:string):Load<Vehicle>{
 return useRemote(signal=>getVehicle(slug,signal),slug);
}
export function useOptions():Load<CatalogueOptions>{
 return useRemote(signal=>getOptions(signal),'options');
}
type SiteState=Load<Site>;
const SiteCtx=createContext<SiteState|null>(null);
export function SiteProvider({children}:{children:ReactNode}){
 const site=useRemote(signal=>getSite(signal),'site');
 return <SiteCtx.Provider value={site}>{children}</SiteCtx.Provider>;
}
export function useSite():SiteState{
 const site=useContext(SiteCtx);
 if(!site)throw new Error('useSite must be inside SiteProvider');
 return site;
}
