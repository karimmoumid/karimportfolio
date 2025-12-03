# Portfolio - Karim MOUMID

[![Symfony](https://img.shields.io/badge/Symfony-7.3-000000?style=flat-square&logo=symfony)](https://symfony.com)
[![PHP](https://img.shields.io/badge/PHP-8.4-777BB4?style=flat-square&logo=php)](https://www.php.net)
[![Bootstrap](https://img.shields.io/badge/Bootstrap-5.3-7952B3?style=flat-square&logo=bootstrap)](https://getbootstrap.com)
[![License](https://img.shields.io/badge/License-MIT-green.svg?style=flat-square)](LICENSE)

Portfolio professionnel développé avec Symfony 7.3, présentant mes compétences et réalisations en développement web full-stack.

## 🚀 Fonctionnalités

### Interface Publique
- **Page d'accueil** avec présentation personnelle et projets en vedette
- **Portfolio de projets** avec système de filtrage par technologies
- **Galerie d'images** pour chaque projet avec carousel Bootstrap
- **Formulaire de contact** conforme RGPD avec envoi d'emails automatisés
- **Pages légales** (Mentions légales, Politique de confidentialité, CGU, Cookies)

### Panel d'Administration
- **Dashboard** avec statistiques en temps réel
- **Gestion des projets** (CRUD complet avec upload d'images multiples)
- **Gestion des compétences** avec logos
- **Gestion des messages** avec statut lu/non lu
- **Compteur de vues** pour chaque projet

### Conformité & Sécurité
- **RGPD Compliant** avec consentement obligatoire
- **Protection CSRF** sur tous les formulaires
- **Authentification sécurisée** avec hash bcrypt
- **Remember Me** pour les sessions
- **Gestion des cookies** avec bannière de consentement

## 🛠️ Technologies Utilisées

### Backend
- **Symfony 7.3** - Framework PHP
- **PHP 8.4** - Langage de programmation
- **Doctrine ORM** - Mapping objet-relationnel
- **Twig** - Moteur de templates
- **Symfony Mailer** - Envoi d'emails
- **Symfony Security** - Authentification et autorisation

### Frontend
- **Bootstrap 5.3** - Framework CSS
- **Font Awesome 6.4** - Icônes
- **JavaScript ES6+** - Interactions dynamiques
- **SCSS** - Préprocesseur CSS

### Base de Données
- **MySQL/MariaDB** - Système de gestion de base de données
- **Doctrine Migrations** - Gestion des schémas

## 📋 Prérequis

- PHP >= 8.4
- Composer
- Symfony CLI
- MySQL/MariaDB
- Node.js & npm (pour les assets)

## 🔧 Installation

### 1. Cloner le projet
```bash
git clone https://github.com/karimmoumid/portfolio.git
cd portfolio
```

### 2. Installer les dépendances PHP
```bash
composer install
```

### 3. Configuration de l'environnement
Copier et configurer le fichier `.env` :
```bash
cp .env .env.local
```

Modifier les variables dans `.env.local` :
```env
# Base de données
DATABASE_URL="mysql://user:password@127.0.0.1:3306/portfolio_db?serverVersion=8.0"

# Mailer (exemple avec MailHog pour le développement)
MAILER_DSN=smtp://localhost:1025

# Configuration personnelle
APP_ADMIN_EMAIL=karimmoumid@gmail.com
APP_SIRET=votre_siret
APP_APE=6201Z
```

### 4. Créer la base de données
```bash
# Créer la base de données
php bin/console doctrine:database:create

# Exécuter les migrations
php bin/console doctrine:migrations:migrate

# Charger les fixtures (optionnel)
php bin/console doctrine:fixtures:load
```

### 5. Créer les dossiers d'upload
```bash
mkdir -p public/uploads/projects
mkdir -p public/uploads/projects/gallery
mkdir -p public/uploads/skills
```

### 6. Installer les assets
```bash
# Installer les dépendances npm (si configuré)
npm install

# Compiler les assets
npm run build
```

### 7. Lancer le serveur de développement
```bash
symfony server:start
# ou
php -S localhost:8000 -t public
```

L'application sera accessible à : http://localhost:8000

## 👤 Création du premier administrateur
```bash
# Créer un utilisateur admin via la console
php bin/console app:create-admin

# Ou utiliser la page d'inscription puis modifier le rôle en base de données
```

## 📁 Structure du Projet
```
portfolio/
├── assets/               # Fichiers CSS/JS sources
├── config/              # Configuration Symfony
├── migrations/          # Migrations Doctrine
├── public/              # Dossier public (point d'entrée)
│   ├── css/            # CSS compilés
│   ├── js/             # JavaScript
│   └── uploads/        # Images uploadées
├── src/
│   ├── Controller/     # Contrôleurs
│   ├── Entity/         # Entités Doctrine
│   ├── Form/           # Types de formulaires
│   ├── Repository/     # Repositories
│   └── Service/        # Services métier
├── templates/           # Templates Twig
│   ├── admin/          # Templates administration
│   ├── emails/         # Templates d'emails
│   ├── legal/          # Pages légales
│   ├── main/           # Pages principales
│   └── project/        # Pages projets
├── .env                # Variables d'environnement
└── composer.json       # Dépendances PHP
```

## 🎯 Fonctionnalités Principales

### Gestion des Projets
- Upload d'image principale et galerie d'images
- Compteur de vues automatique
- Association avec des compétences techniques
- Description courte et complète
- Système de projets similaires

### Système de Contact
- Formulaire avec validation côté client et serveur
- Consentement RGPD obligatoire
- Email automatique à l'administrateur
- Accusé de réception au visiteur
- Stockage en base de données

### Panel d'Administration
- Tableau de bord avec statistiques
- Gestion CRUD complète
- Upload et optimisation d'images
- Système de notifications
- Export des données

## 🔒 Sécurité

- Authentification avec Symfony Security
- Protection CSRF sur tous les formulaires
- Validation des données côté serveur
- Escape automatique dans les templates Twig
- Headers de sécurité configurés
- Gestion des permissions par rôles

## 📧 Configuration Email

### Développement (MailHog)
```bash
# Installer et lancer MailHog
docker run -p 1025:1025 -p 8025:8025 mailhog/mailhog
```
Interface MailHog : http://localhost:8025

### Production
Configurer le DSN dans `.env.local` :
```env
MAILER_DSN=smtp://user:pass@smtp.gmail.com:587
```

## 🚀 Déploiement

### 1. Configuration production
```bash
# Passer en mode production
APP_ENV=prod

# Compiler les assets
npm run build
```

### 2. Optimisations
```bash
# Clear cache
php bin/console cache:clear --env=prod

# Warmup cache
php bin/console cache:warmup --env=prod

# Installer les dépendances sans dev
composer install --no-dev --optimize-autoloader
```

### 3. Permissions
```bash
# Donner les permissions d'écriture
chmod -R 775 var/
chmod -R 775 public/uploads/
```

## 📝 Commandes Utiles
```bash
# Lister toutes les routes
php bin/console debug:router

# Vérifier la configuration
php bin/console debug:config

# Créer un nouveau contrôleur
php bin/console make:controller

# Créer une nouvelle entité
php bin/console make:entity

# Mettre à jour le schéma de base de données
php bin/console doctrine:schema:update --force
```

## 🧪 Tests
```bash
# Lancer les tests unitaires
php bin/phpunit

# Lancer les tests avec couverture
php bin/phpunit --coverage-html coverage
```

## 📊 Monitoring

- Profiler Symfony disponible en mode développement (`/_profiler`)
- Logs dans `var/log/`
- Monitoring des emails envoyés
- Statistiques de visites par projet

## 🤝 Contribution

Les contributions sont les bienvenues ! N'hésitez pas à :
1. Fork le projet
2. Créer une branche (`git checkout -b feature/AmazingFeature`)
3. Commit vos changements (`git commit -m 'Add some AmazingFeature'`)
4. Push sur la branche (`git push origin feature/AmazingFeature`)
5. Ouvrir une Pull Request

## 📄 Licence

Ce projet est sous licence MIT. Voir le fichier [LICENSE](LICENSE) pour plus de détails.

## 👨‍💻 Auteur

**Karim MOUMID**

- Email : karimmoumid@gmail.com
- LinkedIn : [karim-moumid](https://www.linkedin.com/in/karim-moumid-0a0312104/)
- GitHub : [@karimmoumid](https://github.com/karimmoumid)
- Téléphone : +33 7 51 95 33 39
- Localisation : Deuil-la-Barre, France

## 🙏 Remerciements

- [Symfony](https://symfony.com) pour le framework
- [Bootstrap](https://getbootstrap.com) pour le design responsive
- [Font Awesome](https://fontawesome.com) pour les icônes
- EEDN - École Européenne des Nouvelles Technologies

---

© 2024-2025 Karim MOUMID. Tous droits réservés.
