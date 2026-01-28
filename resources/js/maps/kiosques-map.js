/**
 * Module pour la carte des kiosques
 * Gère la création, destruction et réinitialisation de la carte Leaflet
 */

class KiosquesMap {
    constructor() {
        this.map = null;
        this.markers = [];
        this.isInitializing = false;
    }

    /**
     * Initialiser la carte si l'élément existe dans le DOM
     */
    init() {
        // Éviter les initialisations multiples simultanées
        if (this.isInitializing) {
            console.debug('[KiosquesMap] Initialisation déjà en cours');
            return;
        }

        // Vérifier si l'élément existe
        const mapElement = document.getElementById('kiosques_map');
        if (!mapElement) {
            console.debug('[KiosquesMap] Élément #kiosques_map introuvable');
            return;
        }

        // Si la carte existe déjà, ne pas réinitialiser
        if (this.map) {
            console.debug('[KiosquesMap] Carte déjà initialisée');
            return;
        }

        // Vérifier que Leaflet est disponible
        if (typeof L === 'undefined') {
            console.debug('[KiosquesMap] Leaflet non disponible, retry dans 200ms');
            setTimeout(() => this.init(), 200);
            return;
        }

        this.isInitializing = true;
        console.debug('[KiosquesMap] Initialisation de la carte...');

        try {
            // Coordonnées par défaut (Lomé, Togo)
            const defaultLat = 6.1375;
            const defaultLng = 1.2123;

            // Créer la carte
            this.map = L.map('kiosques_map').setView([defaultLat, defaultLng], 12);

            // Ajouter la couche de tuiles
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap contributors',
                maxZoom: 19
            }).addTo(this.map);

            // Charger les données
            this.loadData();
        } catch (error) {
            console.error('[KiosquesMap] Erreur lors de l\'initialisation:', error);
            this.isInitializing = false;
        }
    }

    /**
     * Charger les données des kiosques
     */
    async loadData() {
        try {
            console.debug('[KiosquesMap] Chargement des données...');
            const response = await fetch('/api/kiosques/carte-data');
            
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }

            const kiosques = await response.json();
            console.debug('[KiosquesMap] Kiosques reçus:', kiosques);

            if (!Array.isArray(kiosques) || kiosques.length === 0) {
                console.debug('[KiosquesMap] Aucun kiosque à afficher');
                this.isInitializing = false;
                return;
            }

            this.renderKiosques(kiosques);
            this.isInitializing = false;
        } catch (error) {
            console.error('[KiosquesMap] Erreur lors du chargement des données:', error);
            this.isInitializing = false;
        }
    }

    /**
     * Afficher les kiosques sur la carte
     */
    renderKiosques(kiosques) {
        // Nettoyer les marqueurs existants
        this.clearMarkers();

        // Ajuster la vue pour afficher tous les kiosques
        if (kiosques.length === 1) {
            this.map.setView([kiosques[0].latitude, kiosques[0].longitude], 15);
        } else {
            const bounds = L.latLngBounds(kiosques.map(k => [k.latitude, k.longitude]));
            this.map.fitBounds(bounds, { padding: [50, 50] });
        }

        // Ajouter les marqueurs
        kiosques.forEach(kiosque => {
            // Couleur du marqueur selon le statut
            const primaryBlue = '#1e293b';
            let markerColor = '#10b981'; // Vert par défaut (actif)
            
            if (kiosque.est_sature) {
                markerColor = primaryBlue; // Bleu pour les kiosques saturés
            } else if (kiosque.statut === 'en_travaux') {
                markerColor = '#f59e0b'; // Jaune si en travaux
            } else if (kiosque.statut === 'inactif') {
                markerColor = '#6b7280'; // Gris si inactif
            }

            // Créer un marqueur personnalisé avec l'icône kiosque
            const kiosqueIcon = L.divIcon({
                className: 'kiosque-marker',
                html: `<div style="background-color: ${markerColor}; width: 32px; height: 32px; border-radius: 50%; border: 3px solid white; box-shadow: 0 2px 8px rgba(0,0,0,0.4); display: flex; align-items: center; justify-content: center;"><i class="ki-filled ki-shop text-white" style="font-size: 16px;"></i></div>`,
                iconSize: [32, 32],
                iconAnchor: [16, 16]
            });

            const marker = L.marker([kiosque.latitude, kiosque.longitude], {
                icon: kiosqueIcon
            }).addTo(this.map);

            // Popup avec les informations du kiosque
            const popupContent = `
                <div style="min-width: 200px;">
                    <h4 style="font-weight: 600; margin-bottom: 8px; font-size: 14px;">${kiosque.nom}</h4>
                    ${kiosque.code ? `<p style="margin: 4px 0; font-size: 12px; color: #6b7280;">${kiosque.code}</p>` : ''}
                    ${kiosque.quartier ? `<p style="margin: 4px 0; font-size: 12px; color: #6b7280;">📍 ${kiosque.quartier}${kiosque.ville ? ', ' + kiosque.ville : ''}</p>` : ''}
                    <p style="margin: 4px 0; font-size: 12px; color: #6b7280;">Agents: ${kiosque.agents_count}/${kiosque.capacite}</p>
                    <p style="margin: 4px 0; font-size: 12px; color: #6b7280;">Type: ${kiosque.type === 'fixe' ? 'Fixe' : 'Mobile'}</p>
                    <p style="margin: 4px 0; font-size: 12px; color: #6b7280;">Statut: ${kiosque.statut === 'actif' ? 'Actif' : (kiosque.statut === 'en_travaux' ? 'En travaux' : 'Inactif')}</p>
                    ${kiosque.telephone ? `<p style="margin: 4px 0; font-size: 12px;"><a href="tel:${kiosque.telephone}" style="color: #3b82f6;">${kiosque.telephone}</a></p>` : ''}
                </div>
            `;

            marker.bindPopup(popupContent);
            this.markers.push(marker);
        });
    }

    /**
     * Nettoyer tous les marqueurs de la carte
     */
    clearMarkers() {
        this.markers.forEach(marker => {
            if (this.map && marker) {
                this.map.removeLayer(marker);
            }
        });
        this.markers = [];
    }

    /**
     * Détruire complètement la carte
     */
    destroy() {
        console.debug('[KiosquesMap] Destruction de la carte...');
        
        if (this.map) {
            try {
                this.clearMarkers();
                this.map.remove();
                this.map = null;
            } catch (error) {
                console.warn('[KiosquesMap] Erreur lors de la destruction:', error);
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
const kiosquesMapInstance = new KiosquesMap();

// Exposer globalement pour être accessible depuis ajax-navigation.js
window.KiosquesMap = KiosquesMap;
window.kiosquesMapInstance = kiosquesMapInstance;

// Auto-initialisation si le DOM est prêt
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        setTimeout(() => kiosquesMapInstance.init(), 200);
    });
} else {
    setTimeout(() => kiosquesMapInstance.init(), 200);
}

// Réinitialisation après navigation AJAX
document.addEventListener('ajax-content-loaded', () => {
    console.debug('[KiosquesMap] ajax-content-loaded déclenché');
    // Détruire l'ancienne carte si elle existe
    kiosquesMapInstance.destroy();
    // Réinitialiser après un court délai
    setTimeout(() => kiosquesMapInstance.init(), 200);
});

export default kiosquesMapInstance;
