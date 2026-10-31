import {useEffect,useMemo,useState,type FormEvent} from 'react';
import {ArrowRight,CarFront,Check,ChevronDown,Filter,Info,Search,SlidersHorizontal,X} from 'lucide-react';
import {Shell} from '../components/Shell';
import {ListingCard} from '../components/ListingCard';
import {DEMO_CARS,DEMO_NOTICE} from '../data/showroom';
import {EMPTY_FILTERS,filterAndSort,filtersFromQuery,filtersToQuery,options,type Filters,type SortBy} from '../lib/catalogue';
const labels:Partial<Record<keyof Filters,string>>={make:'Make',model:'Model',body:'Body',fuel:'Fuel',transmission:'Transmission',condition:'Condition',minPrice:'Min price',maxPrice:'Max price',minYear:'From year',maxYear:'To year',q:'Search'};
function url(f:Filters){const query=filtersToQuery(f);return '#/cars'+(query?'?'+query:'')}
const variants=DEMO_CARS;
function Select({label,value,options:items,onChange}:{label:string;value:string;options:string[];onChange:(v:string)=>void}){return <label className="catalogue-filter-label">{label}<span className="catalogue-select"><select value={value} onChange={e=>onChange(e.target.value)}><option value="">All {label.toLowerCase()}</option>{items.map(x=><option key={x} value={x}>{x}</option>)}</select><ChevronDown size={16}/></span></label>}
export function CataloguePage({query}:{query:string}){
 const filters=useMemo(()=>filtersFromQuery(query),[query]);
 const [draft,setDraft]=useState<Filters>(filters);
 const [visible,setVisible]=useState(6);
 const [mobileOpen,setMobileOpen]=useState(false);
 const [notice,setNotice]=useState('');
 useEffect(()=>{setDraft(filters);setVisible(6)},[filters]);
 const result=useMemo(()=>filterAndSort(variants,filters),[filters]);
 const makes=options(variants.map(v=>v.make));
 const models=options(variants.filter(v=>!draft.make||v.make===draft.make).map(v=>v.model));
 const vals=(key:keyof Filters)=>options(variants.map(v=>String(v[key as keyof typeof variants[number]])));
 const chips=(Object.keys(filters) as (keyof Filters)[]).filter(k=>k!=='sort'&&filters[k]!=='');
 function change(key:keyof Filters,v:string){setDraft(x=>({...x,[key]:v,...(key==='make'?{model:''}:{})}));}
 function apply(e:FormEvent<HTMLFormElement>){e.preventDefault();window.location.hash=url(draft);setMobileOpen(false);}
 function patch(key:keyof Filters,v:string){window.location.hash=url({...filters,[key]:v});}
 function clear(){window.location.hash='#/cars';setDraft(EMPTY_FILTERS);}
 function limit(){setNotice('Comparison is limited to three vehicles. Remove one from your shortlist before adding another.');}
 useEffect(()=>{if(!notice)return;const timer=setTimeout(()=>setNotice(''),5000);return()=>clearTimeout(timer)},[notice]);
 return <Shell>
  <section className="catalogue-intro"><div className="container catalogue-intro__inner"><div className="catalogue-breadcrumb"><a href="#/">Home</a><span>/</span> Cars for Sale</div><div className="eyebrow">THE CARFOLK SHOWROOM</div><h1>Find a car that<br/><em>fits your life.</em></h1><p>Explore options, compare important details and discover your ideal drive.</p></div></section>
  <div className="container catalogue-banner"><Info size={18}/><div><strong>Interactive catalogue demonstration</strong><span>{DEMO_NOTICE} Price, year, fuel and model filters work on example data.</span></div></div>
  <div className="container catalogue-shell">
   <aside className={'catalogue-sidebar'+(mobileOpen?' catalogue-sidebar--open':'')} aria-label="Vehicle search filters">
    <div className="catalogue-sidebar__top"><div><SlidersHorizontal size={21}/><strong>Refine your search</strong></div><button type="button" className="catalogue-sidebar__close" aria-label="Close filters" onClick={()=>setMobileOpen(false)}><X size={20}/></button></div>
    <form onSubmit={apply}>
     <label className="catalogue-filter-label" htmlFor="car-query">Search vehicles</label>
     <div className="catalogue-search-field"><Search size={16}/><input id="car-query" value={draft.q} onChange={e=>change('q',e.target.value)} placeholder="Make, model, keyword…"/></div>
     <Select label="Make" value={draft.make} options={makes} onChange={v=>change('make',v)}/>
     <Select label="Model" value={draft.model} options={models} onChange={v=>change('model',v)}/>
     <div className="catalogue-filter-divider"/>
     <div className="catalogue-filter-title">Sample price range (KES)</div>
     <div className="catalogue-two">
      <label>Minimum<input type="number" min="0" step="50000" value={draft.minPrice} onChange={e=>change('minPrice',e.target.value)} placeholder="e.g. 1000000"/></label>
      <label>Maximum<input type="number" min="0" step="50000" value={draft.maxPrice} onChange={e=>change('maxPrice',e.target.value)} placeholder="e.g. 5000000"/></label>
     </div>
     <div className="catalogue-filter-divider"/>
     <div className="catalogue-filter-title">Model year</div>
     <div className="catalogue-two">
      <label>From<input type="number" min="1990" max="2035" value={draft.minYear} onChange={e=>change('minYear',e.target.value)} placeholder="Any"/></label>
      <label>To<input type="number" min="1990" max="2035" value={draft.maxYear} onChange={e=>change('maxYear',e.target.value)} placeholder="Any"/></label>
     </div>
     <div className="catalogue-filter-divider"/>
     <Select label="Body type" value={draft.body} options={vals('body')} onChange={v=>change('body',v)}/>
     <Select label="Fuel type" value={draft.fuel} options={vals('fuel')} onChange={v=>change('fuel',v)}/>
     <Select label="Transmission" value={draft.transmission} options={vals('transmission')} onChange={v=>change('transmission',v)}/>
     <Select label="Condition" value={draft.condition} options={vals('condition')} onChange={v=>change('condition',v)}/>
     <button className="button button--primary catalogue-filter-submit" type="submit"><Filter size={17}/> Apply filters <ArrowRight size={16}/></button>
     <button className="catalogue-clear" type="button" onClick={clear}>Clear all filters</button>
    </form>
   </aside>
   <section className="catalogue-results" aria-label="Vehicle results">
    <div className="catalogue-results__header"><div><span className="eyebrow eyebrow--red">EXPLORE THE COLLECTION</span><h2>Vehicle listings <span>({result.length})</span></h2><p>Examples for layout and feature testing only.</p></div><div className="catalogue-actions"><button className="catalogue-mobile-filter" type="button" onClick={()=>setMobileOpen(true)} aria-expanded={mobileOpen}><SlidersHorizontal size={17}/> Filters</button><label htmlFor="sort">Sort by<select id="sort" value={filters.sort} onChange={e=>patch('sort',e.target.value as SortBy)}><option value="recent">Featured order</option><option value="price-asc">Price: low to high</option><option value="price-desc">Price: high to low</option><option value="year-desc">Newest model year</option><option value="mileage-asc">Lowest mileage</option></select></label></div></div>
    <div className="catalogue-pills"><button type="button" className={!filters.body&&!filters.maxPrice?'is-active':''} onClick={clear}>All examples</button><button type="button" className={filters.body==='SUV'?'is-active':''} onClick={()=>patch('body','SUV')}>SUVs</button><button type="button" className={filters.body==='Hatchback'?'is-active':''} onClick={()=>patch('body','Hatchback')}>Hatchbacks</button><button type="button" className={filters.maxPrice==='2000000'?'is-active':''} onClick={()=>patch('maxPrice','2000000')}>Below KES 2M</button></div>
    {chips.length>0&&<div className="catalogue-chips">{chips.map(k=><button key={k} type="button" onClick={()=>patch(k,'')}><span>{labels[k]}: {k==='minPrice'||k==='maxPrice'?new Intl.NumberFormat('en-KE').format(Number(filters[k])):filters[k]}</span> <X size={13}/></button>)}</div>}
    <div className="catalogue-listing-grid">{result.slice(0,visible).map(car=><ListingCard key={car.id} car={car} onLimit={limit}/>)}</div>
    {!result.length&&<div className="catalogue-no-results"><CarFront size={40}/><h3>No matching examples</h3><p>Try widening the sample price or year range, or choose a different make.</p><button type="button" className="button button--primary" onClick={clear}>Reset all filters</button></div>}
    {visible<result.length&&<div className="catalogue-more"><p>Showing {Math.min(visible,result.length)} of {result.length} examples</p><button type="button" className="button button--outline" onClick={()=>setVisible(x=>x+6)}>Load more examples <ArrowRight size={16}/></button></div>}
    <div className="catalogue-guidance"><Check size={18}/><p>Explore specifications and use the comparison tools. Actual prices, photos and availability will only appear after Carfolk uploads verified inventory.</p></div>
   </section>
  </div>
  {notice&&<div className="toast" role="status"><Info size={18}/><span>{notice}</span><button aria-label="Dismiss" onClick={()=>setNotice('')}><X size={16}/></button></div>}
 </Shell>;
}
