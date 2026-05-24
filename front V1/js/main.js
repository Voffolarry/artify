  // Navbar — ombre au scroll
window.addEventListener('scroll', () => {
  const navbar = document.querySelector('.navbar');
  if (window.scrollY > 40) {
    navbar.style.boxShadow = '0 4px 24px rgba(0,0,0,0.08)';
  } else {
    navbar.style.boxShadow = 'none';
  }
});

// Animation au scroll — les cartes apparaissent en douceur
const observerOptions = {
  threshold: 0.12,
  rootMargin: '0px 0px -40px 0px'
};

const observer = new IntersectionObserver((entries) => {
  entries.forEach(entry => {
    if (entry.isIntersecting) {
      entry.target.style.opacity = '1';
      entry.target.style.transform = 'translateY(0)';
      observer.unobserve(entry.target);
    }
  });
}, observerOptions);

// On applique l'animation à toutes les cartes
document.querySelectorAll('.carte-oeuvre, .carte-artiste, .stat').forEach(el => {
  el.style.opacity = '0';
  el.style.transform = 'translateY(28px)';
  el.style.transition = 'opacity 0.55s ease, transform 0.55s ease';
  observer.observe(el);
});

// Lien actif dans la navbar
const liens = document.querySelectorAll('.nav-liens a');
liens.forEach(lien => {
  if (lien.href === window.location.href) {
    lien.style.color = '#1a1a1a';
    lien.style.borderBottom = '1px solid #9c7c3c';
    lien.style.paddingBottom = '2px';
  }
});