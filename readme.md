# Projet N° 7 Créez un web service exposant une API

BileMo est une entreprise offrant toute une sélection de téléphones mobiles. Cette offre fournit à travers les plateformes l'accès au catalogue via une API (Application Programming Interface) REST.

Le projet est créé en suivant les règles des niveaux 1, 2 et 3 du modèle de Richardson.

## Prérequis

Projet créé avec le framework Symfony 7.2
PHP >=8.2

## Installation

1 - Clonez ou telechargez le repository :
<https://github.com/davidg-34/bilemo.git>

2 - A la racine du répertoire, installer toutes les dépendances avec 'composer install'

3 - Modifier le fichier .env avec vos parametres pour créer votre base de données DATABASE_URL= "mysql://app:!ChangeMe!@127.0.0.1:3306/app?serverVersion=8&charset=utf8mb4"

4 - Créez la base de données et exécutez les migrations :

php bin/console doctrine:database:create php bin/console make:migration php bin/console doctrine:migrations:migrate

5 - Lancer les fixtures

php bin/console doctrine:fixtures:load

## Installer l'authentication avec JWT

Générer les clés privée et publique avec OpenSSL pour l'authentification :

1 - composer require lexik/jwt-authentication-bundle

2 - créer un dossier  'jwt'  au niveau de config

3 - générer 2 clés avec OpenSSL (dans Git Bash sous Windows) :

    1 - clé privée :

openssl genpkey -out config/jwt/private.pem -aes256 -algorithm RSA -pkeyopt rsa_keygen_bits:4096

    2 - clé publique : 

openssl rsa -in config/jwt/private.pem -pubout -out config/jwt/public.pem

4 - renommmer dans le fichier .env JWT_PASSPHRASE=

- le renommer en indiquant le mot de passe créé lors de la génération des clés (le mot de passe doit être identique pour la clé privée et publique)

## accéder à l'API

1 - Démarrer le serveur local : symfony serve ou symfony server:start -d

2 - Dans le navigateur entrer l'adresse <http://127.0.0.1:8000/api/doc> pour accéder à l'api

2 - Ajouter les données dans le body pour s'authentifier :

- username: <fnac@example.com>
- password: pass_123

NB : L'authentication est nécessaire seulement pour ajouter ou supprimer un utilisateur, soit pour POST ou DELETE
