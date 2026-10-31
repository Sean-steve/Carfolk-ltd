export type FinanceInputs={price:number;depositPercent:number;annualRatePercent:number;months:number};
export type FinanceResult={principal:number;depositAmount:number;monthly:number;totalRepayments:number;totalInterest:number;totalCost:number};
export function calculateFinance({price,depositPercent,annualRatePercent,months}:FinanceInputs):FinanceResult{
 if(!Number.isFinite(price)||price<0||!Number.isFinite(depositPercent)||depositPercent<0||depositPercent>100||!Number.isFinite(annualRatePercent)||annualRatePercent<0||!Number.isFinite(months)||months<=0||!Number.isInteger(months))throw new Error('Invalid finance inputs');
 const depositAmount=price*depositPercent/100;
 const principal=price-depositAmount;
 const rate=annualRatePercent/100/12;
 const monthly=rate===0?principal/months:principal*rate/(1-Math.pow(1+rate,-months));
 const totalRepayments=monthly*months;
 return {principal,depositAmount,monthly,totalRepayments,totalInterest:Math.max(0,totalRepayments-principal),totalCost:depositAmount+totalRepayments};
}
