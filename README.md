<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

# Chatbot API

## Description

A mini-app exposing an API to query a simple chatbot using OpenAI and storing the interaction history. Includes a minimal frontend.

---

## Requirements

- **Laravel** 10+ (tested on 12)
- **PHP** 8.2+
- **Composer**
- **SQLite**
- **OpenAI API Key** (set in `.env`)
- **Required PHP extensions:**
  - pdo
  - pdo_sqlite
  - openssl
  - mbstring
  - tokenizer
  - xml
  - ctype
  - json
  - curl
  - zip

---

## Local Installation & Usage

### 1. Clone the repository
```bash
# Clone
git clone https://github.com/BryanFG07/chatbot-api
cd chatbot-api
```

### 2. Basic setup
```bash
# Linux / macOS / Windows PowerShell
cp .env.example .env
composer install
php artisan key:generate
```

### 3. SQLite Database
```bash
# Create file if it doesn't exist
# ----------------------------
# Linux / macOS
# ----------------------------
mkdir -p database
touch database/database.sqlite
php artisan migrate

# ----------------------------
# Windows PowerShell
# ----------------------------
New-Item -ItemType Directory -Path database -Force
New-Item -ItemType File -Path database/database.sqlite -Force
php artisan migrate

# ----------------------------
# Windows CMD
# ----------------------------
if not exist database mkdir database
type nul > database\database.sqlite
php artisan migrate
```


### 4. Add your OpenAI API Key
Edit `.env` and add:
```
OPENAI_API_KEY=your_api_key_here
```

### 5. Run the app
```bash
php artisan serve
# Open http://127.0.0.1:8000
```

---

## Running with Laravel Sail (Docker)

If you prefer to use Docker, you can run the project with Laravel Sail:

```bash
# Clone the repository
git clone https://github.com/BryanFG07/chatbot-api
cd chatbot-api

# Basic setup
cp .env.example .env
composer install
php artisan key:generate

# Start containers
./vendor/bin/sail up -d
./vendor/bin/sail artisan migrate

# Open http://127.0.0.1:8000
```

You can use MySQL or SQLite with Sail.

### Using MySQL with Sail
To use MySQL in a container with Sail:
1. Uncomment and adjust the following lines in your `.env.example`:
  ```
  DB_CONNECTION=mysql
  DB_HOST=mysql
  DB_PORT=3306
  DB_DATABASE=laravel
  DB_USERNAME=sail
  DB_PASSWORD=password
  ```
2. Copy `.env.example` to `.env` and make sure the values are correct.
3. Run the Sail commands as usual:
  ```bash
  ./vendor/bin/sail up -d
  ./vendor/bin/sail artisan migrate
  ```
4. Open http://127.0.0.1:8000

---

## API Endpoints

### 1. `POST /api/ask`
Sends a question to the chatbot and receives a response.

---

### 2. `GET /api/history`
Retrieves chatbot interaction history.

**Query Parameters:**
- `limit` (optional, default: 10) → Number of interactions to return.
- `keyword` (optional) → Filter interactions containing this keyword in the question or answer.

**Behavior:**
- If `keyword` is not specified, returns the last `limit` interactions.
- If `limit` is not specified, returns the last 10 interactions by default.

---

### 3. `DELETE /api/history`
Deletes all chatbot interaction history.

**Optional:** Can be used to clear all saved interactions.



---

## Frontend
- Form to ask questions and "Ask" button.
- Area to display the chatbot's answer.
- "History" section showing the last N interactions.
- Input to set the maximum number of history responses.

---

## Project Structure

- `app/Http/Controllers/ChatController.php` — Main controller
- `app/Services/OpenAIService.php` — Service for OpenAI requests
- `app/Models/Interaction.php` — Interaction model (history)
- `database/migrations/` — Migrations
- `resources/views/chat.blade.php` — Minimal frontend
- `public/js/chat.js` — Frontend JS logic
- `public/css/chat.css` — CSS styles

---

## Usage Examples

```bash
# Ask a question
curl -X POST http://127.0.0.1:8000/api/ask \
  -H "Content-Type: application/json" \
  -d '{"question":"Give me a simple idea for financial wellness."}'

# Get history
curl http://127.0.0.1:8000/api/history?limit=10
 
# Get history filtered by keyword
curl http://127.0.0.1:8000/api/history?limit=10&keyword=finance

# Delete all history
curl -X DELETE http://127.0.0.1:8000/api/history
```

---

## Troubleshooting

### Network Error: SSL certificate problem
If you get an error like:
```
Unable to connect to OpenAI cURL error 60: SSL certificate problem: unable to get local issuer certificate
```
This means your PHP/cURL cannot verify the SSL certificate for OpenAI. To fix:

1. Download the certificate bundle `cacert.pem` from:
   https://curl.se/docs/caextract.html
2. Save it somewhere safe (e.g. `C:/php/cacert.pem`)
3. Edit your `php.ini` and add or update:
   ```
   curl.cainfo = "C:/php/cacert.pem"
   openssl.cafile = "C:/php/cacert.pem"
   ```
4. Restart your web server or PHP process.

This will allow PHP to verify SSL certificates and connect to OpenAI securely.


---

## Author
- BryanFG07