# F1 - Lokalizacje (PHP + MySQL)
## Setup
1. Skopiuj katalog `public/` do katalogu publicznego Twojego serwera (np. /var/www/html/).
2. Skopiuj `src/` i `templates/` do katalogu powyżej publicznego (lub zgodnie z ustawieniami).
3. Utwórz bazę i tabele uruchamiając `sql/schema.sql` w MySQL (użytkownik `red`, hasło `work2hard`).

## Dane połączenia (src/config.php)

Host: 127.0.0.1
DB: f1db
User: red
Pass: work2hard

## Uwaga
Ten projekt to prosty przykład gotowy do dalszego rozwoju. Formularze używają CSRF tokenów oraz PDO.
