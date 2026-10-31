import {afterEach,beforeEach,describe,expect,it,vi} from 'vitest';
import {ApiError,getOptions,getSite,getVehicle,getVehiclesByIds,listVehicles,submitEnquiry,isApiConfigured} from './client';
const fake=<T>(value:T,status=200)=>({ok:status<400,status,json:async()=>value} as Response);
beforeEach(()=>vi.stubGlobal('fetch',vi.fn()));
afterEach(()=>vi.unstubAllGlobals());
describe('Carfolk real backend client',()=>{
 it('uses a real API contract and preserves exact server identifiers',async()=>{
  const fetchMock=vi.mocked(fetch);
  const car={id:'e791b745-8aca-4404-b676-bc4f8881a280',slug:'real-car',make:'Toyota'};
  fetchMock.mockResolvedValueOnce(fake({data:[car],meta:{current_page:1,last_page:1,per_page:12,total:1}}));
  const result=await listVehicles({make:'Toyota',price_max:'5000000',page:1});
  expect(result.data[0].id).toBe(car.id);
  expect(fetchMock.mock.calls[0][0]).toContain('/api/v1/vehicles?make=Toyota&price_max=5000000&page=1');
  fetchMock.mockResolvedValueOnce(fake({data:car}));
  expect((await getVehicle('real-car')).id).toBe(car.id);
  expect(fetchMock.mock.calls[1][0]).toContain('/api/v1/vehicles/real-car');
 });
 it('encodes three-vehicle compare IDs and handles empty list without requests',async()=>{
  const fetchMock=vi.mocked(fetch);
  expect(await getVehiclesByIds([])).toEqual([]);
  expect(fetchMock).not.toHaveBeenCalled();
  fetchMock.mockResolvedValueOnce(fake({data:[]}));
  await getVehiclesByIds(['a','b']);
  expect(fetchMock.mock.calls[0][0]).toContain('ids%5B%5D=a');
  expect(fetchMock.mock.calls[0][0]).toContain('ids%5B%5D=b');
 });
 it('requires HTTP 201/2xx before confirming a public lead',async()=>{
  const fetchMock=vi.mocked(fetch);
  fetchMock.mockResolvedValueOnce(fake({data:{id:'lead-id',status:'requested',message:'Received'}},201));
  const result=await submitEnquiry('test-drive',{vehicle_id:'uuid',name:'Jane',phone:'0712345678',message:'Please confirm availability.',consent:true,preferred_date:'2026-12-02',preferred_time:'15:00'});
  expect(result.status).toBe('requested');
  const opts=fetchMock.mock.calls[0][1] as RequestInit;
  expect(opts.method).toBe('POST');
  expect(JSON.parse(String(opts.body)).vehicle_id).toBe('uuid');
  expect(opts.credentials).toBe('omit');
 });
 it('does not invent success on 422, 503 or network failure',async()=>{
  const fetchMock=vi.mocked(fetch);
  fetchMock.mockResolvedValueOnce(fake({message:'Invalid',errors:{phone:['Invalid number']}},422));
  await expect(submitEnquiry('general',{name:'John',phone:'bad',message:'Sample enquiry',consent:true})).rejects.toMatchObject({status:422,fieldErrors:{phone:['Invalid number']}});
  fetchMock.mockResolvedValueOnce(fake({message:'Disabled'},503));
  await expect(submitEnquiry('general',{name:'John',phone:'0712345678',message:'Please call me back',consent:true})).rejects.toMatchObject({status:503});
  fetchMock.mockRejectedValueOnce(new Error('Network'));
  await expect(getSite()).rejects.toBeInstanceOf(ApiError);
 });
 it('retrieves site configuration and live filter options',async()=>{
  const fetchMock=vi.mocked(fetch);
  fetchMock.mockResolvedValueOnce(fake({data:{leads_enabled:false,privacy_url:null,name:'Carfolk LTD'}}));
  expect((await getSite()).leads_enabled).toBe(false);
  fetchMock.mockResolvedValueOnce(fake({data:{makes:['Toyota'],models_by_make:{Toyota:['RAV4']},bodies:[],fuel_types:[],transmissions:[],conditions:[]}}));
  expect((await getOptions()).models_by_make.Toyota).toEqual(['RAV4']);
  expect(isApiConfigured()).toBe(true);
 });
});
