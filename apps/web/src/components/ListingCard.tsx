import {ArrowUpRight,Check,GitCompareArrows,MapPin} from 'lucide-react';
import {PreviewPhoto} from './PreviewPhoto';
import type {DemoVehicle} from '../data/showroom';
import {kes} from '../data/showroom';
import {toCar,toVehicleEnquiry} from '../lib/route';
import {useCompare} from '../state/CompareContext';
export function ListingCard({car,onLimit}:{car:DemoVehicle;onLimit:()=>void}){
 const {selected,toggleCompare}=useCompare();
 const active=selected.includes(car.id);
 return <article className="listing-card">
  <div className="listing-card__photo">
   <a href={toCar(car.slug)} aria-label={'View '+car.make+' '+car.model+' example details'}><PreviewPhoto src={car.photos[0]} alt={'Illustrative vehicle photo for '+car.make+' '+car.model} /></a>
   <span className="listing-card__demo">DEMO LISTING</span>
   <span className="listing-card__condition">{car.condition} example</span>
  </div>
  <div className="listing-card__body">
   <div className="listing-card__brand">{car.make} <span>· {car.year} example</span></div>
   <h3><a href={toCar(car.slug)}>{car.make} {car.model}</a></h3>
   <p className="listing-card__variant">{car.variant} · {car.body}</p>
   <div className="listing-card__specs"><span>{new Intl.NumberFormat('en-KE').format(car.mileageKm)} km</span><span>{car.fuel}</span><span>{car.transmission}</span></div>
   <div className="listing-card__location"><MapPin size={13}/> {car.location} (sample)</div>
   <div className="listing-card__price"><small>Illustrative price</small><strong>{kes(car.priceKes)}</strong></div>
   <div className="listing-card__actions">
    <button type="button" onClick={()=>{if(!toggleCompare(car.id))onLimit()}} className={active?'listing-card__compare is-active':'listing-card__compare'} aria-pressed={active}>{active?<Check size={16}/>:<GitCompareArrows size={16}/>} {active?'Added':'Compare'}</button>
    <a href={toCar(car.slug)} className="listing-card__view">View example <ArrowUpRight size={16}/></a>
   </div><a className="listing-card__enquiry" href={toVehicleEnquiry(car.slug)}>Enquire about this example <ArrowUpRight size={15}/></a>
  </div>
 </article>;
}
