import {useState} from 'react';
type Props={src:string;alt:string;className?:string;loading?:'lazy'|'eager';};
export function PreviewPhoto({src,alt,className='',loading='lazy'}:Props){
 const [failed,setFailed]=useState(false);
 return <img src={failed?import.meta.env.BASE_URL+'demo-car.svg':src} alt={alt} className={className} loading={loading} onError={()=>setFailed(true)} />;
}
