# StreamHive — Full Stack Streaming Platform

StreamHive is a full-stack academic streaming platform inspired by modern services like Netflix.  
The project demonstrates frontend and backend integration using PHP, MySQL, Vanilla JavaScript, AJAX APIs, and TMDB API integration.

---

# Team Members

- Sara Darwazeh
- Mohammad Darawsheh

---

# Features

## Authentication System
- User registration and login
- Session-based authentication
- Secure password hashing
- Protected routes and admin authorization

## Movie Platform Features
- Browse trending and top-rated movies
- Dynamic movie details pages
- Search movies using AJAX
- Personal watchlist (“My List”)
- Movie rating system
- Responsive UI for desktop and mobile

## Admin Dashboard
- Admin-only dashboard access
- User management system
- Suspend / activate / delete users
- Live database statistics

## TMDB API Integration
- Real TMDB API integration
- Backend API proxy using PHP
- Dynamic movie and poster fetching

## Database Features
- MySQL database persistence
- Users, ratings, and watchlists stored permanently
- Prepared statements for secure queries

---

# Tech Stack

## Frontend
- HTML5
- CSS3
- Bootstrap 5
- Vanilla JavaScript
- Fetch API / AJAX

## Backend
- PHP

## Database
- MySQL (Aiven hosted database)

## APIs
- TMDB API

---

# Project Architecture

```text
Browser
   ↓
JavaScript (AJAX / Fetch API)
   ↓
PHP API Endpoints
   ↓
MySQL Database / TMDB API
   ↓
Dynamic Frontend Rendering
```

---

# Project Structure

```text
streamhive/
├── public/
│   ├── index.php
│   ├── login.php
│   ├── register.php
│   ├── movie.php
│   ├── mylist.php
│   ├── search.php
│   ├── admin/
│   └── assets/
├── app/
│   ├── config/
│   ├── db/
│   ├── controllers/
│   ├── includes/
│   └── api/
├── sql/
└── README.md
```

---

# Installation

## Requirements
- PHP
- Apache
- MySQL
- XAMPP (recommended)

---

## Setup Instructions

1. Clone the repository

2. Place the project inside:

```text
xampp/htdocs/streamhive
```

3. Start Apache and MySQL using XAMPP

4. Configure database credentials inside:

```text
app/config/config.php
```

5. Import the database schema:

```text
sql/schema.sql
```

6. Open the project:

```text
http://localhost/streamhive/public
```

---

# AJAX / API System

The frontend communicates with PHP backend endpoints using Fetch API and AJAX.

Main APIs include:
- TMDB Proxy API
- Search API
- Watchlist API
- Rating API

Main AJAX logic:
```text
public/assets/js/ajax.js
```

---

# Authentication Flow

```text
Login/Register
   ↓
PHP Controllers
   ↓
Database Verification
   ↓
PHP Sessions
   ↓
Protected Routes
```

---

# Security Practices

- Prepared SQL statements
- Password hashing
- Session protection
- Admin authorization checks
- Hidden API credentials

---

# Educational Goals

This project demonstrates:
- Full-stack web development
- AJAX-driven applications
- REST-style backend APIs
- Authentication systems
- Database persistence
- Third-party API integration
- Secure backend development

---

# License

Academic Project — Educational Use Only
