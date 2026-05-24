// ============================================================
//  Artify — profil.js  (données utilisateur branchées API)
// ============================================================

/* ---- Onglets ---- */
const ongletsProfil  = document.querySelectorAll('.profil-onglet');
const sectionsProfil = document.querySelectorAll('.profil-section');

ongletsProfil.forEach(onglet => {
  onglet.addEventListener('click', () => {
    ongletsProfil.forEach(o => o.classList.remove('actif'));
    sectionsProfil.forEach(s => s.classList.add('cache'));
    onglet.classList.add('actif');
    const cible = document.getElementById(onglet.dataset.cible);
    if (cible) cible.classList.remove('cache');
    if (onglet.dataset.cible === 'favoris') chargerFavoris();
    if (onglet.dataset.cible === 'achats')  chargerAchats();
  });
});

/* ---- Vérifier session ---- */
const user = getSession();
if (!user) { location.href = 'login.html'; }

/* ---- Charger profil ---- */
async function chargerProfil() {
  try {
    const data = await apiFetch('/profil/index.php');

    // Identité
    document.getElementById('profil-nom').textContent   = data.prenom + ' ' + data.nom;
    document.getElementById('profil-email').textContent = data.email;
    const dateInscription = data.date_inscription
      ? new Date(data.date_inscription).toLocaleDateString('fr-FR', { month: 'long', year: 'numeric' })
      : '';
    document.getElementById('profil-depuis').textContent = dateInscription ? 'Membre depuis ' + dateInscription : '';

    // Initiales avatar
    const initEl = document.getElementById('profil-initiales');
    if (initEl) initEl.textContent = (data.prenom?.[0] || '') + (data.nom?.[0] || '');

    // Stats
    const stats = data.stats || {};
    animerCompteur(document.getElementById('stat-achats'),  stats.achats || 0);
    animerCompteur(document.getElementById('stat-favoris'), stats.favoris || 0);
    animerCompteur(document.getElementById('stat-artistes'),stats.artistes_suivis || 0);

    // Pré-remplir paramètres
    document.getElementById('param-prenom').value = data.prenom || '';
    document.getElementById('param-nom').value    = data.nom    || '';
    document.getElementById('param-email').value  = data.email  || '';

  } catch (e) {
    if (e.message.includes('authentifi')) { clearSession(); location.href = 'login.html'; }
    toast(e.message, 'erreur');
  }
}

/* ---- Charger achats ---- */
async function chargerAchats() {
  const grille = document.getElementById('grille-achats');
  if (!grille) return;
  grille.innerHTML = '<div class="loader-art"><div class="loader-anneau"></div></div>';
  try {
    const data = await apiFetch('/commandes/index.php');
    const cmds = data.commandes || [];
    if (!cmds.length) { grille.innerHTML = '<p class="vide-msg">Vous n\'avez pas encore effectué d\'achat.</p>'; return; }

    const couleurs = [
      'linear-gradient(140deg,#b8890a,#e6b84a)',
      'linear-gradient(160deg,#1a4a7a,#a8d4f0)',
      'linear-gradient(135deg,#1a3d1a,#8bc48b)',
    ];
    grille.innerHTML = cmds.map((c, i) => {
      const o = c.oeuvre_detail || {};
      const date = c.date_commande ? new Date(c.date_commande).toLocaleDateString('fr-FR', { month:'short', year:'numeric' }) : '';
      const imgSrc = imageUrlAffichable(o.image_url);
      const bg = imgSrc ? '' : `style="background:${couleurs[i%couleurs.length]}"`;
      const img = imgSrc ? `<img src="${imgSrc}" alt="${o.titre||''}" style="width:100%;height:100%;object-fit:cover;">` : '';
      return `
        <div class="carte-achat">
          <div class="achat-img" ${bg}>${img}</div>
          <div class="achat-info">
            <span class="carte-medium">${o.medium || '—'}</span>
            <h3 class="carte-titre">${o.titre || 'Œuvre'}</h3>
            <div class="carte-pied">
              <span class="carte-prix">${formatPrix(c.montant)}</span>
              <span class="achat-date">${date}</span>
            </div>
          </div>
        </div>`;
    }).join('');

    // Mettre à jour stat total investi
    const total = cmds.reduce((s, c) => s + (c.montant || 0), 0);
    const statTotal = document.getElementById('stat-total');
    if (statTotal) statTotal.textContent = formatPrix(total);

  } catch (e) { grille.innerHTML = `<p class="vide-msg" style="color:#c0392b;">${e.message}</p>`; }
}

/* ---- Charger favoris ---- */
async function chargerFavoris() {
  const grille = document.getElementById('grille-favoris');
  if (!grille) return;
  grille.innerHTML = '<div class="loader-art"><div class="loader-anneau"></div></div>';
  try {
    const data = await apiFetch('/favoris/index.php');
    const favs = data.favoris || [];
    if (!favs.length) { grille.innerHTML = '<p class="vide-msg">Aucune œuvre en favoris pour le moment.</p>'; return; }
    const couleurs = ['linear-gradient(140deg,#b8890a,#e6b84a)','linear-gradient(160deg,#1a4a7a,#a8d4f0)','linear-gradient(135deg,#8b1a1a,#f5a070)'];
    grille.innerHTML = favs.map((o, i) => {
      const imgSrc = imageUrlAffichable(o.image_url);
      const bg = imgSrc ? '' : `style="background:${couleurs[i%couleurs.length]}"`;
      const img = imgSrc ? `<img src="${imgSrc}" alt="${o.titre}" style="width:100%;height:100%;object-fit:cover;">` : '';
      const statut = o.statut === 'vendu' ? '<span class="vendu">Vendu</span>' : '<span class="disponible">Disponible</span>';
      return `
        <div class="carte-achat">
          <div class="achat-img" ${bg}>${img}</div>
          <div class="achat-info">
            <span class="carte-medium">${o.medium || '—'}</span>
            <h3 class="carte-titre">${o.titre}</h3>
            <div class="carte-pied">
              <span class="carte-prix">${formatPrix(o.prix)}</span>
              ${statut}
            </div>
          </div>
        </div>`;
    }).join('');
  } catch (e) { grille.innerHTML = `<p class="vide-msg" style="color:#c0392b;">${e.message}</p>`; }
}

/* ---- Enregistrer modifications profil ---- */
document.getElementById('btn-enregistrer')?.addEventListener('click', async () => {
  const prenom = document.getElementById('param-prenom').value.trim();
  const nom    = document.getElementById('param-nom').value.trim();
  const email  = document.getElementById('param-email').value.trim();
  const btn    = document.getElementById('btn-enregistrer');

  if (!prenom || !nom || !email) { toast('Tous les champs sont requis.', 'erreur'); return; }
  btn.disabled = true; btn.textContent = 'Enregistrement…';
  try {
    await apiFetch('/profil/index.php', { method: 'PUT', body: JSON.stringify({ prenom, nom, email }) });
    toast('Profil mis à jour avec succès.');
    // Mettre à jour session
    const s = getSession();
    if (s) { s.prenom = prenom; s.nom = nom; setSession(s); }
    document.getElementById('profil-nom').textContent = prenom + ' ' + nom;
    const initEl = document.getElementById('profil-initiales');
    if (initEl) initEl.textContent = prenom[0] + nom[0];
  } catch (e) { toast(e.message, 'erreur'); }
  finally { btn.disabled = false; btn.textContent = 'Enregistrer les modifications'; }
});

/* ---- Changer mot de passe ---- */
document.getElementById('btn-mdp')?.addEventListener('click', async () => {
  const ancienMdp  = document.getElementById('ancien-mdp').value;
  const nouveauMdp = document.getElementById('nouveau-mdp').value;
  const btn        = document.getElementById('btn-mdp');

  if (!ancienMdp || !nouveauMdp) { toast('Remplissez les deux champs.', 'erreur'); return; }
  if (nouveauMdp.length < 8)     { toast('Nouveau mot de passe trop court.', 'erreur'); return; }
  btn.disabled = true; btn.textContent = 'Modification…';
  try {
    await apiFetch('/profil/index.php?action=changer_mdp', {
      method: 'PUT',
      body: JSON.stringify({ ancien_mot_de_passe: ancienMdp, nouveau_mot_de_passe: nouveauMdp })
    });
    toast('Mot de passe modifié avec succès.');
    document.getElementById('ancien-mdp').value  = '';
    document.getElementById('nouveau-mdp').value = '';
  } catch (e) { toast(e.message, 'erreur'); }
  finally { btn.disabled = false; btn.textContent = 'Changer le mot de passe'; }
});

/* ---- Init ---- */
chargerProfil();
chargerAchats();
