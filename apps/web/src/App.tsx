import {useEffect,useState} from 'react';
import HomePage from './pages/HomePage';
import {CataloguePage} from './pages/CataloguePage';
import {VehicleDetailPage} from './pages/VehicleDetailPage';
import {ComparePage} from './pages/ComparePage';
import {FinancingPage} from './pages/FinancingPage';
import {Shell} from './components/Shell';
import {currentRoute} from './lib/route';
import {CompareProvider} from './state/CompareContext';
import {ArrowRight} from 'lucide-react';

function Router(){
 const [hash,setHash]=useState(()=>window.location.hash);
 useEffect(()=>{const update=()=>setHash(window.location.hash);window.addEventListener('hashchange',update);return()=>window.removeEventListener('hashchange',update)},[]);
 const route=currentRoute();
 useEffect(()=>{
  if(route.name==='about'||route.name==='contact'){
   requestAnimationFrame(()=>document.getElementById(route.name)?.scrollIntoView({block:'start',behavior:'instant'}));
  }else if(route.name==='home'){
   if(!hash||hash==='#/'||hash==='#/home')window.scrollTo(0,0);
  }else window.scrollTo(0,0);
 },[route.name,route.slug]);
 switch(route.name){
  case 'home':case 'about':case 'contact':return <HomePage/>;
  case 'cars':return <CataloguePage query={route.query}/>;
  case 'detail':return <VehicleDetailPage slug={route.slug??''}/>;
  case 'compare':return <ComparePage/>;
  case 'finance':return <FinancingPage query={route.query}/>;
  default:return <Shell><section className="vehicle-not-found container"><h1>We couldn't find that page.</h1><p>Explore the Carfolk showroom design and sample vehicles.</p><a className="button button--primary" href="#/cars">Browse vehicles <ArrowRight size={16}/></a></section></Shell>;
 }
}
export default function App(){return <CompareProvider><Router/></CompareProvider>}
