# StreamHive - Academic Streaming Platform

StreamHive is a minimal academic project that demonstrates a streaming platform concept similar to Netflix. Built with vanilla HTML, CSS, JavaScript, PHP, and MySQL.

## Project Overview

This is an educational project designed to showcase:
- Responsive web design with Bootstrap 5
- AJAX functionality with Fetch API
- PHP backend structure
- MySQL database design
- RESTful API concepts

## Tech Stack

- **Frontend:** HTML5, CSS3, JavaScript (Vanilla)
- **UI Framework:** Bootstrap 5 (CDN)
- **Backend:** PHP
- **Database:** MySQL (placeholders)
- **HTTP Client:** Fetch API

## Features

- User authentication (UI only)
- Browse movies by categories (Trending, New Releases, Top Rated)
- Movie details and ratings
- Personal My List / watchlist
- Live search functionality
- Admin dashboard (UI only)
- Responsive design (mobile, tablet, desktop)
- Dark theme with teal/purple accents

## Project Structure

```
streamhive/
├── README.md                 # This file
├── .gitignore               # Git ignore rules
├── public/                  # Web root
│   ├── index.php           # Home page
│   ├── login.php           # Login page
│   ├── register.php        # Registration page
│   ├── profile.php         # User profile
│   ├── movie.php           # Movie details
│   ├── mylist.php          # My List / Watchlist
│   ├── search.php          # Search page
│   ├── admin/              # Admin dashboard
│   │   ├── index.php       # Admin home
│   │   ├── movies.php      # Manage movies
│   │   └── users.php       # Manage users
│   └── assets/
│       ├── css/
│       │   └── style.css   # Custom styles
│       ├── js/
│       │   ├── main.js     # Main JS
│       │   └── ajax.js     # AJAX functions
│       └── img/
│           ├── logo.svg    # Brand logo
│           └── posters/    # Movie posters
├── app/
│   ├── config/
│   │   └── config.php      # Configuration
│   ├── db/
│   │   └── db.php          # Database connection
│   ├── includes/
│   │   ├── header.php      # Header template
│   │   ├── navbar.php      # Navigation bar
│   │   └── footer.php      # Footer template
│   ├── controllers/
│   │   ├── auth_controller.php
│   │   ├── movie_controller.php
│   │   └── list_controller.php
│   └── api/
│       ├── search.php      # Search API
│       ├── toggle_list.php # Add/Remove watchlist
│       └── rate.php        # Rating API
└── sql/
    └── schema.sql          # Database schema

```

## Installation

1. Clone the repository
2. Set up MySQL database (see `sql/schema.sql`)
3. Configure database connection in `app/config/config.php`
4. Place project in web server root
5. Access via `http://localhost/streamhive/public/`

## Notes

- This project contains placeholder content for educational purposes
- Database functionality is not yet implemented
- Authentication is UI-only
- All movie data is mock/hardcoded

## License

Academic Project - Educational Use Only

