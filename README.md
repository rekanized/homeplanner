<p align="center">
  <img src="public/favicon.svg" width="128" alt="Homeplanner Logo">
</p>

# 🏡 Homeplanner
**Global management of your household ecosystems.**

[![Buy Me a Coffee](https://img.shields.io/badge/Buy%20Me%20a%20Coffee-Donate-yellow.svg)](https://buymeacoffee.com/rekanized)

Homeplanner is a modern, high-performance web application designed to streamline your daily household operations. From financial health to grocery synchronization and task management, Homeplanner brings everything into one unified, premium interface.

## ✨ Core Modules

### 📊 Home Dashboard
Your command center. Get an instant overview of your monthly income/expenses, active shopping items, and pending tasks. Featuring a **Task Productivity Chart** to track your completion trends over time.

### 🛒 Smart Shopping Lists
Never miss an item again. Create multiple lists with real-time synchronization, drag-and-drop sorting, and fast local Swedish/English aisle sorting.

### 📝 Todo System
Efficiently manage your household chores. Track due dates, prioritize tasks with ease, and get alerted on overdue items before they pile up.

### 💰 Economy Manager
Take control of your finances. Track incomes, savings, and expenses with inline editing and automatic category-based auditing. Designed for transparency and quick updates.

### 👨‍👩‍👧‍👦 Kids Points System
Engage children in household chores through a gamified reward economy. Parents can assign tasks with point values, while children track their progress and redeem points for rewards. Features include monthly performance stats and a transparent spending history.

## 🚀 Quick Deploy

Save this as `docker-compose.yml`:

```yaml
services:
  app:
    image: rekanized/homeplanner-app:latest
    restart: unless-stopped
    stop_grace_period: 90s
    environment:
      APP_URL: http://localhost:8080
    volumes:
      - db-data:/data
      - app-storage:/app/storage

  nginx:
    image: rekanized/homeplanner-nginx:latest
    restart: unless-stopped
    ports:
      - "8080:8080"
    volumes:
      - app-storage:/app/storage:ro
    depends_on:
      app:
        condition: service_healthy

volumes:
  db-data:
  app-storage:
```

Start Homeplanner:

```sh
docker-compose up -d
```

Open **http://localhost:8080** to create your first account. If you access it through another hostname or IP address, change `APP_URL` to that address.

`app` runs Homeplanner and its scheduled/background tasks; `nginx` serves the website. Database creation, migrations, and encryption-key generation happen automatically. The two volumes keep your database, key, and uploads between restarts. No `.env` file or manual setup commands are needed.

With the Docker Compose plugin, the equivalent command is `docker compose up -d`.

---
Powered by [Rekanized](https://github.com/rekanized)
