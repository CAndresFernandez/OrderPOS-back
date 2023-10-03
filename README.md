# README

## Installation depuis un projet cloné

1- Après avoir cloné le repo

```cmd
composer install
```

2- Modifier le .env.local

```cmd
DATABASE_URL="mysql://IdentifiantDeConnexion:MotDePasse@127.0.0.1:3306/NomDeLaBDDACréer?serverVersion=8&charset=utf8mb4"
```

3- Créer la BDD

```cmd
php bin/console doctrine:database:create
```

4- Lancer les migrations

```cmd
php bin/console doctrine:migrations:migrate
```

5- Ajouter dans le .env.local (à récupérer auprès d'un dev back)

```cmd
JWT Passphrase:
```

5-b

```cmd
php bin/console lexik:jwt:generate-keypair
```
