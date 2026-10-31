import {ArrowUpRight,Check,GitCompareArrows,MapPin} from 'lucide-react';
import {PreviewPhoto} from './PreviewPhoto';
import type {Vehicle} from '../api/client';
import {kes,vehicleName} from '../api/client';
import {toCar,toVehicleEnquiry} from '../lib/route';
import {useCompare} from '../state/CompareContext';
export function ListingCard({car,onLimit}:{car:Vehicle;onLimit:()=>void}){
 const {selected,toggleCompare}=useCompare();
 const active=selected.includes(car.id);
 return <article className="listing-card">
  <div className="listing-card__photo">
   <a href={toCar(car.slug)} aria-label={'View '+vehicleName(car)}><PreviewPhoto src={car.images[0]?.url??''} alt={car.images[0]?.alt_text||'Photo of '+vehicleName(car)}/></a>
   <span className="listing-card__demo">{car.status==='available'?'AVAILABLE':car.status.toUpperCase()}</span>
   {car.condition&&<span className="listing-card__condition">{car.condition}</span>}
  </div>
  <div className="listing-card__body">
   <div className="listing-card__brand">{car.make} <span>{car.year}</span></div>
   <h3><a href={toCar(car.slug)}>{car.make} {car.model}</a></h3>
   <p className="listing-card__variant">{car.trim??'Vehicle details'} · {car.body_type??'Body details pending'}</p>
   <div className="listing-card__specs">
    {car.mileage_km!==null&&<span>{new Intl.NumberFormat('en-KE').format(car.mileage_km)} km</span>}
    {car.fuel_type&&<span>{car.fuel_type}</span>}{car.transmission&&<span>{car.transmission}</span>}
   </div>
   {car.location&&<div className="listing-card__location"><MapPin size={13}/> {car.location}</div>}
   <div className="listing-card__price"><small>Listed price</small><strong>{kes(car.price_kes)}</strong></div>
   <div className="listing-card__actions">
    <button type="button" onClick={()=>{if(!toggleCompare(car.id))onLimit()}} className={active?'listing-card__compare is-active':'listing-card__compare'} aria-pressed={active}>{active?<Check size={16}/>:<GitCompareArrows size={16}/>} {active?'Added':'Compare'}</button>
    <a href={toCar(car.slug)} className="listing-card__view">View details <ArrowUpRight size={16}/></a>
   </div><a className="listing-card__enquiry" href={toVehicleEnquiry(car.slug)}>Enquire about this car <ArrowUpRight size={15}/></a>
  </div>
 </article>;
}
