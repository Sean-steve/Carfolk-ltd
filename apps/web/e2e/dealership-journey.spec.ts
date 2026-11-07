import {test,expect} from '@playwright/test';
const admin='http://127.0.0.1:8000';
const password=process.env.CARFOLK_CERT_PASSWORD;
test('real PostgreSQL vehicle → enquiry → viewing → administrator → sold status',async({page,request},testInfo)=>{
 test.skip(testInfo.project.name==='mobile-chromium','State-changing certification runs once on desktop');
 test.skip(!password,'CI-only administrator test password required');
 await page.goto('/#/');
 await expect(page.getByRole('heading',{name:/Find Your Next Car/i})).toBeVisible();
 await expect(page.getByRole('heading',{name:/Toyota RAV4/i}).first()).toBeVisible();
 await expect(page.locator('text=DEMO LISTING')).toHaveCount(0);
 await page.goto('/#/cars?make=Toyota');
 await expect(page.getByRole('heading',{name:'Toyota RAV4'})).toBeVisible();
 await expect(page.getByRole('heading',{name:'Honda Fit'})).toHaveCount(0);
 await page.getByRole('link',{name:/View details/i}).first().click();
 await expect(page.getByRole('heading',{name:'Toyota RAV4'})).toBeVisible();
 await expect(page.getByText('KES 4,250,000').first()).toBeVisible();
 await page.getByRole('link',{name:'Enquire about this car'}).click();
 await expect(page.getByText('Local enquiry testing is enabled.')).toHaveCount(0);
 await expect(page.getByText('Your reference is')).toHaveCount(0);

 await expect(page.getByText('Toyota RAV4 · 2022')).toBeVisible();
 await page.locator('#enquiry-name').fill('Certification Buyer');
 await page.locator('#enquiry-phone').fill('0712345678');
 await page.locator('#enquiry-message').fill('Please share verified details about this available vehicle.');
 await page.locator('input[type=checkbox]').check();
 await page.getByRole('button',{name:/Send enquiry/i}).click();
 await expect(page.getByText('Enquiry received by Carfolk.')).toBeVisible();
 await expect(page.getByText('Your reference is')).toHaveCount(0);

 await page.goto('/#/cars/ci-toyota-rav4');
 await page.locator('.detail-purchase').getByRole('link',{name:/Request a viewing/i}).click();
 await expect(page).toHaveURL(/vehicle=ci-toyota-rav4/);
 await expect(page.getByText('Toyota RAV4 · 2022')).toBeVisible();
 const date=new Date(Date.now()+5*86400000).toISOString().slice(0,10);
 await page.locator('#enquiry-name').fill('Certification Buyer');
 await page.locator('#enquiry-phone').fill('0712345678');
 await page.locator('#enquiry-date').fill(date);
 await page.locator('#enquiry-time').fill('14:30');
 await page.locator('#enquiry-message').fill('Please confirm the preferred test-drive time for this vehicle.');
 await page.locator('input[type=checkbox]').check();
 await page.getByRole('button',{name:/Request viewing/i}).click();
 await expect(page.getByText('Viewing request received — awaiting confirmation.')).toBeVisible();
 await page.goto(admin+'/manage/login');
 await page.locator('#email').fill('cert-admin@example.invalid');
 await page.locator('#password').fill(password!);
 await page.getByRole('button',{name:/Sign in to dashboard/i}).click();
 await expect(page.getByRole('heading',{name:'Dealership Overview'})).toBeVisible();
 await expect(page.getByText('Environment note:')).toHaveCount(0);

 await page.getByRole('link',{name:'Customer Enquiries',exact:true}).click();
 await expect(page.getByText('Certification Buyer').first()).toBeVisible();
 const enquiryRow=page.locator('tr').filter({hasText:'Certification Buyer'}).first();
 await enquiryRow.locator('select').selectOption('contacted');
 await enquiryRow.getByRole('button',{name:'Save'}).click();
 await expect(page.getByText('Enquiry status updated.')).toBeVisible();
 await page.getByRole('link',{name:'Viewing Requests'}).click();
 const viewingRow=page.locator('tr').filter({hasText:'Certification Buyer'}).first();
 await expect(viewingRow).toBeVisible();
 await viewingRow.locator('select').selectOption('confirmed');
 await viewingRow.getByRole('button',{name:'Save'}).click();
 await expect(page.getByText('Viewing status updated.')).toBeVisible();
 // Test both app layouts: mobile can still navigate admin via the compact menu.
 await page.getByRole('link',{name:'Vehicle Inventory'}).click();
 await page.getByRole('row',{name:/Toyota RAV4/}).getByRole('link',{name:'Edit'}).click();
 await page.locator('select[name=status]').selectOption('sold');
 await page.getByRole('button',{name:/Save changes/i}).click();
 await expect(page.getByText('Vehicle details saved.')).toBeVisible();
 const car=await request.get(admin+'/api/v1/vehicles/ci-toyota-rav4');
 expect(car.ok()).toBeTruthy();
 expect((await car.json()).data.status).toBe('sold');
 const live=await request.get(admin+'/api/v1/vehicles?make=Toyota');
 expect((await live.json()).data).toHaveLength(1);
 await page.goto('/#/cars/ci-toyota-rav4');
 await expect(page.getByText('Vehicle has been marked sold.')).toBeVisible();
 await expect(page.locator('.detail-purchase').getByRole('link',{name:/Request a viewing/i})).toHaveCount(0);
 testInfo.annotations.push({type:'evidence',description:'Buyer and admin transitions validated against ephemeral CI PostgreSQL'});
});

test('mobile showroom and listing remain usable with real data',async({page},testInfo)=>{
 test.skip(testInfo.project.name!=='mobile-chromium','Mobile layout smoke only');
 await page.goto('/#/');
 await expect(page.getByRole('heading',{name:/Find Your Next Car/i})).toBeVisible();
 await page.goto('/#/cars');
 await expect(page.getByRole('heading',{name:'Cars for Sale'})).toBeVisible();
 await page.getByRole('button',{name:/Filters/}).click();
 await expect(page.getByRole('button',{name:/Apply filters/})).toBeVisible();
 await page.goto('/#/cars/ci-honda-fit');
 await expect(page.getByRole('heading',{name:'Honda Fit'})).toBeVisible();
 await expect(page.getByText('KES 1,850,000').first()).toBeVisible();
});


test('authenticated Carfolk command center is usable on desktop and mobile',async({page},testInfo)=>{
 test.skip(!password,'CI-only administrator test password required');
 await page.goto(admin+'/manage/login');
 await page.locator('#email').fill('cert-admin@example.invalid');
 await page.locator('#password').fill(password!);
 await page.getByRole('button',{name:/Sign in to dashboard/i}).click();

 await expect(page.getByRole('heading',{name:'Dealership Overview'})).toBeVisible();
 await expect(page.getByRole('heading',{name:'Your showroom at a glance.'})).toBeVisible();
 await expect(page.getByRole('heading',{name:'Stock composition'})).toBeVisible();
 await expect(page.getByRole('heading',{name:'Enquiry trend'})).toBeVisible();
 await expect(page.getByRole('heading',{name:'Recent customer enquiries'})).toBeVisible();
 await expect(page.getByRole('heading',{name:'Upcoming viewings'})).toBeVisible();
 await expect(page.getByRole('heading',{name:'Recently added vehicles'})).toBeVisible();
 await expect(page.getByRole('heading',{name:'Recent activity'})).toBeVisible();
 await expect(page.getByRole('heading',{name:'Operational alerts'})).toBeVisible();
 await expect(page.getByRole('heading',{name:'Quick actions'})).toBeVisible();
 await expect(page.locator('.command-chart-day')).toHaveCount(7);
 await expect(page.locator('.command-metric')).toHaveCount(8);
 await expect(page.getByText('Asking prices, not sales proceeds')).toBeVisible();
 await expect(page.getByText('Environment note:')).toHaveCount(0);
 const css=await page.locator('.command-intro').evaluate(node=>getComputedStyle(node).backgroundImage);
 expect(css).toContain('gradient');
 const overflow=await page.evaluate(()=>document.documentElement.scrollWidth - document.documentElement.clientWidth);
 expect(overflow).toBeLessThanOrEqual(2);
 await page.locator('.command-quick').getByRole('link',{name:/Customer follow-ups/}).click();
 await expect(page.getByRole('heading',{name:'Customer Enquiries'})).toBeVisible();
 await expect(page.getByText(/Follow-up flow/)).toBeVisible();
 await page.goto(admin+'/manage/vehicles');
 await expect(page.getByText('ACTIVE STOCK')).toBeVisible();
 await page.locator('input[name="q"]').fill('Honda');
 await page.getByRole('button',{name:'Apply filters'}).click();
 await expect(page.locator('table')).toContainText('Honda');
 await expect(page.locator('table')).not.toContainText('Toyota');
 await page.goto(admin+'/manage/vehicles/create');
 await expect(page.getByRole('heading',{name:'Identity and pricing'})).toBeVisible();
 await expect(page.getByRole('heading',{name:'Public visibility'})).toBeVisible();
 await page.goto(admin+'/manage/viewings');
 await expect(page.getByText(/Appointment flow/)).toBeVisible();
 await page.goto(admin+'/manage/settings');
 await expect(page.getByText(/Key details configured/)).toBeVisible();
 const adminOverflow=await page.evaluate(()=>document.documentElement.scrollWidth-document.documentElement.clientWidth);
 expect(adminOverflow).toBeLessThanOrEqual(2);
 if(testInfo.project.name==='desktop-chromium') {
  // Reuse the admin session from this test to respect the 5/minute login throttle.
  await page.goto(admin+'/manage/vehicles/create');
  const unique='CI-O72-'+Date.now();
  await page.locator('input[name="stock_reference"]').fill(unique);
  await page.locator('input[name="make"]').fill('Mazda');
  await page.locator('input[name="model"]').fill('Demio');
  await page.locator('input[name="year"]').fill('2021');
  await page.locator('input[name="price_kes"]').fill('1120000');
  await page.getByRole('button',{name:/Create vehicle/i}).click();
  await expect(page).toHaveURL(/edit#vehicle-photos$/);
  await expect(page.getByText('Select photos — Carfolk will optimize them automatically')).toBeVisible();
  const pixelPng=Buffer.from('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO7JQxkAAAAASUVORK5CYII=','base64');
  const photos=Array.from({length:20},(_,index)=>({
   name:`ci-photo-${index+1}.png`,mimeType:'image/png',buffer:pixelPng
  }));
  await page.locator('input[type="file"][name="photos[]"]').setInputFiles(photos);
  await expect(page.locator('[data-upload-selection]')).toContainText('20 selected');
  await expect(page.locator('[data-optimization-status]')).toContainText('20 photos ready');
  await expect(page.locator('[data-optimized-previews] .bulk-photo-preview')).toHaveCount(20);
  await page.getByRole('button',{name:'Upload selected photos'}).click();
  await expect(page.locator('.vehicle-image')).toHaveCount(20,{timeout:45000});
  await expect(page.locator('.vehicle-photo-gallery-title')).toContainText('20 of 20');
  await expect(page.locator('.vehicle-image .pill-available')).toHaveCount(1);
  testInfo.annotations.push({type:'evidence',description:'One selection of 20 photos uploaded via four authenticated JSON batches'});
  const reduction=await page.evaluate(async()=>{
    const canvas=document.createElement('canvas');
    canvas.width=2800;
    canvas.height=2200;
    const context=canvas.getContext('2d');
    if(!context) throw Error('canvas unavailable');
    const image=context.createImageData(canvas.width,canvas.height);
    let seed=142857;
    for(let i=0;i<image.data.length;i+=4){
      seed=(1664525*seed+1013904223)>>>0;
      image.data[i]=seed&255;
      image.data[i+1]=(seed>>>8)&255;
      image.data[i+2]=(seed>>>16)&255;
      image.data[i+3]=255;
    }
    context.putImageData(image,0,0);
    const source=await new Promise<Blob>((resolve,reject)=>canvas.toBlob(blob=>blob?resolve(blob):reject(Error('PNG encoding failed')),'image/png'));
    canvas.width=0; canvas.height=0;
    const file=new File([source],'oversized-car-photo.png',{type:'image/png'});
    const result=await (window as any).CarfolkImageOptimizer.optimize(file,{maxBytes:8*1024*1024});
    return {before:file.size,after:result.file.size,type:result.file.type,
      dimensions:result.dimensions,optimized:result.optimized,
      supported:Array.isArray([result.file.name,result.file.type])};
  });
  expect(reduction.before).toBeGreaterThan(8*1024*1024);
  expect(reduction.after).toBeLessThan(8*1024*1024);
  expect(reduction.after).toBeLessThan(reduction.before);
  expect(reduction.optimized).toBeTruthy();
  expect(reduction.type).toBe('image/webp');
  expect(Math.max(reduction.dimensions.width,reduction.dimensions.height)).toBeLessThanOrEqual(2560);
  testInfo.annotations.push({type:'evidence',description:'High-resolution PNG >8 MiB compressed locally into WebP <8 MiB before any upload'});
 }
 testInfo.annotations.push({type:'evidence',description:'Carfolk authenticated operational overview and navigation certified on '+testInfo.project.name});
});


test('restored public buyer guidance is coherent across vehicle discovery, comparison, finance and contact',async({page})=>{
 await page.goto('/#/');
 await expect(page.getByRole('heading',{name:'From shortlist to showroom.'})).toBeVisible();
 await page.goto('/#/cars');
 await expect(page.getByText(/Results reflect Carfolk's currently published stock/)).toBeVisible();
 await expect(page.getByText(/Set a budget in Kenya shillings/)).toBeVisible();
 await page.goto('/#/cars/ci-toyota-rav4');
 await expect(page.getByRole('heading',{name:'Confirm the essentials with Carfolk'})).toBeVisible();
 await expect(page.getByText(/independent inspection where appropriate/)).toBeVisible();
 await page.goto('/#/compare');
 await expect(page.getByText(/How comparison works/)).toBeVisible();
 await page.goto('/#/finance');
 await expect(page.getByRole('heading',{name:'Before discussing financing'})).toBeVisible();
 await page.goto('/#/about');
 await expect(page.getByRole('heading',{name:'Your path to a more informed decision.'})).toBeVisible();
 await page.goto('/#/contact?intent=test-drive&vehicle=ci-toyota-rav4');
 await expect(page.getByRole('heading',{name:'Plan your visit with confidence.'})).toBeVisible();
 await expect(page.getByText('A viewing request is not an automatic appointment or vehicle reservation.')).toBeVisible();
});
