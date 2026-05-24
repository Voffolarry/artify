// ============================================================
//  Artify — catalogue.js
//  Filtres, tri, pagination, modale détail — branché API
// ============================================================

let etat = { categorie: '', statut: '', tri: 'recent', page: 1, total: 0, pages: 1, limite: 9 };

/* ---- Générateur de carte catalogue ---- */
function carteHTML(o) {
  const artiste = o.artiste ? `${o.artiste.prenom} ${o.artiste.nom}` : '—';
  const statut  = o.statut === 'vendu'
    ? '<span class="vendu">Vendu</span>'
    : '<span class="disponible">Disponible</span>';
  const favActif = estFavori(o._id) ? 'fav-actif' : '';

  // couleur de fond CSS pour placeholder
  const couleurs = [
    'linear-gradient(140deg,#b8890a,#e6b84a,#f5e6a0,#c4802a)',
    'linear-gradient(160deg,#1a4a7a,#2e7db5,#a8d4f0)',
    'linear-gradient(180deg,#2d2d2d,#7a7a7a,#e0d8cc)',
    'linear-gradient(135deg,#1a3d1a,#3a7a3a,#8bc48b)',
    'linear-gradient(135deg,#8b1a1a,#c4432a,#f5a070)',
    'linear-gradient(135deg,#0d3b5e,#1a6a8a,#7ec8d8)',
    'linear-gradient(140deg,#2d1a0e,#6b3a1a,#b87a3a)',
  ];
  const idx = Math.abs(o.titre.charCodeAt(0)) % couleurs.length;

  const imgSrc = imageUrlAffichable(o.image_url);
  const imgContent = imgSrc
    ? `<img src="${imgSrc}" alt="${o.titre}" loading="lazy" onerror="this.style.display='none'">`
    : '';
  const imgBg = imgSrc ? '' : `style="background:${couleurs[idx]}"`;

  return `
    <div class="carte-catalogue" data-id="${o._id}" data-statut="${o.statut}">
      <div class="cat-img" ${imgBg}>${imgContent}
        <button class="btn-fav ${favActif}" data-id="${o._id}" data-titre="${o.titre}" title="Favoris" onclick="event.stopPropagation();basculerFavori(this,'${o._id}')">♥</button>
      </div>
      <div class="cat-info">
        <span class="carte-medium">${o.medium || ''}</span>
        <h3 class="carte-titre">${o.titre}</h3>
        <p class="carte-artiste">${artiste}</p>
        <div class="carte-pied">
          <span class="carte-prix">${formatPrix(o.prix)}</span>
          ${statut}
        </div>
      </div>
    </div>`;
}

/* ---- Basculer favori ---- */
function basculerFavori(btn, id) {
  const ajoute = toggleFavoriLocal(id);
  btn.classList.toggle('fav-actif', ajoute);
}

/* ---- Charger oeuvres depuis API ---- */
async function charger() {
  const grille = document.getElementById('grille-catalogue');
  const compteur = document.querySelector('.compteur-resultats');
  grille.innerHTML = '<div class="loader-art"><div class="loader-anneau"></div></div>';

  const params = new URLSearchParams({ tri: etat.tri, page: etat.page, limite: etat.limite });
  if (etat.categorie) params.set('categorie', etat.categorie);
  if (etat.statut)    params.set('statut',    etat.statut);

  try {
    const d = await apiFetch('/oeuvres/index.php?' + params);
    etat.total = d.total; etat.pages = d.pages;
    if (compteur) compteur.textContent = d.total + ' œuvre' + (d.total > 1 ? 's' : '');
    grille.innerHTML = d.oeuvres.length
      ? d.oeuvres.map(carteHTML).join('')
      : '<p class="vide-msg">Aucune œuvre pour cette sélection.</p>';

    // Ouvre modale au clic
    grille.querySelectorAll('.carte-catalogue').forEach(c =>
      c.addEventListener('click', () => ouvrirModale(c.dataset.id)));

    rendrePagination();
    const desc = document.getElementById('catalogue-desc');
    if (desc) {
      desc.textContent =
        d.total + ' œuvre' + (d.total > 1 ? 's' : '') + ' disponible' + (d.total > 1 ? 's' : '') + ' dans notre collection.';
    }
    // Relancer les animations
    grille.querySelectorAll('.carte-catalogue').forEach((el, i) => {
      el.style.opacity = '0'; el.style.transform = 'translateY(24px)';
      el.style.transition = `opacity .5s ease ${i*.07}s,transform .5s ease ${i*.07}s`;
      observer.observe(el);
    });
  } catch (e) {
    grille.innerHTML = `<p class="vide-msg" style="color:#c0392b;">${e.message}</p>`;
  }
}

/* ---- Pagination ---- */
function rendrePagination() {
  let cont = document.getElementById('pagination');
  if (!cont) { cont = document.createElement('div'); cont.id = 'pagination'; cont.className = 'pagination'; document.querySelector('.catalogue-contenu').appendChild(cont); }
  if (etat.pages <= 1) { cont.innerHTML = ''; return; }
  let html = `<button class="page-btn" onclick="changerPage(${etat.page-1})" ${etat.page===1?'disabled':''}>‹</button>`;
  for (let i = 1; i <= etat.pages; i++)
    html += `<button class="page-btn ${i===etat.page?'actif':''}" onclick="changerPage(${i})">${i}</button>`;
  html += `<button class="page-btn" onclick="changerPage(${etat.page+1})" ${etat.page===etat.pages?'disabled':''}>›</button>`;
  cont.innerHTML = html;
}

function changerPage(p) {
  if (p < 1 || p > etat.pages) return;
  etat.page = p;
  charger();
  window.scrollTo({ top: 300, behavior: 'smooth' });
}

/* ---- Modale détail ---- */
async function ouvrirModale(id) {
  const overlay = document.getElementById('modale-overlay');
  if (!overlay) return;
  overlay.style.display = 'flex';
  requestAnimationFrame(() => overlay.classList.add('ouverte'));
  document.getElementById('modale-corps').innerHTML = '<div class="loader-art"><div class="loader-anneau"></div></div>';

  try {
    const o = await apiFetch(`/oeuvres/index.php?id=${id}`);
    const artiste = o.artiste ? `${o.artiste.prenom} ${o.artiste.nom}` : '';
    const couleurs = ['linear-gradient(140deg,#b8890a,#e6b84a)','linear-gradient(160deg,#1a4a7a,#a8d4f0)'];
    const imgSrc = imageUrlAffichable(o.image_url);
    const imgBg = imgSrc ? '' : `style="background:${couleurs[0]}"`;
    const imgContent = imgSrc ? `<img src="${imgSrc}" alt="${o.titre}" style="width:100%;height:100%;object-fit:cover;">` : '';
    const isFav = estFavori(o._id);

    document.getElementById('modale-corps').innerHTML = `
      <div class="modale-img" ${imgBg}>${imgContent}</div>
      <div class="modale-info">
        <span class="carte-medium">${o.medium || ''}</span>
        <h2 class="modale-titre">${o.titre}</h2>
        ${artiste ? `<p class="modale-artiste">${artiste}${o.artiste?.pays ? ' · '+o.artiste.pays : ''}</p>` : ''}
        <hr class="modale-sep">
        <p class="modale-desc">${o.description || 'Œuvre originale, pièce unique.'}</p>
        <div class="modale-details">
          ${o.annee ? `<div class="modale-detail"><label>Année</label><span>${o.annee}</span></div>` : ''}
          ${o.dimensions ? `<div class="modale-detail"><label>Dimensions</label><span>${o.dimensions}</span></div>` : ''}
          ${o.categories?.length ? `<div class="modale-detail"><label>Catégorie</label><span>${o.categories.join(', ')}</span></div>` : ''}
        </div>
        <div class="modale-pied">
          <span class="modale-prix">${formatPrix(o.prix)}</span>
          <div style="display:flex;gap:.8rem;align-items:center;">
            ${o.statut === 'vendu'
              ? '<span class="vendu">Vendu</span>'
              : `<button class="btn-fav ${isFav?'fav-actif':''}" onclick="basculerFavori(this,'${o._id}')" title="Favoris">♥</button>
                 <button class="btn-principal" onclick="acheterOeuvre('${o._id}','${o.titre.replace(/'/g,"\\'")}')">Acquérir</button>`}
          </div>
        </div>
      </div>`;
  } catch (e) {
    document.getElementById('modale-corps').innerHTML = `<p style="padding:2rem;color:#c0392b;">${e.message}</p>`;
  }
}

function fermerModale(e) {
  if (e && e.target.id !== 'modale-overlay') return;
  const overlay = document.getElementById('modale-overlay');
  overlay.classList.remove('ouverte');
  setTimeout(() => overlay.style.display = 'none', 350);
}

/* ---- Filtres catégorie ---- */
document.querySelectorAll('.filtre-btn[data-filtre]').forEach(btn => {
  btn.addEventListener('click', () => {
    document.querySelectorAll('.filtre-btn[data-filtre]').forEach(b => b.classList.remove('actif'));
    btn.classList.add('actif');
    const f = btn.dataset.filtre;
    etat.categorie = (f === 'tous') ? '' : f.charAt(0).toUpperCase() + f.slice(1);
    etat.page = 1; charger();
  });
});

/* Filtres statut */
document.querySelectorAll('.filtre-btn[data-statut]').forEach(btn => {
  btn.addEventListener('click', () => {
    document.querySelectorAll('.filtre-btn[data-statut]').forEach(b => b.classList.remove('actif'));
    btn.classList.add('actif');
    etat.statut = btn.dataset.statut || '';
    etat.page = 1; charger();
  });
});

/* Tri */
const triSel = document.getElementById('tri');
if (triSel) triSel.addEventListener('change', () => { etat.tri = triSel.value.replace('-','_'); etat.page=1; charger(); });

/* ---- Init ---- */
charger();

// Ouvrir modale depuis URL ?id=xxx
const urlParams = new URLSearchParams(location.search);
if (urlParams.has('id')) setTimeout(() => ouvrirModale(urlParams.get('id')), 600);
