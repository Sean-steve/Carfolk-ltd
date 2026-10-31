import {useEffect,useState} from 'react';
import HomePage from './pages/HomePage';
import {CataloguePage} from './pages/CataloguePage';
import {VehicleDetailPage} from './pages/VehicleDetailPage';
import {ComparePage} from './pages/ComparePage';
import {FinancingPage} from './pages/FinancingPage';
import {AboutPage} from './pages/AboutPage';
import {ContactPage} from './pages/ContactPage';
import {PrivacyPage} from './pages/PrivacyPage';
import {Shell} from './components/Shell';
import {currentRoute} from './lib/route';
import {CompareProvider} from './state/CompareContext';
import {SiteProvider} from './api/hooks';
import {ArrowRight} from 'lucide-react';
function Router(){
 const [hash,setHash]=useState(()=>window.location.hash);
 useEffect(()=>{const update=()=>setHash(window.location.hash);window.addEventListener('hashchange',update);return()=>window.removeEventListener('hashchange',update)},[]);
 const route=currentRoute();
 useEffect(()=>{
  if(route.name==='contact'&&route.query)requestAnimationFrame(()=>document.getElementById('enquiry-form')?.scrollIntoView({block:'start'}));
  else window.scrollTo({top:0,behavior:'instant'});
 },[hash,route.name,route.query]);
 switch(route.name){
  case 'home':return <HomePage/>;
  case 'cars':return <CataloguePage query={route.query}/>;
  case 'detail':return <VehicleDetailPage slug={route.slug??''}/>;
  case 'compare':return <ComparePage/>;
  case 'finance':return <FinancingPage query={route.query}/>;
  case 'about':return <AboutPage/>;
  case 'contact':return <ContactPage query={route.query}/>;
  case 'privacy':return <PrivacyPage/>;
  default:return <Shell><section className="vehicle-not-found container"><h1>Page not found</h1><p>Explore the Carfolk showroom.</p><a className="button button--primary" href="#/cars">Browse vehicles <ArrowRight size={16}/></a></section></Shell>;
 }
}
export default function App(){return <SiteProvider><CompareProvider><Router/></CompareProvider></SiteProvider>}
