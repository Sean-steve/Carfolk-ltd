import {ArrowUpRight,ChevronRight,Clock3,Mail,MapPin,MessageCircle,Phone} from 'lucide-react';
import {Shell} from '../components/Shell';
import {EnquiryForm} from '../components/EnquiryForm';
import {emailLink,intentMessage,mapLink,phoneLink,whatsappLink} from '../config/dealership';
import {getVehicle,type Vehicle} from '../api/client';
import {useRemote,useSite} from '../api/hooks';
import {intentFromQuery} from '../lib/enquiries';
export function ContactPage({query}:{query:string}){
 const p=new URLSearchParams(query);
 const vehicleSlug=p.get('vehicle')??'';
 const intent=intentFromQuery(p.get('intent'));
 const {data:site}=useSite();
 const {data:vehicle}=useRemote<Vehicle|null>(signal=>vehicleSlug?getVehicle(vehicleSlug,signal):Promise.resolve(null),vehicleSlug);
 const label=vehicle?vehicle.make+' '+vehicle.model:undefined;
 const whats=whatsappLink(site?.whatsapp??null,intentMessage(intent,label));
 const call=phoneLink(site?.phone??null);
 const email=emailLink(site?.email??null,label?'Carfolk vehicle enquiry: '+label:'Carfolk website enquiry');
 const maps=mapLink(site?.address??null);
 return <Shell>
  <section className="contact-hero"><div className="container contact-hero__inner">
   <div><nav className="catalogue-breadcrumb" aria-label="Breadcrumb"><a href="#/">Home</a><ChevronRight size={14}/> Contact</nav><span className="eyebrow">WE'RE HERE TO HELP</span><h1>Let's find what<br/><em>moves you.</em></h1><p>Questions about a vehicle or requesting a visit? Connect with Carfolk LTD.</p></div>
   <div className="contact-hero__caption"><MessageCircle size={39}/><div><strong>Quality &amp; Affordability</strong><span>Clear information. Real conversations.</span></div></div>
  </div></section>
  <div className="container contact-content"><div className="contact-content__intro"><div><span className="eyebrow eyebrow--red">CONNECT WITH THE DEALERSHIP</span><h2>Choose how to reach Carfolk.</h2><p>Ask about a specific vehicle, request a preferred viewing time, or send a general question. Only verified business channels are shown.</p></div></div>
   {(whats||call||email)&&<div className="contact-methods">
    {whats&&<article className="contact-method"><div className="contact-method__icon contact-method__icon--green"><MessageCircle size={27}/></div><h3>WhatsApp</h3><a className="contact-method__action" href={whats} target="_blank" rel="noopener noreferrer">Message on WhatsApp <ArrowUpRight size={17}/></a></article>}
    {call&&<article className="contact-method"><div className="contact-method__icon"><Phone size={27}/></div><h3>Call us</h3><a className="contact-method__action" href={call}>Call {site?.phone} <ArrowUpRight size={17}/></a></article>}
    {email&&<article className="contact-method"><div className="contact-method__icon"><Mail size={27}/></div><h3>Email enquiries</h3><a className="contact-method__action" href={email}>Email {site?.email} <ArrowUpRight size={17}/></a></article>}
   </div>}
   <div className="contact-content__grid"><EnquiryForm intent={intent} vehicleSlug={vehicleSlug}/><aside className="contact-aside">
    {(site?.address||site?.opening_hours)&&<div className="contact-aside__panel"><span className="eyebrow eyebrow--red">VISIT CARFOLK</span><h3>Visit us</h3>
     {site?.address&&<div className="contact-aside__row"><MapPin size={20}/><div><strong>Dealership address</strong><p>{site.address}</p></div></div>}
     {site?.opening_hours&&<div className="contact-aside__row"><Clock3 size={20}/><div><strong>Opening hours</strong><p>{site.opening_hours}</p></div></div>}
     {maps&&<a className="button button--outline contact-aside__map" href={maps} rel="noopener noreferrer" target="_blank">View map <ArrowUpRight size={17}/></a>}
    </div>}
    <div className="contact-aside__guide"><span className="eyebrow">HOW A VIEWING WORKS</span><h3>Plan your visit with confidence.</h3>
      <div className="contact-aside__step"><b>01</b><span>Choose a published vehicle and send your preferred date and time.</span></div>
      <div className="contact-aside__step"><b>02</b><span>Wait for the dealership to confirm availability and the meeting arrangements.</span></div>
      <div className="contact-aside__step"><b>03</b><span>Ask about condition, documentation and any inspection questions before travelling.</span></div>
      <p><Clock3 size={17}/> A viewing request is not an automatic appointment or vehicle reservation.</p>
    </div>
   </aside></div>
  </div>
 </Shell>;
}
