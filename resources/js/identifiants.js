/**
 * Fiche imprimable d'identifiants de connexion (création d'agent ou d'utilisateur, régénération).
 * Même charte que les exports PDF (App\Support\PdfBranding : logo, couleurs, en-tête, pied de page).
 * Imprimée depuis une iframe cachée : pas de fenêtre popup, et seule la fiche part à l'impression.
 */
const LOGO_PATH = '/assets/media/app/pdv-connect-logo-pdf.png';

// Copie de PdfBranding::COLORS
const COULEURS = {
    primary: '#1a3a6e',
    primaryDark: '#122a52',
    accent: '#f5c400',
    accentSoft: '#fef9e7',
    text: '#1f2937',
    muted: '#6b7280',
    border: '#e2e8f0',
    rowAlt: '#f8fafc',
};

function echapper(valeur) {
    return String(valeur ?? '').replace(/[&<>"']/g, (c) => ({
        '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;',
    })[c]);
}

function imprimerIdentifiants({ nom = '', identifiant, libelleIdentifiant = 'Identifiant', motDePasse }) {
    const c = COULEURS;
    const date = new Date().toLocaleString('fr-FR', { dateStyle: 'short', timeStyle: 'short' });
    const logoUrl = window.location.origin + LOGO_PATH;

    const iframe = document.createElement('iframe');
    iframe.style.cssText = 'position:fixed;right:0;bottom:0;width:0;height:0;border:0;';
    document.body.appendChild(iframe);

    const doc = iframe.contentWindow.document;
    doc.open();
    doc.write(`<!DOCTYPE html><html lang="fr"><head><meta charset="utf-8">
<title>PDV Connect — Identifiants ${echapper(identifiant)}</title>
<style>
  @page { size: A4; margin: 14mm; }
  * { box-sizing: border-box; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
  body { font-family: 'DejaVu Sans', Arial, sans-serif; color: ${c.text}; margin: 0; font-size: 11pt; line-height: 1.45; }
  .entete { display: flex; border-radius: 8px; overflow: hidden; border: 1px solid ${c.border}; }
  .entete-logo { flex: 0 0 46%; background: #fff; padding: 10px 18px; display: flex; align-items: center; }
  .entete-logo img { height: 56px; width: auto; max-width: 100%; }
  .entete-titre { flex: 1; background: ${c.primaryDark}; color: #fff; padding: 12px 22px; display: flex; flex-direction: column; justify-content: center; }
  .entete-titre .titre { font-size: 14pt; font-weight: bold; margin: 0; }
  .entete-titre .meta { font-size: 9pt; color: #cbd5e1; margin: 2px 0 0; }
  .filet { height: 3px; background: ${c.accent}; border-radius: 2px; margin: 10px 0 22px; }
  .alerte { background: ${c.accentSoft}; border-left: 4px solid ${c.accent}; padding: 10px 14px; margin-bottom: 22px; font-size: 10pt; }
  .alerte strong { color: ${c.primary}; }
  table { border-collapse: collapse; width: 100%; }
  th { background: ${c.primary}; color: #fff; text-align: left; padding: 9px 14px; font-size: 10pt; border-bottom: 2px solid ${c.accent}; }
  td { padding: 12px 14px; border-bottom: 1px solid ${c.border}; }
  tr:nth-child(even) td { background: ${c.rowAlt}; }
  td.libelle { width: 42%; color: ${c.muted}; }
  td.valeur { font-family: 'Courier New', monospace; font-weight: bold; font-size: 15pt; letter-spacing: 1.5px; color: ${c.primary}; }
  .consignes { margin-top: 24px; font-size: 10pt; }
  .consignes h2 { font-size: 11pt; color: ${c.primary}; margin: 0 0 6px; }
  .consignes ol { margin: 0; padding-left: 20px; }
  .consignes li { margin-bottom: 4px; }
  .pied { margin-top: 34px; padding-top: 8px; border-top: 1px solid ${c.border}; text-align: center; font-size: 8.5pt; color: ${c.muted}; }
  .pied strong { color: ${c.primary}; }
</style></head><body>
  <div class="entete">
    <div class="entete-logo"><img src="${logoUrl}" alt="PDV Connect"></div>
    <div class="entete-titre">
      <p class="titre">Identifiants de connexion</p>
      <p class="meta">Document confidentiel — édité le ${echapper(date)}</p>
    </div>
  </div>
  <div class="filet"></div>

  <div class="alerte">
    <strong>Mot de passe temporaire :</strong> il devra être remplacé par un mot de passe personnel
    dès la prochaine connexion (application mobile ou plateforme web).
  </div>

  <table>
    <tr><th colspan="2">Compte</th></tr>
    ${nom ? `<tr><td class="libelle">Titulaire</td><td>${echapper(nom)}</td></tr>` : ''}
    <tr><td class="libelle">${echapper(libelleIdentifiant)}</td><td class="valeur">${echapper(identifiant)}</td></tr>
    <tr><td class="libelle">Mot de passe temporaire</td><td class="valeur">${echapper(motDePasse)}</td></tr>
  </table>

  <div class="consignes">
    <h2>Première connexion</h2>
    <ol>
      <li>Se connecter avec l'identifiant et le mot de passe temporaire ci-dessus.</li>
      <li>Choisir un nouveau mot de passe personnel lorsque l'application le demande.</li>
      <li>Ne jamais communiquer son mot de passe ; détruire ce document une fois le mot de passe changé.</li>
    </ol>
  </div>

  <div class="pied"><strong>PDV Connect</strong> — Plateforme de gestion Mobile Money</div>
</body></html>`);
    doc.close();

    let imprime = false;
    const lancer = () => {
        if (imprime) return;
        imprime = true;
        iframe.contentWindow.focus();
        iframe.contentWindow.print();
        setTimeout(() => iframe.remove(), 1000);
    };

    // Attendre le logo avant d'imprimer (sinon il manque sur la fiche) ; secours si l'image ne charge pas
    const logo = doc.querySelector('img');
    if (logo.complete) {
        lancer();
    } else {
        logo.addEventListener('load', lancer);
        logo.addEventListener('error', lancer);
        setTimeout(lancer, 3000);
    }
}

window.imprimerIdentifiants = imprimerIdentifiants;
