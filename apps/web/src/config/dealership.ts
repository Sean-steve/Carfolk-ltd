/**
 * Public, verified dealership contact channels.
 * All fields default to absent; NEVER invent a business address/phone/email.
 * Set VITE_CARFOLK_* variables from client-verified information at build time.
 * These are PUBLIC values compiled into the frontend and must not be secrets.
 */
const env = import.meta.env;
export const publicDealer = {
  name: 'Carfolk LTD',
  tagline: 'Quality & Affordability',
  phone: verifiedPhone(env.VITE_CARFOLK_PHONE),
  whatsapp: verifiedWhatsapp(env.VITE_CARFOLK_WHATSAPP),
  email: verifiedEmail(env.VITE_CARFOLK_EMAIL),
  address: textValue(env.VITE_CARFOLK_ADDRESS, 200),
  openingHours: textValue(env.VITE_CARFOLK_HOURS, 160),
} as const;

function textValue(value:unknown,max:number):string|null {
  if(typeof value!=='string') return null;
  const trimmed=value.trim();
  return trimmed.length>1&&trimmed.length<=max?trimmed:null;
}
export function normalizeKenyanPhone(raw:unknown):string|null {
  if(typeof raw!=='string')return null;
  const cleaned=raw.replace(/[\s()-]/g,'');
  if(!/^\+?\d+$/.test(cleaned))return null;
  if(/^0[17]\d{8}$/.test(cleaned))return '+254'+cleaned.slice(1);
  if(/^254[17]\d{8}$/.test(cleaned))return '+'+cleaned;
  if(/^\+254[17]\d{8}$/.test(cleaned))return cleaned;
  // For dealership desk numbers, permit a verified Kenyan 020 line.
  if(/^020\d{7}$/.test(cleaned))return '+254'+cleaned.slice(1);
  if(/^\+25420\d{7}$/.test(cleaned))return cleaned;
  return null;
}
function verifiedPhone(value:unknown):string|null {return normalizeKenyanPhone(value)}
function verifiedWhatsapp(value:unknown):string|null {
  const phone=normalizeKenyanPhone(value);
  return phone&&/^\+254[17]\d{8}$/.test(phone)?phone:null;
}
function verifiedEmail(value:unknown):string|null{
  const email=textValue(value,254);
  return email&&/^[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,}$/i.test(email)?email:null;
}
export type EnquiryIntent='general'|'vehicle'|'test-drive';
export function intentMessage(intent:EnquiryIntent,vehicleLabel?:string):string {
  const subject=vehicleLabel?vehicleLabel+' (design preview example)':'a vehicle';
  if(intent==='test-drive')return 'Hello Carfolk LTD, I would like to request a viewing or test drive for '+subject+'. Please advise on availability.';
  if(intent==='vehicle')return 'Hello Carfolk LTD, I would like to enquire about '+subject+'. Please share verified vehicle details and availability.';
  return 'Hello Carfolk LTD, I would like to make an enquiry. Please get in touch.';
}
export function whatsappLink(phone:string|null, message:string):string|null{
  if(!phone||!/^\+254[17]\d{8}$/.test(phone))return null;
  return 'https://wa.me/'+phone.slice(1)+'?text='+encodeURIComponent(message);
}
export function emailLink(address:string|null,subject='Carfolk LTD enquiry'):string|null{
 if(!address||!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(address))return null;
 return 'mailto:'+address+'?subject='+encodeURIComponent(subject);
}
export function phoneLink(phone:string|null):string|null{
 if(!phone||!/^\+254(?:[17]\d{8}|20\d{7})$/.test(phone))return null;
 return 'tel:'+phone;
}
export function mapLink(address:string|null):string|null{
 if(!address)return null;
 return 'https://www.google.com/maps/search/?api=1&query='+encodeURIComponent(address);
}
