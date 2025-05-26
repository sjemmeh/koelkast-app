# Koelkast Kassa Systeem - Basis Installatie

Dit project vereist een webserver (zoals Apache of Nginx), PHP (compatibele versie), MariaDB (of MySQL), en Composer.

## Stappen voor Installatie

1.  **Code Verkrijgen:**
    * Clone de repository of kopieer de projectbestanden naar je webserver.
    * Navigeer naar de projectmap in je terminal.

2.  **PHP Dependencies Installeren:**
    ```bash
    composer install --optimize-autoloader --no-dev
    ```

3.  **Configuratiebestand (`.env`):**
    * Kopieer `.env.example` naar `.env`:
      ```bash
      cp .env.example .env
      ```
    * Genereer een applicatiesleutel:
      ```bash
      php artisan key:generate
      ```
    * Open `.env` en configureer minimaal:
        * `APP_URL`: De URL van je applicatie.
        * `DB_DATABASE`: Naam van je database.
        * `DB_USERNAME`: Database gebruikersnaam.
        * `DB_PASSWORD`: Database wachtwoord.
        * Zet `APP_ENV=production` en `APP_DEBUG=false` voor een live omgeving.
    * Het zou ook met sqlite moeten werken, maar niet getest.

4.  **Database Migraties:**
    * Zorg dat de database (gespecificeerd in `.env`) bestaat op je MariaDB server.
    * Voer de migraties uit om de tabellen aan te maken:
      ```bash
      php artisan migrate --force
      ```

5.  **Storage Link:**
    ```bash
    php artisan storage:link
    ```

6.  **Directory Permissies:**
    De webserver heeft schrijfrechten nodig voor de `storage` en `bootstrap/cache` mappen. Pas de eigenaar en permissies aan indien nodig (voorbeeld voor Linux met `www-data` gebruiker):
    ```bash
    sudo chown -R www-data:www-data storage bootstrap/cache
    sudo chmod -R 775 storage bootstrap/cache
    ```

7.  **Webserver Configuratie:**
    * Zorg ervoor dat je webserver (Nginx/Apache) de `public` map van je project als document root gebruikt.
    * Zorg voor correcte URL rewriting (Laravel's `public/.htaccess` doet dit voor Apache; voor Nginx heb je een specifieke server block configuratie nodig).

**Frontend Assets:**
* Dit project gebruikt Bootstrap 5.3 via CDN. Zorg voor een internetverbinding.
* Custom CSS wordt geladen via `public/css/kiosk-custom.css` en `public/css/beheer-custom.css`. Zorg dat deze bestanden correct gelinkt zijn in de Blade layouts (`layouts/kiosk.blade.php` en `layouts/beheer.blade.php`).

Na deze stappen zou de applicatie bereikbaar moeten zijn via de geconfigureerde `APP_URL`. Test alle functionaliteiten.