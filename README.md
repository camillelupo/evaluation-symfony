Intallation

git clone https://github.com/camillelupo/evaluation-symfony.git

composer install

Créer un dossier jwt dans le dossier config et générer des clés SSH avec Openssl

openssl genrsa -out private.pem -aes256 4096

openssl rsa -pubout -in private.pem -out public.pem

Créer un .env à la racine du projetet configuré le avec l'aide du .env.test

php bin/console doctrine:database:create

php bin/console doctrine:schema:update --force

symfony server:start
