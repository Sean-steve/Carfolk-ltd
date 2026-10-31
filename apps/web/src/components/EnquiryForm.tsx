import {useEffect,useRef,useState,type ChangeEvent,type FormEvent} from 'react';
import {ArrowRight,CheckCircle2,Info,MessageCircle,Send,ShieldCheck} from 'lucide-react';
import {getVehicle,submitEnquiry,ApiError,type Vehicle} from '../api/client';
import {useRemote,useSite} from '../api/hooks';
import {EMPTY_DRAFT,countErrors,todayInNairobi,validateEnquiry,type EnquiryDraft,type EnquiryKind,type FormErrors} from '../lib/enquiries';
type Props={intent:EnquiryKind;vehicleSlug:string};
const modes:{value:EnquiryKind;title:string;detail:string}[]=[
 {value:'general',title:'General enquiry',detail:'Ask a question'},
 {value:'vehicle',title:'Vehicle enquiry',detail:'Ask about a car'},
 {value:'test-drive',title:'Request a viewing',detail:'Choose preferred time'},
];
function serverErrors(error:ApiError):FormErrors{
 const names:Record<string,keyof EnquiryDraft>={vehicle_id:'vehicleId',name:'name',phone:'phone',email:'email',message:'message',preferred_date:'preferredDate',preferred_time:'preferredTime',consent:'consent'};
 const out:FormErrors={};
 for(const [name,issues] of Object.entries(error.fieldErrors)){
  const key=names[name];if(key&&issues.length)out[key]=issues[0];
 }
 return out;
}
export function EnquiryForm({intent,vehicleSlug}:Props){
 const {data:site,ready:siteReady,loading:siteLoading,error:siteError}=useSite();
 const {data:vehicle,loading:vehicleLoading,error:vehicleError}=useRemote<Vehicle|null>(s=>vehicleSlug?getVehicle(vehicleSlug,s):Promise.resolve(null),vehicleSlug);
 const [draft,setDraft]=useState<EnquiryDraft>({...EMPTY_DRAFT,kind:intent});
 const [errors,setErrors]=useState<FormErrors>({});
 const [sending,setSending]=useState(false);
 const [success,setSuccess]=useState<{id:string;status:string}|null>(null);
 const [feedback,setFeedback]=useState('');
 const alertRef=useRef<HTMLDivElement>(null);
 useEffect(()=>{
  setDraft(d=>({...d,kind:intent,vehicleId:vehicle?.id??''}));
  setErrors({});setSuccess(null);setFeedback('');
 },[intent,vehicle?.id,vehicleSlug]);
 const live=siteReady&&!siteLoading&&!siteError&&!!site?.leads_enabled&&!!site.privacy_url;
 const validTarget=intent==='general'||(!!vehicle && (intent!=='test-drive'||vehicle.status==='available'));
 const canSubmit=live&&validTarget&&!vehicleLoading;
 const update=<K extends keyof EnquiryDraft>(key:K,value:EnquiryDraft[K])=>{
   setDraft(x=>({...x,[key]:value}));setErrors(x=>({...x,[key]:undefined}));setSuccess(null);setFeedback('');
 };
 const input=<K extends keyof EnquiryDraft>(key:K)=>(e:ChangeEvent<HTMLInputElement|HTMLTextAreaElement>)=>update(key,e.target.value as EnquiryDraft[K]);
 function choose(value:EnquiryKind){
  const p=new URLSearchParams();if(value!=='general')p.set('intent',value);
  if(vehicleSlug&&value!=='general')p.set('vehicle',vehicleSlug);
  window.location.hash='#/contact'+(p.size?'?'+p.toString():'');
 }
 async function submit(e:FormEvent<HTMLFormElement>){
  e.preventDefault();if(sending||!canSubmit)return;
  const next=validateEnquiry({...draft,vehicleId:vehicle?.id??''});
  setErrors(next);
  if(countErrors(next)){setFeedback('Correct the highlighted fields and try again.');alertRef.current?.focus();return;}
  setSending(true);setFeedback('');setSuccess(null);
  const payload={
   ...(intent==='general'?{}:{vehicle_id:vehicle!.id}),
   name:draft.name.trim(),phone:draft.phone.replace(/[\s()-]/g,''),
   ...(draft.email.trim()?{email:draft.email.trim()}:{}),
   message:draft.message.trim(),consent:true as const,
   ...(intent==='test-drive'?{preferred_date:draft.preferredDate,preferred_time:draft.preferredTime}:{}),
  };
  try{
   const result=await submitEnquiry(intent,payload);
   setSuccess({id:result.id,status:result.status});
   setDraft({...EMPTY_DRAFT,kind:intent,vehicleId:vehicle?.id??''});
   setErrors({});
  }catch(e){
   if(e instanceof ApiError){setErrors(serverErrors(e));setFeedback(e.status===0?'Submission could not be confirmed. Before retrying, contact Carfolk to avoid duplicate requests.':e.message)}
   else setFeedback('Your request could not be confirmed. Please contact the dealership before retrying.');
  }finally{setSending(false);requestAnimationFrame(()=>alertRef.current?.focus())}
 }
 const invalid=(field:keyof EnquiryDraft)=>errors[field]?true:undefined;
 return <div className="enquiry-form-card" id="enquiry-form">
  <div className="enquiry-form-card__heading"><div><span className="eyebrow eyebrow--red">CUSTOMER ENQUIRIES</span><h2>How can we help?</h2><p>Send a question or request a viewing. A viewing is only confirmed after the dealership responds.</p></div><div className="enquiry-form-card__icon"><MessageCircle size={28}/></div></div>
  <div className="enquiry-modes" role="group" aria-label="Enquiry type">{modes.map(mode=><button type="button" key={mode.value} className={intent===mode.value?'is-selected':''} onClick={()=>choose(mode.value)} aria-pressed={intent===mode.value}><span>{mode.title}</span><small>{mode.detail}</small></button>)}</div>
  {!live&&<div className="enquiry-form-card__warning" role="status"><Info size={19}/><div><strong>Customer submissions are not enabled.</strong><p>Carfolk must activate a live backend and an approved privacy notice before requests can be accepted. Do not enter personal information in this preview.</p></div></div>}
  {intent!=='general'&&!vehicleLoading&&!vehicle&&<div className="enquiry-form-card__warning"><Info size={19}/><div><strong>Choose a vehicle first.</strong><p>{vehicleError??'Select a published car from the catalogue before requesting a vehicle enquiry or viewing.'} <a href="#/cars">Browse cars →</a></p></div></div>}
  {intent==='test-drive'&&vehicle&&vehicle.status!=='available'&&<div className="enquiry-form-card__warning"><Info size={19}/><div><strong>Viewing unavailable for this vehicle.</strong><p>Its current status is {vehicle.status}. You can send a general enquiry instead.</p></div></div>}
  {vehicle&&<div className="finance-linked"><Info size={18}/><div><strong>{vehicle.make} {vehicle.model} · {vehicle.year}</strong><small>Carfolk published listing · {vehicle.status}</small></div></div>}
  <form className="enquiry-form" onSubmit={submit} noValidate>
   <fieldset disabled={!canSubmit||sending} className="enquiry-fieldset">
   <div className="enquiry-form__grid">
    <div className="enquiry-field"><label htmlFor="enquiry-name">Full name <em>*</em></label><input id="enquiry-name" type="text" autoComplete="name" value={draft.name} onChange={input('name')} maxLength={80} required aria-invalid={invalid('name')}/>{errors.name&&<span className="enquiry-error" role="alert">{errors.name}</span>}</div>
    <div className="enquiry-field"><label htmlFor="enquiry-phone">Phone number <em>*</em></label><input id="enquiry-phone" type="tel" autoComplete="tel" value={draft.phone} onChange={input('phone')} placeholder="07XX XXX XXX" required aria-invalid={invalid('phone')}/>{errors.phone&&<span className="enquiry-error" role="alert">{errors.phone}</span>}</div>
    <div className="enquiry-field enquiry-field--full"><label htmlFor="enquiry-email">Email address <small>(optional)</small></label><input id="enquiry-email" type="email" autoComplete="email" value={draft.email} onChange={input('email')} maxLength={254} aria-invalid={invalid('email')}/>{errors.email&&<span className="enquiry-error" role="alert">{errors.email}</span>}</div>
    {intent==='test-drive'&&<><div className="enquiry-field"><label htmlFor="enquiry-date">Preferred viewing date <em>*</em></label><input id="enquiry-date" type="date" min={todayInNairobi()} value={draft.preferredDate} onChange={input('preferredDate')} required aria-invalid={invalid('preferredDate')}/>{errors.preferredDate&&<span className="enquiry-error" role="alert">{errors.preferredDate}</span>}</div><div className="enquiry-field"><label htmlFor="enquiry-time">Preferred time (EAT) <em>*</em></label><input id="enquiry-time" type="time" value={draft.preferredTime} onChange={input('preferredTime')} required aria-invalid={invalid('preferredTime')}/>{errors.preferredTime&&<span className="enquiry-error" role="alert">{errors.preferredTime}</span>}</div></>}
    <div className="enquiry-field enquiry-field--full"><label htmlFor="enquiry-message">Your message <em>*</em></label><textarea id="enquiry-message" value={draft.message} onChange={input('message')} maxLength={1000} rows={5} required aria-invalid={invalid('message')} placeholder="Tell the dealership what you would like to know…"/><div className="enquiry-field__counter">{draft.message.length}/1000</div>{errors.message&&<span className="enquiry-error" role="alert">{errors.message}</span>}</div>
   </div>
   <label className="enquiry-consent"><input type="checkbox" checked={draft.consent} onChange={e=>update('consent',e.target.checked)} aria-invalid={invalid('consent')}/><span>I agree to Carfolk using my details to respond to this enquiry. I have read the <a href={site?.privacy_url??'#/privacy'} target={site?.privacy_url?'_blank':undefined} rel={site?.privacy_url?'noopener noreferrer':undefined}>privacy notice</a>. <b>*</b></span></label>{errors.consent&&<span className="enquiry-error" role="alert">{errors.consent}</span>}
   <p className="enquiry-form__footer"><ShieldCheck size={16}/> Details are transmitted to Carfolk only after you submit this form.</p>
   <button className="button button--primary enquiry-submit" type="submit" disabled={!canSubmit||sending}><Send size={17}/>{sending?'Sending request…':intent==='test-drive'?'Request viewing':'Send enquiry'} <ArrowRight size={17}/></button>
   </fieldset>
  </form>
  <div className={'enquiry-feedback'+(success?' enquiry-feedback--ok':'')} tabIndex={-1} ref={alertRef} aria-live="polite" aria-atomic="true">
   {success?<><CheckCircle2 size={23}/><div><strong>{intent==='test-drive'?'Viewing request received — awaiting confirmation.':'Enquiry received by Carfolk.'}</strong><p>Your reference is {success.id}. The team can view the request in its administration inbox. No viewing is confirmed automatically.</p></div></>:
    feedback?<><Info size={20}/><div><strong>Could not complete the request</strong><p>{feedback}</p></div></>:null}
  </div>
 </div>;
}
