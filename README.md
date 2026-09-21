# Application métier de gestion d'une entreprise de location de matériel pour professionnels des espaces verts.

## Client
GreenPro loue des tondeuses, broyeurs, taille-haies, nacelles, motoculteurs, etc. à des entreprises de paysagisme.

Ils ont actuellement :

- un logiciel limité (celui-ci)
- des fichiers Excel
- des appels téléphoniques
- beaucoup d'opérations manuelles.

## Lancer le projet

Prérequis : [Docker](https://www.docker.com/) (avec Docker Compose) et `make`. Rien d'autre à installer
sur la machine (ni PHP, ni Composer, ni MySQL) : tout tourne dans les conteneurs.

```bash
git clone <url-du-dépôt> greenpro
cd greenpro
make start
```

`make start` construit l'image et démarre tous les services (build de l'image inclus, donc le premier
lancement est plus long — les suivants sont rapides). Une fois les conteneurs démarrés, dans un
**second terminal** :

```bash
make connect                                        # ouvre un shell dans le conteneur applicatif
bin/console doctrine:migrations:migrate --no-interaction
bin/console doctrine:fixtures:load --no-interaction
```

L'application est alors disponible sur :

| Service | URL |
| --- | --- |
| Application | http://localhost |
| Adminer (client MySQL web) | http://localhost:8001 |
| MailCatcher (emails interceptés en dev) | http://localhost:1080 |

Pour arrêter les conteneurs : `Ctrl+C`, ou `docker compose down` (ajouter `-v` pour repartir d'une base
de données vide).

### Après avoir modifié les entités

```bash
bin/console make:migration            # génère une migration à partir du diff des entités
bin/console doctrine:migrations:migrate --no-interaction
```

### Lancer les tests

```bash
bin/phpunit
```

## Espace admin

Connexion sur `/`, dashboard sur `/admin` (accès réservé aux rôles `MANAGER` et `ADMIN`).

Utilisateurs de test (fixtures) :

| Email | Mot de passe | Rôle |
| --- | --- | --- |
| admin@greenpro.fr | greenpro | ADMIN |
| manager@greenpro.fr | greenpro | MANAGER |
