import {describe,it,expect} from 'vitest';
import {EMPTY_FILTERS,filtersFromQuery,filtersToApi,filtersToQuery} from './catalogue';
describe('live server catalogue query mapping',()=>{
 it('roundtrips filters in hash query',()=>{
  const f={...EMPTY_FILTERS,q:'Toyota RAV4',make:'Toyota',minPrice:'2000000',sort:'price-desc' as const};
  expect(filtersFromQuery(filtersToQuery(f))).toEqual(f);
 });
 it('maps React filter labels to backend contract and page',()=>{
  expect(filtersToApi({...EMPTY_FILTERS,body:'SUV',fuel:'Diesel',maxPrice:'5000000',sort:'year-desc'},2))
    .toEqual({page:2,sort:'year-desc',body_type:'SUV',fuel_type:'Diesel',price_max:'5000000'});
 });
 it('rejects unsafe unknown sort choice',()=>expect(filtersFromQuery('sort=random').sort).toBe('recent'));
});
