# 📚 Book Store — Application PHP + MySQL

Librairie de romans arabes avec thème rose crème chaleureux.

## Structure des fichiers

```
bookstore/
├── index.php          → Boutique principale (catalogue + panier)
├── panier.php         → Page panier & validation commande
├── commandes.php      → Commandes par client ✨
├── login.php          → Connexion client
├── register.php       → Inscription client
├── logout.php         → Déconnexion
├── database.sql       → Schéma + données de test
├── css/
│   └── style.css      → Feuille de style (thème crème/rose)
└── includes/
    ├── config.php     → Connexion PDO + helpers
    ├── header.php     → En-tête commun
    └── footer.php     → Pied de page commun
```

## Installation

### 1. Base de données
```sql
-- Dans phpMyAdmin ou MySQL CLI :
SOURCE /chemin/vers/database.sql;
```

### 2. Configuration
Éditez `includes/config.php` et adaptez :
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'bookstore');
define('DB_USER', 'root');
define('DB_PASS', '');       // votre mot de passe MySQL
```

### 3. Serveur PHP
```bash
# Avec PHP built-in server (développement) :
cd bookstore/
php -S localhost:8000

# Ou placez dans htdocs/www de XAMPP/WAMP
```

### 4. Accès
- Boutique      → http://localhost:8000/index.php
- Commandes     → http://localhost:8000/commandes.php
- Connexion     → http://localhost:8000/login.php

## Comptes de test
| Email                   | Mot de passe  |
|-------------------------|---------------|
| sara@example.com        | sara1234      |
| youssef@example.com     | youssef1234   |
| nadia@example.com       | nadia1234     |

## Fonctionnalités
- ✅ Catalogue de romans arabes avec couvertures
- ✅ Filtres par genre + recherche
- ✅ Panier en session
- ✅ Inscription / Connexion client
- ✅ Passage de commande avec adresse
- ✅ Page commandes par client avec détail des livres
- ✅ Changement de statut des commandes
- ✅ Stats globales (CA, commandes, clients)
- ✅ Thème rose crème cohérent sur toutes les pages
