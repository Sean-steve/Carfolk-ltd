import {useState} from 'react';
import {ArrowUpRight,Menu,X} from 'lucide-react';
import {Brand} from './Brand';
const links=[
 {label:'Home',href:'#/'},
 {label:'Cars for Sale',href:'#/cars'},
 {label:'Compare Cars',href:'#/compare'},
 {label:'Financing',href:'#/finance'},
 {label:'About Us',href:'#/about'},
 {label:'Contact',href:'#/contact'},
];
export function Header(){
 const [open,setOpen]=useState(false);
 const current=window.location.hash;
 return <header className="header"><div className="container header__inner">
   <Brand/>
   <nav id="site-nav" aria-label="Main navigation" className={open?'nav nav--open':'nav'}>
    {links.map((link,i)=><a className={(current===link.href||(!current&&i===0))?'nav__active':''} key={link.href} href={link.href} onClick={()=>setOpen(false)}>{link.label}</a>)}
   </nav>
   <a className="header__cta" href="#/contact">Talk to us <ArrowUpRight size={16} aria-hidden="true"/></a>
   <button className="menu" type="button" aria-label={open?'Close navigation':'Open navigation'} aria-expanded={open} aria-controls="site-nav" onClick={()=>setOpen(s=>!s)}>{open?<X size={24}/>:<Menu size={24}/>}</button>
 </div></header>
}
