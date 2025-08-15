# Pour initialiser un projet symfony

1. Créer les certificats SSL
    - Exécuter la commande suivante puis répondre aux questions posées (il est possible de tout laisser par défaut en mode développement)

    ```bash
    openssl req -x509 -new -out mycert.crt -keyout mycert.key -days 365 -newkey rsa:4096 -sha256 -nodes
    ```
    - Déplacer le fichier `mycert.crt` dans `etc/ssl/certs`
    - Déplacer le fichier `mycert.key` dans `etc/ssl/private`

2. Installer Symfony dans app en exécutant ces commandes **depuis le dossier du projet**

```bash
cd app
symfony new --webapp .
```

3. Depuis le dossier du projet, exécuter
```bash
docker compose up
```