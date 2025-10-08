
    // Auto-hide flash messages after 5 seconds
    document.addEventListener('DOMContentLoaded', function() {
    setTimeout(function() {
        const alerts = document.querySelectorAll('.alert');
        alerts.forEach(alert => {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        });
    }, 5000);
});

    // Smooth scrolling for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        e.preventDefault();
        const target = document.querySelector(this.getAttribute('href'));
        if (target) {
            target.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
        }
    });
});
    // Gestion des cookies RGPD
    document.addEventListener('DOMContentLoaded', function() {
        // Vérifier si l'utilisateur a déjà fait un choix
        if (!localStorage.getItem('cookiesAccepted') && !localStorage.getItem('cookiesRefused')) {
            document.getElementById('cookieBanner').style.display = 'block';
        }
    });

    function acceptCookies() {
        localStorage.setItem('cookiesAccepted', 'true');
        localStorage.setItem('cookiesAcceptedDate', new Date().toISOString());
        document.getElementById('cookieBanner').style.display = 'none';
        // Activer les cookies analytiques si nécessaire
        enableAnalytics();
    }

    function refuseCookies() {
        localStorage.setItem('cookiesRefused', 'true');
        localStorage.setItem('cookiesRefusedDate', new Date().toISOString());
        document.getElementById('cookieBanner').style.display = 'none';
        // Désactiver tous les cookies non essentiels
        disableAnalytics();
    }

    function enableAnalytics() {
        // Si vous utilisez Google Analytics
        // window.dataLayer = window.dataLayer || [];
        // function gtag(){dataLayer.push(arguments);}
        // gtag('js', new Date());
        // gtag('config', 'GA_MEASUREMENT_ID');
    }

    function disableAnalytics() {
        // Désactiver les analytics
        // window['ga-disable-GA_MEASUREMENT_ID'] = true;
    }
