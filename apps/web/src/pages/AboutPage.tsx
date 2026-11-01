import {ArrowRight,ArrowUpRight,CarFront,ChevronRight,HeartHandshake,MessageCircle,ShieldCheck,SlidersHorizontal,Sparkles} from 'lucide-react';
import {Shell} from '../components/Shell';

const principles=[
 {icon:<ShieldCheck size={27}/>,title:'Clarity at every step',body:'Vehicle information should be easy to understand, without making the buyer work for the essentials.'},
 {icon:<SlidersHorizontal size={27}/>,title:'Choices made simpler',body:'Useful search, comparison, and financing tools should help you narrow down the right options.'},
 {icon:<HeartHandshake size={27}/>,title:'A human conversation',body:'A clear path from interest to enquiry makes it easier to connect with the dealership.'},
];
export function AboutPage(){
 return <Shell>
  <section className="about-hero">
   <div className="container about-hero__layout">
    <div><nav className="catalogue-breadcrumb" aria-label="Breadcrumb"><a href="#/">Home</a><ChevronRight size={14}/> About Carfolk</nav>
      <span className="eyebrow">THE PEOPLE BEHIND THE JOURNEY</span>
      <h1>More than a car.<br/><em>A better way forward.</em></h1>
      <p>Carfolk LTD — Quality &amp; Affordability. Explore the thinking behind an automotive experience built around clarity and confident decisions.</p>
      <a href="#/cars" className="button button--primary">Explore vehicle options <ArrowUpRight size={17}/></a>
    </div>
    <div className="about-hero__graphic"><div className="about-hero__graphic-label"><Sparkles size={21}/> CARFOLK LTD</div><span>QUALITY &amp; AFFORDABILITY</span></div>
   </div>
  </section>

  <section className="container about-story">
   <div><span className="eyebrow eyebrow--red">OUR APPROACH</span><h2>Finding your next vehicle should feel exciting, not overwhelming.</h2></div>
   <div><p>Carfolk LTD's online showroom is designed to simplify the way buyers explore vehicles. Clear specifications, useful comparisons, and simple ways to reach out help turn research into a conversation.</p>
   <a className="text-link" href="#/contact">Talk to Carfolk <ArrowRight size={17}/></a></div>
  </section>

  <section className="about-values">
   <div className="container"><div className="section-heading"><div><span className="eyebrow eyebrow--red">WHAT GUIDES THE EXPERIENCE</span><h2>Built for better decisions.</h2></div><p>Thoughtful tools make it easier to consider the options and ask the right questions.</p></div>
    <div className="about-values__grid">{principles.map(p=><article key={p.title} className="about-value"><div className="about-value__icon">{p.icon}</div><h3>{p.title}</h3><p>{p.body}</p></article>)}</div>
   </div>
  </section>

  <section className="container about-cta"><div className="about-cta__inner"><div><span className="eyebrow eyebrow--red">LET'S CONNECT</span><h2>Your next chapter starts with a conversation.</h2><p>Explore the cars, or send an enquiry through our customer contact experience.</p></div><div><a className="button button--primary" href="#/cars"><CarFront size={18}/> Browse vehicles</a><a className="button button--outline" href="#/contact"><MessageCircle size={18}/> Contact us</a></div></div></section>
 </Shell>
}
