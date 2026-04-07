# ChronoTask - Premium PHP Todo Application

ChronoTask is a modern, high-performance Todo application with a PHP backend and a sleek, responsive frontend. It's designed to be secure, portable, and easy to deploy.

## ✨ Features

- **Dynamic Task Management**: Add, edit, toggle, and delete tasks in real-time.
- **Smart Reminders**: Set specific times for your tasks and receive notifications.
- **Task Suggestions**: Quick-add common tasks with a single click.
- **Secure Backend**: PHP-powered API with PDO for safe database interactions.
- **Dockerized**: Fully containerized environment for consistent local development and deployment.
- **Render-Ready**: Optimized for seamless deployment on Render.com.

---

## 🚀 Getting Started

### Prerequisites

- [Docker](https://www.docker.com/) (Recommended)
- OR **PHP 7.4+** and **MySQL** installed locally.

### 🛠️ Local Environment Setup

1. **Clone the repository**:
   ```bash
   git clone <your-repo-url>
   cd todo-app
   ```

2. **Configure Secrets**:
   Copy the `.env.example` to a new file named `.env`:
   ```bash
   cp .env.example .env
   ```
   Open `.env` and add your database credentials.

### 🐳 Running with Docker (easiest)

1. Start the container:
   ```bash
   docker-compose up --build
   ```
2. Your app will be live at: **`http://localhost:8080`**

### 🐘 Running with Standard PHP

1. Ensure your local MySQL server is running and your `.env` is correctly configured.
2. Start a local PHP server:
   ```bash
   php -S localhost:8000
   ```
3. Your app will be live at: **`http://localhost:8000`**

---

## 🌐 Deployment to Render

1. **Connect your GitHub Repo**: In the Render Dashboard, create a new **Web Service**.
2. **Runtime**: Select **Docker** (Render will automatically detect your `Dockerfile`).
3. **Environment**: Add the following variables:
   - `DB_HOST`
   - `DB_NAME`
   - `DB_USERNAME`
   - `DB_PASSWORD`
4. **Deploy**: Render will build and deploy your app instantly!

---

## 🔒 Security

- **Environment Variables**: Sensitive data is never hardcoded. We use `.env` files for local development and Render's environment variables for production.
- **Ignore Files**: Both `.gitignore` and `.dockerignore` are configured to prevent your secrets from ever being leaked.
- **PDO prepared statements**: All database queries are protected against SQL injection.

## 🛠️ Tech Stack

- **Frontend**: HTML5, Vanilla CSS3 (Custom Glassmorphism), JavaScript (ES6+).
- **Backend**: PHP 7.4+ (API-driven).
- **Database**: MySQL (PDO).
- **Containerization**: Docker & Docker Compose.
- **Cloud Infrastructure**: Render.

---

**Brought to you by Techy-Jboy**
