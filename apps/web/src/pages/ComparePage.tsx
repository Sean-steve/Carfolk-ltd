import {ArrowRight,GitCompareArrows,X} from 'lucide-react';
import {Shell} from '../components/Shell';
import {PreviewPhoto} from '../components/PreviewPhoto';
import {kes,vehicleName,type Vehicle} from '../api/client';
import {toCar} from '../lib/route';
import {useCompare} from '../state/CompareContext';
type Row={label:string;value:(car:Vehicle)=>string};
const formatKm=(n:number|null)=>n===null?'—':n.toLocaleString('en-KE')+' km';
const rows:Row[]=[
 {label:'Make',value:v=>v.make},{label:'Model',value:v=>v.model},{label:'Trim',value:v=>v.trim??'—'},
 {label:'Model year',value:v=>String(v.year)},{label:'Listed price',value:v=>kes(v.price_kes)},
 {label:'Mileage',value:v=>formatKm(v.mileage_km)},{label:'Engine',value:v=>v.engine_cc?v.engine_cc+' cc':'—'},
 {label:'Fuel type',value:v=>v.fuel_type??'—'},{label:'Transmission',value:v=>v.transmission??'—'},
 {label:'Drivetrain',value:v=>v.drivetrain??'—'},{label:'Body type',value:v=>v.body_type??'—'},
 {label:'Condition',value:v=>v.condition??'—'},{label:'Availability',value:v=>v.status.toUpperCase()},
 {label:'Colour',value:v=>v.colour??'—'},{label:'Location',value:v=>v.location??'—'},
];
export function ComparePage(){
 const {selected,vehicles:cars,loading,remove,clear}=useCompare();
 return <Shell><section className="catalogue-intro catalogue-intro--compact"><div className="container catalogue-intro__inner"><div className="catalogue-breadcrumb"><a href="#/">Home</a><span>/</span> Compare Cars</div><span className="eyebrow">MAKE THE RIGHT CHOICE</span><h1>Compare with <em>confidence.</em></h1><p>Choose up to three published vehicles and see their details side by side.</p></div></section>
 <div className="container compare-page"><div className="compare-page__heading"><div><h2>Your shortlist <span>({selected.length} / 3)</span></h2></div>{selected.length>0&&<button type="button" className="catalogue-clear" onClick={clear}>Clear shortlist</button>}</div>
 {loading?<div className="compare-empty" role="status">Loading comparison…</div>:
 cars.length===0?<div className="compare-empty"><GitCompareArrows size={42}/><h3>Nothing to compare yet</h3><a className="button button--primary" href="#/cars">Browse cars <ArrowRight size={17}/></a></div>:
 <div className="comparison-sheet"><div className="comparison-sheet__table" style={{gridTemplateColumns:`minmax(145px,.7fr) repeat(${cars.length},minmax(225px,1fr))`}}>
  <div className="comparison-sheet__label comparison-sheet__header">Vehicle</div>
  {cars.map(car=><div key={car.id} className="comparison-sheet__car"><button type="button" aria-label={'Remove '+vehicleName(car)} onClick={()=>remove(car.id)}><X size={16}/></button><a href={toCar(car.slug)}><PreviewPhoto src={car.images[0]?.url??''} alt={car.images[0]?.alt_text??vehicleName(car)}/></a><strong>{vehicleName(car)}</strong><small>{car.year} · {car.status}</small><a className="comparison-sheet__link" href={toCar(car.slug)}>View details <ArrowRight size={14}/></a></div>)}
  {rows.map((row,i)=><div style={{display:'contents'}} key={row.label}><div className={'comparison-sheet__label'+(i%2===0?' is-alt':'')}>{row.label}</div>{cars.map(car=><div key={car.id} className={'comparison-sheet__cell'+(i%2===0?' is-alt':'')}>{row.value(car)}</div>)}</div>)}
 </div></div>}
 <a className="button button--outline" href="#/cars">Add more vehicles <ArrowRight size={17}/></a>
 </div></Shell>;
}
