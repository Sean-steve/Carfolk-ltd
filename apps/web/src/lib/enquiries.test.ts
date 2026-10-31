import {describe,expect,it} from 'vitest';
import {countErrors,EMPTY_DRAFT,intentFromQuery,todayInNairobi,validateEnquiry,type EnquiryDraft} from './enquiries';
const good:EnquiryDraft={...EMPTY_DRAFT,kind:'vehicle',vehicleId:'prado',name:'Jane Doe',phone:'0712345678',email:'',message:'Please share a little more information about this vehicle.',consent:true};
describe('enquiry validation',()=>{
 it('accepts properly completed vehicle enquiry and +254 format',()=>{
  expect(validateEnquiry(good,'2026-10-31')).toEqual({});
  expect(validateEnquiry({...good,phone:'+254712345678'},'2026-10-31')).toEqual({});
 });
 it('rejects invalid phone, missing consent, and short message',()=>{
  const bad=validateEnquiry({...good,phone:'123',consent:false,message:'Hi'},'2026-10-31');
  expect(bad.phone).toBeTruthy();expect(bad.consent).toBeTruthy();expect(bad.message).toBeTruthy();
  expect(countErrors(bad)).toBe(3);
 });
 it('requires a vehicle for vehicle-specific journeys',()=>{
  expect(validateEnquiry({...good,vehicleId:''},'2026-10-31').vehicleId).toBeTruthy();
 });
 it('requires preferred time and future/today date for test drives',()=>{
  const v={...good,kind:'test-drive' as const};
  expect(validateEnquiry({...v,preferredDate:'2026-10-30',preferredTime:'09:00'},'2026-10-31').preferredDate).toBeTruthy();
  expect(validateEnquiry({...v,preferredDate:'2026-11-01',preferredTime:''},'2026-10-31').preferredTime).toBeTruthy();
  expect(validateEnquiry({...v,preferredDate:'2026-11-01',preferredTime:'10:30'},'2026-10-31')).toEqual({});
 });
 it('uses Africa/Nairobi for scheduling',()=>{
  expect(todayInNairobi(new Date('2026-10-31T22:30:00Z'))).toBe('2026-11-01');
 });
 it('does not accept honeypot input',()=>expect(validateEnquiry({...good,website:'spam'},'2026-10-31').website).toBeTruthy());
 it('recognizes only defined enquiry intent values',()=>{expect(intentFromQuery('test-drive')).toBe('test-drive');expect(intentFromQuery('vehicle')).toBe('vehicle');expect(intentFromQuery('unknown')).toBe('general')});
});
