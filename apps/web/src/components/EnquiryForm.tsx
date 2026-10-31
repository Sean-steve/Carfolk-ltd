import {useEffect,useRef,useState,type ChangeEvent,type FormEvent} from 'react';
import {ArrowRight,CheckCircle2,Info,MessageCircle,Send,ShieldCheck} from 'lucide-react';
import {DEMO_CARS,getDemoCar} from '../data/showroom';
import {EMPTY_DRAFT,countErrors,todayInNairobi,validateEnquiry,type EnquiryDraft,type EnquiryKind,type FormErrors} from '../lib/enquiries';

type Props={intent:EnquiryKind;vehicleSlug:string};
const modes:{value:EnquiryKind;title:string;detail:string}[]=[
 {value:'general',title:'General enquiry',detail:'Ask us anything'},
 {value:'vehicle',title:'Vehicle enquiry',detail:'Discuss a particular car'},
 {value:'test-drive',title:'Book a viewing',detail:'Request a preferred time'},
];

export function EnquiryForm({intent,vehicleSlug}:Props){
 const selected=getDemoCar(vehicleSlug);
 const [draft,setDraft]=useState<EnquiryDraft>(()=>({...EMPTY_DRAFT,kind:intent,vehicleId:selected?.id??''}));
 const [errors,setErrors]=useState<FormErrors>({});
 const [validated,setValidated]=useState(false);
 const alertRef=useRef<HTMLDivElement>(null);
 useEffect(()=>{
   setDraft(prev=>({...prev,kind:intent,vehicleId:getDemoCar(vehicleSlug)?.id??''}));
   setErrors({});setValidated(false);
 },[intent,vehicleSlug]);
 function update<K extends keyof EnquiryDraft>(key:K,value:EnquiryDraft[K]){
   setDraft(d=>({...d,[key]:value}));
   setValidated(false);
   setErrors(e=>({...e,[key]:undefined}));
 }
 function input<K extends keyof EnquiryDraft>(key:K){
  return (e:ChangeEvent<HTMLInputElement|HTMLTextAreaElement|HTMLSelectElement>)=>update(key,e.target.value as EnquiryDraft[K]);
 }
 function choose(value:EnquiryKind){
  const p=new URLSearchParams();
  if(value!=='general')p.set('intent',value);
  if(draft.vehicleId&&value!=='general')p.set('vehicle',draft.vehicleId);
  window.location.hash='#/contact'+(p.toString()?'?'+p.toString():'');
 }
 function submit(e:FormEvent<HTMLFormElement>){
   e.preventDefault();
   const result=validateEnquiry(draft);
   setErrors(result);
   setValidated(countErrors(result)===0);
   requestAnimationFrame(()=>alertRef.current?.focus());
 }
 const vehicle=DEMO_CARS.find(x=>x.id===draft.vehicleId);
 const summary=(text:string)=><span className="enquiry-error" role="alert">{text}</span>;
 return <div className="enquiry-form-card" id="enquiry-form">
  <div className="enquiry-form-card__heading">
   <div><span className="eyebrow eyebrow--red">CUSTOMER ENQUIRIES</span><h2>How can we help?</h2><p>Tell Carfolk what you're looking for. The form is a frontend preview and cannot send messages yet.</p></div>
   <div className="enquiry-form-card__icon"><MessageCircle size={28}/></div>
  </div>
  <div className="enquiry-modes" role="group" aria-label="Enquiry type">
   {modes.map(mode=><button type="button" key={mode.value} className={intent===mode.value?'is-selected':''} onClick={()=>choose(mode.value)} aria-pressed={intent===mode.value}><span>{mode.title}</span><small>{mode.detail}</small></button>)}
  </div>
  <div className="enquiry-form-card__warning"><Info size={19}/><div><strong>Preview only — no enquiries are sent.</strong><p>Please use fictitious contact details to test the form. No backend is connected and validation does not create a customer lead or appointment.</p></div></div>
  <form className="enquiry-form" onSubmit={submit} noValidate>
   <div className="enquiry-form__grid">
    <div className="enquiry-field">
     <label htmlFor="enquiry-name">Full name <em>*</em></label>
     <input id="enquiry-name" type="text" autoComplete="off" maxLength={80} value={draft.name} onChange={input('name')} placeholder="e.g. Jane Mwangi" required aria-invalid={!!errors.name} aria-describedby={errors.name?'error-name':undefined}/>
     {errors.name&&<span id="error-name">{summary(errors.name)}</span>}
    </div>
    <div className="enquiry-field">
     <label htmlFor="enquiry-phone">Phone number <em>*</em></label>
     <input id="enquiry-phone" type="tel" autoComplete="off" value={draft.phone} onChange={input('phone')} placeholder="e.g. 07XX XXX XXX" required aria-invalid={!!errors.phone} aria-describedby={errors.phone?'error-phone':undefined}/>
     {errors.phone&&<span id="error-phone">{summary(errors.phone)}</span>}
    </div>
    <div className="enquiry-field enquiry-field--full">
     <label htmlFor="enquiry-email">Email address <small>(optional)</small></label>
     <input id="enquiry-email" type="email" autoComplete="off" maxLength={254} value={draft.email} onChange={input('email')} placeholder="e.g. jane@example.com" aria-invalid={!!errors.email} aria-describedby={errors.email?'error-email':undefined}/>
     {errors.email&&<span id="error-email">{summary(errors.email)}</span>}
    </div>
    {intent!=='general'&&<div className="enquiry-field enquiry-field--full">
     <label htmlFor="enquiry-vehicle">Vehicle of interest <em>*</em></label>
     <select id="enquiry-vehicle" value={draft.vehicleId} onChange={input('vehicleId')} aria-invalid={!!errors.vehicleId} aria-describedby={errors.vehicleId?'error-vehicle':undefined} required>
      <option value="">Choose an illustrative vehicle</option>{DEMO_CARS.map(car=><option value={car.id} key={car.id}>{car.make} {car.model} ({car.year}) — example only</option>)}
     </select>
     {vehicle&&<span className="enquiry-field__help">Selected: {vehicle.make} {vehicle.model}. This is not confirmed stock.</span>}
     {errors.vehicleId&&<span id="error-vehicle">{summary(errors.vehicleId)}</span>}
    </div>}
    {intent==='test-drive'&&<><div className="enquiry-field">
     <label htmlFor="enquiry-date">Preferred viewing date <em>*</em></label>
     <input type="date" id="enquiry-date" min={todayInNairobi()} value={draft.preferredDate} onChange={input('preferredDate')} required aria-invalid={!!errors.preferredDate}/>
     {errors.preferredDate&&summary(errors.preferredDate)}
    </div><div className="enquiry-field">
     <label htmlFor="enquiry-time">Preferred time <em>*</em></label>
     <input type="time" id="enquiry-time" value={draft.preferredTime} onChange={input('preferredTime')} required aria-invalid={!!errors.preferredTime}/>
     {errors.preferredTime&&summary(errors.preferredTime)}
    </div></>}
    <div className="enquiry-field enquiry-field--full">
     <label htmlFor="enquiry-message">Your message <em>*</em></label>
     <textarea id="enquiry-message" value={draft.message} onChange={input('message')} maxLength={1000} rows={5} placeholder={intent==='test-drive'?'Tell us anything we should know before arranging a visit…':'What would you like to know about a vehicle?'} required aria-invalid={!!errors.message} aria-describedby={errors.message?'error-message':undefined}/>
     <div className="enquiry-field__counter">{draft.message.length}/1000</div>
     {errors.message&&<span id="error-message">{summary(errors.message)}</span>}
    </div>
    <div className="enquiry-field enquiry-field--honeypot" aria-hidden="true">
     <label htmlFor="website-check">Leave this field blank</label><input type="text" id="website-check" name="website" tabIndex={-1} autoComplete="off" value={draft.website} onChange={input('website')}/>
    </div>
   </div>
   <label className="enquiry-consent"><input type="checkbox" checked={draft.consent} onChange={e=>update('consent',e.target.checked)} aria-invalid={!!errors.consent}/><span>I agree that Carfolk LTD may contact me regarding this enquiry when the live service becomes available. I have read the <a href="#/privacy">privacy preview notice</a>. <b>*</b></span></label>
   {errors.consent&&summary(errors.consent)}
   <p className="enquiry-form__footer"><ShieldCheck size={16}/> No data is transmitted or saved by this demonstration form.</p>
   <button type="submit" className="button button--primary enquiry-submit"><Send size={17}/> Check enquiry form <ArrowRight size={17}/></button>
  </form>
  <div className={validated?'enquiry-feedback enquiry-feedback--ok':'enquiry-feedback'} tabIndex={-1} ref={alertRef} aria-live="polite" aria-atomic="true">
   {validated?<><CheckCircle2 size={23}/><div><strong>Validation successful — this is only a preview.</strong><p>Your example form is complete. Nothing has been submitted, booked, saved or sent to Carfolk. Real enquiry capture will be activated after the backend and verified privacy policy are ready.</p></div></>:
    countErrors(errors)>0?<><Info size={22}/><div><strong>Please check {countErrors(errors)} field{countErrors(errors)===1?'':'s'}.</strong><p>Correct the highlighted fields before completing the preview validation.</p></div></>:null}
  </div>
 </div>
}
