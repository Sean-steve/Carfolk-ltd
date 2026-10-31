import {createContext,useContext,useEffect,useMemo,useState,type ReactNode} from 'react';
import {getVehiclesByIds,isApiConfigured,type Vehicle} from '../api/client';
type CompareState={
 selected:string[];vehicles:Vehicle[];loading:boolean;
 toggleCompare:(id:string)=>boolean;clear:()=>void;remove:(id:string)=>void;max:number;
};
const CompareCtx=createContext<CompareState|null>(null);
const key='carfolk-live-comparison-v1';
const isId=(s:unknown):s is string=>typeof s==='string'&&/^[0-9a-f]{8}-[0-9a-f-]{27,}$/i.test(s);
function initial():string[]{
 try{const x=JSON.parse(window.sessionStorage.getItem(key)??'[]');return Array.isArray(x)?[...new Set(x.filter(isId))].slice(0,3):[]}catch{return []}
}
export function CompareProvider({children}:{children:ReactNode}){
 const [selected,setSelected]=useState<string[]>(initial);
 const [vehicles,setVehicles]=useState<Vehicle[]>([]);
 const [loading,setLoading]=useState(false);
 useEffect(()=>{try{sessionStorage.setItem(key,JSON.stringify(selected))}catch{/* restricted browsers */}},[selected]);
 useEffect(()=>{
  if(!isApiConfigured()||selected.length===0){setVehicles([]);setLoading(false);return}
  const ctl=new AbortController();setLoading(true);
  getVehiclesByIds(selected,ctl.signal).then(cars=>{
   if(ctl.signal.aborted)return;
   setVehicles(cars);setSelected(prev=>prev.filter(id=>cars.some(v=>v.id===id)));setLoading(false);
  }).catch(()=>{if(!ctl.signal.aborted){setLoading(false);setVehicles([])}});
  return ()=>ctl.abort();
 },[selected.join(',')]);
 const value=useMemo<CompareState>(()=>({
  selected,vehicles,loading,max:3,
  toggleCompare:(id:string)=>{
   if(!isId(id)||!isApiConfigured())return false;
   if(selected.includes(id)){setSelected(prev=>prev.filter(x=>x!==id));return true;}
   if(selected.length>=3)return false;
   setSelected(prev=>[...prev,id]);return true;
  },
  clear:()=>setSelected([]),remove:(id:string)=>setSelected(prev=>prev.filter(x=>x!==id))
 }),[selected,vehicles,loading]);
 return <CompareCtx.Provider value={value}>{children}</CompareCtx.Provider>;
}
export function useCompare():CompareState {
 const context=useContext(CompareCtx);
 if(!context)throw new Error('useCompare must be used inside CompareProvider');
 return context;
}
