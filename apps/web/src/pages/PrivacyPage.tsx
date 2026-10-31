import {ArrowLeft,ChevronRight,Info,LockKeyhole,ShieldCheck} from 'lucide-react';
import {Shell} from '../components/Shell';
import {useSite} from '../api/hooks';
export function PrivacyPage(){
 const {data:site}=useSite();
 return <Shell><section className="catalogue-intro catalogue-intro--compact"><div className="container catalogue-intro__inner"><div className="catalogue-breadcrumb"><a href="#/">Home</a><ChevronRight size={14}/> Privacy</div><span className="eyebrow">YOUR DATA</span><h1>Privacy &amp; <em>transparency.</em></h1><p>How we handle customer information.</p></div></section>
  <article className="container privacy-page">
   <div className="privacy-page__alert"><Info size={23}/><div>
    <strong>{site?.leads_enabled?'Approved privacy policy is available.':'Lead collection is currently disabled.'}</strong>
    <p>{site?.leads_enabled?'Review the dealership’s approved privacy notice before sending an enquiry.':'The customer forms on this website are disabled until a live service and approved privacy notice are provided. This page is not a substitute for the dealership’s formal privacy policy.'}</p>
    {site?.privacy_url&&<a className="button button--outline" target="_blank" rel="noopener noreferrer" href={site.privacy_url}>Read official privacy policy ↗</a>}
   </div></div>
   <div className="privacy-page__grid">
    <div className="privacy-page__section"><LockKeyhole size={27}/><h2>Enquiry information</h2><p>When enabled, forms request your name, Kenyan phone number, optional email, message and your selected vehicle. Viewing requests also include your preferred date and time.</p><p>These details are sent to the Carfolk dealership server when you explicitly submit and consent.</p></div>
    <div className="privacy-page__section"><ShieldCheck size={27}/><h2>Dealership operations</h2><p>Submitted enquiries are stored in the dealership database and visible only to authorized staff. The browsing comparison shortlist stores only vehicle IDs in your current browser session.</p><p>Retention, access and deletion procedures must follow the approved formal privacy policy and applicable law.</p></div>
   </div>
   <section className="privacy-page__next"><h2>Questions about your information?</h2><p>Contact Carfolk through the verified business channels. No business contact details are invented on this website.</p><a href="#/contact" className="button button--outline"><ArrowLeft size={16}/> Back to Contact</a></section>
  </article>
 </Shell>;
}
