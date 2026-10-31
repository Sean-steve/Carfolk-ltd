import {ArrowUpRight,Check,GitCompareArrows} from 'lucide-react';
import type {PreviewVehicle} from '../data/vehicles';
interface Props { vehicle:PreviewVehicle; selected:boolean; onCompare:()=>void; onDetails:()=>void }
export function VehicleCard({vehicle,selected,onCompare,onDetails}:Props){
 return <article className="vehicle-card">
   <div className="vehicle-card__media">
     <img src={vehicle.image} loading="lazy" alt={'Illustrative photo for '+vehicle.name+' showcase layout'} />
     <span className="vehicle-card__badge">DESIGN EXAMPLE</span>
   </div>
   <div className="vehicle-card__content">
    <div className="vehicle-card__top"><span className="vehicle-card__type">{vehicle.bodyType}</span><span className="vehicle-card__year">Example · {vehicle.year}</span></div>
    <h3>{vehicle.name}</h3>
    <p>{vehicle.descriptor} · Not actual Carfolk stock</p>
    <div className="vehicle-card__price"><span>Vehicle price</span><strong>To be confirmed</strong></div>
    <div className="vehicle-card__actions"><button type="button" className={selected?'car-action car-action--selected':'car-action'} onClick={onCompare} aria-pressed={selected}>{selected?<Check size={16}/>:<GitCompareArrows size={16}/>} {selected?'Added':'Compare'}</button><button type="button" className="car-action car-action--primary" onClick={onDetails}>Explore layout <ArrowUpRight size={17}/></button></div>
   </div>
 </article>
}
