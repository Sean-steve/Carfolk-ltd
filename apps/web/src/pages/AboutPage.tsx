import {ArrowRight,ArrowUpRight,CarFront,CheckCircle2,ChevronRight,Compass,HeartHandshake,MessageCircle,ShieldCheck,SlidersHorizontal,Sparkles} from 'lucide-react';
import {Shell} from '../components/Shell';

const principles=[
 {icon:<ShieldCheck size={27}/>,title:'Clarity at every step',body:'Vehicle information should be easy to understand, without making the buyer work for the essentials.'},
 {icon:<SlidersHorizontal size={27}/>,title:'Choices made simpler',body:'Useful search, comparison, and financing tools should help you narrow down the right options.'},
 {icon:<HeartHandshake size={27}/>,title:'A human conversation',body:'A clear path from interest to enquiry makes it easier to connect with the dealership.'},
];
const journey=[
 {number:'01',title:'Browse available choices',body:'Search by make, model, price, year and vehicle type.'},
 {number:'02',title:'Explore the details',body:'Review images, specifications and the important buying considerations.'},
 {number:'03',title:'Compare your options',body:'Put up to three vehicles side by side and decide what matters.'},
 {number:'04',title:'Get in touch',body:'Request more information or choose a preferred viewing date.'},
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
   <p>As the platform develops, the dealership will provide its verified stock details, operating information, and contact channels. Nothing in this preview should be taken as a claim about current inventory, dealer history or inspection guarantees.</p>
   <a className="text-link" href="#/contact">Talk to Carfolk <ArrowRight size={17}/></a></div>
  </section>

  <section className="about-values">
   <div className="container"><div className="section-heading"><div><span className="eyebrow eyebrow--red">WHAT GUIDES THE EXPERIENCE</span><h2>Built for better decisions.</h2></div><p>Thoughtful tools make it easier to consider the options and ask the right questions.</p></div>
    <div className="about-values__grid">{principles.map(p=><article key={p.title} className="about-value"><div className="about-value__icon">{p.icon}</div><h3>{p.title}</h3><p>{p.body}</p></article>)}</div>
   </div>
  </section>

  <section className="container about-journey">
   <div className="section-heading"><div><span className="eyebrow eyebrow--red">HOW IT WORKS</span><h2>A simpler buying journey.</h2></div><p>Discover, explore, compare and connect — all from a cohesive showroom experience.</p></div>
   <div className="about-journey__grid">{journey.map(step=><article key={step.number}><span>{step.number}</span><h3>{step.title}</h3><p>{step.body}</p></article>)}</div>
  </section>

  <section className="about-essentials">
   <div className="container about-essentials__grid">
    <div><span className="eyebrow">THE RIGHT INFORMATION</span><h2>Know what matters before you decide.</h2><p>Our vehicle detail pages are structured to make key questions easier to ask — from mileage and condition to financing estimates and available viewing options.</p><ul><li><CheckCircle2 size={17}/> Detailed vehicle specifications</li><li><CheckCircle2 size={17}/> Clear pricing and availability indicators once verified</li><li><CheckCircle2 size={17}/> Comparison and affordability planning</li></ul></div>
    <div className="about-essentials__accent"><Compass size={70}/><div><strong>Find your next direction.</strong><span>QUALITY &amp; AFFORDABILITY</span></div></div>
   </div>
  </section>

  <section className="container about-cta"><div className="about-cta__inner"><div><span className="eyebrow eyebrow--red">LET'S CONNECT</span><h2>Your next chapter starts with a conversation.</h2><p>Explore the cars, or send an enquiry through our customer contact experience.</p></div><div><a className="button button--primary" href="#/cars"><CarFront size={18}/> Browse vehicles</a><a className="button button--outline" href="#/contact"><MessageCircle size={18}/> Contact us</a></div></div></section>
 </Shell>
}
