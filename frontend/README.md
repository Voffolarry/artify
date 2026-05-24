# Artify — Frontend

Pages statiques HTML/CSS/JS consommant l’API PHP sous `/artify/api/`.

Documentation complète (installation, images, dépannage) : **[README.md](../README.md)** à la racine du projet.

---

## Lancement rapide

1. Démarrer **Apache** (XAMPP)
2. Déployer : `powershell -ExecutionPolicy Bypass -File scripts\deploy-xampp.ps1`
3. Ouvrir : **http://localhost/artify/index.html**

Ne pas utiliser `file://` ni l’URL `/artify/frontend/...`.

---

## Fichiers principaux

| Fichier | Rôle |
|---------|------|
| `js/main.js` | API (`API_BASE`), session, favoris, `imageUrlAffichable()` |
| `js/catalogue.js` | Grille, filtres, modale |
| `js/login.js` | Connexion / inscription |
| `js/profil.js` | Profil, commandes, favoris |
| `js/artiste.js` | Liste et fiche artistes |
| `images/oeuvres/` | Photos œuvres (noms → `backend/api/config/media.php`) |
| `images/artistes/` | Portraits artistes |

---

## Compte de test

Après `php api/config/seed.php` (dans `htdocs\artify`) :

- **Email** : `marie@artify.fr`
- **Mot de passe** : `artify2026`

Connexion requise pour synchroniser les **favoris** et les **achats** avec MongoDB.
