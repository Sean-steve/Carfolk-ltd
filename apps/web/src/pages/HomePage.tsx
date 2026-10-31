import {useEffect,useMemo,useState} from 'react';
import {useCompare} from '../state/CompareContext';

import {ArrowDownRight,ArrowRight,ArrowUpRight,BadgeCheck,CalendarCheck,CarFront,CheckCircle2,ChevronRight,Clock3,GitCompareArrows,Headphones,Heart,Info,MapPin,MessageCircle,Search,ShieldCheck,SlidersHorizontal,Sparkles,X} from 'lucide-react';
import {Header} from '../components/Header';
import {Brand} from '../components/Brand';
import {Button} from '../components/Button';
import {VehicleSearch,type Criteria} from '../components/VehicleSearch';
import {VehicleCard} from '../components/VehicleCard';
import {FinanceEstimate} from '../components/FinanceEstimate';
import {previewVehicles,type PreviewVehicle} from '../data/vehicles';

function SectionHeading({eyebrow,title,description}:{eyebrow:string;title:string;description?:string}){
 return <div className="section-heading"><div><span className="eyebrow eyebrow--red">{eyebrow}</span><h2>{title}</h2></div>{description&&<p>{description}</p>}</div>
}
const values=[
 {icon:<ShieldCheck size={28}/>,title:'Quality at the forefront',text:'A confident way to explore options.'},
 {icon:<SlidersHorizontal size={28}/>,title:'Clear information',text:'Vehicle details, beautifully organized.'},
 {icon:<CalendarCheck size={28}/>,title:'Easy viewing requests',text:'Plan your visit with less hassle.'},
 {icon:<Headphones size={28}/>,title:'People-first support',text:'A simpler journey from interest to enquiry.'},
];
const steps=[
 {number:'01',title:'Explore',text:'Discover vehicle options and narrow your search.'},
 {number:'02',title:'Compare',text:'Understand what makes each option different.'},
 {number:'03',title:'Enquire',text:'Reach out to learn more about a specific vehicle.'},
 {number:'04',title:'Visit',text:'Arrange a viewing before making your decision.'},
];
const blankCriteria:Criteria={make:'',model:'',budget:'',year:'',body:''};

export default function HomePage(){
 const [criteria,setCriteria]=useState<Criteria>(blankCriteria);
 const {selected,toggleCompare:toggleSelected,clear}=useCompare();
 const [detail,setDetail]=useState<PreviewVehicle|null>(null);
 const [compareOpen,setCompareOpen]=useState(false);
 const [notice,setNotice]=useState('');
 const [showAll,setShowAll]=useState(false);

 const results=useMemo(()=>previewVehicles.filter(v=>
  (!criteria.make||v.make===criteria.make)&&(!criteria.model||v.model===criteria.model)&&(!criteria.year||String(v.year)===criteria.year)&&(!criteria.body||v.bodyType===criteria.body)
 ),[criteria]);
 const displayed=showAll?results:results.slice(0,4);

 useEffect(()=>{
  if(!detail&&!compareOpen)return;
  function escape(e:KeyboardEvent){if(e.key==='Escape'){setDetail(null);setCompareOpen(false)}}
  window.addEventListener('keydown',escape);
  const old=document.body.style.overflow;document.body.style.overflow='hidden';
  return ()=>{window.removeEventListener('keydown',escape);document.body.style.overflow=old};
 },[detail,compareOpen]);
 useEffect(()=>{if(!notice)return;const t=window.setTimeout(()=>setNotice(''),7500);return ()=>window.clearTimeout(t)},[notice]);
 function search(c:Criteria){
  const p=new URLSearchParams();
  if(c.make)p.set('make',c.make);if(c.model)p.set('model',c.model);
  if(c.year){p.set('minYear',c.year);p.set('maxYear',c.year)}
  if(c.body)p.set('body',c.body);
  if(c.budget==='under2m')p.set('maxPrice','2000000');
  if(c.budget==='2to5m'){p.set('minPrice','2000000');p.set('maxPrice','5000000')}
  if(c.budget==='over5m')p.set('minPrice','5000000');
  window.location.hash='#/cars'+(p.toString()?'?'+p.toString():'');
 }
 function reset(){setCriteria(blankCriteria);setShowAll(false);setNotice('Example layout reset. Live inventory arrives with the catalogue integration.')}
 function toggleCompare(id:string){if(!toggleSelected(id))setNotice('Choose up to three example vehicles to compare.');}
 const compareVehicles=previewVehicles.filter(v=>selected.includes(v.id));
 return <div id="top">
  <a className="skip-link" href="#main">Skip to content</a>
  <div className="preview-notice"><span className="container"><Sparkles size={14} aria-hidden="true"/> CARFOLK LTD — INTERACTIVE DESIGN PREVIEW <span className="preview-notice__aside">Vehicles and imagery are illustrative; inventory is not live.</span></span></div>
  <Header/>
  <main id="main">
   <section id="home" className="hero" aria-labelledby="hero-title">
    <div className="hero__photo" role="img" aria-label="Atmospheric automotive photograph used for design demonstration"/>
    <div className="hero__grain" aria-hidden="true"/>
    <div className="container hero__inner">
     <div className="hero__copy"><div className="hero__eyebrow"><span/> WELCOME TO CARFOLK LTD</div>
      <h1 id="hero-title">Find Your Next Car<br/><em>With Confidence.</em></h1>
      <p>Quality &amp; Affordability. An easier, more enjoyable way to discover your next drive, explore your options, and take the next step.</p>
      <div className="hero__actions"><Button href="#/cars" icon={<CarFront size={18}/>}>Browse Our Cars</Button><Button href="#/contact?intent=test-drive" variant="outline" icon={<CalendarCheck size={18}/>}>Book a Viewing</Button></div>
      <div className="hero__subline"><span><CheckCircle2 size={16}/> Effortless browsing</span><span><CheckCircle2 size={16}/> Clear vehicle details</span><span><CheckCircle2 size={16}/> Human support</span></div>
     </div>
     <div className="hero__side-note"><span>THE ROAD AHEAD</span><strong>Starts here.</strong><ArrowDownRight size={24}/></div>
    </div>
   </section>

   <div className="container search-wrapper"><VehicleSearch onSearch={search} onReset={reset} resultCount={results.length}/></div>

   <section className="value-strip" aria-label="Why explore with Carfolk"><div className="container value-strip__grid">
    {values.map(v=><div className="value" key={v.title}><div className="value__icon">{v.icon}</div><div><h3>{v.title}</h3><p>{v.text}</p></div></div>)}
   </div></section>

   <section id="vehicles" className="section vehicles-section">
    <div className="container"><SectionHeading eyebrow="OUR SHOWROOM" title="Explore the possibilities." description="Beautifully presented vehicle listings, designed to make choosing easier."/>
     <div className="vehicle-section__meta"><span><Info size={17}/> <strong>Design showcase only:</strong> examples below do not represent Carfolk inventory or confirmed prices.</span><button type="button" onClick={()=>{window.location.hash='#/cars'}}>View all examples <ArrowRight size={17}/></button></div>
     <div className="vehicle-grid">
      {displayed.map(v=><VehicleCard key={v.id} vehicle={v} selected={selected.includes(v.id)} onCompare={()=>toggleCompare(v.id)} onDetails={()=>{window.location.hash='#/cars/'+v.id}}/>)}
     </div>
     {displayed.length===0&&<div className="vehicle-empty"><Search size={34}/><h3>No matching design examples</h3><p>Try another make, model, year or body type. Actual stock search will be available once inventory is connected.</p><Button onClick={reset}>Clear filters</Button></div>}
     <div className="vehicles-foot"><p>Vehicle images and sample details in this preview are illustrative. Actual information will be published by Carfolk after verification.</p><a href="#contact" className="text-link">Need something specific? Contact us <ArrowUpRight size={17}/></a></div>
    </div>
   </section>

   <section id="compare" className="compare-teaser"><div className="container compare-teaser__inner">
    <div className="compare-teaser__mark"><GitCompareArrows size={34}/></div>
    <div><span className="eyebrow">MAKE A MORE INFORMED CHOICE</span><h2>Compare the details. Find your fit.</h2><p>Select up to three example vehicle cards above to try the comparison experience.</p></div>
    <Button variant="outline" onClick={()=>{if(selected.length>=2)window.location.hash='#/compare';else{setNotice('Select at least two sample vehicles using the Compare buttons above.');document.getElementById('vehicles')?.scrollIntoView({behavior:'smooth'})}}} icon={<ArrowUpRight size={18}/>}>Compare examples {selected.length? '('+selected.length+')':''}</Button>
   </div></section>

   <FinanceEstimate/>

   <section id="about" className="about section"><div className="container about__grid">
    <div className="about__image"><img loading="lazy" src="https://images.unsplash.com/photo-1507136566006-cfc505b114fc?auto=format&fit=crop&w=1200&q=85" alt="Illustrative photograph of automotive craftsmanship"/><div className="about__image-label"><Sparkles size={18}/> QUALITY &amp; AFFORDABILITY</div></div>
    <div className="about__copy"><span className="eyebrow eyebrow--red">GET TO KNOW CARFOLK</span><h2>Driven by a better way to buy.</h2><p className="about__lead">Carfolk LTD believes finding a vehicle should feel exciting — not complicated.</p><p>Our digital showroom is designed to put useful information first, simplify discovery and make it easy to take the next step when you see something you love.</p>
      <div className="about__qualities"><div><BadgeCheck size={25}/><span><strong>Thoughtful selection</strong><small>Explore options at your pace.</small></span></div><div><Heart size={25}/><span><strong>Buyer-friendly experience</strong><small>Designed around real decisions.</small></span></div></div>
      <Button href="#contact" icon={<ArrowUpRight size={18}/>}>Learn More About Carfolk</Button>
    </div>
   </div></section>

   <section className="steps-section section"><div className="container"><SectionHeading eyebrow="THE JOURNEY" title="Your next car, made simpler." description="From the first look to the first conversation, every step should feel effortless."/><div className="steps-grid">{steps.map((s,i)=><div className="step" key={s.number}><span>{s.number}</span><h3>{s.title}</h3><p>{s.text}</p>{i<steps.length-1&&<ChevronRight className="step__arrow" size={22} aria-hidden="true"/>}</div>)}</div></div></section>

   <section id="contact" className="contact"><div className="container contact__grid">
    <div><span className="eyebrow">LET'S GET YOU MOVING</span><h2>See something you love?<br/><em>Let's talk cars.</em></h2><p>Have a question about a vehicle or want to arrange a viewing? Carfolk's official contact channels will be linked here as soon as verified business details are provided.</p><div className="contact__actions"><a className="button button--primary" href="#/contact"><MessageCircle size={18}/> Contact Carfolk <ArrowUpRight size={18}/></a><a className="button button--line-light" href="#/contact?intent=test-drive"><CalendarCheck size={18}/> Request a Viewing</a></div><p className="contact__disclaimer"><Info size={15}/> Contact and booking submissions activate when official details and the backend are connected.</p></div>
    <div className="contact__card"><span className="contact__card-overline">THE CARFOLK PROMISE</span><div className="contact__card-logo"><Brand/></div><h3>Quality &amp; Affordability</h3><p>A better car-search experience begins with clarity, useful choices and a conversation when you're ready.</p><div className="contact__card-detail"><MapPin size={18}/><span>Dealership location to be confirmed</span></div><div className="contact__card-detail"><Clock3 size={18}/><span>Operating hours to be confirmed</span></div></div>
   </div></section>
  </main>

  <footer className="footer"><div className="container footer__top"><div className="footer__brand"><Brand/><p>Quality &amp; Affordability.<br/>The road to your next vehicle starts here.</p></div><div><h4>Explore</h4><a href="#/">Home</a><a href="#/cars">Our Vehicles</a><a href="#/compare">Compare Cars</a><a href="#/finance">Financing</a></div><div><h4>Carfolk</h4><a href="#/about">About Us</a><a href="#/contact">Contact</a><a href="#/contact?intent=test-drive">Book a Viewing</a><a href="#/privacy">Privacy Notice</a></div><div className="footer__note"><h4>Showroom Preview</h4><p>Vehicle photographs and specifications on this page are illustrative. They do not represent verified Carfolk LTD inventory.</p></div></div><div className="container footer__bottom"><span>© {new Date().getFullYear()} Carfolk LTD. All rights reserved.</span><span>Designed around Quality &amp; Affordability.</span></div></footer>

  {selected.length>0&&<div className="comparison-tray" role="status"><div className="container comparison-tray__inner"><div><GitCompareArrows size={19}/><strong>{selected.length} / 3</strong> chosen <span>for comparison</span></div><div className="comparison-tray__names">{compareVehicles.map(v=><span key={v.id}>{v.make} {v.model}<button aria-label={'Remove '+v.name} onClick={()=>toggleCompare(v.id)}><X size={13}/></button></span>)}</div><div className="comparison-tray__actions"><button type="button" className="text-button" onClick={()=>clear()}>Clear</button><button type="button" className="button button--primary" disabled={selected.length<2} onClick={()=>setCompareOpen(true)}>Compare now <ArrowRight size={16}/></button></div></div></div>}

  {detail&&<div className="modal-backdrop" onMouseDown={e=>{if(e.target===e.currentTarget)setDetail(null)}}><div className="modal" role="dialog" aria-modal="true" aria-labelledby="detail-title"><button className="modal__close" type="button" aria-label="Close vehicle preview" onClick={()=>setDetail(null)}><X/></button><img className="modal__image" src={detail.image} alt={'Example image for '+detail.name}/><div className="modal__body"><span className="eyebrow eyebrow--red">ILLUSTRATIVE SHOWCASE ONLY</span><h2 id="detail-title">{detail.name}</h2><p>This is an example of how a future vehicle page may appear. Carfolk has not listed this vehicle, and no price or availability is confirmed.</p><div className="modal__specs"><span>Make <b>{detail.make}</b></span><span>Model <b>{detail.model}</b></span><span>Year <b>{detail.year} (example)</b></span><span>Body <b>{detail.bodyType}</b></span></div><Button href="#contact" onClick={()=>setDetail(null)} icon={<ArrowUpRight size={16}/>}>Contact Carfolk</Button></div></div></div>}
  {compareOpen&&<div className="modal-backdrop" onMouseDown={e=>{if(e.target===e.currentTarget)setCompareOpen(false)}}><div className="modal modal--compare" role="dialog" aria-modal="true" aria-labelledby="compare-title"><button type="button" className="modal__close" aria-label="Close comparison" onClick={()=>setCompareOpen(false)}><X/></button><div className="modal__body"><span className="eyebrow eyebrow--red">DESIGN EXAMPLES</span><h2 id="compare-title">Compare your shortlist</h2><p>This preview compares illustrative model details only. Actual comparison will use published Carfolk inventory.</p><div className="compare-table"><div className="compare-table__row compare-table__row--head"><strong>Specification</strong>{compareVehicles.map(v=><strong key={v.id}>{v.name}</strong>)}</div>{(['make','model','year','bodyType'] as const).map(key=><div className="compare-table__row" key={key}><span>{key==='bodyType'?'Body type':key.charAt(0).toUpperCase()+key.slice(1)}</span>{compareVehicles.map(v=><span key={v.id}>{v[key]}</span>)}</div>)}<div className="compare-table__row"><span>Price</span>{compareVehicles.map(v=><span key={v.id}>Unconfirmed</span>)}</div></div><Button onClick={()=>setCompareOpen(false)}>Close comparison</Button></div></div></div>}
  {notice&&<div className="toast" role="status"><Info size={18}/><span>{notice}</span><button aria-label="Dismiss notification" onClick={()=>setNotice('')}><X size={17}/></button></div>}
 </div>;
}
