// Basculer entre connexion et inscription
const ongletConnexion = document.getElementById('onglet-connexion');
const ongletInscription = document.getElementById('onglet-inscription');
const formConnexion = document.getElementById('form-connexion');
const formInscription = document.getElementById('form-inscription');
const switchInscription = document.getElementById('switch-inscription');
const switchConnexion = document.getElementById('switch-connexion');

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
switchInscription.addEventListener('click', (e) => {
  e.preventDefault();
  afficherInscription();
});
switchConnexion.addEventListener('click', (e) => {
  e.preventDefault();
  afficherConnexion();
});