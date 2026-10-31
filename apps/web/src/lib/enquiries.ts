/**
 * Single-dealership enquiry contract. Phase 3 is a frontend-only preview.
 * Nothing in this module sends/stores customer contact information.
 */
export type EnquiryKind='general'|'vehicle'|'test-drive';
export type EnquiryDraft={
 kind:EnquiryKind;
 vehicleId:string;
 name:string;
 phone:string;
 email:string;
 message:string;
 preferredDate:string;
 preferredTime:string;
 consent:boolean;
 website:string; // honeypot: future API should reject non-empty values
};
export type FormErrors=Partial<Record<keyof EnquiryDraft,string>>;
export const EMPTY_DRAFT:EnquiryDraft={
 kind:'general',vehicleId:'',name:'',phone:'',email:'',message:'',preferredDate:'',preferredTime:'',consent:false,website:'',
};
export function todayInNairobi(now=new Date()):string {
 const fmt=new Intl.DateTimeFormat('en-GB',{timeZone:'Africa/Nairobi',year:'numeric',month:'2-digit',day:'2-digit'});
 const parts=fmt.formatToParts(now);
 const find=(type:string)=>parts.find(p=>p.type===type)?.value??'';
 return [find('year'),find('month'),find('day')].join('-');
}
export function validateEnquiry(draft:EnquiryDraft,today=todayInNairobi()):FormErrors{
 const errors:FormErrors={};
 const trimmedName=draft.name.trim();
 if(trimmedName.length<2||trimmedName.length>80)errors.name='Enter your full name (2–80 characters).';
 const phone=draft.phone.replace(/[\s()-]/g,'');
 if(!/^(?:0[17]\d{8}|(?:\+?254)[17]\d{8})$/.test(phone))errors.phone='Enter a valid Kenyan mobile number, e.g. 07XXXXXXXX.';
 const email=draft.email.trim();
 if(email.length>0&&!/^[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,}$/i.test(email))errors.email='Enter a valid email address or leave this field blank.';
 if(email.length>254)errors.email='Email address is too long.';
 if(draft.kind!=='general'&&!draft.vehicleId)errors.vehicleId='Choose an example vehicle.';
 if(draft.message.trim().length<10||draft.message.trim().length>1000)errors.message='Enter a message between 10 and 1,000 characters.';
 if(draft.kind==='test-drive'){
  if(!/^\d{4}-\d{2}-\d{2}$/.test(draft.preferredDate)||draft.preferredDate<today)errors.preferredDate='Choose today or a future date.';
  if(!/^([01]\d|2[0-3]):[0-5]\d$/.test(draft.preferredTime))errors.preferredTime='Choose a preferred time.';
 }
 if(!draft.consent)errors.consent='Please agree to be contacted about this enquiry.';
 if(draft.website.trim())errors.website='This request cannot be accepted.';
 return errors;
}
export function countErrors(errors:FormErrors):number{return Object.values(errors).filter(Boolean).length}
export function intentFromQuery(raw:string|null):EnquiryKind {
 if(raw==='test-drive')return 'test-drive';
 if(raw==='vehicle')return 'vehicle';
 return 'general';
}
