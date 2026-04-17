# Documentation PDV.Connect

## Diagrammes UML

Le fichier **`diagrammes-uml.md`** contient les diagrammes du projet au format Mermaid :

1. **Diagramme de classes** — Modèle de domaine (Utilisateur, Profil, Agent, Kiosque, Transaction, Operateur, Solde, Salaire, etc.).
2. **Diagramme de cas d'utilisation** — Acteurs (Administrateur, Utilisateur, Agent) et cas d'usage (connexion, profil, utilisateurs, agents, kiosques, transactions, dashboard, rôles, entreprise).
3. **Diagrammes d'activité** — Connexion, changement de mot de passe, création de transaction, mise à jour du profil.
4. **Diagrammes de séquence** — Connexion, changement de mot de passe, mise à jour du profil, consultation du dashboard.

### Comment visualiser

- **GitHub / GitLab** : ouvrir `diagrammes-uml.md` ; les blocs Mermaid sont rendus automatiquement.
- **Éditeur (VS Code, Cursor)** : avec une extension « Mermaid » ou « Markdown Preview Mermaid ».
- **En ligne** : copier un bloc de code ` ```mermaid ... ``` ` dans [Mermaid Live Editor](https://mermaid.live) pour prévisualiser ou exporter en PNG/SVG.

### Mise à jour

En cas d’évolution du modèle (nouveaux champs, relations, contrôleurs), adapter les diagrammes dans `diagrammes-uml.md` pour garder la cohérence avec le code.
