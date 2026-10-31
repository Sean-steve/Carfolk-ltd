import {ArrowRight,Check,GitCompareArrows,Info,Plus,X} from 'lucide-react';
import {Shell} from '../components/Shell';
import {PreviewPhoto} from '../components/PreviewPhoto';
import {DEMO_CARS,kes} from '../data/showroom';
import {toCar} from '../lib/route';
import {useCompare} from '../state/CompareContext';
type Row={label:string;value:(car:typeof DEMO_CARS[number])=>string};
const rows:Row[]=[
 {label:'Make',value:v=>v.make},{label:'Model',value:v=>v.model},{label:'Trim',value:v=>v.variant},
 {label:'Model year',value:v=>String(v.year)},{label:'Demo price',value:v=>kes(v.priceKes)},
 {label:'Mileage',value:v=>new Intl.NumberFormat('en-KE').format(v.mileageKm)+' km'},
 {label:'Engine',value:v=>v.engineCc+' cc'},{label:'Fuel type',value:v=>v.fuel},
 {label:'Transmission',value:v=>v.transmission},{label:'Drivetrain',value:v=>v.drive},
 {label:'Body type',value:v=>v.body},{label:'Condition',value:v=>v.condition},
 {label:'Colour',value:v=>v.colour},{label:'Location',value:v=>v.location+' (sample)'}
];
export function ComparePage(){
 const {selected,remove,clear,toggleCompare}=useCompare();
 const chosen=DEMO_CARS.filter(v=>selected.includes(v.id));
 const available=DEMO_CARS.filter(v=>!selected.includes(v.id));
 return <Shell>
 <section className="catalogue-intro catalogue-intro--compact"><div className="container catalogue-intro__inner"><div className="catalogue-breadcrumb"><a href="#/">Home</a><span>/</span> Compare Cars</div><span className="eyebrow">FIND YOUR PERFECT MATCH</span><h1>Compare with <em>confidence.</em></h1><p>Line up the details that matter, side by side.</p></div></section>
 <div className="container compare-page">
 <div className="detail-preview-banner"><Info size={20}/><span><strong>Example comparisons:</strong> All vehicles, specifications and prices below are demonstration data, not real stock or guarantees.</span></div>
 <div className="compare-page__heading"><div><h2>Your shortlist <span>({chosen.length} / 3)</span></h2><p>Choose between two and three examples to compare.</p></div>{selected.length>0&&<button type="button" onClick={clear} className="catalogue-clear">Clear shortlist</button>}</div>
 {chosen.length===0&&<div className="compare-empty"><GitCompareArrows size={42}/><h3>Nothing on your shortlist yet</h3><p>Browse the vehicle catalogue and select Compare on the examples you want to evaluate.</p><a className="button button--primary" href="#/cars">Browse vehicles <ArrowRight size={16}/></a></div>}
 {chosen.length>0&&<div className="comparison-sheet" aria-label="Side by side vehicle comparison"><div className="comparison-sheet__table" style={{gridTemplateColumns:`minmax(140px,.8fr) repeat(${chosen.length}, minmax(220px,1fr))`}}>
  <div className="comparison-sheet__label comparison-sheet__header"><span>Vehicle</span></div>
  {chosen.map(car=><div className="comparison-sheet__car" key={car.id}><button type="button" aria-label={'Remove '+car.make+' '+car.model} onClick={()=>remove(car.id)}><X size={16}/></button><a href={toCar(car.slug)}><PreviewPhoto src={car.photos[0]} alt={'Illustrative '+car.make+' '+car.model}/></a><span>DESIGN EXAMPLE</span><strong>{car.make} {car.model}</strong><small>{car.year} · {car.variant}</small><a href={toCar(car.slug)} className="comparison-sheet__link">View details <ArrowRight size={14}/></a></div>)}
  {rows.map((row,i)=><div className="comparison-sheet__row-fragment" key={row.label} style={{display:'contents'}}>
    <div className={'comparison-sheet__label'+(i%2===0?' is-alt':'')}>{row.label}</div>
    {chosen.map(car=><div className={'comparison-sheet__cell'+(i%2===0?' is-alt':'')} key={car.id}>{row.value(car)}</div>)}
  </div>)}
 </div></div>}
 {chosen.length<3&&<div className="compare-add"><h3>{chosen.length===0?'Suggested examples':'Add another example'}</h3><p>Choose from the sample catalogue to see how the comparison changes.</p><div className="compare-add__list">{available.slice(0,4).map(car=><button type="button" key={car.id} onClick={()=>toggleCompare(car.id)}><Plus size={17}/><span>{car.make} {car.model}</span><small>{car.year} · sample</small></button>)}</div></div>}
 <div className="compare-page__foot"><Check size={18}/><p>When Carfolk's inventory goes live, this feature will compare actual verified vehicle details and pricing.</p></div>
 </div>
 </Shell>;
}
