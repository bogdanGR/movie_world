# 🎬 Movie Voting App

This is a Symfony-based web application for browsing, voting (like/hate), and managing movies. Users can register, add movies, and vote on others' movies.
Implemented as a Case study for PHP Software Engineer at Travelstaytion.
---

## 🚀 Features

- User registration & login
- Add & list movies
- Vote (like/hate) on movies
- Sort by date, likes, and hates
- Filter by user ("My Movies")
- Fixtures included for demo/testing

---

## 🛠️ Requirements

- PHP 8.2 or higher
- Composer
- Symfony CLI (optional but recommended)
- MySQL
---

## 📦 Installation

### 1. Clone the Repository

```bash
git clone git@github.com:bogdanGR/movie_world.git
cd movie-voting-app
```
### 2. Install Dependencies and setup env

```bash  
composer install
cp .env .env.local
DATABASE_URL="mysql://db_user:db_password@127.0.0.1:3306/db_name?serverVersion=8.0"
```
### 3. Create the Database & Run Migrations
```bash
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
```

### 4. Load Fixtures (Dummy data)
```bash 
php bin/console doctrine:fixtures:load
```

### ▶️ Running the App
```bash
symfony serve
```
