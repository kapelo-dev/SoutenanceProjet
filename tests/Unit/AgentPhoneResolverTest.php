<?php

namespace Tests\Unit;

use App\Models\Agent;
use App\Support\AgentPhoneResolver;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AgentPhoneResolverTest extends TestCase
{
    use RefreshDatabase;

    public function test_resolve_for_sms_rejects_mismatched_agent_code(): void
    {
        Agent::create([
            'nom' => 'Test',
            'prenom' => 'Agent',
            'code_agent' => '5150328',
            'telephone' => '90123456',
            'statut' => 'actif',
        ]);

        $result = AgentPhoneResolver::resolveForSmsTransaction('22890123456', '9999999');

        $this->assertNull($result['agent']);
        $this->assertSame(422, $result['status']);
        $this->assertStringContainsString('code agent', strtolower($result['error']));
    }

    public function test_resolve_for_sms_accepts_matching_agent_code(): void
    {
        $agent = Agent::create([
            'nom' => 'Test',
            'prenom' => 'Agent',
            'code_agent' => '5150328',
            'telephone' => '90123456',
            'statut' => 'actif',
        ]);

        $result = AgentPhoneResolver::resolveForSmsTransaction('22890123456', '5150328');

        $this->assertNotNull($result['agent']);
        $this->assertSame($agent->id, $result['agent']->id);
        $this->assertNull($result['error']);
    }

    public function test_resolve_for_sms_without_code_uses_sim_only(): void
    {
        $agent = Agent::create([
            'nom' => 'Test',
            'prenom' => 'Agent',
            'code_agent' => '5150328',
            'telephone' => '90123456',
            'statut' => 'actif',
        ]);

        $result = AgentPhoneResolver::resolveForSmsTransaction('22890123456', null);

        $this->assertNotNull($result['agent']);
        $this->assertSame($agent->id, $result['agent']->id);
    }
}
