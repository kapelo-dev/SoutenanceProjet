const GAUGE_BAR = { ok: 'bg-emerald-600', warning: 'bg-amber-500', error: 'bg-destructive', info: 'bg-muted-foreground/40' };
const BANNER = {
    ok: { border: 'border-emerald-200/50', bg: 'bg-emerald-50/80 dark:bg-emerald-950/20', text: 'text-emerald-900 dark:text-emerald-100', icon: 'ki-shield-tick', badge: 'kt-badge-success', badgeLabel: 'Normal' },
    warning: { border: 'border-amber-200/50', bg: 'bg-amber-50/80 dark:bg-amber-950/20', text: 'text-amber-950 dark:text-amber-100', icon: 'ki-shield-search', badge: 'kt-badge-warning', badgeLabel: 'Surveillance' },
    error: { border: 'border-destructive/30', bg: 'bg-destructive/5', text: 'text-destructive', icon: 'ki-shield-cross', badge: 'kt-badge-destructive', badgeLabel: 'Alerte' },
};

function renderHealth(health, stats) {
    const cfg = BANNER[health.status] || BANNER.warning;
    const banner = document.getElementById('sec_health_banner');
    if (banner) banner.className = `rounded-2xl border p-5 mb-6 ${cfg.border} ${cfg.bg} ${cfg.text}`;

    document.getElementById('sec_health_icon')?.classList && (document.getElementById('sec_health_icon').className = `ki-filled ${cfg.icon} text-2xl`);
    const label = document.getElementById('sec_health_label');
    if (label) label.textContent = health.label || '—';
    const summary = document.getElementById('sec_health_summary');
    if (summary) summary.textContent = health.summary || '';
    const badge = document.getElementById('sec_health_badge');
    if (badge) { badge.className = `kt-badge kt-badge-sm ${cfg.badge}`; badge.textContent = cfg.badgeLabel; }

    if (stats) {
        const failed = document.querySelector('[data-count="failed"]');
        const success = document.querySelector('[data-count="success"]');
        const ips = document.querySelector('[data-count="ips"]');
        if (failed) failed.textContent = stats.login_failed_24h ?? 0;
        if (success) success.textContent = stats.login_success_24h ?? 0;
        if (ips) ips.textContent = stats.suspicious_ips_count ?? 0;
    }
}

function renderGauge(key, gauge) {
    const card = document.querySelector(`[data-gauge="${key}"]`);
    if (!card || !gauge) return;
    card.querySelector('[data-gauge-label]').textContent = gauge.label;
    card.querySelector('[data-gauge-value]').textContent = gauge.value;
    card.querySelector('[data-gauge-detail]').textContent = gauge.detail;
    const color = GAUGE_BAR[gauge.status] || GAUGE_BAR.ok;
    const accent = card.querySelector('[data-gauge-accent]');
    if (accent) accent.className = `h-1 ${color}`;
    const bar = card.querySelector('[data-gauge-bar]');
    if (bar) { bar.style.width = `${Math.min(100, gauge.percent)}%`; bar.className = `h-full rounded-full ${color}`; }
    const pct = card.querySelector('[data-gauge-percent]');
    if (pct) pct.textContent = `${gauge.percent}%`;
}

function renderTimeline(timeline) {
    const container = document.getElementById('sec_timeline');
    if (!container || !timeline?.length) return;
    const max = Math.max(1, ...timeline.map(t => Math.max(t.failed, t.success)));
    container.innerHTML = `
        <div class="flex items-end gap-1 h-32">
            ${timeline.map(p => {
                const fh = Math.max(2, Math.round((p.failed / max) * 100));
                const sh = Math.max(2, Math.round((p.success / max) * 100));
                return `<div class="flex-1 flex flex-col items-center gap-0.5" title="${p.label}: ${p.failed} échec(s), ${p.success} OK">
                    <div class="w-full flex items-end justify-center gap-px h-24">
                        <div class="w-[45%] rounded-t bg-destructive/70" style="height:${fh}%"></div>
                        <div class="w-[45%] rounded-t bg-emerald-600/70" style="height:${sh}%"></div>
                    </div>
                    <span class="text-[9px] text-secondary-foreground">${p.label}</span>
                </div>`;
            }).join('')}
        </div>
        <div class="flex gap-4 mt-3 text-xs text-secondary-foreground">
            <span class="inline-flex items-center gap-1"><span class="size-2 rounded bg-destructive/70"></span> Échecs</span>
            <span class="inline-flex items-center gap-1"><span class="size-2 rounded bg-emerald-600/70"></span> Succès</span>
        </div>`;
}

function renderTopIps(rows) {
    const tbody = document.getElementById('sec_top_ips_body');
    if (!tbody) return;
    if (!rows?.length) {
        tbody.innerHTML = '<tr><td colspan="2" class="px-5 py-6 text-center text-secondary-foreground">Aucune tentative</td></tr>';
        return;
    }
    tbody.innerHTML = rows.map(r => `<tr>
        <td class="px-5 py-2 font-mono text-xs">${r.ip}</td>
        <td class="px-5 py-2 text-right font-semibold text-destructive">${r.failures}</td>
    </tr>`).join('');
}

function csrfToken() {
    return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
        || document.querySelector('#sec_block_ip_form input[name="_token"]')?.value
        || '';
}

function renderBlockedIps(rows) {
    const panel = document.getElementById('sec_blocked_ips');
    if (!panel) return;
    if (!rows?.length) {
        panel.innerHTML = '<p class="px-5 py-6 text-sm text-secondary-foreground">Aucune IP bloquée actuellement.</p>';
        return;
    }
    panel.innerHTML = rows.map(b => `<div class="flex items-center justify-between gap-3 px-5 py-3 border-b border-border last:border-0" data-block-id="${b.id}">
        <div class="min-w-0">
            <div class="font-mono text-sm font-medium">${b.ip}</div>
            <div class="text-xs text-secondary-foreground truncate">${b.reason}</div>
            <div class="text-[11px] text-secondary-foreground mt-0.5">${b.source_label}${b.failed_attempts > 0 ? ' · ' + b.failed_attempts + ' échec(s)' : ''}</div>
        </div>
        <button type="button" class="kt-btn kt-btn-sm kt-btn-outline shrink-0 sec-unblock-btn" data-block-id="${b.id}">Débloquer</button>
    </div>`).join('');
}

function renderSuspiciousIps(rows) {
    const panel = document.getElementById('sec_suspicious_ips');
    if (!panel) return;
    if (!rows?.length) {
        panel.innerHTML = '<p class="px-5 py-6 text-sm text-secondary-foreground">Aucune IP suspecte sur les dernières 24h.</p>';
        return;
    }
    panel.innerHTML = rows.map(ip => {
        const badge = ip.is_blocked
            ? '<span class="kt-badge kt-badge-sm kt-badge-destructive kt-badge-outline">Bloquée</span>'
            : '<span class="kt-badge kt-badge-sm kt-badge-warning kt-badge-outline">Surveillée</span>';
        return `<div class="flex items-center justify-between gap-3 px-5 py-3 border-b border-border last:border-0">
        <div><div class="font-mono text-sm">${ip.ip}</div>
        <div class="text-xs text-secondary-foreground">${ip.failures} tentative(s)</div></div>
        ${badge}
    </div>`;
    }).join('');
}

function renderRecent(events) {
    const tbody = document.getElementById('sec_recent_body');
    if (!tbody || !events) return;
    const badge = { critical: 'kt-badge-destructive', warning: 'kt-badge-warning', ok: 'kt-badge-success', info: 'kt-badge-success' };
    tbody.innerHTML = events.map(e => {
        const d = new Date(e.at);
        const date = d.toLocaleString('fr-FR', { day: 'numeric', month: 'short', hour: '2-digit', minute: '2-digit' });
        return `<tr>
            <td class="px-5 py-2.5 text-xs whitespace-nowrap">${date}</td>
            <td class="px-5 py-2.5"><span class="kt-badge kt-badge-sm kt-badge-outline ${badge[e.severity] || badge.ok}">${e.action_label}</span></td>
            <td class="px-5 py-2.5 text-secondary-foreground truncate max-w-xs">${e.description || ''}</td>
            <td class="px-5 py-2.5 font-mono text-xs">${e.ip || '—'}</td>
        </tr>`;
    }).join('');
}

function renderAlerts(alerts) {
    const panel = document.getElementById('sec_alerts_panel');
    if (!panel) return;
    const actionable = (alerts || []).filter(a => a.severity === 'critical' || a.severity === 'warning');
    if (!actionable.length) {
        panel.innerHTML = `<div class="kt-card border border-emerald-200/40 bg-emerald-50/30 dark:bg-emerald-950/10" id="sec_alerts_ok">
            <div class="kt-card-content flex items-center gap-4 p-5">
                <i class="ki-filled ki-shield-tick text-3xl text-emerald-600"></i>
                <div><div class="font-semibold text-foreground">Aucune menace active détectée</div>
                <div class="text-sm text-secondary-foreground">Les tentatives n'ont pas abouti sur les dernières 24h.</div></div>
            </div></div>`;
        return;
    }
    panel.innerHTML = `<div class="kt-card border border-border">
        <div class="kt-card-header border-b border-border bg-muted/30">
            <h3 class="kt-card-title flex items-center gap-2"><i class="ki-filled ki-information-3 text-destructive"></i> Alertes sécurité</h3>
        </div>
        <div class="kt-card-content p-0 divide-y divide-border" id="sec_alerts_list">
            ${actionable.map((a, i) => {
                const sev = a.severity === 'critical';
                const cls = sev ? 'bg-destructive/10 text-destructive border-destructive/20' : 'bg-amber-50 text-amber-950 border-amber-200/40';
                const resolveBtn = a.key && a.key !== 'status.ok'
                    ? `<div class="mt-3 flex flex-wrap items-center gap-2">
                        <button type="button" class="kt-btn kt-btn-sm kt-btn-success sec-resolve-alert-btn" data-alert-key="${a.key}" data-alert-title="${a.title}">
                            <i class="ki-filled ki-check-circle"></i> Marquer comme résolue
                        </button>
                        <span class="text-xs text-secondary-foreground">La menace a été traitée (IP bloquée, compte sécurisé, etc.)</span>
                    </div>`
                    : '';
                return `<div class="flex gap-4 p-5"><div class="flex size-8 shrink-0 items-center justify-center rounded-lg font-bold text-sm ${cls} border">${i + 1}</div>
                <div class="min-w-0 flex-1"><div class="font-semibold mb-1">${a.title}</div>
                <p class="text-sm text-secondary-foreground mb-2">${a.problem}</p>
                <div class="rounded-lg bg-muted/50 border px-3 py-2 text-sm"><strong>Action :</strong> ${a.action}</div>${resolveBtn}</div></div>`;
            }).join('')}
        </div></div>`;
}

function renderMetrics(data) {
    renderHealth(data.health || {}, data.stats);
    renderAlerts(data.alerts);
    Object.entries(data.gauges || {}).forEach(([k, g]) => renderGauge(k, g));
    renderTimeline(data.timeline);
    renderTopIps(data.top_ips);
    renderSuspiciousIps(data.suspicious_ips);
    renderBlockedIps(data.blocked_ips);
    renderRecent(data.recent_events);
    const updated = document.getElementById('sec_updated');
    if (updated && data.generated_at) {
        updated.textContent = 'Mis à jour ' + new Date(data.generated_at).toLocaleTimeString('fr-FR');
    }
}

function initSecurityDashboard() {
    const root = document.getElementById('sec_dashboard_root');
    if (!root || root._secInited) return;
    root._secInited = true;

    try { renderMetrics(JSON.parse(root.dataset.initial || '{}')); } catch (_) {}

    const refresh = async () => {
        const btn = document.getElementById('sec_refresh');
        if (btn) btn.disabled = true;
        try {
            const res = await fetch(root.dataset.metricsUrl, { headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' } });
            const payload = await res.json();
            if (payload.success) renderMetrics(payload.data);
        } catch (e) { console.error('[DashboardSecurite]', e); }
        finally { if (btn) btn.disabled = false; }
    };

    document.getElementById('sec_refresh')?.addEventListener('click', refresh);
    root._secTimer = setInterval(refresh, 30000);

    document.getElementById('sec_block_ip_form')?.addEventListener('submit', async (e) => {
        e.preventDefault();
        const form = e.target;
        const fd = new FormData(form);
        try {
            const res = await fetch(root.dataset.blockUrl, {
                method: 'POST',
                headers: {
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': csrfToken(),
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: fd,
            });
            const payload = await res.json();
            if (!payload.success) {
                window.AppToast?.error(payload.message || 'Erreur lors du blocage.');
                return;
            }
            form.reset();
            renderBlockedIps(payload.blocked_ips);
            await refresh();
        } catch (err) {
            console.error('[DashboardSecurite] block', err);
        }
    });

    root.addEventListener('click', async (e) => {
        const resolveBtn = e.target.closest('.sec-resolve-alert-btn');
        if (resolveBtn) {
            const alertKey = resolveBtn.dataset.alertKey;
            const alertTitle = resolveBtn.dataset.alertTitle || 'cette alerte';
            if (!alertKey) return;

            const note = prompt(`Note optionnelle pour « ${alertTitle} » (laisser vide si aucune) :`);
            if (note === null) return;

            resolveBtn.disabled = true;
            try {
                const res = await fetch(root.dataset.resolveAlertUrl, {
                    method: 'POST',
                    headers: {
                        Accept: 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken(),
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: JSON.stringify({ alert_key: alertKey, note: note || null }),
                });
                const payload = await res.json();
                if (!payload.success) {
                    window.AppToast?.error(payload.message || 'Impossible de lever l\'alerte.');
                    return;
                }
                renderMetrics(payload.data);
            } catch (err) {
                console.error('[DashboardSecurite] resolve', err);
            } finally {
                resolveBtn.disabled = false;
            }
            return;
        }

        const btn = e.target.closest('.sec-unblock-btn');
        if (!btn) return;
        const id = btn.dataset.blockId;
        if (!id || !confirm('Débloquer cette adresse IP ?')) return;
        try {
            const res = await fetch(`${root.dataset.unblockUrl}/${id}`, {
                method: 'DELETE',
                headers: {
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': csrfToken(),
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });
            const payload = await res.json();
            if (!payload.success) {
                window.AppToast?.error(payload.message || 'Erreur lors du déblocage.');
                return;
            }
            renderBlockedIps(payload.blocked_ips);
            await refresh();
        } catch (err) {
            console.error('[DashboardSecurite] unblock', err);
        }
    });

    document.addEventListener('ajax-content-loaded', () => {
        clearInterval(root._secTimer);
        root._secInited = false;
    }, { once: true });
}

document.addEventListener('DOMContentLoaded', initSecurityDashboard);
document.addEventListener('ajax-content-loaded', initSecurityDashboard);

export { initSecurityDashboard };
