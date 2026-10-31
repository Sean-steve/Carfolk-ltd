import {ArrowLeft,ChevronRight,Info,LockKeyhole,ShieldCheck} from 'lucide-react';
import {Shell} from '../components/Shell';
export function PrivacyPage(){
 return <Shell>
  <section className="catalogue-intro catalogue-intro--compact"><div className="container catalogue-intro__inner"><div className="catalogue-breadcrumb"><a href="#/">Home</a><ChevronRight size={14}/> Privacy</div><span className="eyebrow">CUSTOMER INFORMATION</span><h1>Privacy &amp; <em>transparency.</em></h1><p>How the Carfolk platform is designed to handle enquiries.</p></div></section>
  <article className="container privacy-page">
   <div className="privacy-page__alert"><Info size={23}/><div><strong>Preview-stage notice — not the final business privacy policy.</strong><p>The website is currently a static GitHub Pages demonstration. Enquiry and viewing forms only check entered values in your browser; they do not submit, email, upload or save them. Do not enter sensitive information during this preview.</p></div></div>
   <div className="privacy-page__grid">
    <div className="privacy-page__section"><LockKeyhole size={27}/><h2>Information used in future enquiries</h2><p>When a production backend is activated, the dealership expects to request your name, contact phone number, an optional email address, the vehicle of interest, your message, and optional viewing date/time.</p><p>Personal information will be used only for the purposes disclosed in the final Carfolk privacy policy and applicable service processes.</p></div>
    <div className="privacy-page__section"><ShieldCheck size={27}/><h2>Preview-stage security boundaries</h2><p>The current forms do not perform network submissions or store personal information locally. A limited example-car comparison selection is stored in your browser session; it contains no customer contact data.</p><p>Prior to accepting real leads, Carfolk must approve a full data privacy notice, retention and deletion periods, security controls, access policies, and contact preferences.</p></div>
   </div>
   <section className="privacy-page__next"><h2>What happens before launch?</h2><p>Production enquiry capture, email notifications and the administrator inbox will be built and tested during the backend and integration phases. This page will be replaced by the client's approved and legally reviewed privacy notice before customers are asked to submit information.</p><a href="#/contact" className="button button--outline"><ArrowLeft size={16}/> Back to contact</a></section>
  </article>
 </Shell>
}
