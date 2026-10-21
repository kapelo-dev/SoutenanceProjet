/**
 * Fiche imprimable d'identifiants de connexion (création d'agent, régénération de mot de passe).
 * Imprimée depuis une iframe cachée : pas de fenêtre popup, et seule la fiche part à l'impression.
 */
function echapper(valeur) {
    return String(valeur ?? '').replace(/[&<>"']/g, (c) => ({
        '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;',
    })[c]);
}

function imprimerIdentifiants({ application = '', nom = '', identifiant, libelleIdentifiant = 'Identifiant', motDePasse }) {
    const date = new Date().toLocaleString('fr-FR');

    const iframe = document.createElement('iframe');
    iframe.style.cssText = 'position:fixed;right:0;bottom:0;width:0;height:0;border:0;';
    document.body.appendChild(iframe);

    const doc = iframe.contentWindow.document;
    doc.open();
    doc.write(`<!DOCTYPE html><html lang="fr"><head><meta charset="utf-8">
<title>Identifiants ${echapper(identifiant)}</title>
<style>
  body { font-family: Arial, sans-serif; color: #111; margin: 40px; }
  h1 { font-size: 20px; margin: 0 0 4px; }
  .sous-titre { color: #555; font-size: 13px; margin-bottom: 24px; }
  table { border-collapse: collapse; width: 100%; max-width: 520px; }
  td { border: 1px solid #999; padding: 10px 14px; font-size: 15px; }
  td:first-child { background: #f3f3f3; width: 45%; }
  .valeur { font-family: 'Courier New', monospace; font-weight: bold; font-size: 17px; letter-spacing: 1px; }
  .note { margin-top: 24px; font-size: 13px; color: #333; max-width: 520px; line-height: 1.5; }
</style></head><body>
  <h1>${application ? echapper(application) + ' — ' : ''}Identifiants de connexion</h1>
  <div class="sous-titre">Édité le ${echapper(date)}</div>
  <table>
    ${nom ? `<tr><td>Utilisateur</td><td>${echapper(nom)}</td></tr>` : ''}
    <tr><td>${echapper(libelleIdentifiant)}</td><td class="valeur">${echapper(identifiant)}</td></tr>
    <tr><td>Mot de passe temporaire</td><td class="valeur">${echapper(motDePasse)}</td></tr>
  </table>
  <p class="note">À la prochaine connexion (application mobile ou web), il faudra choisir un nouveau mot de passe.
  Ce document est confidentiel : remettez-le uniquement à la personne concernée puis détruisez-le.</p>
</body></html>`);
    doc.close();

    const lancer = () => {
        iframe.contentWindow.focus();
        iframe.contentWindow.print();
        setTimeout(() => iframe.remove(), 1000);
    };
    if (doc.readyState === 'complete') {
        lancer();
    } else {
        iframe.onload = lancer;
    }
}

window.imprimerIdentifiants = imprimerIdentifiants;
