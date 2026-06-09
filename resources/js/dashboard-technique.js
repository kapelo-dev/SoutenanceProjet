const GAUGE_BAR = { ok: 'bg-[#1a3a6e]', warning: 'bg-[#f5c400]', error: 'bg-destructive', info: 'bg-muted-foreground/40' };
const BANNER = {
    ok: { border: 'border-[#1a3a6e]/20', bg: 'bg-[#1a3a6e]', text: 'text-white', icon: 'ki-shield-tick', badge: 'kt-badge-success', badgeLabel: 'Opérationnel' },
    warning: { border: 'border-[#f5c400]/40', bg: 'bg-[#fef9e7] dark:bg-[#f5c400]/10', text: 'text-[#1a3a6e] dark:text-[#f5c400]', icon: 'ki-information-2', badge: 'kt-badge-warning', badgeLabel: 'Surveillance' },
    error: { border: 'border-destructive/30', bg: 'bg-destructive/5', text: 'text-destructive', icon: 'ki-information-3', badge: 'kt-badge-destructive', badgeLabel: 'Critique' },
};
const SERVICE_BADGE = { ok: ['kt-badge-success', 'OK'], warning: ['kt-badge-warning', 'Alerte'], error: ['kt-badge-destructive', 'Erreur'] };

function renderAlerts(alerts, health) {
    const panel = document.getElementById('tech_alerts_panel');
    if (!panel) return;

    const actionable = (alerts || []).filter(a => a.severity === 'critical' || a.severity === 'warning');

    if (actionable.length === 0) {
        panel.innerHTML = `<div class="kt-card border border-[#1a3a6e]/15 bg-[#fef9e7]/30 dark:bg-[#1a3a6e]/5" id="tech_alerts_ok">
            <div class="kt-card-content flex items-center gap-4 p-5">
                <i class="ki-filled ki-check-circle text-3xl text-success"></i>
                <div>
                    <div class="font-semibold text-foreground">Aucune action requise</div>
                    <div class="text-sm text-secondary-foreground">Tous les services critiques fonctionnent. Les métriques non disponibles (RAM, CPU) sont normales en local ou hébergement mutualisé.</div>
                </div>
            </div>
        </div>`;
        return;
    }

    panel.innerHTML = `<div class="kt-card border border-border">
        <div class="kt-card-header border-b border-border bg-muted/30">
            <h3 class="kt-card-title flex items-center gap-2"><i class="ki-filled ki-wrench text-primary"></i> Que faire ?</h3>
            <span class="text-xs text-secondary-foreground">Actions recommandées par priorité</span>
        </div>
        <div class="kt-card-content p-0 divide-y divide-border" id="tech_alerts_list">
            ${actionable.map((a, i) => {
                const sev = a.severity === 'critical';
                const cls = sev ? 'bg-destructive/10 text-destructive border-destructive/20' : 'bg-[#fef9e7] text-[#1a3a6e] border-[#f5c400]/30';
                const badge = sev ? 'kt-badge-destructive' : 'kt-badge-warning';
                return `<div class="flex gap-4 p-5">
                    <div class="flex size-8 shrink-0 items-center justify-center rounded-lg font-bold text-sm ${cls} border">${i + 1}</div>
                    <div class="min-w-0 flex-1">
                        <div class="flex flex-wrap items-center gap-2 mb-1">
                            <span class="font-semibold text-foreground">${a.title}</span>
                            <span class="kt-badge kt-badge-sm kt-badge-outline ${badge}">${sev ? 'Critique' : 'Alerte'}</span>
                        </div>
                        <p class="text-sm text-secondary-foreground mb-2">${a.problem}</p>
                        <div class="rounded-lg bg-muted/50 border border-border px-3 py-2.5 text-sm">
                            <span class="font-medium text-foreground">Action : </span>${a.action}
                        </div>
                    </div>
                </div>`;
            }).join('')}
        </div>
    </div>`;
}

function renderGauge(key, gauge) {
    const card = document.querySelector(`[data-gauge="${key}"]`);
    if (!card || !gauge) return;

    card.querySelector('[data-gauge-label]').textContent = gauge.label;
    card.querySelector('[data-gauge-value]').textContent = gauge.value;
    card.querySelector('[data-gauge-detail]').textContent = gauge.detail;

    const accent = card.querySelector('[data-gauge-accent]');
    const bar = card.querySelector('[data-gauge-bar]');
    const color = GAUGE_BAR[gauge.status] || GAUGE_BAR.ok;
    if (accent) accent.className = `h-1 ${color}`;

    const pctEl = card.querySelector('[data-gauge-percent]');
    if (gauge.status === 'info') {
        if (pctEl) pctEl.outerHTML = '<span class="text-xs kt-badge kt-badge-outline">N/A</span>';
        if (bar) bar.closest('.h-2')?.classList.add('hidden');
    } else {
        if (bar) {
            bar.closest('.h-2')?.classList.remove('hidden');
            bar.style.width = `${Math.min(100, gauge.percent)}%`;
            bar.className = `h-full rounded-full transition-all duration-700 ${color}`;
        }
        const pct = card.querySelector('[data-gauge-percent]') || card.querySelector('.text-xl');
        if (pct) pct.textContent = `${gauge.percent}%`;
    }
}

function renderServices(services) {
    const tbody = document.getElementById('tech_services_body');
    if (!tbody) return;

    tbody.innerHTML = services.map(s => {
        const [badgeCls, badgeLabel] = SERVICE_BADGE[s.status] || ['kt-badge-outline', '—'];
        return `<tr>
            <td class="px-5 py-3"><div class="font-medium">${s.name}</div><div class="text-xs text-secondary-foreground truncate max-w-xs">${s.detail || ''}</div></td>
            <td class="px-5 py-3"><span class="kt-badge kt-badge-sm kt-badge-outline ${badgeCls}">${badgeLabel}</span></td>
            <td class="px-5 py-3 text-right font-mono font-medium">${s.value}</td>
        </tr>`;
    }).join('');
}

function renderSystem(rows) {
    const container = document.getElementById('tech_system');
    if (!container) return;
    container.innerHTML = `<dl class="divide-y divide-border">${rows.map(r =>
        `<div class="flex justify-between gap-4 py-2.5 text-sm"><dt class="text-secondary-foreground">${r.label}</dt><dd class="font-mono font-medium text-right">${r.value}</dd></div>`
    ).join('')}</dl>`;
}

function renderHealth(health) {
    const cfg = BANNER[health.status] || BANNER.warning;
    const banner = document.getElementById('tech_health_banner');
    if (banner) banner.className = `rounded-2xl border p-5 mb-6 ${cfg.border} ${cfg.bg} ${cfg.text}`;

    const icon = document.getElementById('tech_health_icon');
    if (icon) icon.className = `ki-filled ${cfg.icon} text-2xl`;

    const label = document.getElementById('tech_health_label');
    if (label) label.textContent = health.label || '—';

    const summary = document.getElementById('tech_health_summary');
    if (summary) summary.textContent = health.summary || '';

    const badge = document.getElementById('tech_health_badge');
    if (badge) { badge.className = `kt-badge kt-badge-sm ${cfg.badge}`; badge.textContent = cfg.badgeLabel; }

    const crit = document.querySelector('[data-count="critical"]');
    const warn = document.querySelector('[data-count="warnings"]');
    if (crit) crit.textContent = health.critical ?? 0;
    if (warn) warn.textContent = health.warnings ?? 0;
}

function renderBackups(backups) {
    if (!backups) return;

    const last = document.querySelector('[data-backup-last]');
    const lastFile = document.querySelector('[data-backup-last-file]');
    const size = document.querySelector('[data-backup-size]');
    const duration = document.querySelector('[data-backup-duration]');
    const minioStatus = document.querySelector('[data-backup-minio-status]');
    const bucket = document.querySelector('[data-backup-bucket]');
    const tbody = document.getElementById('tech_backups_body');

    if (last) last.textContent = backups.last_success?.created_at_human || '—';
    if (lastFile) lastFile.textContent = backups.last_success?.filename || 'Aucune';
    if (size) size.textContent = backups.last_success?.size || '—';
    if (duration) duration.textContent = backups.last_success?.duration_ms ? `${backups.last_success.duration_ms} ms` : '—';

    if (minioStatus) {
        minioStatus.textContent = backups.minio?.reachable ? 'Connecté' : (backups.minio?.configured ? 'Injoignable' : 'Non configuré');
    }
    if (bucket) bucket.textContent = backups.minio?.bucket || '—';

    if (!tbody) return;

    const rows = backups.recent || [];
    if (!rows.length) {
        tbody.innerHTML = '<tr><td colspan="6" class="px-4 py-8 text-center text-secondary-foreground">Aucune sauvegarde enregistrée.</td></tr>';
        return;
    }

    tbody.innerHTML = rows.map(row => {
        const badge = row.status === 'success' ? 'kt-badge-success' : 'kt-badge-destructive';
        const label = row.status === 'success' ? 'OK' : 'Échec';
        return `<tr>
            <td class="px-4 py-3 whitespace-nowrap">${row.created_at_human || '—'}</td>
            <td class="px-4 py-3 font-mono text-xs">${row.filename}</td>
            <td class="px-4 py-3">${row.size}</td>
            <td class="px-4 py-3">${row.duration_ms} ms</td>
            <td class="px-4 py-3">${row.trigger}</td>
            <td class="px-4 py-3 text-right"><span class="kt-badge kt-badge-sm ${badge}">${label}</span></td>
        </tr>`;
    }).join('');
}

function renderMetrics(data) {
    renderHealth(data.health || {});
    renderAlerts(data.alerts || [], data.health);
    Object.entries(data.gauges || {}).forEach(([k, g]) => renderGauge(k, g));
    renderServices(data.services || []);
    renderSystem(data.system || []);
    renderBackups(data.backups || {});

    const updated = document.getElementById('tech_updated');
    if (updated && data.generated_at) {
        updated.textContent = 'Mis à jour ' + new Date(data.generated_at).toLocaleTimeString('fr-FR');
    }
}

function initTechnicalDashboard() {
    const root = document.getElementById('tech_dashboard_root');
    if (!root || root._techInited) return;
    root._techInited = true;

    try { renderMetrics(JSON.parse(root.dataset.initial || '{}')); } catch (_) {}

    const refresh = async () => {
        const btn = document.getElementById('tech_refresh');
        if (btn) btn.disabled = true;
        try {
            const res = await fetch(root.dataset.metricsUrl, { headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' } });
            const payload = await res.json();
            if (payload.success) renderMetrics(payload.data);
        } catch (e) { console.error('[DashboardTechnique]', e); }
        finally { if (btn) btn.disabled = false; }
    };

    document.getElementById('tech_refresh')?.addEventListener('click', refresh);

    const backupBtn = document.getElementById('tech_run_backup');
    if (backupBtn && root.dataset.backupUrl) {
        backupBtn.addEventListener('click', async () => {
            if (!confirm('Lancer une sauvegarde complète de la base de données vers MinIO ?')) return;
            backupBtn.disabled = true;
            const original = backupBtn.innerHTML;
            backupBtn.innerHTML = '<i class="ki-filled ki-loading"></i> Sauvegarde…';
            try {
                const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
                const res = await fetch(root.dataset.backupUrl, {
                    method: 'POST',
                    headers: {
                        Accept: 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': csrf,
                    },
                });
                const payload = await res.json();
                if (payload.success && payload.data) {
                    renderMetrics(payload.data);
                    window.AppToast?.success(payload.message || 'Sauvegarde réussie.');
                } else {
                    window.AppToast?.error(payload.message || 'Échec de la sauvegarde.');
                    if (payload.data) renderMetrics(payload.data);
                }
            } catch (e) {
                console.error('[DashboardTechnique] backup', e);
                window.AppToast?.error('Erreur lors de la sauvegarde.');
            } finally {
                backupBtn.disabled = false;
                backupBtn.innerHTML = original;
            }
        });
    }

    root._techTimer = setInterval(refresh, 15000);

    document.addEventListener('ajax-content-loaded', () => {
        clearInterval(root._techTimer);
        root._techInited = false;
    }, { once: true });
}

document.addEventListener('DOMContentLoaded', initTechnicalDashboard);
document.addEventListener('ajax-content-loaded', initTechnicalDashboard);

export { initTechnicalDashboard };
