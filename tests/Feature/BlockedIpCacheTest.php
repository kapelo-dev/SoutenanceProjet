<?php

namespace Tests\Feature;

use App\Services\IpBlockService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class BlockedIpCacheTest extends TestCase
{
    use RefreshDatabase;

    private const IP = '10.20.30.40';

    private IpBlockService $service;

    protected function setUp(): void
    {
        parent::setUp();

        config(['security.ip_blocking_enabled' => true]);
        $this->service = app(IpBlockService::class);
    }

    private function getLogin()
    {
        return $this->withServerVariables(['REMOTE_ADDR' => self::IP])->get(route('login'));
    }

    public function test_ip_bloquee_puis_debloquee_immediatement(): void
    {
        $this->getLogin()->assertOk();

        $this->service->block(self::IP, 'Test', 'manual');
        $this->getLogin()->assertForbidden()->assertSee('Test');

        $this->service->unblock(self::IP);
        $this->getLogin()->assertOk();
    }

    public function test_blocage_expire_meme_si_le_cache_est_rempli(): void
    {
        $this->service->block(self::IP, 'Auto', 'auto', null, 5, now()->addHour());
        $this->assertTrue($this->service->isBlocked(self::IP));

        $this->travel(61)->minutes();

        $this->assertFalse($this->service->isBlocked(self::IP));
        $this->getLogin()->assertOk();
    }

    public function test_aucune_requete_blocked_ips_quand_le_cache_est_chaud(): void
    {
        $this->service->block('192.168.1.1', 'Autre IP', 'manual');
        $this->getLogin()->assertOk();

        DB::flushQueryLog();
        DB::enableQueryLog();
        $this->getLogin()->assertOk();
        $queries = collect(DB::getQueryLog())->pluck('query')->filter(fn ($q) => str_contains($q, 'blocked_ips'));
        DB::disableQueryLog();

        $this->assertCount(0, $queries);
    }
}
