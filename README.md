# Projet SIO - Gestion des Voitures

Bienvenue sur le projet **SIO - Gestion des Voitures**. Ce site web permet de gérer des voitures, leurs descriptions, leurs marques et modèles, ainsi que les utilisateurs. Il inclut des fonctionnalités comme la pagination, la recherche, et un système de gestion des utilisateurs avec des rôles (utilisateur ou administrateur).

## Description du Site

Le site propose :
- Une interface utilisateur moderne et responsive grâce à Bootstrap.
- La gestion des voitures avec ajout, modification, suppression et affichage des descriptions et images.
- **La description de chaque voiture s'affiche correctement sur la page de détails ou dans la liste des voitures, selon la configuration de l'application.**
- Un système de recherche pour trouver des voitures par marque ou modèle.
- Une gestion des utilisateurs avec des rôles (utilisateur ou administrateur).
- Une pagination dynamique pour naviguer entre les pages de contenu.

## Informations de Connexion

Des comptes de test sont disponibles pour se connecter :
- **Compte utilisateur** :
  - **Identifiant** : `user`
  - **Mot de passe** : `Password123$`
- **Compte administrateur** :
  - **Identifiant** : `admin`
  - **Mot de passe** : `Password123$`

## Installation

1. Clonez ce dépôt sur votre machine locale :
   ```bash
   git clone https://github.com/subarupnh/voiture-auto.git
   cd MonProjetLAMP
   ```
2. Configurez votre environnement LAMP (Linux, Apache, MySQL, PHP).
3. Importez le fichier SQL dans votre base de données MySQL :
   ```bash
   mysql -u root -p login_system < database/schema.sql
   ```
4. Configurez les informations de connexion à la base de données dans le fichier `config.php` :
   ```php
   define('DB_HOST', 'db');        // Nom du service Docker pour la base de données
   define('DB_NAME', 'login_system');
   define('DB_USER', 'root');
   define('DB_PASS', 'root');
   ```

5. Démarrez l'environnement Docker :
   ```bash
   docker-compose up -d
   ```

6. Accédez au projet :
   - Site web : `http://localhost/MonProjetLAMP`
   - PHPMyAdmin : `http://localhost:1200`
     - Utilisateur : root
     - Mot de passe : root

7. Vérifiez que :
   - Les services Docker sont en cours d'exécution
   - La base de données est accessible via PHPMyAdmin
   - Le site web s'affiche correctement dans votre navigateur

En cas de problème :
- Vérifiez les logs Docker : `docker-compose logs`
- Assurez-vous que les ports 80 et 1200 sont disponibles
- Vérifiez les permissions des dossiers du projet


