# Nasazení projektu a automatické čištění dat

## 1. Co bylo přidáno do projektu

- **Soft delete pro `pocasi_data`**
  - `app/Database/Migrations/2026-09-14-090000_AddDeletedAtToPocasiData.php` — přidá sloupec `deleted_at`
  - `app/Models/SData.php` — `useSoftDeletes = true`
  - `app/Controllers/Pocasi.php` — metody `mazani()` (formulář) a `mazaniSmazat()` (zpracování)
  - `app/Views/mazani.php` — formulář: stanice + měsíc + rok
  - Route: `GET /mazani`, `POST /mazani` (chráněno CSRF filtrem)
- **Automatické čištění starých dat**
  - `app/Commands/PurgeOldData.php` — spark příkaz `pocasi:purge-old`, soft-deletuje záznamy starší než 11 let (dá se změnit přes `--years`)

## 2. Před nasazením — spustit migraci

Na serveru (nebo lokálně před nahráním) spusť:

```bash
php spark migrate
```

Tím se v tabulce `pocasi_data` vytvoří sloupec `deleted_at`, pokud tam ještě není (migrace je idempotentní — dá se spustit klidně vícekrát, nic nerozbije).

Ruční alternativa přímo v DB (např. přes phpMyAdmin), pokud nemáš na serveru přístup ke spark/CLI:

```sql
ALTER TABLE pocasi_data ADD COLUMN deleted_at DATETIME NULL;
```

## 3. Nasazení na server (obecný postup)

Přesné kroky závisí na tom, jaký přístup ti učitel dal (FTP/cPanel vs. SSH). Obecně:

1. **Nahraj soubory**
   - Přes SSH: `git clone`/`git pull` repozitáře na server.
   - Přes FTP: nahraj celou složku projektu (klidně bez `vendor/`, `node_modules/` — ty se dobuildí na serveru, viz níže).
2. **Composer závislosti** (pokud máš SSH):
   ```bash
   composer install --no-dev --optimize-autoloader
   ```
   Pokud SSH nemáš, nahraj `vendor/` složku rovnou (jak je teď v ZIPu).
3. **`.env` soubor** — na serveru vytvoř/uprav `.env` (nebo přejmenuj `env` na `.env`, pokud tam ještě je) a nastav:
   ```ini
   CI_ENVIRONMENT = production

   app.baseURL = 'https://tvoje-domena-nebo-cesta/'

   database.default.hostname = localhost
   database.default.database = nazev_databaze
   database.default.username = uzivatel
   database.default.password = heslo
   database.default.DBDriver = MySQLi
   ```
   **Nikdy nenahrávej `.env` do produkce s `CI_ENVIRONMENT = development`** — v produkci by se zobrazovaly detailní chybové hlášky a debug toolbar.
4. **Document root** musí směřovat do složky `public/`, ne do kořene projektu. Pokud to na serveru nejde nastavit (typické u sdíleného hostingu), buď:
   - přesuň obsah `public/` do kořene a uprav cesty v `index.php` (`FCPATH`, `SYSTEMPATH` apod.), nebo
   - použij `.htaccess` redirect do `public/` (méně čisté, ale funguje bez přístupu k nastavení vhostu).
5. **Práva na zápis** — složka `writable/` (a její podsložky `cache`, `logs`, `session`, `uploads`, `debugbar`) musí mít práva k zápisu pro webserver (např. `chmod -R 755 writable` nebo dle konkrétního hostingu).
6. **Import databáze** — nahraj SQL dump s daty (stanice, země, naměřená data) do databáze na serveru, pokud tam ještě není.
7. **Spusť migraci** na serveru (`php spark migrate`), aby vznikl sloupec `deleted_at`.
8. **Otestuj** — otevři web, projdi si `/`, `/staniceAll`, `/mazani`, ověř že se nic neláme a že nejede debug toolbar.

## 4. Automatické měsíční mazání dat starších 11 let

Příkaz, který se má spouštět:

```bash
php spark pocasi:purge-old
```

(volitelně `php spark pocasi:purge-old --years 11`, ale 11 je i výchozí hodnota, takže stačí bez parametru)

### Varianta A — cron na Linux serveru (nejběžnější)

Přes SSH:

```bash
crontab -e
```

a přidej řádek, který spustí příkaz vždy 1. den v měsíci ve 3:00 ráno:

```
0 3 1 * * cd /cesta/k/projektu && php spark pocasi:purge-old >> writable/logs/purge.log 2>&1
```

### Varianta B — cPanel / sdílený hosting bez SSH

V cPanelu najdi **Cron Jobs**, zvol "Once per month" (nebo nastav ručně stejné hodnoty jako výše: minuta 0, hodina 3, den 1, měsíc *, den v týdnu *) a jako příkaz zadej:

```
/usr/bin/php /cesta/k/projektu/spark pocasi:purge-old
```

(cesta k `php` binárce se liší podle hostingu — cPanel obvykle nabízí správnou cestu v rozbalovacím seznamu nebo přes "Cron Job Environment").

### Varianta C — CodeIgniter Task Scheduler (pokud používáš CI ≥ 4.5 a chceš plánování řešit v kódu)

Pokud by šlo o novější projekt s vlastním `app/Config/Scheduler.php`, dalo by se přidat:

```php
$schedule->command('pocasi:purge-old')->monthly();
```

a pak by stačil jediný cron řádek spouštějící `php spark tasks:run` každou minutu. Pro tento projekt ale stačí přímý cron na `pocasi:purge-old` (varianta A/B) — je to jednodušší a nezávisí to na dalším nastavení.

### Ruční test příkazu

Než necháš cron běžet automaticky, vyzkoušej si příkaz ručně:

```bash
php spark pocasi:purge-old
```

Vypíše, kolik záznamů smazal (soft delete) a od jakého data. Pokud žádné takto staré záznamy v databázi nejsou, napíše to taky — nic se nerozbije, dá se spouštět opakovaně.
