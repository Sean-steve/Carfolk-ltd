import type { AnchorHTMLAttributes, ButtonHTMLAttributes, ReactNode } from 'react';
type Style='primary'|'outline'|'ghost';
type Common={children:ReactNode;variant?:Style;icon?:ReactNode;className?:string};
type LinkProps=Common&AnchorHTMLAttributes<HTMLAnchorElement>&{href:string};
type BtnProps=Common&ButtonHTMLAttributes<HTMLButtonElement>&{href?:never};
export function Button(props:LinkProps|BtnProps){const {children,variant='primary',icon,className='',...rest}=props;const classes=['button','button--'+variant,className].filter(Boolean).join(' ');if('href' in rest&&typeof rest.href==='string'){return <a {...rest as AnchorHTMLAttributes<HTMLAnchorElement>} className={classes}>{children}{icon}</a>}return <button type="button" {...rest as ButtonHTMLAttributes<HTMLButtonElement>} className={classes}>{children}{icon}</button>}
