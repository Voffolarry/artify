// ============================================================
//  Artify — artiste.js  (branché API)
// ============================================================

/* ---- Artiste du mois (vedette) ---- */
async function chargerArtisteMois() {
  try {
    const data  = await apiFetch('/artistes/index.php');
    const liste = data.artistes || [];
    if (!liste.length) return;
    const a = liste[0]; // premier = artiste du mois

    document.getElementById('vedette-nom').textContent = a.prenom + ' ' + a.nom;
    document.getElementById('vedette-pays').textContent = (a.specialite||'') + ' · ' + (a.pays||'');
    document.getElementById('vedette-bio').textContent  = a.bio || '';
    document.getElementById('vedette-oeuvres').textContent = a.nb_oeuvres || 0;

    const portraitEl = document.getElementById('vedette-portrait');
    const photo = imageUrlAffichable(a.photo_url);
    if (photo) {
      portraitEl.style.background = '';
      portraitEl.innerHTML = `<img src="${photo}" alt="${a.prenom} ${a.nom}" style="width:100%;height:100%;object-fit:cover;">`;
    } else {
      portraitEl.textContent = (a.prenom?.[0]||'') + (a.nom?.[0]||'');
    }

    // Lien vers ses oeuvres
    const lienOeuvres = document.getElementById('vedette-lien-oeuvres');
    if (lienOeuvres) lienOeuvres.href = `catalogue.html?artiste_id=${a._id}`;

  } catch (e) { console.error(e); }
}

/* ---- Grille tous les artistes ---- */
async function chargerTousArtistes() {
  const grille = document.getElementById('grille-tous-artistes');
  if (!grille) return;
  grille.innerHTML = '<div class="loader-art" style="grid-column:1/-1;"><div class="loader-anneau"></div></div>';

  const gradients = [
    'linear-gradient(135deg,#c4802a,#e6b84a)',
    'linear-gradient(135deg,#2e7db5,#a8d4f0)',
    'linear-gradient(135deg,#4a4a4a,#9a9a9a)',
    'linear-gradient(135deg,#3a7a3a,#8bc48b)',
    'linear-gradient(135deg,#8b4a1a,#d4a060)',
    'linear-gradient(135deg,#2a3a5a,#6a8ab5)',
    'linear-gradient(135deg,#8b1a1a,#c4432a)',
    'linear-gradient(135deg,#1a4a3a,#3ab080)',
  ];

  try {
    const data    = await apiFetch('/artistes/index.php');
    const artistes = data.artistes || [];

    grille.innerHTML = artistes.map((a, i) => {
      const bg  = gradients[i % gradients.length];
      const photo = imageUrlAffichable(a.photo_url);
      const img = photo
        ? `<img src="${photo}" alt="${a.prenom} ${a.nom}" style="width:100%;height:100%;object-fit:cover;">`
        : `<span style="font-family:'Playfair Display',serif;font-size:3rem;font-style:italic;color:rgba(255,255,255,.7);">${a.prenom?.[0]||''}${a.nom?.[0]||''}</span>`;

      return `
        <div class="carte-artiste-detail" onclick="ouvrirArtiste('${a._id}','${a.prenom} ${a.nom}')">
          <div class="artiste-img-wrap">
            <div class="artiste-portrait" style="background:${bg};display:flex;align-items:center;justify-content:center;">${img}</div>
            <span class="artiste-pays-badge">${a.pays || ''}</span>
          </div>
          <div class="artiste-detail-info">
            <h3>${a.prenom} ${a.nom}</h3>
            <p class="artiste-specialite">${a.specialite || ''}</p>
            <p class="artiste-bio-court">${a.bio || ''}</p>
            <div class="artiste-detail-pied">
              <span class="artiste-nb-oeuvres">${a.nb_oeuvres || 0} œuvres</span>
              <span class="lien-artiste">Voir le profil →</span>
            </div>
          </div>
        </div>`;
    }).join('');

    // Observer animations
    grille.querySelectorAll('.carte-artiste-detail').forEach((el, i) => {
      el.style.opacity = '0'; el.style.transform = 'translateY(28px)';
      el.style.transition = `opacity .5s ease ${i*.08}s,transform .5s ease ${i*.08}s`;
      observer.observe(el);
    });

  } catch (e) {
    grille.innerHTML = `<p class="vide-msg" style="grid-column:1/-1;color:#c0392b;">${e.message}</p>`;
  }
}

/* ---- Modale artiste ---- */
async function ouvrirArtiste(id, nom) {
  const overlay = document.getElementById('modale-artiste-overlay');
  if (!overlay) return;
  overlay.style.display = 'flex';
  requestAnimationFrame(() => overlay.classList.add('ouverte'));
  document.getElementById('modale-artiste-corps').innerHTML =
    '<div class="loader-art"><div class="loader-anneau"></div></div>';

  try {
    const a = await apiFetch(`/artistes/index.php?id=${id}`);
    const gradients = ['linear-gradient(135deg,#c4802a,#e6b84a)','linear-gradient(135deg,#2e7db5,#a8d4f0)'];
    const oeuvres  = (a.oeuvres || []).slice(0, 6);

    document.getElementById('modale-artiste-corps').innerHTML = `
      <div style="display:grid;grid-template-columns:200px 1fr;gap:2rem;align-items:start;">
        <div style="aspect-ratio:1;background:${gradients[0]};display:flex;align-items:center;justify-content:center;font-family:'Playfair Display',serif;font-size:4rem;font-style:italic;color:rgba(255,255,255,.7);">
          ${imageUrlAffichable(a.photo_url) ? `<img src="${imageUrlAffichable(a.photo_url)}" alt="${a.prenom} ${a.nom}" style="width:100%;height:100%;object-fit:cover;">` : (a.prenom?.[0]||'')+(a.nom?.[0]||'')}
        </div>
        <div>
          <span class="vedette-tag">${a.specialite || ''}</span>
          <h2 style="font-family:'Playfair Display',serif;font-size:2rem;font-weight:700;margin:.5rem 0;">${a.prenom} ${a.nom}</h2>
          <p style="font-size:.78rem;color:#9c7c3c;text-transform:uppercase;letter-spacing:.1em;margin-bottom:1rem;">${a.pays || ''}</p>
          <p style="color:#4a4540;line-height:1.8;font-size:.95rem;">${a.bio || ''}</p>
          <p style="margin-top:1rem;font-size:.8rem;color:#6b6560;"><strong style="color:#9c7c3c;">${a.nb_oeuvres||0}</strong> œuvres disponibles</p>
          <a href="catalogue.html?artiste_id=${id}" class="btn-principal" style="display:inline-block;margin-top:1.2rem;font-size:.78rem;">Voir ses œuvres</a>
        </div>
      </div>
      ${oeuvres.length ? `
      <hr style="margin:2rem 0;border:none;border-top:1px solid #e8e4dc;">
      <p class="section-label" style="margin-bottom:1rem;">Quelques œuvres</p>
      <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:.8rem;">
        ${oeuvres.map((o,i) => {
          const bg = ['linear-gradient(140deg,#b8890a,#e6b84a)','linear-gradient(160deg,#1a4a7a,#a8d4f0)','linear-gradient(135deg,#1a3d1a,#8bc48b)'][i%3];
          const imgSrc = imageUrlAffichable(o.image_url);
          const img = imgSrc ? `<img src="${imgSrc}" alt="${o.titre}" style="width:100%;height:100%;object-fit:cover;">` : '';
          return `<div style="aspect-ratio:1;background:${bg};cursor:pointer;overflow:hidden;" onclick="location.href='catalogue.html?id=${o._id}'">${img}</div>`;
        }).join('')}
      </div>` : ''}`;

  } catch (e) {
    document.getElementById('modale-artiste-corps').innerHTML = `<p style="color:#c0392b;">${e.message}</p>`;
  }
}

function fermerModaleArtiste(e) {
  if (e && e.target.id !== 'modale-artiste-overlay') return;
  const overlay = document.getElementById('modale-artiste-overlay');
  overlay.classList.remove('ouverte');
  setTimeout(() => overlay.style.display = 'none', 350);
}

/* ---- Init ---- */
chargerArtisteMois();
chargerTousArtistes();
