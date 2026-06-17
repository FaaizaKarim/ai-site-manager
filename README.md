# AI Site Manager

A full-stack web application to manage multiple websites with AI-powered
content editing using the Claude API.

## Live Demo

[https://your-railway-url.up.railway.app](https://ai-site-manager-production.up.railway.app/auth/login.php)

## Features

- Multi-site management with dashboard
- Page content editor per site
- Claude AI: rewrite, improve, shorten, or generate content
- Full audit log of all AI requests
- Session-based authentication

## Tech Stack

PHP · MySQL · JavaScript · Claude API (Anthropic) · Cursor

## Setup

1. Clone the repo
2. Copy `.env.example` to `.env` and fill in your values
3. Import `schema.sql` into MySQL via phpMyAdmin
4. Place the project in your XAMPP `htdocs` folder as `ai-site-manager`
5. Run on PHP 8+ with Apache (default: `http://localhost:8080/ai-site-manager`)

### Default Login

- Email: `admin@example.com`
- Password: `admin123`

## Screenshots

[Add screenshots of dashboard, editor, AI panel]
