import {createContext,useContext,useEffect,useMemo,useState,type ReactNode} from 'react';
import {DEMO_CARS} from '../data/showroom';
type CompareState={selected:string[];toggleCompare:(id:string)=>boolean;clear:()=>void;remove:(id:string)=>void;max:number};
const CompareContext=createContext<CompareState|null>(null);
const storageKey='carfolk-preview-compare-v2';
function initial():string[]{
 try{const raw=window.sessionStorage.getItem(storageKey);const a=raw?JSON.parse(raw):[];return Array.isArray(a)?a.filter((x):x is string=>typeof x==='string'&&DEMO_CARS.some(c=>c.id===x)).slice(0,3):[]}catch{return []}
}
export function CompareProvider({children}:{children:ReactNode}){
 const [selected,setSelected]=useState<string[]>(initial);
 useEffect(()=>{try{window.sessionStorage.setItem(storageKey,JSON.stringify(selected))}catch{/* safe without session storage */}},[selected]);
 const value=useMemo<CompareState>(()=>({
  selected,
  toggleCompare:(id:string)=>{if(!DEMO_CARS.some(c=>c.id===id))return false;if(selected.includes(id)){setSelected(x=>x.filter(a=>a!==id));return true}if(selected.length>=3)return false;setSelected(x=>[...x,id]);return true},
  clear:()=>setSelected([]),
  remove:(id:string)=>setSelected(x=>x.filter(a=>a!==id)),
  max:3,
 }),[selected]);
 return <CompareContext.Provider value={value}>{children}</CompareContext.Provider>;
}
export function useCompare():CompareState{
 const ctx=useContext(CompareContext);
 if(!ctx)throw new Error('useCompare must be used within CompareProvider');
 return ctx;
}
