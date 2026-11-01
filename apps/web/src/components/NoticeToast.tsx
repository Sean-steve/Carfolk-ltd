import {useEffect,useRef,useState} from 'react';
import {AlertCircle,CheckCircle2,Info,X} from 'lucide-react';

type Tone='info'|'success'|'error';
type Props={message:string;tone?:Tone;onDismiss:()=>void;duration?:number};
/** Accessible temporary feedback: enters, remains readable, then animates away. */
export function NoticeToast({message,tone='info',onDismiss,duration=6000}:Props){
 const [closing,setClosing]=useState(false);
 const dismiss=useRef(onDismiss);
 useEffect(()=>{dismiss.current=onDismiss},[onDismiss]);
 useEffect(()=>{
  setClosing(false);
  const timer=window.setTimeout(()=>setClosing(true),duration);
  return ()=>window.clearTimeout(timer);
 },[message,tone,duration]);
 useEffect(()=>{
  if(!closing)return;
  const timer=window.setTimeout(()=>dismiss.current(),260);
  return ()=>window.clearTimeout(timer);
 },[closing]);
 const Icon=tone==='success'?CheckCircle2:tone==='error'?AlertCircle:Info;
 return <div className={'toast notice-toast notice-toast--'+tone+(closing?' notice-toast--closing':'')} role={tone==='error'?'alert':'status'}>
  <Icon size={19} aria-hidden="true"/><span>{message}</span>
  <button type="button" onClick={()=>setClosing(true)} aria-label="Dismiss notification"><X size={17}/></button>
 </div>;
}
