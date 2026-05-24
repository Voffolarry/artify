// ============================================================
//  Artify — login.js  (connexion + inscription branchés API)
// ============================================================

const ongletConnexion   = document.getElementById('onglet-connexion');
const ongletInscription = document.getElementById('onglet-inscription');
const formConnexion     = document.getElementById('form-connexion');
const formInscription   = document.getElementById('form-inscription');
const switchInscription = document.getElementById('switch-inscription');
const switchConnexion   = document.getElementById('switch-connexion');

function afficherConnexion() {
  ongletConnexion.classList.add('actif');
  ongletInscription.classList.remove('actif');
  formConnexion.classList.remove('cache');
  formInscription.classList.add('cache');
}
function afficherInscription() {
  ongletInscription.classList.add('actif');
  ongletConnexion.classList.remove('actif');
  formInscription.classList.remove('cache');
  formConnexion.classList.add('cache');
}

ongletConnexion.addEventListener('click', afficherConnexion);
ongletInscription.addEventListener('click', afficherInscription);
switchInscription.addEventListener('click', (e) => { e.preventDefault(); afficherInscription(); });
switchConnexion.addEventListener('click',   (e) => { e.preventDefault(); afficherConnexion(); });

/* ---- Connexion ---- */
document.getElementById('btn-connexion').addEventListener('click', async () => {
  const email = document.getElementById('email-co').value.trim();
  const mdp   = document.getElementById('mdp-co').value;
  const btn   = document.getElementById('btn-connexion');

  if (!email || !mdp) { afficherErreur('form-connexion', 'Veuillez remplir tous les champs.'); return; }

  btn.disabled = true; btn.textContent = 'Connexion…';
  try {
    const data = await apiFetch('/auth/connexion.php', {
      method: 'POST',
      body: JSON.stringify({ email, mot_de_passe: mdp })
    });
    setSession(data.utilisateur);
    toast('Bienvenue ' + (data.utilisateur.prenom || '') + ' !');
    setTimeout(() => location.href = 'profil.html', 800);
  } catch (e) {
    afficherErreur('form-connexion', e.message);
    btn.disabled = false; btn.textContent = 'Se connecter';
  }
});

/* ---- Inscription ---- */
document.getElementById('btn-inscription').addEventListener('click', async () => {
  const prenom   = document.getElementById('prenom').value.trim();
  const nom      = document.getElementById('nom').value.trim();
  const email    = document.getElementById('email-ins').value.trim();
  const mdp      = document.getElementById('mdp-ins').value;
  const mdpConf  = document.getElementById('mdp-conf').value;
  const btn      = document.getElementById('btn-inscription');

  if (!prenom || !nom || !email || !mdp) { afficherErreur('form-inscription', 'Veuillez remplir tous les champs.'); return; }
  if (mdp !== mdpConf) { afficherErreur('form-inscription', 'Les mots de passe ne correspondent pas.'); return; }
  if (mdp.length < 8)  { afficherErreur('form-inscription', 'Mot de passe trop court (8 caractères minimum).'); return; }

  btn.disabled = true; btn.textContent = 'Création…';
  try {
    const data = await apiFetch('/auth/inscription.php', {
      method: 'POST',
      body: JSON.stringify({ prenom, nom, email, mot_de_passe: mdp })
    });
    setSession(data.utilisateur);
    toast('Compte créé avec succès ! Bienvenue ' + prenom + ' !');
    setTimeout(() => location.href = 'profil.html', 800);
  } catch (e) {
    afficherErreur('form-inscription', e.message);
    btn.disabled = false; btn.textContent = 'Créer mon compte';
  }
});

/* ---- Message d'erreur dans le formulaire ---- */
function afficherErreur(formId, msg) {
  const form = document.getElementById(formId);
  let err = form.querySelector('.form-erreur');
  if (!err) { err = document.createElement('p'); err.className = 'form-erreur'; form.insertBefore(err, form.firstChild); }
  err.textContent = msg;
  setTimeout(() => err.remove(), 4000);
}

/* ---- Rediriger si déjà connecté ---- */
if (getSession()) location.href = 'profil.html';
