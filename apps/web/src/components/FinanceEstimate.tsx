import {useMemo,useState} from 'react';
import {Calculator,Info} from 'lucide-react';
const currency=(amount:number)=>new Intl.NumberFormat('en-KE',{style:'currency',currency:'KES',maximumFractionDigits:0}).format(amount);
export function FinanceEstimate(){
 const [price,setPrice]=useState(3000000);
 const [deposit,setDeposit]=useState(30);
 const [annual,setAnnual]=useState(12);
 const [months,setMonths]=useState(48);
 const [visible,setVisible]=useState(false);
 const estimate=useMemo(()=>{
  const principal=price*(1-deposit/100),rate=annual/100/12;
  return Math.round(rate===0?principal/months:principal*rate/(1-Math.pow(1+rate,-months)));
 },[price,deposit,annual,months]);
 return <div className="finance-box" id="financing">
   <div className="finance-box__visual"><div className="finance-box__dark"><span className="eyebrow">FLEXIBLE POSSIBILITIES</span><h2>Make your next<br/>move a smart one.</h2><p>Explore the numbers before you decide. Use our example repayment calculator to plan with confidence.</p><button type="button" className="button button--primary" onClick={()=>setVisible(v=>!v)} aria-expanded={visible} aria-controls="finance-calculator"><Calculator size={18}/> {visible?'Hide calculator':'Estimate repayments'}</button></div></div>
   <div className="finance-box__details"><div className="finance-box__icon"><Calculator size={24}/></div><h3>Vehicle Financing</h3><p>Understand your options with transparent example calculations.</p><ul><li>Adjustable deposit</li><li>Flexible repayment terms</li><li>Clear monthly estimates</li></ul><small><Info size={14}/> Illustrative only. No lender partnership or loan approval is implied.</small></div>
   {visible&&<div className="finance-calculator" id="finance-calculator"><div><h3>Illustrative repayment calculator</h3><p>Choose sample values to explore repayment estimates. Rates are hypothetical.</p><div className="finance-calculator__inputs">
     <label>Vehicle price (KES)<input type="number" min="100000" max="100000000" step="50000" value={price} onChange={e=>setPrice(Math.max(100000,Number(e.target.value)||100000))}/></label>
     <label>Deposit (%)<input type="number" min="0" max="95" value={deposit} onChange={e=>setDeposit(Math.min(95,Math.max(0,Number(e.target.value)||0)))}/></label>
     <label>Annual interest (%)<input type="number" min="0" max="40" step="0.5" value={annual} onChange={e=>setAnnual(Math.min(40,Math.max(0,Number(e.target.value)||0)))}/></label>
     <label>Term<select value={months} onChange={e=>setMonths(Number(e.target.value))}><option value={24}>24 months</option><option value={36}>36 months</option><option value={48}>48 months</option><option value={60}>60 months</option></select></label>
   </div></div><div className="finance-calculator__result"><span>Estimated monthly repayment</span><strong>{currency(estimate)}</strong><p>Estimated deposit: {currency(price*deposit/100)}</p><small>Excludes fees, insurance, taxes and other costs. Not a quote or offer.</small></div></div>}
 </div>
}
