<?php

namespace Tests\Feature;

use App\Services\IpBlockService;
use App\Support\PermissionCache;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use RuntimeException;
use Tests\TestCase;

/**
 * Cache inaccessible (ex. fichiers de cache créés par root, illisibles par le serveur web) :
 * les permissions et les IP bloquées sont lues en base au lieu de provoquer une erreur 500.
 */
class CacheIndisponibleTest extends TestCase
{
    use RefreshDatabase;

    private function casserLeCache(): void
    {
        Cache::shouldReceive('memo')->andThrow(new RuntimeException('fopen(): Permission denied'));
    }

    public function test_permissions_calculees_sans_cache(): void
    {
        $this->casserLeCache();

        $this->assertSame('valeur', PermissionCache::remember('cle', fn () => 'valeur'));
        PermissionCache::flush(); // ne lève pas d'exception
    }

    public function test_ip_bloquees_verifiees_sans_cache(): void
    {
        app(IpBlockService::class)->block('10.9.8.7', 'Test', 'auto');
        $this->casserLeCache();

        $this->assertTrue(app(IpBlockService::class)->isBlocked('10.9.8.7'));
        $this->assertFalse(app(IpBlockService::class)->isBlocked('10.9.8.6'));
    }
}
