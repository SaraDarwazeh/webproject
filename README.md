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
- Personal watchlist ("My List")
- Movie rating and commenting system
- Responsive UI for desktop and mobile

## Subscription & Points System
- Points-based content purchasing
- Subscription plans for unlimited access
- Transaction history per user
- Access-gated features (rating, commenting) for subscribers
- Admin analytics for subscriptions and revenue

## Jellyfin Streaming Integration (Proof of Concept)
- Local Jellyfin media server integration for actual video playback
- Mapped TMDB content to Jellyfin library items via a config-driven content map
- In-browser video player with full playback controls
- TV series support: season/episode browsing and per-episode streaming
- Access control enforced through the subscription/purchase system
- PHP API bridge proxies requests between the frontend and Jellyfin

> **Note:** This is a proof of concept. The Jellyfin server runs locally, so streaming
> is only available on the machine hosting Jellyfin. A production deployment would
> use a publicly accessible media server or a tunneling service (e.g. Tailscale).

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
- Users, ratings, watchlists, and transactions stored permanently
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

## Media Server
- Jellyfin (local, proof of concept)

## APIs
- TMDB API
- Jellyfin API (local bridge)

---

# Project Architecture

```text
Browser
   ↓
JavaScript (AJAX / Fetch API)
   ↓
PHP API Endpoints
   ↓
MySQL Database / TMDB API / Jellyfin Server
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
│   ├── watch.php              ← Video player page (Jellyfin PoC)
│   ├── admin/
│   └── assets/
├── app/
│   ├── config/
│   │   ├── config.php
│   │   └── jellyfin_config.php ← Jellyfin content map
│   ├── db/
│   ├── controllers/
│   ├── includes/
│   └── api/
│       ├── tmdb.php
│       └── jellyfin.php        ← Jellyfin API bridge
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
- Jellyfin Server (optional, for streaming PoC)

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

### Jellyfin Setup (Optional)

To enable video playback for the proof of concept:

1. Install and run [Jellyfin](https://jellyfin.org/) on the same machine
2. Add your media library in Jellyfin
3. Generate an API key in Jellyfin Dashboard → API Keys
4. Update `app/config/jellyfin_config.php` with your API key and content IDs

---

# AJAX / API System

The frontend communicates with PHP backend endpoints using Fetch API and AJAX.

Main APIs include:
- TMDB Proxy API
- Search API
- Watchlist API
- Rating API
- Jellyfin Bridge API (stream URLs, seasons, episodes)
- Purchase / Subscription API

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
- Media server integration
- Secure backend development

---

# License

Academic Project — Educational Use Only
