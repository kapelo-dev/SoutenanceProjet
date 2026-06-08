/**
 * Carte de performance du mois — marqueurs classés (sans cercles géants qui se chevauchent)
 */

class DashboardMonthMap {
    constructor() {
        this.map = null;
        this.markers = [];
        this.isInitializing = false;
    }

    init() {
        if (this.isInitializing) return;

        const mapElement = document.getElementById('dashboard_month_map');
        if (!mapElement) return;
        if (this.map) return;

        if (typeof L === 'undefined') {
            setTimeout(() => this.init(), 200);
            return;
        }

        this.isInitializing = true;

        try {
            this.map = L.map('dashboard_month_map', { zoomControl: true }).setView([6.1375, 1.2123], 13);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap',
                maxZoom: 19,
            }).addTo(this.map);

            this.loadData();

            setTimeout(() => {
                if (this.map) this.map.invalidateSize();
            }, 250);
        } catch (error) {
            console.error('[DashboardMap] Erreur initialisation:', error);
            this.isInitializing = false;
        }
    }

    async loadData() {
        try {
            const response = await fetch('/api/dashboard/carte-performance-mois');
            if (!response.ok) throw new Error(`HTTP ${response.status}`);

            const points = await response.json();
            if (!Array.isArray(points) || points.length === 0) {
                this.renderRanking([]);
                this.isInitializing = false;
                return;
            }

            this.renderPoints(points);
            this.renderRanking(points);
            this.isInitializing = false;
        } catch (error) {
            console.error('[DashboardMap] Erreur chargement:', error);
            this.isInitializing = false;
        }
    }

    /** Décale légèrement les kiosques au même emplacement GPS */
    spreadOverlapping(points) {
        const groups = {};
        points.forEach((p) => {
            const key = `${p.latitude.toFixed(4)}|${p.longitude.toFixed(4)}`;
            if (!groups[key]) groups[key] = [];
            groups[key].push(p);
        });

        const spread = [];
        Object.values(groups).forEach((group) => {
            if (group.length === 1) {
                spread.push(group[0]);
                return;
            }
            const step = (2 * Math.PI) / group.length;
            const offsetM = 120;
            group.forEach((p, i) => {
                const angle = i * step;
                const latRad = (p.latitude * Math.PI) / 180;
                spread.push({
                    ...p,
                    latitude: p.latitude + (offsetM / 111320) * Math.cos(angle),
                    longitude: p.longitude + (offsetM / (111320 * Math.cos(latRad))) * Math.sin(angle),
                });
            });
        });
        return spread;
    }

    performanceTier(point, maxMontant) {
        if (maxMontant <= 0 || point.montant <= 0) return 'low';
        const ratio = point.montant / maxMontant;
        if (ratio >= 0.66) return 'high';
        if (ratio >= 0.33) return 'mid';
        return 'low';
    }

    tierStyles(tier) {
        const map = {
            high: { bg: '#059669', ring: '#34d399', label: 'Forte' },
            mid: { bg: '#d97706', ring: '#fbbf24', label: 'Moyenne' },
            low: { bg: '#64748b', ring: '#94a3b8', label: 'Faible' },
        };
        return map[tier] || map.low;
    }

    formatMontant(montant) {
        return new Intl.NumberFormat('fr-FR', {
            style: 'currency',
            currency: 'XOF',
            maximumFractionDigits: 0,
        }).format(montant || 0);
    }

    createMarkerIcon(point, tier) {
        const style = this.tierStyles(tier);
        const html = `
            <div class="dashboard-kiosk-marker" style="
                width:36px;height:36px;border-radius:50%;
                background:${style.bg};color:#fff;
                border:3px solid ${style.ring};
                display:flex;align-items:center;justify-content:center;
                font-size:13px;font-weight:700;
                box-shadow:0 4px 12px rgba(15,23,42,.25);
            ">${point.rang || '—'}</div>`;

        return L.divIcon({
            html,
            className: 'dashboard-kiosk-marker-wrap',
            iconSize: [36, 36],
            iconAnchor: [18, 18],
            popupAnchor: [0, -20],
        });
    }

    renderPoints(points) {
        this.clearMarkers();

        const sorted = [...points].sort((a, b) => (b.montant || 0) - (a.montant || 0));
        const maxMontant = sorted[0]?.montant || 0;
        const spread = this.spreadOverlapping(sorted);
        const bounds = [];

        spread.forEach((point) => {
            if (!point.latitude || !point.longitude) return;

            const center = [point.latitude, point.longitude];
            bounds.push(center);
            const tier = this.performanceTier(point, maxMontant);
            const style = this.tierStyles(tier);

            const marker = L.marker(center, {
                icon: this.createMarkerIcon(point, tier),
                zIndexOffset: 1000 - (point.rang || 99),
            }).addTo(this.map);

            const zoneLabel = point.zone || point.nom || 'Zone';
            const villeLabel = point.ville ? `, ${point.ville}` : '';
            marker.bindPopup(`
                <div style="min-width:220px;font-size:13px;line-height:1.5">
                    <div style="font-weight:700;margin-bottom:6px">Zone ${zoneLabel}${villeLabel}</div>
                    <div><strong>Rang :</strong> #${point.rang || '—'}</div>
                    <div><strong>Kiosques :</strong> ${point.kiosques ?? 1}</div>
                    <div><strong>CA du mois :</strong> ${this.formatMontant(point.montant)}</div>
                    <div><strong>Part :</strong> ${point.part_pct ?? 0} %</div>
                    <div><strong>Transactions :</strong> ${point.transactions ?? 0}</div>
                    <div style="margin-top:6px;color:${style.bg};font-weight:600">Performance ${style.label.toLowerCase()}</div>
                </div>
            `);

            this.markers.push(marker);
        });

        if (bounds.length === 1) {
            this.map.setView(bounds[0], 14);
        } else if (bounds.length > 1) {
            this.map.fitBounds(bounds, { padding: [48, 48], maxZoom: 14 });
        }
    }

    renderRanking(points) {
        const container = document.getElementById('dashboard_month_map_ranking');
        if (!container) return;

        if (!points.length) {
            container.innerHTML = '<p class="text-xs text-secondary-foreground">Aucune zone géolocalisée avec activité ce mois.</p>';
            return;
        }

        const sorted = [...points].sort((a, b) => (a.rang || 99) - (b.rang || 99));
        const maxMontant = Math.max(...sorted.map((p) => p.montant || 0), 1);

        container.innerHTML = `
            <div class="flex flex-wrap items-center gap-3 mb-3 text-xs text-secondary-foreground">
                <span class="inline-flex items-center gap-1.5"><span style="width:10px;height:10px;border-radius:50%;background:#059669;display:inline-block"></span> Forte</span>
                <span class="inline-flex items-center gap-1.5"><span style="width:10px;height:10px;border-radius:50%;background:#d97706;display:inline-block"></span> Moyenne</span>
                <span class="inline-flex items-center gap-1.5"><span style="width:10px;height:10px;border-radius:50%;background:#64748b;display:inline-block"></span> Faible</span>
            </div>
            <div class="flex flex-col gap-2.5">
                ${sorted.map((p) => {
                    const tier = this.performanceTier(p, maxMontant);
                    const style = this.tierStyles(tier);
                    const width = Math.max(4, Math.round(((p.montant || 0) / maxMontant) * 100));
                    return `
                        <div class="flex flex-col gap-1">
                            <div class="flex items-center justify-between gap-2 text-xs">
                                <span class="font-medium text-foreground truncate">
                                    <span style="color:${style.bg};font-weight:700">#${p.rang}</span> ${p.nom}
                                </span>
                                <span class="shrink-0 font-semibold text-foreground">${this.formatMontant(p.montant)}</span>
                            </div>
                            <div class="h-2 rounded-full bg-muted overflow-hidden">
                                <div class="h-full rounded-full transition-all" style="width:${width}%;background:${style.bg}"></div>
                            </div>
                            <div class="text-[11px] text-secondary-foreground">${p.kiosques ?? 1} kiosque(s) · ${p.transactions ?? 0} trans. · ${p.part_pct ?? 0} % du CA</div>
                        </div>`;
                }).join('')}
            </div>`;
    }

    clearMarkers() {
        this.markers.forEach((m) => {
            if (this.map && m) this.map.removeLayer(m);
        });
        this.markers = [];
    }

    destroy() {
        if (this.map) {
            try {
                this.clearMarkers();
                this.map.remove();
                this.map = null;
            } catch (e) {
                console.warn('[DashboardMap] destroy:', e);
            }
        }
        const ranking = document.getElementById('dashboard_month_map_ranking');
        if (ranking) ranking.innerHTML = '';
        this.isInitializing = false;
    }

    isInitialized() {
        return this.map !== null;
    }
}

const dashboardMonthMapInstance = new DashboardMonthMap();
window.DashboardMonthMap = DashboardMonthMap;
window.dashboardMonthMapInstance = dashboardMonthMapInstance;

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => setTimeout(() => dashboardMonthMapInstance.init(), 200));
} else {
    setTimeout(() => dashboardMonthMapInstance.init(), 200);
}

document.addEventListener('ajax-content-loaded', () => {
    dashboardMonthMapInstance.destroy();
});

export default dashboardMonthMapInstance;
