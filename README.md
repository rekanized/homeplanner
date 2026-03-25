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
Never miss an item again. Create multiple lists with real-time synchronization, drag-and-drop sorting, and integrated **OpenFoodFacts** support for a truly intelligent grocery experience.

### 📝 Todo System
Efficiently manage your household chores. Track due dates, prioritize tasks with ease, and get alerted on overdue items before they pile up.

### 💰 Economy Manager
Take control of your finances. Track incomes, savings, and expenses with inline editing and automatic category-based auditing. Designed for transparency and quick updates.

### 👨‍👩‍👧‍👦 Kids Points System (Coming Soon)
A dedicated module to engage children in household chores through a points-based reward system.

## 🚀 Quick Deploy

Create a `docker-compose.yml` and run `docker compose up -d`:

```yaml
services:
  app:
    image: rekanized/homeplanner-app:latest
    restart: unless-stopped
    environment:
      - APP_NAME=Homeplanner
      - APP_ENV=production
      - APP_DEBUG=false
      - APP_URL=${APP_URL:-http://localhost:8080}
      - DB_CONNECTION=sqlite
      - DB_DATABASE=/data/database.sqlite
      - SESSION_DRIVER=database
      - CACHE_STORE=database
      - QUEUE_CONNECTION=database
    volumes:
      - db-data:/data
      - app-storage:/app/storage

  nginx:
    image: rekanized/homeplanner-nginx:latest
    restart: unless-stopped
    ports:
      - "8080:8080"
    depends_on:
      - app

volumes:
  db-data:
  app-storage:
```

---
Powered by [Rekanized](https://github.com/rekanized)

