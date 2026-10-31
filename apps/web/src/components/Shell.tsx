import {ArrowUpRight,GitCompareArrows,Sparkles,X} from 'lucide-react';
import type {ReactNode} from 'react';
import {Header} from './Header';
import {Brand} from './Brand';
import {useCompare} from '../state/CompareContext';
import {DEMO_CARS} from '../data/showroom';
export function Shell({children}:{children:ReactNode}){
 const {selected,clear,remove}=useCompare();
 const names=DEMO_CARS.filter(v=>selected.includes(v.id));
 return <div id="top">
  <a className="skip-link" href="#main">Skip to content</a>
  <div className="preview-notice"><span className="container"><Sparkles size={14}/> CARFOLK LTD — DESIGN PREVIEW <span className="preview-notice__aside">Listings and prices are illustrative. Not actual inventory.</span></span></div>
  <Header/>
  <main id="main">{children}</main>
  <footer className="footer"><div className="container footer__top"><div className="footer__brand"><Brand/><p>Quality &amp; Affordability.<br/>Discover your next drive with confidence.</p></div><div><h4>Explore</h4><a href="#/">Home</a><a href="#/cars">Our Vehicles</a><a href="#/compare">Compare Cars</a><a href="#/finance">Financing</a></div><div><h4>Carfolk</h4><a href="#/about">About Us</a><a href="#/contact">Contact</a><a href="#/contact">Book a Viewing</a></div><div className="footer__note"><h4>Preview Notice</h4><p>Automotive photos, vehicle specifications and sample prices are for UI evaluation only and are not representations of Carfolk LTD stock.</p></div></div><div className="container footer__bottom"><span>© {new Date().getFullYear()} Carfolk LTD.</span><span>Quality &amp; Affordability.</span></div></footer>
  {selected.length>0&&<div className="comparison-tray" role="status"><div className="container comparison-tray__inner"><div><GitCompareArrows size={19}/><strong>{selected.length} / 3</strong> chosen <span>for comparison</span></div><div className="comparison-tray__names">{names.map(v=><span key={v.id}>{v.make} {v.model}<button type="button" aria-label={'Remove '+v.make+' '+v.model} onClick={()=>remove(v.id)}><X size={13}/></button></span>)}</div><div className="comparison-tray__actions"><button className="text-button" type="button" onClick={clear}>Clear</button><a aria-disabled={selected.length<2} className={'button button--primary'+(selected.length<2?' catalogue-disabled':'')} href={selected.length>=2?'#/compare':'#/cars'}>{selected.length>=2?'Compare now':'Add another car'} <ArrowUpRight size={16}/></a></div></div></div>}
 </div>;
}
