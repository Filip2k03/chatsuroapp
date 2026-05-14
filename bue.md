Slopara Secure Chat - System Blueprint

Tech Stack

Frontend: HTML5, CSS3 (Liquid Glass UI), Vanilla JavaScript

Backend: Pure PHP 8.x

Database: MySQL / MariaDB

Security: OpenSSL AES-256-CBC (Message Encryption), Argon2id (Password Hashing)

The 8-File Production Architecture

index.html - The Single Page Application (SPA) frontend.

schema.sql - Database table definitions.

db.php - PDO Database connection wrapper.

crypto.php - Utility functions for AES encryption/decryption.

auth.php - Handles login validation, sessions, and AFK heartbeat.

chat_api.php - Handles storing and fetching encrypted messages.

file_api.php - Converts text payloads into downloadable .py or .html files.

blueprint.md - Documentation.

Core Mechanisms

AFK Tracker: Frontend JS tracks mousemove/keydown. If idle > 120s, it pauses the heartbeat. auth.php updates last_active. If NOW() - last_active > 120, the user is marked offline globally.

Zero-Knowledge DB: Messages are encrypted in chat_api.php before hitting MySQL. A database breach reveals only ciphertext.