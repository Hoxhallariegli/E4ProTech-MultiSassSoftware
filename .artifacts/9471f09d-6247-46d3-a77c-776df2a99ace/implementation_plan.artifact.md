# Rregullimi i gabimit 403 Forbidden për Broadcasting (Flutter & Web)

Ky ndryshim do të zgjidhë problemin ku aplikacioni Flutter nuk mund të vërtetohet (authenticate) në kanalet private të Laravel Reverb/Websockets për shkak të mbrojtjes CSRF.

## Problemi
Gabimi `403 Forbidden` ndodh sepse rrugët e broadcasting janë të mbrojtura me middleware `web`, i cili kërkon një CSRF Token. Aplikacionet mobile (Flutter) nuk përdorin CSRF, por përdorin `Bearer Token`.

## Ndryshimet e Propozuara

### 1. Backend (Laravel)

#### [MODIFY] [bootstrap/app.php](file:///C:/laragon/www/LaraFluterAuto/bootstrap/app.php)
Do të përditësojmë konfigurimin e broadcasting për të përdorur middleware `api` në vend të `web`.
- `api` middleware nuk kërkon CSRF token.
- `auth:sanctum` do të vazhdojë të lejojë vërtetimin si për Web (përmes session) ashtu edhe për Flutter (përmes Bearer Token).

#### [MODIFY] [app/Http/Middleware/VerifyCsrfToken.php](file:///C:/laragon/www/LaraFluterAuto/app/Http/Middleware/VerifyCsrfToken.php)
Për siguri shtesë, do të shtojmë `broadcasting/auth` në listën e përjashtimeve (except) nëse middleware `web` aplikohet diku tjetër globalisht.

## Plani i Verifikimit

### Verifikimi Manual
1. Provo të hysh në aplikacionin Flutter dhe shiko nëse kanalet private lidhen pa gabimin 403.
2. Provo versionin Web të aplikacionit për të siguruar që njoftimet (notifications) funksionojnë ende.
3. Kontrollo log-et e Laravel (`storage/logs/laravel.log`) për ndonjë gabim të ri vërtetimi.
