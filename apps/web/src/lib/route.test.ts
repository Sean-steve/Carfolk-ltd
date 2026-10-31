import {afterEach,describe,expect,it,vi} from 'vitest';
import {currentRoute,toCar,toVehicleEnquiry,toVehicleViewing} from './route';
afterEach(()=>vi.unstubAllGlobals());
describe('Carfolk buyer and conversion routes',()=>{
 it('preserves vehicle enquiry and booking context',()=>{
  expect(toVehicleEnquiry('prado')).toBe('#/contact?intent=vehicle&vehicle=prado');
  expect(toVehicleViewing('rav4')).toBe('#/contact?intent=test-drive&vehicle=rav4');
  expect(toCar('toyota rav4')).toBe('#/cars/toyota%20rav4');
 });
 it('routes to the standalone About, Contact and Privacy pages',()=>{
  vi.stubGlobal('window',{location:{hash:'#/about'}});
  expect(currentRoute().name).toBe('about');
  vi.stubGlobal('window',{location:{hash:'#/privacy'}});
  expect(currentRoute().name).toBe('privacy');
  vi.stubGlobal('window',{location:{hash:'#/contact?intent=test-drive&vehicle=prado'}});
  expect(currentRoute()).toEqual({name:'contact',query:'intent=test-drive&vehicle=prado'});
 });
});
