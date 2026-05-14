Slopara Secure Chat - Enterprise MVC Blueprint

Tech Stack

Frontend: HTML5, CSS3 (Liquid Glass UI), Vanilla JavaScript (Separated Components)

Backend: Pure PHP 8.x MVC Architecture

Database: MySQL / MariaDB (Indexed for high-speed polling)

Security: OpenSSL AES-256-CBC, Argon2id, HTTPOnly Strict Sessions, ENV Configuration

The MVC Directory Structure

/ (Root)

index.php - Front Controller & MVC Entry Point.

.env - Secure Environment Variables (Excluded from git).

.htaccess - Clean URL rewrite engine for Apache.

/core/

env.php - Custom .env parsing engine.

router.php - Custom API & View routing dispatcher.

/includes/

header.php - Dynamic HTML <head> injection.

styles.php - Separated CSS styling logic.

scripts.php - Separated JS polling & UI logic.

/views/

chat.php - The main DOM structure and UI wrapper.

/api/ (Root Handlers)

auth.php, chat_api.php, file_api.php - Secure API logic.

crypto.php, db.php - Database and Encryption engines.

Security Upgrades

Zero-Knowledge DB: Encrypted at rest using env('APP_KEY').

Environment Engine: Database credentials are no longer hardcoded in PHP.

Session Hardening: Cookies restricted via HttpOnly and SameSite=Strict.

Clean URLs: Traffic is funneled through index.php, protecting direct access to core files.