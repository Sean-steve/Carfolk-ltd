<?php
namespace Tests\Feature;
use App\Support\LeadAcceptance;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
class LeadCollectionGateTest extends TestCase
{
    use RefreshDatabase;
    public function test_disabled_by_default(): void
    {
        config()->set('carfolk.leads_enabled',false);
        config()->set('carfolk.privacy_url','https://carfolk.example/privacy');
        $this->assertFalse(LeadAcceptance::enabled());
        $this->getJson('/api/v1/site')->assertJsonPath('data.leads_enabled',false);
        $this->postJson('/api/v1/enquiries',[])->assertStatus(503);
    }
    public function test_invalid_policy_blocks_capture_and_public_flag(): void
    {
        config()->set('carfolk.leads_enabled',true);
        config()->set('carfolk.privacy_url','not-a-valid-url');
        $this->assertFalse(LeadAcceptance::enabled());
        $this->getJson('/api/v1/site')->assertJsonPath('data.leads_enabled',false);
        $this->postJson('/api/v1/enquiries',[])->assertStatus(503);
    }
    public function test_approved_test_environment_https_policy_reports_active():void
    {
        config()->set('carfolk.leads_enabled',true);
        config()->set('carfolk.privacy_url','https://carfolk.example/privacy');
        $this->assertTrue(LeadAcceptance::enabled());
        $this->getJson('/api/v1/site')->assertJsonPath('data.leads_enabled',true);
    }
}
