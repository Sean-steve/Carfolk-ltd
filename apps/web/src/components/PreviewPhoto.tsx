import {useEffect,useState} from 'react';
type Props={src:string;alt:string;className?:string;loading?:'lazy'|'eager'};
export function PreviewPhoto({src,alt,className='',loading='lazy'}:Props){
 const [failed,setFailed]=useState(!src);
 useEffect(()=>setFailed(!src),[src]);
 return <img src={failed?import.meta.env.BASE_URL+'no-photo.svg':src} alt={failed?'Vehicle photo not yet uploaded':alt} className={className} loading={loading} onError={()=>setFailed(true)}/>;
}
