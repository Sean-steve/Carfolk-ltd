import {useState,type FormEvent} from 'react';
import {CarFront,Search,SlidersHorizontal,X} from 'lucide-react';
import {previewVehicles} from '../data/vehicles';
export interface Criteria {make:string;model:string;budget:string;year:string;body:string}
interface Props {onSearch:(criteria:Criteria)=>void; onReset:()=>void; resultCount:number}
const initial:Criteria={make:'',model:'',budget:'',year:'',body:''};
export function VehicleSearch({onSearch,onReset,resultCount}:Props){
 const [criteria,setCriteria]=useState<Criteria>(initial);
 const [tab,setTab]=useState<'guided'|'quick'>('guided');
 const models=[...new Set(previewVehicles.filter(v=>!criteria.make||v.make===criteria.make).map(v=>v.model))];
 function update(key:keyof Criteria,value:string){setCriteria(prev=>({...prev,[key]:value,...(key==='make'?{model:''}:{})}));}
 function submit(e:FormEvent<HTMLFormElement>){e.preventDefault();onSearch(criteria);}
 function reset(){setCriteria(initial);onReset();}
 return <div className="search-dock" id="search"><div className="search-dock__tabs" role="tablist" aria-label="Search modes">
  <button type="button" role="tab" aria-selected={tab==='guided'} onClick={()=>setTab('guided')} className={tab==='guided'?'active':''}><CarFront size={18}/> Search Vehicles</button>
  <button type="button" role="tab" aria-selected={tab==='quick'} onClick={()=>setTab('quick')} className={tab==='quick'?'active':''}><Search size={18}/> Quick Search</button>
 </div>
 <form className="search-dock__form" onSubmit={submit}>
  <div className="search-dock__fields">
   <label>Make<select value={criteria.make} onChange={e=>update('make',e.target.value)}><option value="">Any make</option><option value="Toyota">Toyota</option><option value="Honda">Honda</option><option value="Mazda">Mazda</option></select></label>
   {tab==='guided'&&<label>Model<select value={criteria.model} onChange={e=>update('model',e.target.value)}><option value="">Any model</option>{models.map(m=><option key={m} value={m}>{m}</option>)}</select></label>}
   <label>Price range<select value={criteria.budget} onChange={e=>update('budget',e.target.value)}><option value="">Any price</option><option value="under2m">Below KES 2M</option><option value="2to5m">KES 2M – 5M</option><option value="over5m">Above KES 5M</option></select></label>
   {tab==='guided'&&<label>Year<select value={criteria.year} onChange={e=>update('year',e.target.value)}><option value="">Any year</option>{[2023,2022,2021,2020,2019,2018].map(y=><option key={y} value={y}>{y}</option>)}</select></label>}
   <label>Body type<select value={criteria.body} onChange={e=>update('body',e.target.value)}><option value="">Any body type</option><option value="SUV">SUV</option><option value="Crossover">Crossover</option></select></label>
  </div>
  <button className="search-dock__submit" type="submit"><Search size={18}/> Search Cars</button>
  </form>
  <div className="search-dock__hint"><span><SlidersHorizontal size={14}/> Preview filtering across {resultCount} illustrative vehicles. Real prices and stock connect in Phase 2.</span><button type="button" onClick={reset}><X size={14}/> Reset filters</button></div>
 </div>
}
