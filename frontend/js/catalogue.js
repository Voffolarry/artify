// Filtres par catégorie
const boutonsFiltres = document.querySelectorAll('.filtre-btn');
const cartesCatalogue = document.querySelectorAll('.carte-catalogue');
const compteur = document.querySelector('.compteur-resultats');

boutonsFiltres.forEach(btn => {
  btn.addEventListener('click', () => {

    // Bouton actif
    boutonsFiltres.forEach(b => b.classList.remove('actif'));
    btn.classList.add('actif');

    const filtre = btn.dataset.filtre;
    let visible = 0;

    cartesCatalogue.forEach(carte => {
      if (filtre === 'tous' || carte.dataset.categorie === filtre) {
        carte.classList.remove('masque');
        visible++;
      } else {
        carte.classList.add('masque');
      }
    });

    compteur.textContent = visible + ' œuvre' + (visible > 1 ? 's' : '');
  });
});

// Animation entrée des cartes
const obs = new IntersectionObserver((entries) => {
  entries.forEach(entry => {
    if (entry.isIntersecting) {
      entry.target.style.opacity = '1';
      entry.target.style.transform = 'translateY(0)';
      obs.unobserve(entry.target);
    }
  });
}, { threshold: 0.1 });

cartesCatalogue.forEach((carte, i) => {
  carte.style.opacity = '0';
  carte.style.transform = 'translateY(24px)';
  carte.style.transition = `opacity 0.5s ease ${i * 0.07}s, transform 0.5s ease ${i * 0.07}s`;
  obs.observe(carte);
});