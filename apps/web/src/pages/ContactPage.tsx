import {ArrowRight,ArrowUpRight,ChevronRight,Clock3,Info,Mail,MapPin,MessageCircle,Phone,ShieldCheck} from 'lucide-react';
import {Shell} from '../components/Shell';
import {EnquiryForm} from '../components/EnquiryForm';
import {publicDealer,emailLink,intentMessage,mapLink,phoneLink,whatsappLink} from '../config/dealership';
import {getDemoCar} from '../data/showroom';
import {intentFromQuery} from '../lib/enquiries';

export function ContactPage({query}:{query:string}){
 const params=new URLSearchParams(query);
 const vehicleSlug=params.get('vehicle')??'';
 const example=getDemoCar(vehicleSlug);
 const intent=intentFromQuery(params.get('intent'));
 const context=example?example.make+' '+example.model:undefined;
 const whats=whatsappLink(publicDealer.whatsapp,intentMessage(intent,context));
 const call=phoneLink(publicDealer.phone);
 const email=emailLink(publicDealer.email,context?'Carfolk vehicle enquiry: '+context:'Carfolk website enquiry');
 const maps=mapLink(publicDealer.address);
 return <Shell>
  <section className="contact-hero">
   <div className="container contact-hero__inner">
    <div><nav className="catalogue-breadcrumb" aria-label="Breadcrumb"><a href="#/">Home</a><ChevronRight size={14}/> Contact</nav><span className="eyebrow">WE'RE HERE TO HELP</span><h1>Let's find what<br/><em>moves you.</em></h1><p>Questions about a vehicle, financing options or booking a viewing? Find the easiest way to connect with Carfolk LTD.</p></div>
    <div className="contact-hero__caption"><MessageCircle size={39}/><div><strong>Quality &amp; Affordability</strong><span>Real conversations. Clear next steps.</span></div></div>
   </div>
  </section>
  <div className="container contact-content">
   <div className="contact-content__intro"><div><span className="eyebrow eyebrow--red">YOUR NEXT CONVERSATION</span><h2>Choose how you'd like to reach us.</h2><p>Verified dealership contact details appear here when supplied by Carfolk. We don't publish guessed phone numbers, email addresses or locations.</p></div></div>
   <div className="contact-methods">
    <article className="contact-method"><div className="contact-method__icon contact-method__icon--green"><MessageCircle size={27}/></div><h3>WhatsApp</h3><p>Start a conversation about a specific vehicle.</p>
      {whats?<a href={whats} target="_blank" rel="noopener noreferrer" className="contact-method__action">Message on WhatsApp <ArrowUpRight size={17}/></a>:<span className="contact-method__pending"><Info size={15}/> Official WhatsApp number not yet verified</span>}
    </article>
    <article className="contact-method"><div className="contact-method__icon"><Phone size={27}/></div><h3>Give us a call</h3><p>Speak with the dealership team directly.</p>
      {call?<a href={call} className="contact-method__action">Call {publicDealer.phone} <ArrowUpRight size={17}/></a>:<span className="contact-method__pending"><Info size={15}/> Official phone number pending confirmation</span>}
    </article>
    <article className="contact-method"><div className="contact-method__icon"><Mail size={27}/></div><h3>Email enquiries</h3><p>Prefer a message you can send from your email app?</p>
      {email?<a href={email} className="contact-method__action">Email {publicDealer.email} <ArrowUpRight size={17}/></a>:<span className="contact-method__pending"><Info size={15}/> Official email address pending confirmation</span>}
    </article>
   </div>
   <div className="contact-content__grid">
    <EnquiryForm intent={intent} vehicleSlug={vehicleSlug}/>
    <aside className="contact-aside">
      <div className="contact-aside__panel">
       <span className="eyebrow eyebrow--red">VISIT CARFOLK</span><h3>Come see us.</h3><p>Contact the team to confirm a visit before travelling to the showroom.</p>
       <div className="contact-aside__row"><MapPin size={20}/><div><strong>Dealership address</strong><p>{publicDealer.address??'Full address pending verification'}</p></div></div>
       <div className="contact-aside__row"><Clock3 size={20}/><div><strong>Opening hours</strong><p>{publicDealer.openingHours??'Opening hours to be confirmed'}</p></div></div>
       {maps?<a className="button button--outline contact-aside__map" href={maps} target="_blank" rel="noopener noreferrer">View map <ArrowUpRight size={17}/></a>:<div className="contact-aside__map-pending"><MapPin size={32}/><span>Verified dealership location will appear here.</span></div>}
      </div>
      <div className="contact-aside__guide"><span className="eyebrow">VISIT PLANNING</span><h3>What happens after you enquire?</h3><div className="contact-aside__step"><b>01</b><span>Tell the team about the vehicle you are interested in.</span></div><div className="contact-aside__step"><b>02</b><span>Confirm current stock, details and a suitable appointment.</span></div><div className="contact-aside__step"><b>03</b><span>Visit and inspect before making your decision.</span></div><p><ShieldCheck size={16}/> These are proposed steps, not a promise of availability or a confirmed appointment.</p></div>
      <div className="contact-aside__questions"><CalendarDays size={27}/><h3>Ready to browse?</h3><p>Explore cars before deciding which one you'd like to ask about.</p><a href="#/cars">Browse vehicle examples <ArrowRight size={16}/></a></div>
    </aside>
   </div>
   <section className="contact-faq"><div className="section-heading"><div><span className="eyebrow eyebrow--red">GOOD TO KNOW</span><h2>Common questions.</h2></div></div>
    <div className="contact-faq__grid">
     <details><summary>Can I book a test drive online?</summary><p>The design includes a viewing-request form. During the static preview it validates entries only; actual appointment requests will be handled after backend integration. A request is not a confirmed booking.</p></details>
     <details><summary>Are the listed vehicles available for sale?</summary><p>Not yet. All displayed cars and prices are demonstration content. Carfolk will publish verified inventory when the administration system is connected.</p></details>
     <details><summary>Can I pay or reserve a vehicle online?</summary><p>No online checkout or reservation deposit processing is included in Model A. Speak directly with the dealership for its actual purchasing process.</p></details>
     <details><summary>Does the financing calculator mean I qualify for a loan?</summary><p>No. All financing estimates are illustrative. They are not credit offers or approvals from a lender.</p></details>
    </div>
   </section>
  </div>
 </Shell>;
}
