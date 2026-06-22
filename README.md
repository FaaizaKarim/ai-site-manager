# AI Site Manager

A full-stack web application to manage multiple websites with AI-powered content editing using the Claude API.

## 🚀 Live Demo

**[https://ai-site-manager-production.up.railway.app/auth/login.php](https://ai-site-manager-production.up.railway.app/auth/login.php)**

Test Credentials:
- Email: `admin@example.com`
- Password: `password` (default from schema)

---

## 📋 Project Overview

**AI Site Manager** is a SaaS-ready content management solution that enables users to:
- Manage multiple websites from a single dashboard
- Edit page content with an intuitive rich editor
- Leverage Claude AI for intelligent content operations (improve, rewrite, shorten, optimize for SEO, generate)
- Track all AI-powered edits with comprehensive audit logs
- Deploy to production with Railway via Docker

### Key Metrics
- **Repository**: FaaizaKarim/ai-site-manager
- **Language**: PHP 8+
- **Created**: 16 days ago (as of June 22, 2026)
- **Status**: Active development
- **Access**: Public
- **Size**: ~822 KB

---

## ✨ Core Features

### 1. Multi-Site Management
- Create and manage unlimited websites
- Organize multiple sites in a unified dashboard
- Site-level metadata (name, URL, description)
- Quick access to pages per site

### 2. Content Editor
- Full-featured page content editor per site
- Page status tracking (draft/published)
- URL slug generation for SEO
- WYSIWYG editing capabilities
- Image upload support (JPG, PNG, GIF, WebP, max 2MB)

### 3. AI-Powered Content Assistance
- **Improve**: Enhance writing quality, clarity, and flow
- **Rewrite**: Transform content with more engaging tone
- **Shorten**: Condense content to half size while retaining key points
- **SEO Optimize**: Optimize content for search engines
- **Generate**: Create full page content from scratch based on title

### 4. Comprehensive Audit Logging
- Complete history of all AI requests
- Tracks action type, prompt, and AI response
- Enables content version tracking and accountability
- Linked to specific pages for easy retrieval

### 5. Session-Based Authentication
- Secure login/logout system
- Password reset functionality via email
- User session management with remember-me option
- Protected routes requiring authentication

### 6. Dashboard & Analytics
- Quick stats: Total sites, pages, AI requests
- Published vs. draft page breakdown
- Real-time page search and filtering
- User-friendly interface with sidebar navigation

---

## 🏗️ Tech Stack

| Component | Technology |
|-----------|-----------|
| **Backend** | PHP 8.0+ with PDO |
| **Database** | MySQL 8.0+ (InnoDB) |
| **Frontend** | HTML5, CSS3, Vanilla JavaScript |
| **AI Integration** | Claude API (Anthropic) |
| **API Model** | claude-sonnet-4-20250514 |
| **Web Server** | Apache with FrankenPHP (Docker) |
| **Container** | Docker + Railway deployment |
| **HTTP/2 Support** | Caddy (Caddyfile config) |
| **Development Tool** | Cursor IDE |

---

## 📁 Project Structure

```
ai-site-manager/
├── api/
│   ├── ai-assist.php         # Claude API integration endpoint
│   └── upload-image.php      # Image upload handler
├── auth/
│   ├── login.php             # Login page
│   ├── register.php          # User registration
│   ├── logout.php            # Logout handler
│   ├── session.php           # Session utilities
│   ├── sidebar.php           # Navigation sidebar
│   └── password-reset.php    # Password reset flow
├── config/
│   ├── db.php               # Database connection & env loader
│   └── claude.php           # Claude API configuration
├── pages/
│   ├── dashboard.php        # Main dashboard
│   ├── site.php             # Site pages list
│   ├── add-site.php         # Create new site
│   ├── add-page.php         # Create new page
│   ├── editor.php           # Page content editor
│   ├── preview.php          # Page preview
│   ├── delete-site.php      # Delete site handler
│   └── audit-log.php        # AI request history
├── assets/
│   ├── css/style.css        # Application styling
│   ├── js/editor.js         # Editor functionality
│   └── uploads/             # User-uploaded images
├── migrations/              # Database schema versions
├── vendor/                  # Composer dependencies
├── .env.example             # Environment variables template
├── .dockerignore            # Docker ignore list
├── .gitattributes           # Git attributes
├── .gitignore               # Git ignore rules
├── schema.sql               # Database schema (MySQL)
├── Dockerfile               # Docker image definition
├── docker-entrypoint.sh     # Docker startup script
├── Caddyfile                # HTTP/2 reverse proxy config
├── railway.toml             # Railway deployment config
├── health.php               # Health check endpoint
└── index.php                # App entry point (redirects)
```

---

## 🗄️ Database Schema

### Tables Overview

#### `users`
```sql
- id (INT, PK)
- email (VARCHAR 255, UNIQUE)
- password_hash (VARCHAR 255)
- name (VARCHAR 100)
- created_at (TIMESTAMP)
```

#### `sites`
```sql
- id (INT, PK)
- user_id (INT, FK → users)
- name (VARCHAR 255)
- url (VARCHAR 500)
- description (TEXT)
- created_at (TIMESTAMP)
```

#### `pages`
```sql
- id (INT, PK)
- site_id (INT, FK → sites)
- title (VARCHAR 255)
- slug (VARCHAR 255)
- content (LONGTEXT)
- status (ENUM: 'draft', 'published')
- created_at (TIMESTAMP)
- updated_at (TIMESTAMP)
```

#### `ai_logs`
```sql
- id (INT, PK)
- page_id (INT, FK → pages)
- action (VARCHAR 50) — improve|rewrite|shorten|seo|generate
- prompt (TEXT)
- response (LONGTEXT)
- created_at (TIMESTAMP)
```

#### `password_resets`
```sql
- id (INT, PK)
- email (VARCHAR 255)
- token (VARCHAR 64)
- expires_at (DATETIME)
- used (TINYINT)
- created_at (TIMESTAMP)
```

**Default User**: `admin@example.com` (password: "password")

---

## ⚙️ Setup & Installation

### Prerequisites
- PHP 8.0 or higher
- MySQL 8.0 or higher
- Node.js/npm (optional, for local development)
- Docker & Docker Compose (for containerized deployment)

### Local Development (XAMPP/LAMP)

1. **Clone the repository**
   ```bash
   git clone https://github.com/FaaizaKarim/ai-site-manager.git
   cd ai-site-manager
   ```

2. **Set up environment variables**
   ```bash
   cp .env.example .env
   # Edit .env and fill in your values
   ```

3. **Import database schema**
   - Open phpMyAdmin
   - Create a database named `ai_site_manager`
   - Import `schema.sql` into the database

4. **Place project in XAMPP**
   ```bash
   # Copy project to XAMPP htdocs
   cp -r ai-site-manager /opt/lampp/htdocs/
   ```

5. **Start services**
   ```bash
   # Start Apache and MySQL via XAMPP
   ```

6. **Access the application**
   ```
   http://localhost:8080/ai-site-manager/auth/login.php
   ```

### Docker Deployment (Railway)

1. **Configure environment on Railway**
   - Set `DB_HOST=mysql.railway.internal`
   - Set `CLAUDE_API_KEY` to your Anthropic API key
   - Configure other env vars as needed

2. **Deploy via Railway**
   ```bash
   railway up
   ```

3. **Access production instance**
   ```
   https://ai-site-manager-production.up.railway.app/auth/login.php
   ```

---

## 🔑 Environment Variables

Create a `.env` file (copy from `.env.example`) with:

```env
# Database Configuration
DB_HOST=localhost              # MySQL host
DB_NAME=ai_site_manager        # Database name
DB_USER=root                   # DB username
DB_PASS=                        # DB password
DB_PORT=3306                   # DB port

# Claude AI Configuration
CLAUDE_API_KEY=sk-ant-...      # Your Anthropic API key (required)
CLAUDE_MODEL=claude-sonnet-4-20250514  # Model version
CLAUDE_MAX_TOKENS=2048         # Max tokens per request

# Application Configuration
APP_NAME=AI Site Manager
APP_URL=https://your-app.up.railway.app
APP_SECRET=mySecretKey123      # Session encryption key

# Email Configuration (Optional)
MAIL_USER=your-gmail@gmail.com
MAIL_PASS=your-16-char-app-password
```

---

## 🚀 Deployment

### Railway (Recommended)

1. Connect GitHub repository to Railway
2. Configure environment variables in Railway dashboard
3. Railway automatically detects `Dockerfile` and deploys
4. Database is provisioned via Railway MySQL add-on

### Docker (Local)

```bash
# Build image
docker build -t ai-site-manager .

# Run container
docker run -p 8080:8080 \
  -e DB_HOST=mysql \
  -e CLAUDE_API_KEY=sk-ant-... \
  ai-site-manager
```

---

## 🔐 Security Features

- ✅ **Password Hashing**: bcrypt hashing with PHP `password_hash()`
- ✅ **SQL Injection Prevention**: Prepared statements with parameterized queries
- ✅ **Session Management**: Secure PHP session handling
- ✅ **CORS Headers**: Proper header validation
- ✅ **Input Validation**: Form validation on upload, content, and user input
- ✅ **File Upload Validation**: MIME type checking, size limits (2MB max)
- ✅ **Authentication Checks**: `requireLogin()` on all protected routes

---

## 📚 API Endpoints

### Authentication
- `POST /auth/login.php` — User login
- `POST /auth/register.php` — New account creation
- `POST /auth/logout.php` — Clear session
- `POST /auth/password-reset.php` — Password recovery

### Content Management
- `GET /pages/dashboard.php` — Main dashboard
- `GET /pages/site.php?id={siteId}` — View site pages
- `POST /pages/add-site.php` — Create new site
- `POST /pages/add-page.php` — Create new page
- `POST /pages/editor.php?id={pageId}` — Edit page content
- `GET /pages/preview.php?id={pageId}` — Preview page
- `POST /pages/delete-site.php?id={siteId}` — Delete site

### AI Integration
- `POST /api/ai-assist.php` — AI content operations
  - **Actions**: `improve`, `rewrite`, `shorten`, `seo`, `generate`
  - **Request**: `{ action, content, title, page_id }`

### File Management
- `POST /api/upload-image.php` — Upload image to page

### Utilities
- `GET /health.php` — Health check endpoint

---

## 🧪 Testing the Application

### Default Account
- **Email**: admin@example.com
- **Password**: password

### Test Workflow
1. Log in with default credentials
2. Create a new site
3. Add a page to the site
4. Use AI assistant to improve/rewrite content
5. Preview changes
6. Check audit log of AI requests
7. Publish page

---

## 🐛 Known Limitations & Future Enhancements

### Current Limitations
- Single-user deployments (admin account only initially)
- No user registration UI (via email only)
- Limited pagination on large datasets
- No image cropping/resizing tools
- No multi-language support

### Planned Features
- [ ] User registration and role-based access
- [ ] Team collaboration tools
- [ ] Advanced SEO analytics
- [ ] Email notification system
- [ ] Scheduled content publishing
- [ ] Version history/rollback
- [ ] Export to static HTML
- [ ] Mobile app companion

---

## 📊 Performance Considerations

- **Database**: InnoDB engine for ACID compliance
- **Caching**: Page preview caching recommended
- **API Limits**: Claude API rate limiting applies (see Anthropic docs)
- **File Uploads**: 2MB max, stored locally in `/assets/uploads/`
- **Session Timeout**: PHP default 24 minutes (configurable)

---

## 🛠️ Troubleshooting

### "Database connection failed"
- Verify `DB_HOST`, `DB_USER`, `DB_PASS` in `.env`
- Ensure MySQL is running
- Check network connectivity (especially on Railway)

### "Claude API key not configured"
- Add `CLAUDE_API_KEY` to `.env` with valid Anthropic key
- Restart PHP/Docker container

### "Image upload failing"
- Verify `/assets/uploads/` folder exists and is writable
- Check file size (must be < 2MB)
- Confirm MIME type is supported (JPG, PNG, GIF, WebP)

### "Pages not displaying"
- Clear browser cache
- Check `pages` table has entries for the site
- Verify `page.status = 'published'`

---

## 📄 License

This project is provided as-is. No specific license is currently assigned.

---

## 👤 Author

**FaaizaKarim**  
GitHub: [@FaaizaKarim](https://github.com/FaaizaKarim)

---

## 💬 Support & Feedback

For issues, feature requests, or questions:
1. Open a GitHub Issue
2. Include relevant error logs
3. Describe steps to reproduce
4. Attach screenshots if applicable

---

## 📞 Live Demo & Contact

**Live Demo**: [https://ai-site-manager-production.up.railway.app/auth/login.php](https://ai-site-manager-production.up.railway.app/auth/login.php)

---

*Last Updated: June 22, 2026*  
*Repository: [github.com/FaaizaKarim/ai-site-manager](https://github.com/FaaizaKarim/ai-site-manager)*
