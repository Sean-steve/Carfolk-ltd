import {useEffect,useMemo,useState} from 'react';
import {ArrowRight,BadgeInfo,Calculator,Check,ChevronRight,Info,Landmark,ShieldCheck} from 'lucide-react';
import {Shell} from '../components/Shell';
import {getDemoCar,kes} from '../data/showroom';
import {calculateFinance} from '../lib/finance';
export function FinancingPage({query}:{query:string}){
 const params=useMemo(()=>new URLSearchParams(query),[query]);
 const linkedCar=getDemoCar(params.get('vehicle')??'');
 const requested=Number(params.get('price'));
 const initial=linkedCar?linkedCar.priceKes:Math.max(100000,Number.isFinite(requested)&&requested>0?Math.min(100000000,requested):3000000);
 const [price,setPrice]=useState(initial),[deposit,setDeposit]=useState(30),[annual,setAnnual]=useState(12),[months,setMonths]=useState(48);
 useEffect(()=>{setPrice(initial)},[initial]);
 const result=useMemo(()=>calculateFinance({price,depositPercent:deposit,annualRatePercent:annual,months}),[price,deposit,annual,months]);
 return <Shell>
  <section className="catalogue-intro catalogue-intro--compact finance-intro"><div className="container catalogue-intro__inner"><div className="catalogue-breadcrumb"><a href="#/">Home</a><ChevronRight size={15}/> Financing</div><span className="eyebrow">PLAN YOUR NEXT DRIVE</span><h1>Know the numbers.<br/><em>Own your decision.</em></h1><p>Explore illustrative monthly repayments with a flexible calculator.</p></div></section>
  <section className="container finance-page">
   <div className="detail-preview-banner"><Info size={20}/><span><strong>Planning tool only:</strong> No bank, lender, interest rate, approval or financing offer is represented. Figures are illustrative and exclude charges.</span></div>
   <div className="finance-page__intro"><div><span className="eyebrow eyebrow--red">VEHICLE FINANCE CALCULATOR</span><h2>See what your monthly payments could look like.</h2><p>Adjust the sample values to explore how deposits, interest and repayment periods affect the total cost.</p></div><div className="finance-page__icon"><Calculator size={40}/></div></div>
   <div className="finance-page__grid">
    <form className="finance-fields" onSubmit={e=>e.preventDefault()}>
     <div className="finance-fields__heading"><h3>Build your estimate</h3><p>All values are editable; no personal data is collected.</p></div>
     {linkedCar&&<div className="finance-linked"><BadgeInfo size={18}/><div><strong>{linkedCar.make} {linkedCar.model}</strong><small>Using illustrative price from the demonstration listing.</small></div></div>}
     <label className="finance-control">Example vehicle price (KES)<input type="number" min="100000" max="100000000" step="50000" value={price} onChange={e=>setPrice(Math.min(100000000,Math.max(100000,Number(e.target.value)||100000)))}/></label>
     <div className="finance-control"><div className="finance-control__header"><label htmlFor="deposit">Deposit percentage</label><strong>{deposit}% · {kes(result.depositAmount)}</strong></div><input id="deposit" type="range" min="0" max="90" step="5" value={deposit} onChange={e=>setDeposit(Number(e.target.value))}/><div className="finance-range"><span>0%</span><span>90%</span></div></div>
     <div className="finance-control"><div className="finance-control__header"><label htmlFor="interest">Assumed annual interest rate</label><strong>{annual.toFixed(1)}%</strong></div><input id="interest" type="range" min="0" max="30" step="0.5" value={annual} onChange={e=>setAnnual(Number(e.target.value))}/><div className="finance-range"><span>0%</span><span>30%</span></div></div>
     <label className="finance-control">Loan duration<select value={months} onChange={e=>setMonths(Number(e.target.value))}><option value={12}>12 months</option><option value={24}>24 months</option><option value={36}>36 months</option><option value={48}>48 months</option><option value={60}>60 months</option><option value={72}>72 months</option></select></label>
     <button type="button" className="finance-reset" onClick={()=>{setPrice(3000000);setDeposit(30);setAnnual(12);setMonths(48)}}>Reset example values</button>
    </form>
    <div className="finance-result">
     <div className="finance-result__top"><Landmark size={24}/><span>YOUR ILLUSTRATIVE ESTIMATE</span></div>
     <span className="finance-result__monthly">Estimated monthly repayment</span>
     <strong className="finance-result__amount">{kes(Math.round(result.monthly))}</strong><p>per month for {months} months</p>
     <div className="finance-result__meter" role="img" aria-label={`Deposit ${deposit} percent, loan ${100-deposit} percent`}><div style={{width:deposit+'%'}}/></div>
     <div className="finance-result__legend"><span><i/> Deposit {deposit}%</span><span><i/> Financed {100-deposit}%</span></div>
     <dl><div><dt>Example vehicle price</dt><dd>{kes(price)}</dd></div><div><dt>Deposit amount</dt><dd>{kes(Math.round(result.depositAmount))}</dd></div><div><dt>Illustrative loan principal</dt><dd>{kes(Math.round(result.principal))}</dd></div><div><dt>Estimated total interest</dt><dd>{kes(Math.round(result.totalInterest))}</dd></div><div className="finance-result__grand"><dt>Estimated total incl. deposit</dt><dd>{kes(Math.round(result.totalCost))}</dd></div></dl>
     <div className="finance-result__warn"><ShieldCheck size={18}/><span>These calculations assume a fixed nominal annual interest rate with monthly compounding. Actual financing may differ significantly.</span></div>
     <a className="button button--primary" href="#/contact">Ask about financing enquiries <ArrowRight size={18}/></a>
    </div>
   </div>
   <div className="finance-page__notes"><div><Check size={20}/><h3>No application required</h3><p>Explore your monthly numbers without supplying personal information.</p></div><div><Info size={20}/><h3>Illustration, not a quote</h3><p>Interest rates, fees, credit eligibility, insurance and taxes are subject to a real lender's terms.</p></div><div><Calculator size={20}/><h3>Understand the trade-offs</h3><p>A longer term often lowers monthly costs but raises total interest paid.</p></div></div>
   <div className="finance-page__bottom"><h2>Ready to explore vehicle options?</h2><p>Browse examples, then ask Carfolk about actual stock and any available financing partnerships.</p><a className="button button--outline" href="#/cars">Browse vehicle examples <ArrowRight size={17}/></a></div>
  </section>
 </Shell>;
}
