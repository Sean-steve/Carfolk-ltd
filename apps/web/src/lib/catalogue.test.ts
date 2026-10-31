import {describe,it,expect} from 'vitest';
import {DEMO_CARS} from '../data/showroom';
import {EMPTY_FILTERS,filterAndSort,filtersFromQuery,filtersToQuery} from './catalogue';
describe('Carfolk catalogue filters',()=>{
 it('filters by make, year, price, transmission, body and fuel',()=>{
  const out=filterAndSort(DEMO_CARS,{...EMPTY_FILTERS,make:'Honda',minYear:'2020',maxPrice:'4000000',transmission:'Automatic',body:'SUV',fuel:'Petrol'});
  expect(out.map(x=>x.id)).toEqual(['crv']);
 });
 it('returns empty for impossible query and supports clear state',()=>{
  expect(filterAndSort(DEMO_CARS,{...EMPTY_FILTERS,q:'not-a-vehicle'})).toHaveLength(0);
  expect(filterAndSort(DEMO_CARS,EMPTY_FILTERS)).toHaveLength(DEMO_CARS.length);
 });
 it('sorts ascending and descending by example price',()=>{
  const asc=filterAndSort(DEMO_CARS,{...EMPTY_FILTERS,sort:'price-asc'});
  const desc=filterAndSort(DEMO_CARS,{...EMPTY_FILTERS,sort:'price-desc'});
  expect(asc[0].priceKes).toBeLessThanOrEqual(asc[asc.length-1].priceKes);
  expect(desc[0].priceKes).toBeGreaterThanOrEqual(desc[desc.length-1].priceKes);
 });
 it('roundtrips URL filters and validates sort enum',()=>{
  const filters={...EMPTY_FILTERS,q:'toyota rav4',make:'Toyota',minPrice:'2000000',sort:'price-desc' as const};
  expect(filtersFromQuery(filtersToQuery(filters))).toEqual(filters);
  expect(filtersFromQuery('sort=nope').sort).toBe('recent');
 });
});
