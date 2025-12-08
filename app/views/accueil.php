<?php include "app/views/header.php"; ?>

<!-- Section Hero -->
<section class="hero">
    <div class="hero-content">
        <h1>Participez à notre voyage en commandant votre vin !</h1>
        <p class="hero-subtitle">Chaque carton commandé nous aide à financer notre projet étudiant.</p>
        <a href="index.php?page=produits" class="cta-button">Commander maintenant</a>
    </div>
</section>

<!-- Section Fonctionnement -->
<section class="fonctionnement">
    <div class="container">
        <h2>Comment ça marche ?</h2>
        <div class="etapes">
            <div class="etape">
                <div class="etape-icon">1</div>
                <h3>Vous passez commande</h3>
                <p>Avant le 2 novembre</p>
                <p class="etape-detail">Parcourez notre sélection et ajoutez vos produits au panier</p>
            </div>
            <div class="etape">
                <div class="etape-icon">2</div>
                <h3>Centralisation</h3>
                <p>L'école centralise les achats</p>
                <p class="etape-detail">Nous regroupons toutes les commandes pour passer une commande unique au producteur</p>
            </div>
            <div class="etape">
                <div class="etape-icon">3</div>
                <h3>Livraison</h3>
                <p>Début décembre</p>
                <p class="etape-detail">Réception et distribution des cartons par nos soins</p>
            </div>
        </div>
    </div>
</section>

<!-- Section Compte à rebours -->
<section class="countdown">
    <div class="container">
        <h2>Temps restant pour commander</h2>
        <div id="countdown-timer" class="countdown-timer">
            <div class="time-unit">
                <span id="days" class="time-number">00</span>
                <span class="time-label">Jours</span>
            </div>
            <div class="time-unit">
                <span id="hours" class="time-number">00</span>
                <span class="time-label">Heures</span>
            </div>
            <div class="time-unit">
                <span id="minutes" class="time-number">00</span>
                <span class="time-label">Minutes</span>
            </div>
            <div class="time-unit">
                <span id="seconds" class="time-number">00</span>
                <span class="time-label">Secondes</span>
            </div>
        </div>
        <p id="countdown-message" class="countdown-message"></p>
        <p class="countdown-note">⚠️ Les commandes ne pourront plus être modifiées après cette date</p>
    </div>
</section>

<!-- Section Infos pratiques -->
<section class="infos">
    <div class="container">
        <h2>Informations pratiques</h2>
        <div class="infos-grid">
            <div class="info-card">
                <div class="info-icon">📅</div>
                <h3>Date limite</h3>
                <p>2 novembre 2025 à 23h59</p>
                <p class="info-detail">Dernier moment pour passer commande</p>
            </div>
            <div class="info-card">
                <div class="info-icon">🚚</div>
                <h3>Livraison</h3>
                <p>Début décembre 2025</p>
                <p class="info-detail">Livraison groupée pour plus d'efficacité</p>
            </div>
            <div class="info-card">
                <div class="info-icon">💳</div>
                <h3>Paiement</h3>
                <p>Virement bancaire</p>
                <p class="info-detail">Instructions envoyées après commande</p>
            </div>
        </div>
    </div>
</section>

<!-- Section Avantages -->
<section class="avantages">
    <div class="container">
        <h2>Pourquoi commander chez nous ?</h2>
        <div class="avantages-grid">
            <div class="avantage-card">
                <h3>🎯 Prix avantageux</h3>
                <p>Commande groupée = meilleurs prix que dans le commerce</p>
            </div>
            <div class="avantage-card">
                <h3>🤝 Soutien direct</h3>
                <p>Les commissions contribuent directement au financement de notre voyage scolaire</p>
            </div>
            <div class="avantage-card">
                <h3>🍷 Qualité garantie</h3>
                <p>Sélection rigoureuse de produits de qualité auprès de producteurs locaux</p>
            </div>
        </div>
    </div>
</section>

<!-- Section Contact -->
<section class="contact">
    <div class="container">
        <h2>Une question ? Contactez-nous !</h2>
        <div class="contact-info">
            <div class="contact-item">
                <div class="contact-icon">📧</div>
                <h3>Email</h3>
                <a href="mailto:vin-contact@ewenevin.fr">vin-contact@ewenevin.fr</a>
                <p>Réponse sous 24h</p>
            </div>
            <div class="contact-item">
                <div class="contact-icon">📞</div>
                <h3>Téléphone</h3>
                <p>+33 6 98 49 75 48</p>
                <p>Appel/SMS</p>
            </div>
        </div>
    </div>
</section>

<!-- Section CTA Final -->
<section class="cta-final">
    <div class="container">
        <h2>Prêt à nous soutenir ?</h2>
        <p>Rejoignez les nombreuses familles qui participent déjà à notre projet</p>
        <a href="index.php?page=produits" class="cta-button-large">Découvrir nos produits</a>
    </div>
</section>

<script>
// Compte à rebours amélioré
function updateCountdown() {
    const deadline = new Date('November 2, 2025 23:59:59').getTime();
    const now = new Date().getTime();
    const timeLeft = deadline - now;
    
    const countdownElement = document.getElementById('countdown-timer');
    const messageElement = document.getElementById('countdown-message');
    
    if (timeLeft < 0) {
        countdownElement.style.display = 'none';
        messageElement.textContent = 'Les commandes sont terminées.';
        messageElement.style.color = '#ff6b6b';
        messageElement.style.fontSize = '1.8rem';
        messageElement.style.fontWeight = 'bold';
        return;
    }
    
    const days = Math.floor(timeLeft / (1000 * 60 * 60 * 24));
    const hours = Math.floor((timeLeft % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
    const minutes = Math.floor((timeLeft % (1000 * 60 * 60)) / (1000 * 60));
    const seconds = Math.floor((timeLeft % (1000 * 60)) / 1000);
    
    document.getElementById('days').textContent = days.toString().padStart(2, '0');
    document.getElementById('hours').textContent = hours.toString().padStart(2, '0');
    document.getElementById('minutes').textContent = minutes.toString().padStart(2, '0');
    document.getElementById('seconds').textContent = seconds.toString().padStart(2, '0');
    
    // Animation des unités de temps
    if (seconds === 59) {
        animateTimeUnit('seconds');
    }
    if (minutes === 59 && seconds === 59) {
        animateTimeUnit('minutes');
    }
    if (hours === 23 && minutes === 59 && seconds === 59) {
        animateTimeUnit('hours');
    }
    if (days > 0 && hours === 23 && minutes === 59 && seconds === 59) {
        animateTimeUnit('days');
    }
}

function animateTimeUnit(unit) {
    const element = document.getElementById(unit);
    element.style.transform = 'scale(1.2)';
    setTimeout(() => {
        element.style.transform = 'scale(1)';
    }, 300);
}

// Mettre à jour le compte à rebours toutes les secondes
setInterval(updateCountdown, 1000);

// Initialiser immédiatement
updateCountdown();

// Animation au défilement améliorée
document.addEventListener('DOMContentLoaded', function() {
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };
    
    const observer = new IntersectionObserver(function(entries) {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
                
                // Animation séquentielle pour les étapes
                if (entry.target.classList.contains('etape')) {
                    const delay = Array.from(entry.target.parentElement.children).indexOf(entry.target) * 200;
                    entry.target.style.transitionDelay = delay + 'ms';
                }
            }
        });
    }, observerOptions);
    
    // Observer les éléments pour l'animation
    const animatedElements = document.querySelectorAll('.etape, .info-card, .avantage-card, .contact-item');
    animatedElements.forEach(element => {
        element.style.opacity = '0';
        element.style.transform = 'translateY(30px)';
        element.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
        observer.observe(element);
    });
    
    // Animation du hero
    const heroContent = document.querySelector('.hero-content');
    heroContent.style.opacity = '0';
    heroContent.style.transform = 'translateY(50px)';
    heroContent.style.transition = 'opacity 1s ease, transform 1s ease';
    
    setTimeout(() => {
        heroContent.style.opacity = '1';
        heroContent.style.transform = 'translateY(0)';
    }, 300);
});

// Effet de parallaxe sur le hero
window.addEventListener('scroll', function() {
    const scrolled = window.pageYOffset;
    const hero = document.querySelector('.hero');
    if (hero) {
        hero.style.transform = `translateY(${scrolled * 0.5}px)`;
    }
});
</script>

<?php include "app/views/footer.php"; ?>