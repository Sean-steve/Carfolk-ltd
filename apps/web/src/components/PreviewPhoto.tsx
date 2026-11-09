import {useEffect,useState} from 'react';

type Props={
 src:string;
 /** Original photo URL to try when an optimized WebP is unavailable. */
 fallbackSrc?:string;
 alt:string;
 className?:string;
 loading?:'lazy'|'eager';
};

const placeholder=import.meta.env.BASE_URL+'no-photo.svg';

/**
 * Network errors on an optimized variant should never hide a healthy original.
 * Only display the placeholder once both candidates have failed.
 */
export function PreviewPhoto({src,fallbackSrc='',alt,className='',loading='lazy'}:Props){
 const preferred=src||fallbackSrc;
 const [activeSrc,setActiveSrc]=useState(preferred||placeholder);

 useEffect(()=>{
  setActiveSrc(preferred||placeholder);
 },[preferred,fallbackSrc]);

 function retryOriginal(){
  setActiveSrc(current=>{
   if(current===preferred && fallbackSrc && fallbackSrc!==preferred) return fallbackSrc;
   return placeholder;
  });
 }

 return <img
  src={activeSrc}
  alt={activeSrc===placeholder?'Vehicle photo not available':alt}
  className={className}
  loading={loading}
  decoding="async"
  onError={retryOriginal}
 />;
}
