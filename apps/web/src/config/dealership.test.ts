import {describe,expect,it} from 'vitest';
import {emailLink,intentMessage,mapLink,normalizeKenyanPhone,phoneLink,whatsappLink} from './dealership';
describe('verified public contact links',()=>{
 it('normalizes valid Kenyan mobile formats',()=>{
  expect(normalizeKenyanPhone('0712 345 678')).toBe('+254712345678');
  expect(normalizeKenyanPhone('254712345678')).toBe('+254712345678');
  expect(normalizeKenyanPhone('+254712345678')).toBe('+254712345678');
  expect(normalizeKenyanPhone('0112345678')).toBe('+254112345678');
 });
 it('rejects arbitrary strings and unverified numbers',()=>{
  expect(normalizeKenyanPhone('javascript:alert(1)')).toBeNull();
  expect(normalizeKenyanPhone('12345')).toBeNull();
  expect(whatsappLink(null,'hello')).toBeNull();
  expect(whatsappLink('+254200000000','hello')).toBeNull();
 });
 it('builds encoded links without including visitor PII',()=>{
  const link=whatsappLink('+254712345678',intentMessage('vehicle','Toyota RAV4'));
  expect(link).toContain('https://wa.me/254712345678?text=');
  expect(link).toContain('Toyota%20RAV4');
  expect(phoneLink('+254712345678')).toBe('tel:+254712345678');
  expect(emailLink('info@example.org','Carfolk question')).toBe('mailto:info@example.org?subject=Carfolk%20question');
  expect(mapLink('Nairobi CBD')).toContain('Nairobi%20CBD');
 });
});
