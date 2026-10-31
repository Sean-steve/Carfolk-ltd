import {ArrowUpRight,GitCompareArrows,Info,X} from 'lucide-react';
import type {ReactNode} from 'react';
import {Header} from './Header';
import {Brand} from './Brand';
import {useCompare} from '../state/CompareContext';
import {useSite} from '../api/hooks';
export function Shell({children}:{children:ReactNode}){
 const {selected,vehicles,clear,remove}=useCompare();
 const site=useSite();
 const offline=!site.ready||!!site.error;
 return <div id="top">
  <a className="skip-link" href="#main">Skip to content</a>
  {offline&&<div className="preview-notice"><span className="container"><Info size={14}/> CARFOLK SHOWROOM — LIVE DATA NOT CONNECTED <span className="preview-notice__aside">No demonstration stock or enquiries are displayed.</span></span></div>}
  <Header/>
  <main id="main">{children}</main>
  <footer className="footer"><div className="container footer__top"><div className="footer__brand"><Brand/><p>{site.data?.tagline??'Quality & Affordability'}<br/>Browse verified dealership inventory.</p></div>
   <div><h4>Explore</h4><a href="#/">Home</a><a href="#/cars">Vehicles</a><a href="#/compare">Compare Cars</a><a href="#/finance">Financing</a></div>
   <div><h4>Carfolk</h4><a href="#/about">About Us</a><a href="#/contact">Contact</a><a href="#/contact?intent=test-drive">Request a Viewing</a><a href="#/privacy">Privacy</a></div>
   <div className="footer__note"><h4>Carfolk LTD</h4><p>Availability is subject to confirmation. Viewing requests are not appointments until accepted by the dealership.</p></div>
  </div><div className="container footer__bottom"><span>© {new Date().getFullYear()} Carfolk LTD.</span><span>Quality &amp; Affordability.</span></div></footer>
  {selected.length>0&&<div className="comparison-tray" role="status"><div className="container comparison-tray__inner">
   <div><GitCompareArrows size={19}/><strong>{selected.length} / 3</strong> chosen <span>for comparison</span></div>
   <div className="comparison-tray__names">{selected.map(id=>{const v=vehicles.find(car=>car.id===id);return <span key={id}>{v?v.make+' '+v.model:'Loading…'}<button type="button" aria-label="Remove vehicle from comparison" onClick={()=>remove(id)}><X size={13}/></button></span>})}</div>
   <div className="comparison-tray__actions"><button className="text-button" type="button" onClick={clear}>Clear</button>
    <a className={'button button--primary'+(selected.length<2?' catalogue-disabled':'')} href={selected.length>=2?'#/compare':'#/cars'}>{selected.length>=2?'Compare now':'Add another car'} <ArrowUpRight size={16}/></a></div>
  </div></div>}
 </div>;
}
