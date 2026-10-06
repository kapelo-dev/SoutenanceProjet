<?php

namespace Tests\Feature;

use App\Models\ConfigAppMobile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SmsConfigApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_renvoie_les_expediteurs_avec_le_token_sms(): void
    {
        ConfigAppMobile::create([
            'api_token' => 'token-sms-test',
            'filtres_sms' => ['FLOOZ', ' MIXX ', ''],
            'actif' => true,
        ]);

        $this->getJson('/api/mobile/sms-config', ['Authorization' => 'Bearer token-sms-test'])
            ->assertOk()
            ->assertJson(['filtres_sms' => ['FLOOZ', 'MIXX']])
            ->assertJsonStructure(['filtres_sms', 'updated_at']);
    }

    public function test_liste_vide_sans_filtre_enregistre(): void
    {
        ConfigAppMobile::create(['api_token' => 'token-sms-test', 'actif' => true]);

        $this->getJson('/api/mobile/sms-config', ['Authorization' => 'Bearer token-sms-test'])
            ->assertOk()
            ->assertJson(['filtres_sms' => []]);
    }

    public function test_refuse_sans_token_valide(): void
    {
        ConfigAppMobile::create(['api_token' => 'token-sms-test', 'filtres_sms' => ['FLOOZ'], 'actif' => true]);

        $this->getJson('/api/mobile/sms-config')->assertUnauthorized();
        $this->getJson('/api/mobile/sms-config', ['Authorization' => 'Bearer mauvais'])->assertUnauthorized();
    }
}
