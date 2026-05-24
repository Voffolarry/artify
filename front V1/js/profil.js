// Onglets profil
const ongletsProfil = document.querySelectorAll('.profil-onglet');
const sectionsProfil = document.querySelectorAll('.profil-section');

ongletsProfil.forEach(onglet => {
  onglet.addEventListener('click', () => {
    ongletsProfil.forEach(o => o.classList.remove('actif'));
    sectionsProfil.forEach(s => s.classList.add('cache'));

    onglet.classList.add('actif');
    document.getElementById(onglet.dataset.cible).classList.remove('cache');
  });
});