import {useEffect,useState} from 'react';
import {ArrowRight,ArrowUpRight,CalendarDays,Check,ChevronLeft,ChevronRight,Fuel,GitCompareArrows,Gauge,Info,MapPin,Settings2,Share2,Tag,X} from 'lucide-react';
import {Shell} from '../components/Shell';
import {PreviewPhoto} from '../components/PreviewPhoto';
import {useVehicle} from '../api/hooks';
import {kes,vehicleName} from '../api/client';
import {useCompare} from '../state/CompareContext';
import {toVehicleEnquiry,toVehicleViewing} from '../lib/route';
export function VehicleDetailPage({slug}:{slug:string}){
 const {data:vehicle,loading,error,ready}=useVehicle(slug);
 const [index,setIndex]=useState(0),[expanded,setExpanded]=useState(false),[notice,setNotice]=useState('');
 const {selected,toggleCompare}=useCompare();
 const images=vehicle?.images??[];
 const photoCount=Math.max(1,images.length);
 useEffect(()=>{setIndex(0);setExpanded(false)},[slug]);
 useEffect(()=>{
  const handle=(e:KeyboardEvent)=>{if(e.key==='Escape')setExpanded(false);
   if(expanded&&e.key==='ArrowRight')setIndex(i=>(i+1)%photoCount);
   if(expanded&&e.key==='ArrowLeft')setIndex(i=>(i+photoCount-1)%photoCount);
  };
  window.addEventListener('keydown',handle);return()=>window.removeEventListener('keydown',handle);
 },[expanded,photoCount]);
 if(loading)return <Shell><section className="vehicle-not-found container" role="status"><h1>Loading vehicle details…</h1></section></Shell>;
 if(!vehicle)return <Shell><section className="vehicle-not-found container"><h1>{!ready?'Live vehicle details not connected':error?'Vehicle details unavailable':'Vehicle not found'}</h1><p>{error??'Only published dealership vehicles are available here.'}</p><a className="button button--primary" href="#/cars">Return to listings <ArrowRight size={16}/></a></section></Shell>;
 const active=selected.includes(vehicle.id);
 const photo=images[index]??null;
 const next=(delta:number)=>setIndex(i=>(i+delta+photoCount)%photoCount);
 const specs=[
  ['Model year',String(vehicle.year)],['Mileage',vehicle.mileage_km===null?'Ask the dealership':new Intl.NumberFormat('en-KE').format(vehicle.mileage_km)+' km'],
  ['Engine',vehicle.engine_cc?vehicle.engine_cc+' cc':'Ask the dealership'],
  ['Transmission',vehicle.transmission??'Ask the dealership'],['Fuel',vehicle.fuel_type??'Ask the dealership'],
  ['Drivetrain',vehicle.drivetrain??'Ask the dealership'],['Body type',vehicle.body_type??'Ask the dealership'],
  ['Condition',vehicle.condition??'Ask the dealership'],['Colour',vehicle.colour??'Ask the dealership'],['Location',vehicle.location??'Contact Carfolk']
 ];
 async function share(){try{if(navigator.share)await navigator.share({title:vehicleName(vehicle!),url:location.href});
  else if(navigator.clipboard){await navigator.clipboard.writeText(location.href);setNotice('Vehicle link copied.')}
 }catch{/* share cancelled */}}
 return <Shell><div className="container vehicle-detail">
  <nav className="vehicle-detail__crumb" aria-label="Breadcrumb"><a href="#/">Home</a><ChevronRight size={14}/><a href="#/cars">Cars for Sale</a><ChevronRight size={14}/><span>{vehicleName(vehicle)}</span></nav>
  <div className="vehicle-detail__heading"><div><span className="eyebrow eyebrow--red">CARFOLK VEHICLE DETAILS</span><h1>{vehicle.make} {vehicle.model}</h1><p>{vehicle.trim??'Vehicle'} · {vehicle.year} · {vehicle.body_type??'Body unlisted'}</p></div><button className="detail-share" type="button" onClick={share}><Share2 size={18}/> Share vehicle</button></div>
  <div className="vehicle-detail__main"><div>
   <div className="vehicle-gallery">
    <div className="vehicle-gallery__stage"><PreviewPhoto src={photo?.url??''} loading="eager" alt={photo?.alt_text??vehicleName(vehicle)+' vehicle photo'}/>
     <span className="vehicle-gallery__label">{photo?index+1+' / '+photoCount:'PHOTO PENDING'}</span>
     {images.length>0&&<><button className="vehicle-gallery__zoom" type="button" onClick={()=>setExpanded(true)}>View larger <ArrowUpRight size={16}/></button><button className="vehicle-gallery__arrow is-left" onClick={()=>next(-1)} aria-label="Previous image"><ChevronLeft/></button><button className="vehicle-gallery__arrow is-right" onClick={()=>next(1)} aria-label="Next image"><ChevronRight/></button></>}
    </div>
    {images.length>1&&<div className="vehicle-gallery__thumbs">{images.map((pic,i)=><button type="button" key={pic.id} className={i===index?'is-selected':''} aria-label={'Show photo '+(i+1)} aria-pressed={i===index} onClick={()=>setIndex(i)}><PreviewPhoto src={pic.url} alt={pic.alt_text??'Vehicle photo '+(i+1)}/></button>)}</div>}
   </div>
   <div className="detail-panels"><section className="detail-panel"><span className="eyebrow eyebrow--red">OVERVIEW</span><h2>About this vehicle</h2><p>{vehicle.description||'Contact Carfolk for a full vehicle description, inspection details and specifications.'}</p></section>
    <section className="detail-panel"><span className="eyebrow eyebrow--red">SPECIFICATIONS</span><h2>Vehicle details</h2><div className="detail-spec-grid">{specs.map(([label,value])=><div key={label}><span>{label}</span><strong>{value}</strong></div>)}</div></section>
    {vehicle.features.length>0&&<section className="detail-panel"><span className="eyebrow eyebrow--red">FEATURES</span><h2>Listed highlights</h2><div className="detail-features">{vehicle.features.map(f=><span key={f}><Check size={18}/>{f}</span>)}</div></section>}
   </div>
  </div>
  <aside className="detail-purchase">
   <div className="detail-purchase__price"><span>Listed price</span><strong>{kes(vehicle.price_kes)}</strong><small>Confirm price and availability with Carfolk before purchase.</small></div>
   <div className="detail-purchase__quick"><span><Gauge size={17}/>{vehicle.mileage_km===null?'Mileage unlisted':vehicle.mileage_km.toLocaleString('en-KE')+' km'}</span><span><Fuel size={17}/>{vehicle.fuel_type??'Fuel unlisted'}</span><span><Settings2 size={17}/>{vehicle.transmission??'Transmission unlisted'}</span><span><MapPin size={17}/>{vehicle.location??'Location unlisted'}</span></div>
   <div className="detail-purchase__note"><Info size={19}/><span>{vehicle.status==='available'?'Vehicle listed as available. Confirm before travelling.':vehicle.status==='reserved'?'Vehicle is currently reserved. Ask about status.':'Vehicle has been marked sold.'}</span></div>
   {vehicle.status==='available'&&<a className="button button--primary" href={toVehicleViewing(vehicle.slug)}><CalendarDays size={18}/> Request a viewing <ArrowUpRight size={18}/></a>}
   <a className="button button--outline" href={toVehicleEnquiry(vehicle.slug)}>Enquire about this car <ArrowUpRight size={18}/></a>
   <a className="button button--outline" href={'#/finance?vehicle='+encodeURIComponent(vehicle.slug)}><Tag size={18}/> Estimate payments</a>
   <button type="button" className={active?'detail-compare is-active':'detail-compare'} onClick={()=>{if(!toggleCompare(vehicle.id))setNotice('You may compare up to three vehicles.')}}><GitCompareArrows size={18}/>{active?'Remove from comparison':'Add to comparison'}</button>
   <small className="detail-purchase__disclaimer"><Info size={14}/> Viewing requests are not confirmed appointments until Carfolk responds.</small>
  </aside></div>
  </div>
  {expanded&&<div className="gallery-lightbox" role="dialog" aria-modal="true" aria-label="Vehicle photographs" onMouseDown={e=>{if(e.target===e.currentTarget)setExpanded(false)}}><button className="gallery-lightbox__close" onClick={()=>setExpanded(false)} aria-label="Close gallery"><X size={26}/></button><div className="gallery-lightbox__image"><PreviewPhoto src={photo?.url??''} alt={photo?.alt_text??'Vehicle photograph'}/></div><div className="gallery-lightbox__controls"><button onClick={()=>next(-1)} aria-label="Previous photo"><ChevronLeft/></button><span>Image {index+1} of {photoCount}</span><button onClick={()=>next(1)} aria-label="Next photo"><ChevronRight/></button></div></div>}
  {notice&&<div className="toast" role="status"><Info size={18}/>{notice}<button onClick={()=>setNotice('')} aria-label="Close"><X size={15}/></button></div>}
 </Shell>;
}
