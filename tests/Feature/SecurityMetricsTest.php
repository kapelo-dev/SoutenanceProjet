<?php

namespace Tests\Feature;

use App\Models\SystemLog;
use App\Support\SecurityMetrics;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SecurityMetricsTest extends TestCase
{
    use RefreshDatabase;

    private function log(string $action, string $date): void
    {
        $log = SystemLog::create(['action' => $action, 'description' => $action, 'ip_address' => '10.1.1.1']);
        $log->forceFill(['created_at' => $date])->saveQuietly();
    }

    public function test_compteurs_et_courbe_des_24_heures(): void
    {
        $this->travelTo('2026-10-07 14:30:00');

        $this->log('login_failed', '2026-10-07 14:05:00'); // heure courante
        $this->log('login_failed', '2026-10-07 14:10:00');
        $this->log('login', '2026-10-07 14:20:00');
        $this->log('login', '2026-10-07 09:59:59');       // 09h
        $this->log('login_failed', '2026-10-06 15:00:00'); // il y a 23 h : première heure de la courbe
        $this->log('login_failed', '2026-10-06 14:00:00'); // hors 24 h, dans les 7 jours
        $this->log('delete', '2026-10-07 11:00:00');
        $this->log('export', '2026-10-01 11:00:00');       // sensible mais hors 24 h

        $metrics = SecurityMetrics::collect();
        $stats = $metrics['stats'];

        $this->assertSame(3, $stats['login_failed_24h']);
        $this->assertSame(2, $stats['login_success_24h']);
        $this->assertSame(4, $stats['login_failed_7d']);

        $courbe = collect($metrics['timeline'])->keyBy('label');
        $this->assertCount(24, $metrics['timeline']);
        $this->assertSame('15h', $metrics['timeline'][0]['label']);
        $this->assertSame(['label' => '14h', 'failed' => 2, 'success' => 1], $metrics['timeline'][23]);
        $this->assertSame(1, $courbe['09h']['success']);
        $this->assertSame(1, $metrics['timeline'][0]['failed']);
        $this->assertSame(0, $courbe['10h']['failed'] + $courbe['10h']['success']);
    }
}
