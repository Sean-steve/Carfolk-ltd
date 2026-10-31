import {ArrowRight,ArrowUpRight,CalendarDays,ChevronRight,Clock3,Info,Mail,MapPin,MessageCircle,Phone,ShieldCheck} from 'lucide-react';
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
  <div className="container contact-content"><div className="contact-content__intro"><div><span className="eyebrow eyebrow--red">CONNECT WITH THE DEALERSHIP</span><h2>Choose how to reach Carfolk.</h2><p>Only contact channels configured by the dealership are shown as active links.</p></div></div>
   <div className="contact-methods">
    <article className="contact-method"><div className="contact-method__icon contact-method__icon--green"><MessageCircle size={27}/></div><h3>WhatsApp</h3><p>Message Carfolk about vehicles and visits.</p>{whats?<a className="contact-method__action" href={whats} target="_blank" rel="noopener noreferrer">Message on WhatsApp <ArrowUpRight size={17}/></a>:<span className="contact-method__pending"><Info size={15}/> Official WhatsApp not yet provided</span>}</article>
    <article className="contact-method"><div className="contact-method__icon"><Phone size={27}/></div><h3>Call us</h3><p>Speak directly with the dealership.</p>{call?<a className="contact-method__action" href={call}>Call {site?.phone} <ArrowUpRight size={17}/></a>:<span className="contact-method__pending"><Info size={15}/> Telephone details pending</span>}</article>
    <article className="contact-method"><div className="contact-method__icon"><Mail size={27}/></div><h3>Email enquiries</h3><p>Contact the dealership from your email app.</p>{email?<a className="contact-method__action" href={email}>Email {site?.email} <ArrowUpRight size={17}/></a>:<span className="contact-method__pending"><Info size={15}/> Email address pending</span>}</article>
   </div>
   <div className="contact-content__grid"><EnquiryForm intent={intent} vehicleSlug={vehicleSlug}/><aside className="contact-aside">
    <div className="contact-aside__panel"><span className="eyebrow eyebrow--red">VISIT CARFOLK</span><h3>Come see us.</h3><p>Confirm location, availability and a visit with the team first.</p>
     <div className="contact-aside__row"><MapPin size={20}/><div><strong>Dealership address</strong><p>{site?.address??'Location not yet provided'}</p></div></div>
     <div className="contact-aside__row"><Clock3 size={20}/><div><strong>Opening hours</strong><p>{site?.opening_hours??'Hours not yet provided'}</p></div></div>
     {maps?<a className="button button--outline contact-aside__map" href={maps} rel="noopener noreferrer" target="_blank">View map <ArrowUpRight size={17}/></a>:<div className="contact-aside__map-pending"><MapPin size={32}/>Verified location will appear here.</div>}
    </div>
    <div className="contact-aside__guide"><span className="eyebrow">VISIT PLANNING</span><h3>What happens after a request?</h3><div className="contact-aside__step"><b>01</b><span>Send a vehicle question or viewing request.</span></div><div className="contact-aside__step"><b>02</b><span>Carfolk checks stock and contacts you.</span></div><div className="contact-aside__step"><b>03</b><span>Agree on a time to inspect the vehicle.</span></div><p><ShieldCheck size={16}/> Request submission does not confirm any viewing.</p></div>
    <div className="contact-aside__questions"><CalendarDays size={27}/><h3>Explore available cars.</h3><p>Find a published vehicle before requesting a test drive.</p><a href="#/cars">Browse cars <ArrowRight size={16}/></a></div>
   </aside></div>
   <section className="contact-faq"><div className="section-heading"><div><span className="eyebrow eyebrow--red">GOOD TO KNOW</span><h2>Common questions.</h2></div></div><div className="contact-faq__grid">
    <details><summary>Is a viewing request a confirmed booking?</summary><p>No. The request is stored for the team when lead collection is enabled; Carfolk must confirm a suitable time.</p></details>
    <details><summary>Are the cars shown available?</summary><p>Listings are published from the dealership's database. Contact the team to verify current status and price.</p></details>
    <details><summary>Can I reserve or pay for a car online?</summary><p>No. Online checkout and reservation deposits are not included in this Model A website.</p></details>
    <details><summary>Does the financing calculator approve a loan?</summary><p>No. Estimates are hypothetical and not lender offers or eligibility decisions.</p></details>
   </div></section>
  </div>
 </Shell>;
}
