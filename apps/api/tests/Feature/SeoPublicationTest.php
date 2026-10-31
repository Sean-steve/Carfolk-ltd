<?php
namespace Tests\Feature;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
class SeoPublicationTest extends TestCase
{
    use RefreshDatabase;
    private function car(array $attrs=[]): Vehicle
    {
        return Vehicle::create(array_merge([
            'stock_reference'=>'SEO-001','slug'=>'seo-toyota-rav4','make'=>'Toyota','model'=>'RAV4',
            'year'=>2022,'price_kes'=>3300000,'status'=>'available','is_published'=>true
        ],$attrs));
    }
    public function test_robots_and_sitemap_fail_closed_when_seo_unapproved(): void
    {
        config()->set('carfolk.seo_enabled',false);
        config()->set('carfolk.public_url','https://carfolk.example');
        $this->car();
        $this->get('/robots.txt')->assertOk()->assertSee('Disallow: /');
        $this->get('/sitemap.xml')->assertOk()->assertDontSee('seo-toyota-rav4');
        $this->get('/listing/seo-toyota-rav4')->assertOk()->assertSee('noindex,nofollow');
    }
    public function test_only_published_inventory_is_indexable(): void
    {
        config()->set('carfolk.seo_enabled',true);
        config()->set('carfolk.public_url','https://carfolk.example');
        $this->car(['description'=>'Actual dealer entered description.']);
        $this->car(['stock_reference'=>'SEO-002','slug'=>'seo-draft','is_published'=>false]);
        $this->car(['stock_reference'=>'SEO-003','slug'=>'seo-archived','archived_at'=>now()]);
        $this->get('/sitemap.xml')->assertOk()->assertSee('https://carfolk.example/listing/seo-toyota-rav4')->assertDontSee('seo-draft')->assertDontSee('seo-archived');
        $this->get('/robots.txt')->assertOk()->assertSee('Sitemap: https://carfolk.example/sitemap.xml');
        $this->get('/listing/seo-toyota-rav4')->assertOk()->assertSee('index,follow')->assertSee('3300000')->assertSee('Actual dealer entered description.');
        $this->get('/listing/seo-draft')->assertNotFound();
    }
    public function test_missing_https_public_origin_disables_indexing(): void
    {
        config()->set('carfolk.seo_enabled',true);
        config()->set('carfolk.public_url','http://localhost');
        $this->get('/robots.txt')->assertSee('Disallow: /');
    }
    public function test_admin_responses_are_not_cached():void
    {
        $this->get('/manage/login')->assertOk()->assertHeader('X-Frame-Options','DENY')->assertHeader('X-Content-Type-Options','nosniff')->assertHeader('Cache-Control','no-store, private');
    }
}
