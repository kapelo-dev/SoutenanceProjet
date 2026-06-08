/**
 * Courbe d'évolution des transactions sur le dashboard
 */

class DashboardEvolutionChart {
    constructor() {
        this.chart = null;
        this.container = null;
        this.activePeriod = '7jours';
    }

    init() {
        this.container = document.getElementById('dashboard_evolution_chart');
        if (!this.container) {
            return;
        }

        if (typeof ApexCharts === 'undefined') {
            setTimeout(() => this.init(), 200);
            return;
        }

        this.bindPeriodButtons();

        const initial = this.readInitialData();
        this.render(initial);

        if (!initial.length) {
            this.loadPeriod(this.activePeriod);
        }
    }

    readInitialData() {
        const raw = this.container?.dataset?.initial;
        if (!raw) {
            return [];
        }

        try {
            return JSON.parse(raw).map((item) => ({
                label: this.formatDayLabel(item.date, item.jour),
                montant: Number(item.montant) || 0,
                count: Number(item.count) || 0,
            }));
        } catch {
            return [];
        }
    }

    formatDayLabel(dateStr, dayName) {
        const date = new Date(dateStr);
        if (Number.isNaN(date.getTime())) {
            return dayName || dateStr;
        }

        return date.toLocaleDateString('fr-FR', { weekday: 'short', day: '2-digit', month: 'short' });
    }

    bindPeriodButtons() {
        document.querySelectorAll('[data-dashboard-evolution-period]').forEach((button) => {
            if (button._evolutionBound) {
                return;
            }
            button._evolutionBound = true;

            button.addEventListener('click', () => {
                const period = button.getAttribute('data-dashboard-evolution-period');
                if (!period || period === this.activePeriod) {
                    return;
                }

                this.activePeriod = period;
                document.querySelectorAll('[data-dashboard-evolution-period]').forEach((el) => {
                    el.classList.toggle('kt-btn-primary', el === button);
                    el.classList.toggle('kt-btn-outline', el !== button);
                });

                this.loadPeriod(period);
            });
        });
    }

    async loadPeriod(period) {
        try {
            const response = await fetch(`/api/dashboard/graphique-transactions?periode=${period}`, {
                headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            });

            if (!response.ok) {
                throw new Error('Erreur chargement graphique');
            }

            const data = await response.json();
            this.render(data.map((item) => ({
                label: item.label,
                montant: Number(item.montant) || 0,
                count: Number(item.count) || 0,
            })));
        } catch (error) {
            console.error('[DashboardEvolutionChart]', error);
        }
    }

    render(data) {
        if (!this.container || typeof ApexCharts === 'undefined') {
            return;
        }

        if (this.chart) {
            this.chart.destroy();
            this.chart = null;
        }

        const isDark = document.documentElement.classList.contains('dark');
        const labels = data.map((item) => item.label);
        const montants = data.map((item) => item.montant);
        const counts = data.map((item) => item.count);

        const options = {
            chart: {
                type: 'area',
                height: 320,
                fontFamily: 'inherit',
                toolbar: { show: false },
                zoom: { enabled: false },
                animations: { enabled: true, easing: 'easeinout', speed: 500 },
            },
            series: [
                { name: 'Montant (FCFA)', data: montants },
                { name: 'Transactions', data: counts },
            ],
            colors: ['#1a3a6e', '#f5c400'],
            stroke: { curve: 'smooth', width: [3, 2] },
            fill: {
                type: 'gradient',
                gradient: {
                    shadeIntensity: 1,
                    opacityFrom: 0.35,
                    opacityTo: 0.03,
                    stops: [0, 90, 100],
                },
            },
            dataLabels: { enabled: false },
            xaxis: {
                categories: labels,
                axisBorder: { show: false },
                axisTicks: { show: false },
                labels: {
                    style: { colors: isDark ? '#94a3b8' : '#64748b', fontSize: '11px' },
                    rotate: labels.length > 14 ? -45 : 0,
                },
            },
            yaxis: [
                {
                    title: { text: 'Montant (FCFA)', style: { color: isDark ? '#94a3b8' : '#64748b', fontSize: '11px' } },
                    labels: {
                        formatter: (value) => this.formatCompact(value),
                        style: { colors: isDark ? '#94a3b8' : '#64748b', fontSize: '11px' },
                    },
                },
                {
                    opposite: true,
                    title: { text: 'Transactions', style: { color: isDark ? '#94a3b8' : '#64748b', fontSize: '11px' } },
                    labels: {
                        formatter: (value) => Math.round(value).toString(),
                        style: { colors: isDark ? '#94a3b8' : '#64748b', fontSize: '11px' },
                    },
                },
            ],
            grid: {
                borderColor: isDark ? '#334155' : '#e2e8f0',
                strokeDashArray: 4,
                padding: { left: 8, right: 8 },
            },
            legend: {
                position: 'top',
                horizontalAlign: 'right',
                labels: { colors: isDark ? '#cbd5e1' : '#475569' },
            },
            tooltip: {
                shared: true,
                intersect: false,
                y: {
                    formatter: (value, { seriesIndex }) => {
                        if (seriesIndex === 0) {
                            return `${new Intl.NumberFormat('fr-FR').format(value)} FCFA`;
                        }
                        return `${value} transaction${value > 1 ? 's' : ''}`;
                    },
                },
            },
            markers: {
                size: 0,
                hover: { size: 5 },
            },
        };

        this.chart = new ApexCharts(this.container, options);
        this.chart.render();
    }

    formatCompact(value) {
        const n = Number(value) || 0;
        if (n >= 1_000_000) {
            return `${(n / 1_000_000).toFixed(1)}M`;
        }
        if (n >= 1_000) {
            return `${Math.round(n / 1_000)}k`;
        }
        return Math.round(n).toString();
    }

    destroy() {
        if (this.chart) {
            this.chart.destroy();
            this.chart = null;
        }
        this.container = null;
    }
}

const dashboardEvolutionChartInstance = new DashboardEvolutionChart();
window.dashboardEvolutionChartInstance = dashboardEvolutionChartInstance;

document.addEventListener('DOMContentLoaded', () => {
    setTimeout(() => dashboardEvolutionChartInstance.init(), 150);
});

document.addEventListener('ajax-content-loaded', () => {
    dashboardEvolutionChartInstance.destroy();
    setTimeout(() => dashboardEvolutionChartInstance.init(), 150);
});

export default dashboardEvolutionChartInstance;
