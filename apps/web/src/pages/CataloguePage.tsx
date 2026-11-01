import {useEffect,useMemo,useState,type FormEvent} from 'react';
import {ArrowRight,CarFront,ChevronDown,Filter,Info,Search,SlidersHorizontal,X} from 'lucide-react';
import {Shell} from '../components/Shell';
import {ListingCard} from '../components/ListingCard';
import {useOptions,useVehicles} from '../api/hooks';
import {EMPTY_FILTERS,filtersFromQuery,filtersToApi,filtersToQuery,type Filters} from '../lib/catalogue';
import type {SortBy} from '../lib/types';
const labels:Partial<Record<keyof Filters,string>>={make:'Make',model:'Model',body:'Body',fuel:'Fuel',transmission:'Transmission',condition:'Condition',minPrice:'Min price',maxPrice:'Max price',minYear:'From year',maxYear:'To year',q:'Search'};
function href(f:Filters,page=1){const p=filtersToQuery(f);const extra=new URLSearchParams(p);if(page>1)extra.set('page',String(page));return '#/cars'+(extra.size?'?'+extra.toString():'')}
function Select({label,value,items,onChange}:{label:string;value:string;items:string[];onChange:(v:string)=>void}){
 return <label className="catalogue-filter-label">{label}<span className="catalogue-select"><select value={value} onChange={e=>onChange(e.target.value)}><option value="">All {label.toLowerCase()}</option>{items.map(x=><option key={x} value={x}>{x}</option>)}</select><ChevronDown size={16}/></span></label>
}
export function CataloguePage({query}:{query:string}){
 const filters=useMemo(()=>filtersFromQuery(query),[query]);
 const rawPage=Number(new URLSearchParams(query).get('page')??1);
 const page=Number.isSafeInteger(rawPage)&&rawPage>0?rawPage:1;
 const [draft,setDraft]=useState(filters),[mobileOpen,setMobileOpen]=useState(false),[notice,setNotice]=useState('');
 useEffect(()=>setDraft(filters),[filters]);
 const {data,loading,error,ready}=useVehicles(filtersToApi(filters,page));
 const opts=useOptions();
 const makes=opts.data?.makes??[];
 const models=draft.make?opts.data?.models_by_make[draft.make]??[]:[...new Set(Object.values(opts.data?.models_by_make??{}).flat())].sort();
 const select=(key:keyof Filters,value:string)=>setDraft(f=>({...f,[key]:value,...(key==='make'?{model:''}:{})}));
 const apply=(e:FormEvent)=>{e.preventDefault();window.location.hash=href(draft);setMobileOpen(false)};
 const patch=(key:keyof Filters,value:string)=>{window.location.hash=href({...filters,[key]:value,...(key==='make'?{model:''}:{})})};
 const clear=()=>{window.location.hash='#/cars';setDraft({...EMPTY_FILTERS})};
 const result=data?.data??[];
 const chips=(Object.keys(filters) as (keyof Filters)[]).filter(k=>k!=='sort'&&filters[k]);
 return <Shell>
  <section className="catalogue-intro"><div className="container catalogue-intro__inner"><div className="catalogue-breadcrumb"><a href="#/">Home</a><span>/</span> Cars for Sale</div><span className="eyebrow">CARFOLK INVENTORY</span><h1>Find a car that<br/><em>fits your life.</em></h1><p>Discover published dealership vehicles, compare their specifications and get in touch.</p></div></section>
  <div className="container catalogue-shell">
   <aside className={'catalogue-sidebar'+(mobileOpen?' catalogue-sidebar--open':'')} aria-label="Vehicle filters">
    <div className="catalogue-sidebar__top"><div><SlidersHorizontal size={21}/><strong>Refine your search</strong></div><button type="button" className="catalogue-sidebar__close" aria-label="Close filters" onClick={()=>setMobileOpen(false)}><X size={20}/></button></div>
    <form onSubmit={apply}>
     <label className="catalogue-filter-label" htmlFor="car-query">Search vehicles</label><div className="catalogue-search-field"><Search size={16}/><input id="car-query" value={draft.q} onChange={e=>select('q',e.target.value)} maxLength={100} placeholder="Make, model or keyword"/></div>
     <Select label="Make" value={draft.make} items={makes} onChange={v=>select('make',v)}/>
     <Select label="Model" value={draft.model} items={models} onChange={v=>select('model',v)}/>
     <div className="catalogue-filter-divider"/><div className="catalogue-filter-title">Price range (KES)</div>
     <div className="catalogue-two"><label>Minimum<input type="number" min="0" step="50000" value={draft.minPrice} onChange={e=>select('minPrice',e.target.value)} placeholder="Any"/></label><label>Maximum<input type="number" min="0" step="50000" value={draft.maxPrice} onChange={e=>select('maxPrice',e.target.value)} placeholder="Any"/></label></div>
     <div className="catalogue-filter-divider"/><div className="catalogue-filter-title">Model year</div>
     <div className="catalogue-two"><label>From<input type="number" min="1950" max="2035" value={draft.minYear} onChange={e=>select('minYear',e.target.value)} placeholder="Any"/></label><label>To<input type="number" min="1950" max="2035" value={draft.maxYear} onChange={e=>select('maxYear',e.target.value)} placeholder="Any"/></label></div>
     <div className="catalogue-filter-divider"/>
     <Select label="Body type" value={draft.body} items={opts.data?.bodies??[]} onChange={v=>select('body',v)}/>
     <Select label="Fuel type" value={draft.fuel} items={opts.data?.fuel_types??[]} onChange={v=>select('fuel',v)}/>
     <Select label="Transmission" value={draft.transmission} items={opts.data?.transmissions??[]} onChange={v=>select('transmission',v)}/>
     <Select label="Condition" value={draft.condition} items={opts.data?.conditions??[]} onChange={v=>select('condition',v)}/>
     <button className="button button--primary catalogue-filter-submit" type="submit"><Filter size={17}/> Apply filters <ArrowRight size={16}/></button>
     <button className="catalogue-clear" type="button" onClick={clear}>Clear all filters</button>
    </form>
   </aside>
   <section className="catalogue-results" aria-label="Search results">
    <div className="catalogue-results__header"><div><span className="eyebrow eyebrow--red">DISCOVER YOUR NEXT VEHICLE</span><h2>Cars for Sale <span>({data?.meta.total??'—'})</span></h2></div>
     <div className="catalogue-actions"><button className="catalogue-mobile-filter" type="button" onClick={()=>setMobileOpen(true)} aria-expanded={mobileOpen}><SlidersHorizontal size={17}/> Filters</button>
      <label htmlFor="sort">Sort by<select id="sort" value={filters.sort} onChange={e=>patch('sort',e.target.value as SortBy)}><option value="recent">Latest / featured</option><option value="price-asc">Price: low to high</option><option value="price-desc">Price: high to low</option><option value="year-desc">Newest model year</option><option value="mileage-asc">Lowest mileage</option></select></label></div></div>
    <div className="catalogue-pills"><button type="button" className={!filters.body&&!filters.maxPrice?'is-active':''} onClick={clear}>All cars</button><button type="button" className={filters.body==='SUV'?'is-active':''} onClick={()=>patch('body','SUV')}>SUVs</button><button type="button" className={filters.body==='Hatchback'?'is-active':''} onClick={()=>patch('body','Hatchback')}>Hatchbacks</button><button type="button" className={filters.maxPrice==='2000000'?'is-active':''} onClick={()=>patch('maxPrice','2000000')}>Under KES 2M</button></div>
    {!!chips.length&&<div className="catalogue-chips">{chips.map(k=><button type="button" key={k} onClick={()=>patch(k,'')}>{labels[k]}: {filters[k]} <X size={13}/></button>)}</div>}
    {loading?<div className="catalogue-no-results" role="status"><h3>Loading Carfolk vehicles…</h3></div>:
     !ready||error?<div className="catalogue-no-results"><Info size={40}/><h3>Live catalogue unavailable</h3><p>{error??'Please try again shortly.'}</p></div>:
     result.length?<div className="catalogue-listing-grid">{result.map(car=><ListingCard key={car.id} car={car} onLimit={()=>setNotice('Choose up to three vehicles to compare.')}/>)}</div>:
     <div className="catalogue-no-results"><CarFront size={40}/><h3>No matching vehicles</h3><button className="button button--primary" type="button" onClick={clear}>Clear filters</button></div>}
    {!!data?.meta&&data.meta.last_page>1&&<nav className="catalogue-more" aria-label="Vehicle result pages"><p>Page {data.meta.current_page} of {data.meta.last_page} · {data.meta.total} vehicles</p><div className="catalogue-page-controls">{page>1&&<a className="button button--outline" href={href(filters,page-1)}>← Previous page</a>}{page<data.meta.last_page&&<a className="button button--primary" href={href(filters,page+1)}>Next page →</a>}</div></nav>}
   </section>
  </div>
  {notice&&<div className="toast" role="status"><Info size={18}/><span>{notice}</span><button onClick={()=>setNotice('')} aria-label="Dismiss"><X size={16}/></button></div>}
 </Shell>;
}
