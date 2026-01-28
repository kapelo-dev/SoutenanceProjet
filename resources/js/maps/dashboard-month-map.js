/**
 * Module pour la carte de performance du mois sur le dashboard
 * Gère la création, destruction et réinitialisation de la carte Leaflet
 */

class DashboardMonthMap {
    constructor() {
        this.map = null;
        this.circles = [];
        this.isInitializing = false;
    }

    /**
     * Initialiser la carte si l'élément existe dans le DOM
     */
    init() {
        // Éviter les initialisations multiples simultanées
        if (this.isInitializing) {
            console.debug('[DashboardMap] Initialisation déjà en cours');
            return;
        }

        // Vérifier si l'élément existe
        const mapElement = document.getElementById('dashboard_month_map');
        if (!mapElement) {
            console.debug('[DashboardMap] Élément #dashboard_month_map introuvable');
            return;
        }

        // Si la carte existe déjà, ne pas réinitialiser
        if (this.map) {
            console.debug('[DashboardMap] Carte déjà initialisée');
            return;
        }

        // Vérifier que Leaflet est disponible
        if (typeof L === 'undefined') {
            console.debug('[DashboardMap] Leaflet non disponible, retry dans 200ms');
            setTimeout(() => this.init(), 200);
            return;
        }

        this.isInitializing = true;
        console.debug('[DashboardMap] Initialisation de la carte...');

        try {
            // Coordonnées par défaut (Lomé, Togo)
            const defaultLat = 6.1375;
            const defaultLng = 1.2123;

            // Créer la carte
            this.map = L.map('dashboard_month_map', {
                zoomControl: true
            }).setView([defaultLat, defaultLng], 14);

            // Ajouter la couche de tuiles
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap contributors',
                maxZoom: 19
            }).addTo(this.map);

            // Charger les données
            this.loadData();
        } catch (error) {
            console.error('[DashboardMap] Erreur lors de l\'initialisation:', error);
            this.isInitializing = false;
        }
    }

    /**
     * Charger les données de performance du mois
     */
    async loadData() {
        try {
            console.debug('[DashboardMap] Chargement des données...');
            const response = await fetch('/api/dashboard/carte-performance-mois');
            
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }

            const points = await response.json();
            console.debug('[DashboardMap] Points reçus:', points);

            if (!Array.isArray(points) || points.length === 0) {
                console.debug('[DashboardMap] Aucun point à afficher');
                this.isInitializing = false;
                return;
            }

            this.renderPoints(points);
            this.isInitializing = false;
        } catch (error) {
            console.error('[DashboardMap] Erreur lors du chargement des données:', error);
            this.isInitializing = false;
        }
    }

    /**
     * Afficher les points sur la carte
     */
    renderPoints(points) {
        // Nettoyer les cercles existants
        this.clearCircles();

        const montants = points.map(p => p.montant || 0);
        const maxMontant = Math.max(...montants);
        const primaryBlue = '#3b82f6';
        const bounds = [];

        points.forEach(point => {
            if (!point.latitude || !point.longitude) {
                return;
            }

            const center = [point.latitude, point.longitude];
            bounds.push(center);

            const montant = point.montant || 0;
            const ratio = maxMontant > 0 ? (montant / maxMontant) : 0;

            // Rayon en mètres : base + amplification par le CA
            const radius = 300 + 2500 * ratio;

            const isTop = montant === maxMontant && maxMontant > 0;

            const circle = L.circle(center, {
                radius: radius,
                color: primaryBlue,
                weight: isTop ? 3 : 1.5,
                fillColor: primaryBlue,
                fillOpacity: isTop ? 0.25 : 0.12
            }).addTo(this.map);

            const montantFormatte = new Intl.NumberFormat('fr-FR', {
                style: 'currency',
                currency: 'XOF',
                maximumFractionDigits: 0
            }).format(montant);

            circle.bindPopup(`
                <div style="min-width: 200px;">
                    <strong>${point.nom}</strong><br/>
                    Chiffre d'affaires du mois : ${montantFormatte}
                </div>
            `);

            this.circles.push(circle);
        });

        // Ajuster la vue
        if (bounds.length === 1) {
            this.map.setView(bounds[0], 15);
        } else if (bounds.length > 1) {
            this.map.fitBounds(bounds, { padding: [40, 40] });
        }
    }

    /**
     * Nettoyer tous les cercles de la carte
     */
    clearCircles() {
        this.circles.forEach(circle => {
            if (this.map && circle) {
                this.map.removeLayer(circle);
            }
        });
        this.circles = [];
    }

    /**
     * Détruire complètement la carte
     */
    destroy() {
        console.debug('[DashboardMap] Destruction de la carte...');
        
        if (this.map) {
            try {
                this.clearCircles();
                this.map.remove();
                this.map = null;
            } catch (error) {
                console.warn('[DashboardMap] Erreur lors de la destruction:', error);
            }
        }
        
        this.isInitializing = false;
    }

    /**
     * Vérifier si la carte est initialisée
     */
    isInitialized() {
        return this.map !== null;
    }
}

// Créer une instance unique (singleton)
const dashboardMonthMapInstance = new DashboardMonthMap();

// Exposer globalement pour être accessible depuis ajax-navigation.js
window.DashboardMonthMap = DashboardMonthMap;
window.dashboardMonthMapInstance = dashboardMonthMapInstance;

// Auto-initialisation si le DOM est prêt
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        setTimeout(() => dashboardMonthMapInstance.init(), 200);
    });
} else {
    setTimeout(() => dashboardMonthMapInstance.init(), 200);
}

// Réinitialisation après navigation AJAX
document.addEventListener('ajax-content-loaded', () => {
    console.debug('[DashboardMap] ajax-content-loaded déclenché');
    // Détruire l'ancienne carte si elle existe
    dashboardMonthMapInstance.destroy();
    // Réinitialiser après un court délai
    setTimeout(() => dashboardMonthMapInstance.init(), 200);
});

export default dashboardMonthMapInstance;
