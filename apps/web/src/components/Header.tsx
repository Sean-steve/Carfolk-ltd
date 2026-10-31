import {useState} from 'react';
import {Menu,X} from 'lucide-react';
import {Brand} from './Brand';
const links=[{label:'Foundations',href:'#foundations'},{label:'Components',href:'#components'},{label:'Typography',href:'#typography'},{label:'Next Phase',href:'#next'}];
export function Header(){const[open,setOpen]=useState(false);return <header className="header"><div className="container header__inner"><Brand/><nav id="site-nav" aria-label="Design system navigation" className={open?'nav nav--open':'nav'}>{links.map(l=><a key={l.href} href={l.href} onClick={()=>setOpen(false)}>{l.label}</a>)}</nav><button className="menu" type="button" aria-label={open?'Close menu':'Open menu'} aria-expanded={open} aria-controls="site-nav" onClick={()=>setOpen(!open)}>{open?<X/>:<Menu/>}</button></div></header>}
