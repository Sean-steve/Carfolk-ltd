import {useEffect,useMemo,useState} from 'react';
import {ArrowRight,BadgeInfo,Calculator,ChevronRight,Info,Landmark} from 'lucide-react';
import {Shell} from '../components/Shell';
import {getVehicle,kes} from '../api/client';
import {useRemote} from '../api/hooks';
import {calculateFinance} from '../lib/finance';
export function FinancingPage({query}:{query:string}){
 const slug=new URLSearchParams(query).get('vehicle')??'';
 const {data:linked,loading,error}=useRemote(signal=>slug?getVehicle(slug,signal):Promise.resolve(null),slug);
 const [price,setPrice]=useState(3000000),[deposit,setDeposit]=useState(30),[annual,setAnnual]=useState(12),[months,setMonths]=useState(48);
 useEffect(()=>{if(linked)setPrice(linked.price_kes)},[linked?.id]);
 const result=useMemo(()=>calculateFinance({price,depositPercent:deposit,annualRatePercent:annual,months}),[price,deposit,annual,months]);
 return <Shell>
  <section className="catalogue-intro catalogue-intro--compact finance-intro"><div className="container catalogue-intro__inner"><div className="catalogue-breadcrumb"><a href="#/">Home</a><ChevronRight size={15}/> Financing</div><span className="eyebrow">PLAN YOUR NEXT DRIVE</span><h1>Know the numbers.<br/><em>Own your decision.</em></h1><p>Explore hypothetical monthly repayments with a flexible calculator.</p></div></section>
  <section className="container finance-page">
   <div className="detail-preview-banner"><Info size={20}/><span><strong>Illustrative only:</strong> This calculator is not a financing offer, lender quote, credit approval or statement of actual loan rates. Fees and charges are excluded.</span></div>
   <div className="finance-page__intro"><div><span className="eyebrow eyebrow--red">FINANCING PLANNER</span><h2>Estimate what your monthly payments could look like.</h2><p>Adjust deposit, interest assumptions and the repayment period to model your budget.</p></div><div className="finance-page__icon"><Calculator size={40}/></div></div>
   <div className="finance-page__grid"><form className="finance-fields" onSubmit={e=>e.preventDefault()}>
    <div className="finance-fields__heading"><h3>Build your estimate</h3></div>
    {slug&&<div className="finance-linked"><BadgeInfo size={18}/><div><strong>{linked?linked.make+' '+linked.model:loading?'Loading listed vehicle…':'Vehicle unavailable'}</strong><small>{linked?'Using the current published price as a starting amount.':error??'Enter an amount below to estimate.'}</small></div></div>}
    <label className="finance-control">Vehicle amount (KES)<input type="number" min="100000" max="100000000" step="50000" value={price} onChange={e=>setPrice(Math.min(100000000,Math.max(100000,Number(e.target.value)||100000)))}/></label>
    <div className="finance-control"><div className="finance-control__header"><label htmlFor="deposit">Deposit percentage</label><strong>{deposit}% · {kes(result.depositAmount)}</strong></div><input id="deposit" type="range" min="0" max="90" step="5" value={deposit} onChange={e=>setDeposit(Number(e.target.value))}/><div className="finance-range"><span>0%</span><span>90%</span></div></div>
    <div className="finance-control"><div className="finance-control__header"><label htmlFor="interest">Assumed annual interest rate</label><strong>{annual.toFixed(1)}%</strong></div><input id="interest" type="range" min="0" max="30" step="0.5" value={annual} onChange={e=>setAnnual(Number(e.target.value))}/><div className="finance-range"><span>0%</span><span>30%</span></div></div>
    <label className="finance-control">Illustrative term<select value={months} onChange={e=>setMonths(Number(e.target.value))}><option value={12}>12 months</option><option value={24}>24 months</option><option value={36}>36 months</option><option value={48}>48 months</option><option value={60}>60 months</option><option value={72}>72 months</option></select></label>
    <button type="button" className="finance-reset" onClick={()=>{setPrice(linked?.price_kes??3000000);setDeposit(30);setAnnual(12);setMonths(48)}}>Reset estimates</button>
   </form>
   <div className="finance-result"><div className="finance-result__top"><Landmark size={24}/><span>ILLUSTRATIVE FINANCE ESTIMATE</span></div><span className="finance-result__monthly">Estimated monthly repayment</span><strong className="finance-result__amount">{kes(Math.round(result.monthly))}</strong><p>per month for {months} months</p>
    <div className="finance-result__meter" role="img" aria-label={`Deposit ${deposit}%, loan ${100-deposit}%`}><div style={{width:deposit+'%'}}/></div>
    <div className="finance-result__legend"><span><i/> Deposit {deposit}%</span><span><i/> Financed {100-deposit}%</span></div>
    <dl><div><dt>Vehicle amount</dt><dd>{kes(price)}</dd></div><div><dt>Deposit</dt><dd>{kes(Math.round(result.depositAmount))}</dd></div><div><dt>Financed principal</dt><dd>{kes(Math.round(result.principal))}</dd></div><div><dt>Total estimated interest</dt><dd>{kes(Math.round(result.totalInterest))}</dd></div><div className="finance-result__grand"><dt>Estimated total incl. deposit</dt><dd>{kes(Math.round(result.totalCost))}</dd></div></dl>
    <a href="#/contact" className="button button--primary">Contact Carfolk <ArrowRight size={18}/></a>
   </div></div>
   <div className="finance-page__bottom"><h2>Explore your vehicle choices.</h2><p>Browse Carfolk's published listings, then confirm terms directly with the dealership.</p><a className="button button--outline" href="#/cars">Browse Cars <ArrowRight size={17}/></a></div>
  </section></Shell>;
}
