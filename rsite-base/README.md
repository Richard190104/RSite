# RSite — MO SRZ Medzilaborce

Template webstranky s admin panelom. Postavené na **CakePHP 5**, PHP 8.1+, MySQL.

## Technológie

- **PHP 8.1+** (lokálne testované na 8.3, cez Laragon)
- **CakePHP 5** — MVC framework
- **MySQL/MariaDB**
- **Sass/SCSS** — kompiluje sa cez npm (Node.js), nie cez PHP
- **cakephp/authentication** — prihlásenie do admina
- **cakephp/migrations** — schéma DB

## Lokálne spustenie

1. `composer install`
2. `npm install`
3. Skopíruj `config/app_local.example.php` → `config/app_local.php`, vyplň lokálne DB údaje (Laragon default: `root` / prázdne heslo)
4. `bin/cake migrations migrate` — vytvorí a naplní všetky tabuľky
5. `bin/cake create_admin_user` — vytvorí prihlasovací účet do `/admin` (pýta sa na username/heslo, nie je to webový formulár — **žiadny verejný registračný formulár neexistuje zámerne**)
6. `npm run sass:build` (alebo `npm run sass:watch` počas vývoja) — skompiluje SCSS do `webroot/css/*.css`
7. Appka je v `webroot/` — spusti si vlastný PHP server (`php -S localhost:8765 -t webroot webroot/index.php`) alebo cez Laragon/Apache

## Architektúra — Admin vs Verejná časť

Appka je rozdelená na dve úplne nezávislé časti:

| | Admin (`/admin/*`) | Verejná časť (`/`) |
|---|---|---|
| Controllery | `src/Controller/Admin/*` (namespace `App\Controller\Admin`) | `src/Controller/*` |
| Šablóny | `templates/Admin/*` | `templates/Pages/*` a pod. |
| Layout | `templates/layout/admin.php` | `templates/layout/default.php` |
| CSS | `resources/scss/admin.scss` → `webroot/css/admin.css` | `resources/scss/app.scss` → `webroot/css/app.css` |
| Prístup | vyžaduje prihlásenie (pozri nižšie) | verejné |

Tieto dve CSS sú **úplne oddelené** — štýly z jednej sa nikdy nedostanú do druhej (iný `.scss` entry point, iný `<link>` v layoute). Admin má navyše technickejší/plochší vzhľad (menej farieb, ploché tlačidlá) definovaný v `resources/scss/_admin-layout.scss`, scoped pod `.admin-shell`/`.admin-login` — aj keby sa `admin.css` omylom načítal na verejnej stránke, nič by sa nezmenilo (tie triedy tam neexistujú).

Routing: `config/routes.php` — `$routes->prefix('Admin', ...)` definuje celú `/admin/*` sekciu.

## Prihlásenie do admina

- Plugin `cakephp/authentication`, konfigurácia v `src/Application.php` (`getAuthenticationService()`).
- `Admin\AppController` (base trieda pre všetky admin controllery) načíta `Authentication.Authentication` komponentu a v `beforeFilter()` whitelistuje len akciu `login` — všetko ostatné pod `/admin/*` vyžaduje session.
- Heslá sú bcrypt hash (`DefaultPasswordHasher`), nikdy plaintext.
- **Vytvorenie/zmena admin účtu ide len cez CLI**: `bin/cake create_admin_user --username=... --password=...` (alebo interaktívne bez parametrov).

## Databáza — prehľad tabuliek

Migrácie v `config/Migrations/` (chronologicky, `bin/cake migrations status` ukáže stav).

| Tabuľka | Účel | Poznámka |
|---|---|---|
| `admin_users` | prihlasovacie účty do `/admin` | bcrypt heslo |
| `banners` | hero banner + "mini banner" dlaždice | pozri nižšie |
| `pages` | fixné stránky (`home`, `news`, `gallery`) | `content` = JSON |
| `navbar_categories` | kategórie v navigácii, `hasMany Pages` | |
| `categories` | kategórie pre News/Events/Galleries, **hierarchické** (`parent_id` self-FK) | `show_in_gallery` bool |
| `news` | novinky | `image` upload, `category_id` FK |
| `events` | podujatia | `category_id` FK, `date` |
| `galleries` | fotky | `image` upload, `category_id` FK |
| `texts` | key/value základné texty webu (title, name, organisation, city, mainContact...) | pozri nižšie |

### `pages` — fixné stránky, nie generický CMS

`pages` obsahuje len 3 riadky (`home`, `news`, `gallery`) — sú to **systémové** stránky, nie niečo, čo admin vytvára/maže. `content` (JSON stĺpec) drží štruktúrovaný obsah špecifický pre danú stránku:
- `home`: `{about_us_text: string, quick_access: [page_id, ...]}` — edituje sa cez `Admin\PagesController::editHome()`.
- `news`/`gallery`: zatiaľ bez špecifických polí (placeholder v `Admin\PagesController::edit()`).

`page.title` je pre tieto 3 fixné riadky **prekladaný ako UI string** (`__($page->title)`), nie zobrazovaný natvrdo z DB — pretože "Home"/"News"/"Gallery" sú v podstate systémové labely, nie admin obsah. **Dôležité:** je to dynamické `__()` volanie, `bin/cake i18n extract` ho nenájde automaticky — pri pridaní novej fixnej stránky treba `msgid` doplniť do `.po` súborov ručne.

### `banners` — hero banner aj "mini" dlaždice v jednom

`location` stĺpec určuje, kde sa banner zobrazí:
- reálny `pages.slug` (napr. `home`) → hlavný hero banner na tej stránke
- `home_mini` (virtuálna lokácia, `BannersTable::VIRTUAL_LOCATIONS`) → "o nás" dlaždice na homepage, nie sú to reálne stránky

`is_enabled` (bool) — per-banner zapnutie/vypnutie zobrazenia (nie globálny switch).
`settings` (JSON) — voľné banner-špecifické nastavenia (momentálne len `subtitle`), rozšíriteľné bez migrácie na nové klúče podľa potreby.

### `categories` — hierarchické, zdieľané medzi News/Events/Galleries

`parent_id` (self-FK, `ON DELETE SET NULL`) — kategória môže mať rodiča (subkategória). `show_in_gallery` (bool) — či sa fotky s touto kategóriou majú zobrazovať aj vo verejnej galérii, alebo slúžia len na prepojenie s podujatím.

### `texts` — key/value, riadky pridáva LEN migrácia

`slug` + `value`. Admin cez `/admin/texts` **len edituje existujúce hodnoty**, nikdy nepridáva/nemaže riadky — nové texty (nový `slug`) sa pridávajú výhradne cez migráciu (`INSERT`). Pozri `config/Migrations/..._AddMainContactToTexts.php` ako vzor.

## Dôležitý gotcha — JSON stĺpce na shared hostingu

`pages.content` a `banners.settings` sú JSON stĺpce. **Lokálne to CakePHP rozpozná automaticky** zo schémy DB, ale na niektorých MySQL/MariaDB verziách (narazili sme na to na InfinityFree hostingu) sa JSON typ nereflektuje správne a ORM skúša PHP array uložiť ako plain string → crash.

**Riešenie použité v projekte:** v `initialize()` daného Table (napr. `PagesTable`, `BannersTable`) explicitne:
```php
$this->getSchema()->setColumnType('content', 'json');
```
Ak pridáš nový JSON stĺpec na inú tabuľku, **urob to isté hneď**.

## Upload obrázkov

Spoločná logika je v `src/Controller/Admin/ImageUploadTrait.php` (`use ImageUploadTrait;` v controlleri) — používajú ju `BannersController`, `NewsController`, `GalleriesController`. Validuje typ/veľkosť, presúva súbor do `webroot/img/{banners,news,galleries}/` pod náhodným UUID menom (nikdy pod menom z klienta), maže starý súbor pri replace/delete.

## Preklady (i18n)

- UI stringy: `__('English text')` v kóde/šablónach, default locale `en_US`, preklad do `sk_SK` v `resources/locales/sk_SK/default.po`.
- **Postup pri novom texte:**
  1. `bin/cake i18n extract --paths=src,templates --output=resources/locales --merge=yes --overwrite` — prepíše **len** `default.pot` (šablónu), nikdy `.po` súbory priamo.
  2. Porovnaj nové `msgid` s existujúcimi v `sk_SK/default.po` (napr. `comm -23` na zoradené zoznamy) a doplň **len nové**.
  3. `bin/cake cache clear _cake_core_` — preklady sa cachujú, bez tohto sa zmena neprejaví.
- **Dynamické `__($variable)` volania** (napr. `__($page->title)`, `__(ucfirst($text->slug))`) **extract nevidí** — tie treba do `.po` dopĺňať ručne.

## Štýly (SCSS)

- `resources/scss/app.scss` — verejný web. `resources/scss/admin.scss` — admin. Oba `@use` spoločný `_base.scss` (farby, flash správy, formuláre) a `_variables.scss`.
- `npm run sass:build` — jednorazový build (minified). `npm run sass:watch` — automaticky prekompiluje pri zmene.
- **CakePHP CSS nekompiluje samo** — po zmene `.scss` treba vždy pustiť build, inak sa zmena neprejaví ani lokálne ani na produkcii.

## Nasadenie na produkciu

Momentálne beží na **InfinityFree** (free hosting, **bez SSH prístupu**). Toto výrazne ovplyvňuje workflow:

- **Nahrávanie súborov:** len FTP (FileZilla). Nikdy nenahrávaj `node_modules/`, `.git/`, `.env` (má nastavený FileZilla filter — Ctrl+I → "Directory listing filters").
- **`config/.env` na serveri musí mať `DEBUG="false"`** a zakomentovaný/upravený `DATABASE_URL` (inak prebije `app_local.php` a appka sa pripája na lokálny Laragon).
- **`config/app_local.php` na serveri** má produkčné DB údaje (iné než lokálne) — nikdy ho neprepíš lokálnou verziou.
- **Migrácie bez SSH:** `webroot/run-migrations.php` — jednorazový skript, ktorý spustí `Migrations\Migrations::migrate()` naživo cez web request (vyžaduje `?token=...` v URL, token nastavený v samotnom súbore). **Po použití vždy zmaž zo servera** — je to citlivý endpoint. Lokálnu kópiu si nechaj na budúce použitie.
- **Malé SQL zmeny** (napr. jeden `ALTER TABLE`) sa dajú spustiť aj priamo cez phpMyAdmin (SQL tab) — rýchlejšie než cez `run-migrations.php`, ale **nezapíše sa do `cake_migrations`** trackingu, takže je lepšie použiť `run-migrations.php`, keď je to možné.
- **Live databáza nevie, čo bolo aplikované manuálne** — ak niekedy použiješ ručný SQL mimo `run-migrations.php`, si sám povinný si to zapamätať/zapísať.
- **Chyby na produkcii:** `logs/error.log` na serveri (nie ten lokálny!). Cesty v ňom začínajú `/home/vol.../htdocs/...` — ak vidíš `C:\Users\...`, pozeráš sa na lokálny log omylom.

Ak by sa raz prešlo na hosting so SSH (zvažovali sme Websupport.sk, ~3€/mesiac), celý tento postup sa zjednoduší na `git pull && composer install && bin/cake migrations migrate`.

## Admin sekcie (aktuálny stav)

Zoznam v `templates/element/Admin/sidebar.php`: Dashboard, Texts, Banners, Navbar categories, Pages, News, Categories, Events, Galleries.

## Čo ešte chýba / je len základ

- Verejné vykresľovanie homepage (sekcie "O nás", quick access, novinky) — zatiaľ len `PagesController::home()` naťahuje dáta, dizajn/markup ešte nie je hotový.
- Verejné listovanie News/Events/Galleries (admin CRUD je hotový, verejná strana zatiaľ nie).
- Navbar element (`templates/element/navbar.php`) má základný CSS-only dropdown, mobilné zobrazenie (bez hover) ešte nerieši.
- Brute-force ochrana na login (rate limiting) — zatiaľ nie je, len bcrypt + CSRF + session.