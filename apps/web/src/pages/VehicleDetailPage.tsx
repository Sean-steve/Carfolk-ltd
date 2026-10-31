import {useEffect,useState} from 'react';
import {ArrowRight,ArrowUpRight,CalendarDays,Check,ChevronLeft,ChevronRight,Fuel,GitCompareArrows,Gauge,Info,MapPin,Settings2,ShieldCheck,Share2,Tag,X} from 'lucide-react';
import {Shell} from '../components/Shell';
import {PreviewPhoto} from '../components/PreviewPhoto';
import {ListingCard} from '../components/ListingCard';
import {DEMO_CARS,getDemoCar,kes} from '../data/showroom';
import {useCompare} from '../state/CompareContext';
import {toVehicleEnquiry,toVehicleViewing} from '../lib/route';
export function VehicleDetailPage({slug}:{slug:string}){
 const vehicle=getDemoCar(slug);
 const [index,setIndex]=useState(0);
 const [expanded,setExpanded]=useState(false);
 const [notice,setNotice]=useState('');
 const {selected,toggleCompare}=useCompare();
 useEffect(()=>{setIndex(0);setExpanded(false)},[slug]);
 useEffect(()=>{const onKey=(e:KeyboardEvent)=>{if(e.key==='Escape')setExpanded(false);if(expanded&&e.key==='ArrowRight')setIndex(i=>vehicle?(i+1)%vehicle.photos.length:0);if(expanded&&e.key==='ArrowLeft')setIndex(i=>vehicle?(i+vehicle.photos.length-1)%vehicle.photos.length:0)};window.addEventListener('keydown',onKey);return()=>window.removeEventListener('keydown',onKey)},[expanded,vehicle]);
 if(!vehicle)return <Shell><section className="vehicle-not-found container"><h1>Vehicle example not found</h1><p>That sample vehicle is not part of the current catalogue.</p><a className="button button--primary" href="#/cars">Return to vehicle listings <ArrowRight size={16}/></a></section></Shell>;
 const active=selected.includes(vehicle.id);
 const next=(delta:number)=>setIndex(v=>(v+delta+vehicle.photos.length)%vehicle.photos.length);
 const specs=[
  {label:'Model year',value:vehicle.year+' (sample)'},{label:'Mileage',value:new Intl.NumberFormat('en-KE').format(vehicle.mileageKm)+' km'},
  {label:'Engine',value:new Intl.NumberFormat('en-KE').format(vehicle.engineCc)+' cc'},{label:'Transmission',value:vehicle.transmission},
  {label:'Fuel',value:vehicle.fuel},{label:'Drivetrain',value:vehicle.drive},
  {label:'Body type',value:vehicle.body},{label:'Condition',value:vehicle.condition},
  {label:'Exterior colour',value:vehicle.colour},{label:'Location',value:vehicle.location+' (sample)'}
 ];
 const related=DEMO_CARS.filter(x=>x.id!==vehicle.id&&(x.make===vehicle.make||x.body===vehicle.body)).slice(0,3);
 async function share(){const url=window.location.href;try{if(navigator.share)await navigator.share({title:'Carfolk design example',text:'View an illustrative vehicle listing',url});else if(navigator.clipboard){await navigator.clipboard.writeText(url);setNotice('Demo listing link copied.');}else setNotice('You can copy this page address to share the example.')}catch{/* User cancelled sharing */}}
 function addCompare(){if(!vehicle)return;if(!toggleCompare(vehicle.id))setNotice('Choose up to three example vehicles. Remove one from comparison first.');}
 return <Shell>
  <div className="container vehicle-detail">
   <nav className="vehicle-detail__crumb" aria-label="Breadcrumb"><a href="#/">Home</a><ChevronRight size={14}/><a href="#/cars">Cars for Sale</a><ChevronRight size={14}/><span>{vehicle.make} {vehicle.model}</span></nav>
   <div className="detail-preview-banner"><Info size={20}/><span><strong>Demonstration vehicle</strong> — Imagery, specifications, mileage and price are invented for preview and filtering; not Carfolk LTD inventory.</span></div>
   <div className="vehicle-detail__heading"><div><span className="eyebrow eyebrow--red">CARFOLK VEHICLE PREVIEW</span><h1>{vehicle.make} {vehicle.model}</h1><p>{vehicle.variant} · {vehicle.year} · {vehicle.body} <span>· {vehicle.condition} example</span></p></div><button className="detail-share" onClick={share} type="button"><Share2 size={18}/> Share example</button></div>
   <div className="vehicle-detail__main">
    <div>
     <div className="vehicle-gallery">
      <div className="vehicle-gallery__stage"><PreviewPhoto src={vehicle.photos[index]} loading="eager" alt={'Illustrative vehicle gallery image '+(index+1)+' for '+vehicle.make+' '+vehicle.model}/><span className="vehicle-gallery__label">DEMO IMAGE · {index+1}/{vehicle.photos.length}</span><button type="button" className="vehicle-gallery__zoom" onClick={()=>setExpanded(true)}>View larger <ArrowUpRight size={16}/></button><button type="button" aria-label="Previous image" className="vehicle-gallery__arrow is-left" onClick={()=>next(-1)}><ChevronLeft size={21}/></button><button type="button" aria-label="Next image" className="vehicle-gallery__arrow is-right" onClick={()=>next(1)}><ChevronRight size={21}/></button></div>
      <div className="vehicle-gallery__thumbs">{vehicle.photos.map((src,i)=><button type="button" key={i} aria-label={'Show image '+(i+1)} aria-pressed={i===index} className={index===i?'is-selected':''} onClick={()=>setIndex(i)}><PreviewPhoto src={src} alt={'Gallery thumbnail '+(i+1)}/></button>)}</div>
     </div>
     <div className="detail-panels"><section className="detail-panel"><span className="eyebrow eyebrow--red">THE OVERVIEW</span><h2>About this example</h2><p>{vehicle.summary}</p><p>The Carfolk vehicle detail experience will use verified dealer-uploaded data and photographs when the inventory system goes live.</p></section>
      <section className="detail-panel" id="specifications"><span className="eyebrow eyebrow--red">AT A GLANCE</span><h2>Vehicle specifications</h2><div className="detail-spec-grid">{specs.map(s=><div key={s.label}><span>{s.label}</span><strong>{s.value}</strong></div>)}</div></section>
      <section className="detail-panel"><span className="eyebrow eyebrow--red">FEATURES</span><h2>Illustrative highlights</h2><div className="detail-features">{vehicle.highlights.map(v=><span key={v}><Check size={18}/>{v}</span>)}</div></section>
     </div>
    </div>
    <aside className="detail-purchase">
     <div className="detail-purchase__price"><span>Illustrative vehicle price</span><strong>{kes(vehicle.priceKes)}</strong><small>Not an offer or current Carfolk selling price.</small></div>
     <div className="detail-purchase__quick"><span><Gauge size={17}/>{new Intl.NumberFormat('en-KE').format(vehicle.mileageKm)} km</span><span><Fuel size={17}/>{vehicle.fuel}</span><span><Settings2 size={17}/>{vehicle.transmission}</span><span><MapPin size={17}/>{vehicle.location} (example)</span></div>
     <div className="detail-purchase__note"><ShieldCheck size={19}/><span>Availability and vehicle verification are pending real dealership inventory.</span></div>
     <a className="button button--primary" href={toVehicleViewing(vehicle.slug)}><CalendarDays size={18}/> Request a viewing <ArrowUpRight size={18}/></a>
     <a className="button button--outline" href={toVehicleEnquiry(vehicle.slug)}>Enquire about this car <ArrowUpRight size={18}/></a>
     <a className="button button--outline" href={`#/finance?price=${vehicle.priceKes}&vehicle=${encodeURIComponent(vehicle.slug)}`}><Tag size={18}/> Estimate monthly payments</a>
     <button type="button" className={active?'detail-compare is-active':'detail-compare'} onClick={addCompare}><GitCompareArrows size={18}/>{active?'Remove from comparison':'Add to comparison'}</button>
     {active&&<a href="#/compare" className="detail-secondary-link">Go to comparison <ArrowRight size={16}/></a>}
     <small className="detail-purchase__disclaimer"><Info size={14}/> Forms are available as a design preview only. They do not send or save customer details until the backend is connected.</small>
    </aside>
   </div>
   <section className="detail-related"><div className="section-heading"><div><span className="eyebrow eyebrow--red">MORE TO EXPLORE</span><h2>Keep discovering.</h2></div><a href="#/cars" className="detail-secondary-link">Browse all examples <ArrowRight size={16}/></a></div><div className="catalogue-listing-grid">{related.map(car=><ListingCard key={car.id} car={car} onLimit={()=>setNotice('You can select a maximum of three examples for comparison.')}/>)}</div></section>
  </div>
  {expanded&&<div className="gallery-lightbox" role="dialog" aria-modal="true" aria-label="Vehicle gallery preview" onMouseDown={e=>{if(e.target===e.currentTarget)setExpanded(false)}}><button type="button" className="gallery-lightbox__close" onClick={()=>setExpanded(false)} aria-label="Close gallery"><X size={26}/></button><div className="gallery-lightbox__image"><PreviewPhoto src={vehicle.photos[index]} alt={'Enlarged illustrative gallery image '+(index+1)}/></div><div className="gallery-lightbox__controls"><button type="button" aria-label="Previous photo" onClick={()=>next(-1)}><ChevronLeft/></button><span>Example image {index+1} of {vehicle.photos.length}</span><button type="button" aria-label="Next photo" onClick={()=>next(1)}><ChevronRight/></button></div></div>}
  {notice&&<div className="toast" role="status"><Info size={18}/><span>{notice}</span><button onClick={()=>setNotice('')} aria-label="Dismiss notice"><X size={17}/></button></div>}
 </Shell>;
}
