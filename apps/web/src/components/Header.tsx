import {useEffect,useState} from 'react';
import {ArrowUpRight,Menu,X} from 'lucide-react';
import {Brand} from './Brand';
const links=[{label:'Home',href:'#home'},{label:'Cars for Sale',href:'#vehicles'},{label:'Compare Cars',href:'#compare'},{label:'Financing',href:'#financing'},{label:'About Us',href:'#about'},{label:'Contact',href:'#contact'}];
export function Header(){
  const [open,setOpen]=useState(false);
  useEffect(()=>{const close=()=>setOpen(false);window.addEventListener('resize',close);return()=>window.removeEventListener('resize',close)},[]);
  return <header className="header"><div className="container header__inner">
    <Brand/>
    <nav id="site-nav" aria-label="Main navigation" className={open?'nav nav--open':'nav'}>
      {links.map((link,i)=><a className={i===0?'nav__active':''} key={link.href} href={link.href} onClick={()=>setOpen(false)}>{link.label}</a>)}
    </nav>
    <a className="header__cta" href="#contact">Talk to us <ArrowUpRight size={16} aria-hidden="true"/></a>
    <button className="menu" type="button" aria-label={open?'Close navigation':'Open navigation'} aria-expanded={open} aria-controls="site-nav" onClick={()=>setOpen(s=>!s)}>{open?<X size={24}/>:<Menu size={24}/>}</button>
  </div></header>
}
