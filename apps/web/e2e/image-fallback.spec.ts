import {test,expect} from '@playwright/test';

const original='/test-original-photo.jpg';
const brokenWebp='/test-broken-optimized-photo.webp';
const svg='<svg xmlns="http://www.w3.org/2000/svg" width="640" height="400"><rect width="640" height="400" fill="#e3e9ee"/><circle cx="320" cy="200" r="90" fill="#d92336"/></svg>';

const car={
 id:'photo-recovery-test',slug:'photo-recovery-test',make:'Toyota',model:'RAV4',
 trim:null,year:2023,price_kes:2500000,mileage_km:80000,engine_cc:null,
 fuel_type:null,transmission:null,drivetrain:null,body_type:null,
 condition:null,colour:null,location:null,description:null,features:[],
 status:'available',is_featured:false,
 images:[{id:'photo-original-1',url:original,thumbnail_url:brokenWebp,
   card_url:brokenWebp,detail_url:brokenWebp,alt_text:'Original vehicle photograph',
   is_primary:true,sort_order:1}],
};

test('a broken optimized WebP falls back to the original on listing and detail pages', async ({page})=>{
 let brokenRequests=0;
 let loadedOriginals=0;
 await page.route('**/test-broken-optimized-photo.webp',async route=>{
  brokenRequests++;
  await route.fulfill({status:404,contentType:'text/html',body:'not found'});
 });
 await page.route('**/test-original-photo.jpg',async route=>{
  loadedOriginals++;
  await route.fulfill({status:200,contentType:'image/svg+xml',body:svg});
 });
 await page.route('**/api/v1/vehicles**',async route=>{
  const pathname=new URL(route.request().url()).pathname;
  await route.fulfill({status:200,contentType:'application/json',body:JSON.stringify(
   pathname.endsWith('/photo-recovery-test')
    ? {data:car}
    : {data:[car],meta:{current_page:1,last_page:1,per_page:12,total:1}},
  )});
 });

 await page.goto('/#/cars');
 const card=page.locator('.listing-card__photo img').first();
 await expect(card).toHaveAttribute('src',original);
 await expect.poll(()=>card.evaluate((img:HTMLImageElement)=>img.naturalWidth)).toBeGreaterThan(0);

 await page.goto('/#/cars/photo-recovery-test');
 const hero=page.locator('.vehicle-gallery__stage img');
 await expect(hero).toHaveAttribute('src',original);
 await expect.poll(()=>hero.evaluate((img:HTMLImageElement)=>img.naturalWidth)).toBeGreaterThan(0);
 expect(brokenRequests).toBeGreaterThanOrEqual(2);
 // The browser can reuse the same successful original from its memory cache
 // when navigating to the detail page. The two decoded images above are the
 // actual functional assertion; at least one HTTP fetch is sufficient.
 expect(loadedOriginals).toBeGreaterThanOrEqual(1);
});
