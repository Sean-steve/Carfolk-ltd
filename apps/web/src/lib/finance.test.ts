import {describe,it,expect} from 'vitest';
import {calculateFinance} from './finance';
describe('Carfolk finance estimator',()=>{
 it('computes zero-interest equal payments',()=>{
  const r=calculateFinance({price:2400000,depositPercent:20,annualRatePercent:0,months:24});
  expect(r.depositAmount).toBe(480000);
  expect(r.principal).toBe(1920000);
  expect(r.monthly).toBe(80000);
  expect(r.totalInterest).toBe(0);
  expect(r.totalCost).toBe(2400000);
 });
 it('computes standard amortized monthly payments',()=>{
  const r=calculateFinance({price:3000000,depositPercent:30,annualRatePercent:12,months:48});
  expect(r.monthly).toBeGreaterThan(50000);
  expect(r.monthly).toBeLessThan(60000);
  expect(r.totalInterest).toBeGreaterThan(0);
  expect(r.totalCost).toBeGreaterThan(3000000);
 });
 it('handles 100% deposit and rejects invalid values',()=>{
  const r=calculateFinance({price:1000000,depositPercent:100,annualRatePercent:14,months:12});
  expect(r.monthly).toBe(0);
  expect(()=>calculateFinance({price:-1,depositPercent:30,annualRatePercent:12,months:48})).toThrow();
  expect(()=>calculateFinance({price:1000,depositPercent:101,annualRatePercent:12,months:48})).toThrow();
  expect(()=>calculateFinance({price:1000,depositPercent:30,annualRatePercent:12,months:0})).toThrow();
 });
});
