# Artify

Galerie d'art en ligne — frontend HTML/CSS/JS + API REST PHP + MongoDB.

---

## Structure du dépôt

```
artify/
├── frontend/          Pages HTML, CSS, JS, images statiques
│   ├── images/
│   │   ├── oeuvres/   Photos des œuvres (noms = media.php)
│   │   └── artistes/  Portraits des artistes
│   └── js/main.js     Session, API, favoris, images
├── backend/
│   ├── api/           Endpoints PHP (auth, oeuvres, favoris…)
│   └── api/config/    db.php, seed, media, scripts utilitaires
├── scripts/
│   └── deploy-xampp.ps1   Déploiement vers XAMPP (recommandé)
└── README.md
```

**Ne pas** copier tout le dossier Git tel quel dans `htdocs` : les URLs `/artify/api/...` et `/artify/images/...` ne fonctionneront pas. Utilisez le script de déploiement (voir ci-dessous).

---

## Prérequis

| Outil | Version |
|-------|---------|
| PHP | ≥ 8.1 |
| XAMPP | ≥ 8.x (Apache + PHP) |
| MongoDB | Atlas ou local ≥ 7.x |
| Extension PHP `mongodb` | `pecl install mongodb` puis `extension=mongodb` dans `php.ini` |
| Composer | ≥ 2.x |

---

## Installation (Windows / XAMPP)

### 1. Extension MongoDB pour PHP

```bash
pecl install mongodb
```

Dans `php.ini` (XAMPP) :

```ini
extension=mongodb
```

### 2. Variables d'environnement (`.env`)

```powershell
cd backend
copy .env.example .env
```

Éditez `backend/.env` :

| Variable | Description |
|----------|-------------|
| `MONGO_URI` | Chaîne MongoDB (Atlas ou `mongodb://127.0.0.1:27017`) |
| `MONGO_DB` | Nom de la base (`artify`) |
| `SECRET_KEY` | Clé secrète sessions PHP (chaîne aléatoire longue) |
| `MEDIA_BASE_URL` | Préfixe URL images (`/artify/images`) |

Le fichier **`.env` n'est jamais versionné** (voir `backend/.gitignore`). Seul `.env.example` est dans Git.

Le script `deploy-xampp.ps1` copie `.env` vers `htdocs/artify/` automatiquement.

### 3. Dépendances PHP

```powershell
cd backend
composer install
```

### 4. Déployer vers XAMPP

```powershell
cd c:\projects\ARTIFY\artify
powershell -ExecutionPolicy Bypass -File scripts\deploy-xampp.ps1
```

Le script copie :

- `frontend/*` → `C:\xampp\htdocs\artify\`
- `backend/api` → `C:\xampp\htdocs\artify\api\`
- `.htaccess`, `composer.json`, installe `vendor/` si besoin

Autre cible : `$env:ARTIFY_HTDOCS = "D:\mon\chemin\artify"` avant d'exécuter le script.

### 5. Démarrer les services

1. **XAMPP** — démarrer **Apache**
2. **MongoDB** — cluster Atlas accessible ou `mongod` en local

### 6. Initialiser la base

```powershell
cd C:\xampp\htdocs\artify
php api\config\seed.php
```

Contenu créé :

- 4 artistes, 9 œuvres
- Compte test : **`marie@artify.fr`** / **`artify2026`**

### 7. Tester la connexion MongoDB

```powershell
php api\config\test-db.php
```

---

## Accéder au site

| Page | URL |
|------|-----|
| Accueil | http://localhost/artify/index.html |
| Catalogue | http://localhost/artify/catalogue.html |
| Connexion | http://localhost/artify/login.html |
| Profil | http://localhost/artify/profil.html |

**Important**

- Ouvrir via **`http://localhost`**, jamais en double-cliquant un fichier HTML (`file://` bloque l’API).
- Utiliser **`/artify/`**, pas **`/artify/frontend/`** (ancienne copie manuelle du dépôt).

Après chaque modification du code :

```powershell
powershell -ExecutionPolicy Bypass -File scripts\deploy-xampp.ps1
```

Puis recharger la page avec **Ctrl+F5**.

---

## Images (œuvres et artistes)

### Fichiers sur disque

| Dossier | Rôle |
|---------|------|
| `frontend/images/oeuvres/` | Vignettes catalogue |
| `frontend/images/artistes/` | Portraits page artistes |
| `frontend/images/fond-hero.jpg` | Fond CSS (accueil, login…) |
| `frontend/images/fond-artistes.jpg` | Fond page artistes |

### Noms de fichiers attendus

Définis dans `backend/api/config/media.php` :

**Œuvres**

| Fichier | Titre en base |
|---------|----------------|
| `lumiere-aout.jpg` | Lumière d'Août |
| `jardin-secret.jpg` | Jardin Secret |
| `crepuscule-violet.jpg` | Crépuscule Violet |
| `sao-paulo-4h.jpg` | São Paulo 4h du Matin |
| `marche-belem.jpg` | Marché de Belém |
| `vide-fertile.jpg` | Vide Fertile |
| `equilibre.jpg` | Équilibre |
| `zellige-bleu.jpg` | Zellige Bleu |
| `medina-rose.jpg` | Médina en Rose |

**Artistes**

| Fichier | Artiste |
|---------|---------|
| `isabelle-moreau.jpg` | Isabelle Moreau |
| `carlos-santos.jpg` | Carlos Santos |
| `yuki-nakamura.jpg` | Yuki Nakamura |
| `amira-elfassi.jpg` | Amira El Fassi |

### Chemins en base MongoDB

Format enregistré (URLs absolues depuis la racine du site) :

```
/artify/images/oeuvres/lumiere-aout.jpg
/artify/images/artistes/isabelle-moreau.jpg
```

Le navigateur charge : `http://localhost` + ce chemin.

### Mettre à jour les chemins en base (sans effacer les données)

```powershell
cd C:\xampp\htdocs\artify
php api\config\update-images.php
```

Puis redéployer si vous avez ajouté de nouveaux fichiers dans `frontend/images/`.

---

## API REST

Base URL : **`http://localhost/artify/api`**

Préférez les URLs avec **`index.php`** (fiables sous Apache) :

| Méthode | URL | Auth | Description |
|---------|-----|------|-------------|
| POST | `/auth/inscription.php` | Non | Créer un compte |
| POST | `/auth/connexion.php` | Non | Connexion (session PHP) |
| POST | `/auth/deconnexion.php` | Oui | Déconnexion |
| GET | `/oeuvres/index.php` | Non | Liste (filtres, tri, pagination) |
| GET | `/oeuvres/index.php?id=XXX` | Non | Détail d'une œuvre |
| PUT | `/oeuvres/index.php?id=XXX` | Oui | Modifier le statut |
| GET | `/artistes/index.php` | Non | Liste des artistes |
| GET | `/artistes/index.php?id=XXX` | Non | Détail + œuvres |
| GET | `/stats/index.php` | Non | Compteurs accueil |
| GET | `/favoris/index.php` | **Oui** | Liste des favoris |
| POST | `/favoris/index.php` | **Oui** | Ajouter un favori |
| DELETE | `/favoris/index.php?oeuvre_id=XXX` | **Oui** | Retirer un favori |
| GET | `/commandes/index.php` | Oui | Historique commandes |
| POST | `/commandes/index.php` | Oui | Créer une commande |
| GET | `/profil/index.php` | Oui | Profil + stats |
| PUT | `/profil/index.php` | Oui | Modifier le profil |

**Favoris** : une session active est obligatoire (`credentials: 'include'` + connexion préalable). Sans connexion, le cœur en catalogue enregistre le favori uniquement dans `localStorage`.

**Paramètres GET** (`/oeuvres/index.php`) :

- `categorie` — `Peinture`, `Aquarelle`, `Photographie`, `Sculpture`
- `statut` — `disponible`, `vendu`
- `tri` — `prix_asc`, `prix_desc`, `recent`
- `artiste_id` — filtre par artiste
- `page`, `limite` — pagination

### Format de réponse

Succès :

```json
{ "succes": true, "data": { ... } }
```

Erreur :

```json
{ "succes": false, "erreur": "Message lisible" }
```

### Exemple — connexion

```javascript
const res = await fetch('/artify/api/auth/connexion.php', {
  method: 'POST',
  headers: { 'Content-Type': 'application/json' },
  credentials: 'include',
  body: JSON.stringify({
    email: 'marie@artify.fr',
    mot_de_passe: 'artify2026'
  })
});
const json = await res.json();
```

### Exemple — ajouter un favori

```javascript
await fetch('/artify/api/favoris/index.php', {
  method: 'POST',
  headers: { 'Content-Type': 'application/json' },
  credentials: 'include',
  body: JSON.stringify({ oeuvre_id: 'ID_MONGODB_DE_LOEUVRE' })
});
```

---

## Structure déployée dans XAMPP

```
C:\xampp\htdocs\artify\
├── index.html, catalogue.html, login.html, profil.html, artiste.html
├── css/, js/
├── images/
│   ├── fond-hero.jpg, fond-artistes.jpg
│   ├── oeuvres/
│   └── artistes/
├── api/
│   ├── auth/
│   ├── oeuvres/, artistes/, favoris/, commandes/, profil/, stats/
│   └── config/
├── vendor/
└── .htaccess
```

---

## Dépannage

| Symptôme | Cause probable | Solution |
|----------|----------------|----------|
| CORS / `file://` / `origin null` | Page ouverte hors Apache | `http://localhost/artify/...` + `deploy-xampp.ps1` |
| API 404 sur `/artify/api/...` | Dépôt entier copié sans `api/` à la racine | Relancer `deploy-xampp.ps1` |
| Images 404 | Fichiers absents ou non déployés | Vérifier `htdocs\artify\images\...`, noms = `media.php`, redéployer |
| `Unexpected token '<'` sur favoris | HTML (404/401) au lieu de JSON | URL `.../favoris/index.php`, se connecter d'abord |
| Favoris non synchronisés | Non connecté | Se connecter ; sinon stockage local uniquement |
| `Class MongoDB\Client not found` | Composer / extension | `composer install` dans `backend`, extension `mongodb` |
| Variable d'environnement manquante | `.env` absent | `copy backend\.env.example backend\.env` puis renseigner |
| Connexion MongoDB échouée | URI, IP Atlas, réseau | `php api/config/test-db.php`, autoriser IP dans Atlas |

---

## Sécurité

- Mots de passe : `PASSWORD_BCRYPT`
- Sessions PHP + `session_regenerate_id()` à la connexion
- Sanitisation des entrées, validation email et ObjectId
- Routes protégées via `verifierSession()` dans `helpers.php`
- Secrets uniquement dans `backend/.env` (jamais dans `db.php` ni Git)
- Ne pas exposer `.env` sur un dépôt public

---

## Documentation complémentaire

- [frontend/README.md](frontend/README.md) — pages et assets
- [backend/README.md](backend/README.md) — API et configuration PHP

*Artify — Mai 2026*
