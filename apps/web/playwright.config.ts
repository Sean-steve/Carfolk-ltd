import {defineConfig,devices} from '@playwright/test';
export default defineConfig({
 testDir:'./e2e',
 timeout:90_000,
 expect:{timeout:15_000},
 retries:0,
 workers:1,
 reporter:[['line'],['html',{open:'never',outputFolder:'playwright-report'}]],
 use:{baseURL:'http://127.0.0.1:5173',trace:'retain-on-failure',screenshot:'only-on-failure'},
 projects:[{name:'desktop-chromium',use:{...devices['Desktop Chrome']}},{name:'mobile-chromium',use:{...devices['Pixel 7']}}],
});
