// ============================================================
//  Artify — main.js  (toutes les pages)
//  Navbar scroll + session + API + helpers
// ============================================================

/** Base API : /artify/api (deploy) ou /artify/backend/api (depot entier dans htdocs) */
function resolveApiBase() {
  if (location.protocol === 'file:') {
    return 'http://localhost/artify/api';
  }
  const m = location.pathname.match(/^(\/[^/]+)/);
  const prefix = m ? m[1] : '/artify';
  if (location.pathname.includes('/backend/api/') || location.pathname.startsWith(prefix + '/backend/')) {
    return prefix + '/backend/api';
  }
  return prefix + '/api';
}
const API_BASE = resolveApiBase();

if (location.protocol === 'file:') {
  document.addEventListener('DOMContentLoaded', () => {
    const b = document.createElement('div');
    b.setAttribute('role', 'alert');
    b.style.cssText =
      'position:fixed;top:0;left:0;right:0;z-index:99999;background:#8b1a1a;color:#fff;padding:1rem 1.25rem;font:500 .9rem/1.5 \"DM Sans\",sans-serif;text-align:center;';
    b.innerHTML =
      'Ouvrez Artify via <strong>http://localhost/artify/</strong> (XAMPP), pas en double-cliquant le fichier HTML. ' +
      'Exécutez <code style="background:rgba(0,0,0,.2);padding:.1em .4em">scripts/deploy-xampp.ps1</code> puis démarrez Apache.';
    document.body.prepend(b);
  });
}

/* ---- Navbar scroll shadow ---- */
window.addEventListener('scroll', () => {
  const navbar = document.querySelector('.navbar');
  if (!navbar) return;
  navbar.style.boxShadow = window.scrollY > 40
    ? '0 4px 24px rgba(0,0,0,0.08)'
    : 'none';
});

/* ---- Lien actif navbar ---- */
(function () {
  const page = location.pathname.split('/').pop() || 'index.html';
  document.querySelectorAll('.nav-liens a').forEach(a => {
    const href = a.getAttribute('href');
    if (href === page) a.classList.add('actif');
  });
})();

/* ---- Observer animations au scroll ---- */
const observerOptions = { threshold: 0.12, rootMargin: '0px 0px -40px 0px' };
const observer = new IntersectionObserver((entries) => {
  entries.forEach(entry => {
    if (entry.isIntersecting) {
      entry.target.style.opacity = '1';
      entry.target.style.transform = 'translateY(0)';
      observer.unobserve(entry.target);
    }
  });
}, observerOptions);

function observerCartes(selector = '.carte-oeuvre, .carte-artiste, .stat') {
  document.querySelectorAll(selector).forEach((el, i) => {
    el.style.opacity = '0';
    el.style.transform = 'translateY(28px)';
    el.style.transition = `opacity 0.55s ease ${i * 0.07}s, transform 0.55s ease ${i * 0.07}s`;
    observer.observe(el);
  });
}
observerCartes();

/* ======================================================
   SESSION UTILISATEUR
====================================================== */
function getSession() {
  try { return JSON.parse(sessionStorage.getItem('artify_user') || 'null'); }
  catch { return null; }
}
function setSession(user) {
  sessionStorage.setItem('artify_user', JSON.stringify(user));
}
function clearSession() {
  sessionStorage.removeItem('artify_user');
}

/* Mettre à jour la navbar selon la session */
(function majNavbar() {
  const user = getSession();
  const navLiens = document.querySelector('.nav-liens');
  if (!navLiens) return;

  // Retirer les liens connexion/déconnexion existants pour les remplacer dynamiquement
  const lienCo = navLiens.querySelector('a[href="login.html"]');
  if (user) {
    if (lienCo) lienCo.textContent = user.prenom || 'Mon profil';
    if (lienCo) lienCo.href = 'profil.html';

    // Ajouter lien déconnexion si pas déjà là
    if (!navLiens.querySelector('[data-deco]')) {
      const decoBtn = document.createElement('a');
      decoBtn.href = '#';
      decoBtn.textContent = 'Déconnexion';
      decoBtn.dataset.deco = '1';
      decoBtn.style.color = '#9c7c3c';
      decoBtn.addEventListener('click', (e) => { e.preventDefault(); deconnexion(); });
      navLiens.appendChild(decoBtn);
    }
  }
})();

/* ======================================================
   API HELPER
====================================================== */
async function apiFetch(endpoint, options = {}) {
  const res = await fetch(API_BASE + endpoint, {
    credentials: 'include',
    headers: { 'Content-Type': 'application/json', ...options.headers },
    ...options,
  });
  const text = await res.text();
  let data;
  try {
    data = text ? JSON.parse(text) : {};
  } catch {
    throw new Error(res.ok ? 'Réponse serveur invalide' : `Erreur ${res.status} — vérifiez la connexion et l’URL API`);
  }
  if (!data.succes) throw new Error(data.erreur || 'Erreur serveur');
  return data.data;
}

/* ======================================================
   UTILITAIRES
====================================================== */
/** Normalise l'URL image (absolue ou relative → /artify/images/...). */
function imageUrlAffichable(url) {
  if (!url || !String(url).trim()) return '';
  const u = String(url).trim();
  if (/^https?:\/\//i.test(u)) return u;
  if (u.startsWith('/')) return u;
  const m = location.pathname.match(/^(\/[^/]+)/);
  const prefix = m ? m[1] : '/artify';
  return prefix + '/' + u.replace(/^\//, '');
}

function formatPrix(n) {
  return new Intl.NumberFormat('fr-FR', {
    style: 'currency', currency: 'EUR', maximumFractionDigits: 0
  }).format(n);
}

function toast(message, type = 'ok') {
  let cont = document.querySelector('.toast-container');
  if (!cont) {
    cont = document.createElement('div');
    cont.className = 'toast-container';
    cont.style.cssText = 'position:fixed;bottom:2rem;right:2rem;z-index:9000;display:flex;flex-direction:column;gap:.6rem;';
    document.body.appendChild(cont);
  }
  const el = document.createElement('div');
  const bg = type === 'erreur' ? '#8b1a1a' : '#1a1a1a';
  el.style.cssText = `background:${bg};color:#faf9f6;padding:.9rem 1.4rem;font-size:.82rem;border-left:3px solid #d4b06a;max-width:300px;animation:fadeInToast .3s ease;`;
  el.textContent = message;
  cont.appendChild(el);
  setTimeout(() => { el.style.opacity = '0'; el.style.transition = '.3s'; setTimeout(() => el.remove(), 350); }, 3500);
}

/* Favoris localStorage */
function getFavoris() {
  try { return JSON.parse(localStorage.getItem('artify_favoris') || '[]'); } catch { return []; }
}
function estFavori(id) { return getFavoris().includes(String(id)); }
function toggleFavoriLocal(id) {
  const favs = getFavoris();
  const idx = favs.indexOf(String(id));
  const ajoute = idx === -1;
  if (ajoute) { favs.push(String(id)); }
  else { favs.splice(idx, 1); }
  localStorage.setItem('artify_favoris', JSON.stringify(favs));

  const user = getSession();
  if (!user) {
    toast(ajoute ? '♥ Favori enregistré localement — connectez-vous pour synchroniser' : 'Retiré des favoris (local)');
    return ajoute;
  }

  if (ajoute) toast('♥ Ajouté aux favoris');
  else toast('Retiré des favoris');

  const url = '/favoris/index.php';
  const body = ajoute
    ? JSON.stringify({ oeuvre_id: id })
    : JSON.stringify({ action: 'retirer', oeuvre_id: id });
  apiFetch(url, { method: 'POST', body }).catch((e) => {
    const msg = e.message || '';
    if (msg.includes('Non authentifié') || msg.includes('401')) {
      toast('Session expirée — reconnectez-vous pour synchroniser les favoris', 'erreur');
      clearSession();
    } else {
      toast(msg || 'Synchronisation favoris impossible', 'erreur');
    }
  });
  return ajoute;
}

/* Achat */
async function acheterOeuvre(id, titre) {
  const user = getSession();
  if (!user) {
    toast('Connectez-vous pour acquérir cette œuvre', 'erreur');
    setTimeout(() => location.href = 'login.html', 1200);
    return;
  }
  if (!confirm(`Confirmer l'acquisition de "${titre}" ?`)) return;
  try {
    await apiFetch('/commandes/index.php', { method: 'POST', body: JSON.stringify({ oeuvre_id: id }) });
    toast('✦ Félicitations ! L\'œuvre est désormais vôtre.');
    setTimeout(() => location.reload(), 1800);
  } catch (e) {
    toast(e.message, 'erreur');
  }
}

/* Déconnexion */
async function deconnexion() {
  try { await apiFetch('/auth/deconnexion.php', { method: 'POST' }); } catch {}
  clearSession();
  location.href = 'index.html';
}

/* Compteur animé */
function animerCompteur(el, cible, duree = 1600) {
  const start = performance.now();
  function step(now) {
    const t = Math.min((now - start) / duree, 1);
    const ease = 1 - Math.pow(1 - t, 3);
    el.textContent = Math.round(cible * ease);
    if (t < 1) requestAnimationFrame(step);
    else el.textContent = cible;
  }
  requestAnimationFrame(step);
}
