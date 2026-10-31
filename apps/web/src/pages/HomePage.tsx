import {useState,type FormEvent} from 'react';
import {ArrowDownRight,ArrowRight,ArrowUpRight,CalendarCheck,CarFront,CheckCircle2,GitCompareArrows,Headphones,Info,MessageCircle,Search,ShieldCheck,SlidersHorizontal} from 'lucide-react';
import {Shell} from '../components/Shell';
import {ListingCard} from '../components/ListingCard';
import {useOptions,useVehicles} from '../api/hooks';
function Heading({eyebrow,title,description}:{eyebrow:string;title:string;description:string}){
 return <div className="section-heading"><div><span className="eyebrow eyebrow--red">{eyebrow}</span><h2>{title}</h2></div><p>{description}</p></div>;
}
const values=[
 {icon:<ShieldCheck size={28}/>,title:'Quality at the forefront',text:'Clear details and a thoughtful buying journey.'},
 {icon:<SlidersHorizontal size={28}/>,title:'Find what fits',text:'Filter verified listings by budget, model, and year.'},
 {icon:<CalendarCheck size={28}/>,title:'Viewing requests',text:'Request a suitable time to inspect your choice.'},
 {icon:<Headphones size={28}/>,title:'Dealership support',text:'Ask Carfolk for up-to-date vehicle information.'},
];
export default function HomePage(){
 const {data,loading,error,ready}=useVehicles({page:1});
 const opts=useOptions();
 const [make,setMake]=useState(''),[model,setModel]=useState(''),[budget,setBudget]=useState(''),[body,setBody]=useState('');
 const [notice,setNotice]=useState('');
 const cars=data?.data.slice(0,4)??[];
 const models=make?opts.data?.models_by_make[make]??[]:Object.values(opts.data?.models_by_make??{}).flat();
 function go(e:FormEvent){e.preventDefault();const p=new URLSearchParams();
  if(make)p.set('make',make);if(model)p.set('model',model);if(body)p.set('body',body);
  if(budget==='under2m')p.set('maxPrice','2000000');
  if(budget==='2to5m'){p.set('minPrice','2000000');p.set('maxPrice','5000000');}
  if(budget==='over5m')p.set('minPrice','5000000');
  window.location.hash='#/cars'+(p.size?'?'+p.toString():'');
 }
 return <Shell>
  <section className="hero" aria-labelledby="hero-title"><div className="hero__photo" role="img" aria-label="Decorative automotive scene"/><div className="hero__grain"/>
   <div className="container hero__inner"><div className="hero__copy">
    <div className="hero__eyebrow"><span/> WELCOME TO CARFOLK LTD</div>
    <h1 id="hero-title">Find Your Next Car<br/><em>With Confidence.</em></h1>
    <p>Quality &amp; Affordability. Browse Carfolk's published vehicles, compare your options and get in touch to arrange a viewing.</p>
    <div className="hero__actions"><a className="button button--primary" href="#/cars"><CarFront size={18}/> Browse Our Cars</a><a className="button button--outline" href="#/contact?intent=test-drive"><CalendarCheck size={18}/> Request a Viewing</a></div>
    <div className="hero__subline"><span><CheckCircle2 size={16}/> Dealership stock</span><span><CheckCircle2 size={16}/> Clear listings</span><span><CheckCircle2 size={16}/> Direct enquiries</span></div>
   </div><div className="hero__side-note"><span>THE ROAD AHEAD</span><strong>Starts here.</strong><ArrowDownRight size={24}/></div></div>
  </section>
  <div className="container search-wrapper"><div className="search-dock"><div className="search-dock__tabs"><button className="active" type="button"><CarFront size={18}/> Find your vehicle</button></div>
   <form className="search-dock__form" onSubmit={go}><div className="search-dock__fields">
    <label>Make<select value={make} onChange={e=>{setMake(e.target.value);setModel('')}}><option value="">Any make</option>{(opts.data?.makes??[]).map(x=><option key={x}>{x}</option>)}</select></label>
    <label>Model<select value={model} onChange={e=>setModel(e.target.value)}><option value="">Any model</option>{[...new Set(models)].sort().map(x=><option key={x}>{x}</option>)}</select></label>
    <label>Budget<select value={budget} onChange={e=>setBudget(e.target.value)}><option value="">Any price</option><option value="under2m">Below KES 2M</option><option value="2to5m">KES 2M – 5M</option><option value="over5m">Over KES 5M</option></select></label>
    <label>Body style<select value={body} onChange={e=>setBody(e.target.value)}><option value="">Any type</option>{(opts.data?.bodies??[]).map(x=><option key={x}>{x}</option>)}</select></label>
   </div><button className="search-dock__submit" type="submit"><Search size={17}/> Search cars</button></form>
   <div className="search-dock__hint"><span><SlidersHorizontal size={14}/> {ready&&!error?'Searching Carfolk published inventory':'Live inventory is not connected on this preview.'}</span><a className="text-link" href="#/cars">Advanced filters <ArrowRight size={15}/></a></div>
  </div></div>
  <section className="value-strip"><div className="container value-strip__grid">{values.map(v=><div className="value" key={v.title}><span className="value__icon">{v.icon}</span><div><h3>{v.title}</h3><p>{v.text}</p></div></div>)}</div></section>
  <section className="section" id="vehicles"><div className="container">
   <Heading eyebrow="CARFOLK SHOWROOM" title="Featured vehicles." description="Freshly published dealership inventory, with current price and availability information."/>
   <div className="vehicle-section__meta"><span><Info size={17}/> {data?data.meta.total+' published vehicle'+(data.meta.total===1?'':'s'):'Inventory status'}</span><a className="text-link" href="#/cars">View all vehicles <ArrowRight size={17}/></a></div>
   {loading?<div className="vehicle-empty" role="status"><h3>Loading dealership inventory…</h3></div>:
    !ready||error?<div className="vehicle-empty"><Info size={32}/><h3>Live inventory not yet available</h3><p>{error??'The live Carfolk API has not been connected to this website preview.'} No sample stock is shown.</p></div>:
    cars.length?<div className="catalogue-listing-grid">{cars.map(car=><ListingCard key={car.id} car={car} onLimit={()=>setNotice('A maximum of three cars can be compared.')}/>)}</div>:
    <div className="vehicle-empty"><CarFront size={34}/><h3>No published vehicles yet</h3><p>Carfolk's inventory will appear here after the dealership adds and publishes listings.</p></div>}
   <div className="vehicles-foot"><p>Vehicle availability is subject to confirmation directly with Carfolk.</p><a className="text-link" href="#/contact">Ask about a vehicle <ArrowUpRight size={17}/></a></div>
  </div></section>
  <section className="compare-teaser"><div className="container compare-teaser__inner"><div className="compare-teaser__mark"><GitCompareArrows size={34}/></div><div><span className="eyebrow eyebrow--red">SHOP SMARTER</span><h2>Compare the details. Find your fit.</h2><p>Choose up to three published vehicles and compare their real specifications.</p></div><a href="#/compare" className="button button--primary">Compare cars <ArrowUpRight size={17}/></a></div></section>
  <section className="finance-box"><div className="finance-box__visual"><div className="finance-box__dark"><span className="eyebrow">KNOW YOUR NUMBERS</span><h2>Plan your next<br/>move confidently.</h2><p>Explore an illustrative monthly repayment estimate. This is not an offer or a loan approval.</p><a className="button button--primary" href="#/finance">Estimate monthly payments <ArrowRight size={16}/></a></div></div><div className="finance-box__details"><h3>Vehicle finance planning</h3><p>Change the deposit, interest assumption, and repayment term. Check your eligibility and lender terms independently.</p><ul><li>Deposit scenarios</li><li>Transparent repayment illustration</li><li>No credit application required</li></ul></div></section>
  <section className="section about" id="about"><div className="container about__grid"><div className="about__image"><img src="https://images.unsplash.com/photo-1486496572940-2bb2341fdbdf?auto=format&fit=crop&w=1100&q=85" alt="Decorative automotive photograph"/></div><div className="about__copy"><span className="eyebrow eyebrow--red">ABOUT CARFOLK LTD</span><h2>Your next car, with clearer choices.</h2><p className="about__lead">Quality &amp; Affordability.</p><p>Carfolk's digital showroom helps customers discover vehicles, review specifications, compare options and make enquiries directly with the dealership.</p><a className="button button--outline" href="#/about">Discover more <ArrowUpRight size={17}/></a></div></div></section>
  <section className="steps-section section"><div className="container"><Heading eyebrow="YOUR JOURNEY" title="From browsing to buying." description="A straightforward way to research, inspect and contact the dealership."/><div className="steps-grid">
   {[['01','Explore','Browse published vehicle listings.'],['02','Compare','Check prices and specifications.'],['03','Enquire','Ask Carfolk for details and current availability.'],['04','Visit','Request a viewing and wait for confirmation.']].map(s=><article className="step" key={s[0]}><span>{s[0]}</span><h3>{s[1]}</h3><p>{s[2]}</p></article>)}
  </div></div></section>
  <section className="contact"><div className="container contact__grid"><div><span className="eyebrow">GET IN TOUCH</span><h2>Ready to find <em>your next drive?</em></h2><p>Reach Carfolk to discuss availability, ask questions, or request a viewing.</p><div className="contact__actions"><a className="button button--primary" href="#/contact"><MessageCircle size={18}/> Contact Carfolk</a><a className="button button--line-light" href="#/contact?intent=test-drive"><CalendarCheck size={18}/> Request a viewing</a></div></div><div className="contact__card"><span className="contact__card-overline">CARFOLK LTD</span><h3>Quality &amp; Affordability</h3><p>Explore inventory and contact the dealership directly. Appointments require confirmation.</p></div></div></section>
  {notice&&<div className="toast" role="status"><Info size={18}/><span>{notice}</span><button onClick={()=>setNotice('')} aria-label="Dismiss"><ArrowRight size={16}/></button></div>}
 </Shell>;
}
