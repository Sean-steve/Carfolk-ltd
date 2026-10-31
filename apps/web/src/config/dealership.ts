/**
 * Public link formatting. Contact details are always read from Laravel /site,
 * not invented or bundled as unverified defaults in the browser.
 */
export function normalizeKenyanPhone(raw:unknown):string|null {
 if(typeof raw!=='string')return null;
 const cleaned=raw.replace(/[\s()-]/g,'');
 if(/^0[17]\d{8}$/.test(cleaned))return '+254'+cleaned.slice(1);
 if(/^254[17]\d{8}$/.test(cleaned))return '+'+cleaned;
 if(/^\+254[17]\d{8}$/.test(cleaned))return cleaned;
 if(/^020\d{7}$/.test(cleaned))return '+254'+cleaned.slice(1);
 if(/^\+25420\d{7}$/.test(cleaned))return cleaned;
 return null;
}
export type EnquiryIntent='general'|'vehicle'|'test-drive';
export function intentMessage(intent:EnquiryIntent,vehicleLabel?:string):string {
 const subject=vehicleLabel??'a vehicle';
 if(intent==='test-drive')return 'Hello Carfolk LTD, I would like to request a viewing for '+subject+'. Please confirm availability.';
 if(intent==='vehicle')return 'Hello Carfolk LTD, I would like to enquire about '+subject+'. Please share details and availability.';
 return 'Hello Carfolk LTD, I would like to make an enquiry. Please contact me.';
}
export function whatsappLink(phone:string|null,message:string):string|null {
 const value=normalizeKenyanPhone(phone);
 if(!value||!/^\+254[17]\d{8}$/.test(value))return null;
 return 'https://wa.me/'+value.slice(1)+'?text='+encodeURIComponent(message);
}
export function emailLink(address:string|null,subject='Carfolk LTD enquiry'):string|null{
 if(!address||!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(address))return null;
 return 'mailto:'+address+'?subject='+encodeURIComponent(subject);
}
export function phoneLink(phone:string|null):string|null{
 const value=normalizeKenyanPhone(phone);
 return value?'tel:'+value:null;
}
export function mapLink(address:string|null):string|null {
 return address?'https://www.google.com/maps/search/?api=1&query='+encodeURIComponent(address):null;
}
