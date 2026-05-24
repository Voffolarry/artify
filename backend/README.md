# Artify — Backend (PHP + MongoDB)

API REST JSON pour le frontend Artify.

Documentation complète (déploiement XAMPP, images, dépannage) : **[README.md](../README.md)** à la racine du projet.

---

## Configuration

| Fichier | Rôle |
|---------|------|
| `.env` | Secrets (MongoDB, `SECRET_KEY`) — **non versionné** |
| `.env.example` | Modèle à copier vers `.env` |
| `api/config/env.php` | Charge Dotenv |
| `api/config/db.php` | Connexion MongoDB (lit `.env`) |
| `api/config/helpers.php` | CORS, session, réponses JSON |
| `api/config/media.php` | Mapping titres ↔ fichiers images |
| `api/config/seed.php` | Peuplement initial (**efface** les collections) |
| `api/config/update-images.php` | Met à jour `image_url` / `photo_url` sans tout effacer |
| `api/config/test-db.php` | Test de connexion MongoDB |
| `.htaccess` | Réécriture `/api/oeuvres/` → `api/oeuvres/index.php` |

### Composer

```powershell
cd backend
composer install
```

Dépendance principale : `mongodb/mongodb`.

---

## Scripts utiles (depuis `htdocs\artify` après déploiement)

```powershell
php api\config\test-db.php
php api\config\seed.php
php api\config\update-images.php
```

---

## Endpoints

Tous les détails (méthodes, auth, paramètres, exemples) sont dans le **[README principal](../README.md#api-rest)**.

Règle : utiliser **`/api/.../index.php`** dans le frontend (ex. `/artify/api/favoris/index.php`).

Routes protégées (session obligatoire) : favoris, commandes, profil, déconnexion.

---

## Format de réponse

```json
{ "succes": true, "data": { } }
```

```json
{ "succes": false, "erreur": "Message" }
```
