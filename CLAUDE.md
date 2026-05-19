# Rosali Hotel Website

## Project Overview
Hotel website for Rosali Hotel with PHP backend and admin panel.
To be deployed on cPanel (Rumahweb shared hosting).

## Local Development
- Server: XAMPP on Windows
- URL: http://localhost:8088/rosalihotel/
- Database: MySQL via phpMyAdmin at http://localhost:8088/phpmyadmin
- Database name: rosalihotel

## Tech Stack
- Frontend: HTML/CSS/JS (converted from static design)
- Backend: PHP 8.5
- Database: MySQL
- Local: XAMPP
- Production: cPanel (Rumahweb), PHP 8.5

## Database Credentials (local)
- Host: localhost
- Database: rosalihotel
- User: root
- Password: (empty on XAMPP default)

## Deployment Target
- Main domain: www.rosalihotel.id
- Testing subdomain: testing.rosalihotel.id
- Document root: public_html/

## Code Rules
- Plain PHP only, no frameworks
- No TypeScript
- Keep includes/ for shared code (db, auth, functions)
- All uploaded images go to uploads/ subfolders
- Admin panel lives in admin/ folder
- Passwords must use password_hash() with PASSWORD_BCRYPT

## Folder Structure
- includes/ — db.php, auth.php, functions.php
- admin/ — all admin panel pages
- uploads/ — rooms/, gallery/, events/, cafe/
- assets/ — css/, js/, images/

## Room Tours
- 3D Gaussian Splat viewer planned for rooms page
- Splat files stored in uploads/splats/
- Use gsplat.js library for rendering (WebGL, client-side only)
- Normal video tours also supported, stored in uploads/videos/
- Video format: MP4 (H.264)
- No server-side processing needed for either format

## Security
- All user inputs must be sanitized and validated server-side
- Use prepared statements (PDO) for all database queries — no raw SQL with user input
- Admin panel protected by session auth — check auth.php on every admin page
- File uploads: validate file type (whitelist), validate MIME type, rename uploaded files to random hash
- No directory listing (add Options -Indexes to .htaccess)
- Sensitive config (db credentials) only in includes/db.php, never in public files
- Session timeout for admin after 30 minutes of inactivity
- CSRF protection on all forms

## SEO
- Each page must have unique <title> and <meta description>
- Use semantic HTML (h1, h2, article, section, nav)
- Images must have descriptive alt text
- sitemap.xml to be generated and submitted to Google Search Console
- robots.txt in root folder
- Page URLs should be clean (rooms.php not rooms.php?id=1 where possible)
- Open Graph tags for social sharing (og:title, og:description, og:image)
- Schema.org markup for Hotel and LodgingBusiness types
- Page load speed: compress images before upload, minify CSS/JS for production

## Theme System
- 6 themes: garden, boutique, javanese, rosa, coastal, batik
- Default active theme: rosa
- Theme stored in database (settings table), not localStorage
- Admin panel has Theme Switcher page to change active theme
- Theme applied server-side: PHP reads active theme from DB, 
  outputs correct class on <body> tag
- Theme CSS lives in shared.jsx (keep as-is, loaded client-side)
- Future themes can be added to shared.jsx THEME_CSS block + registered in DB
- Color overrides per theme also stored in DB (rosali_color_overrides column)
- URL ?theme= parameter still works for previewing themes without changing active

## Claude Code Run Mode
- Run with --dangerously-skip-permissions for autonomous builds
- Git commit after each major completed section as checkpoint
- Initialize git repo in this folder before first session

## Build Progress
- Phase 1 complete: folder structure, SQL tables, HTML→PHP rename, git setup
- Phase 2 complete: admin login, dashboard, logout, seed admin user
- Phase 3 complete: settings table, theme system DB-driven, all pages wired
- Phase 5 complete: admin audit, media library rewrite (images/videos/splats),
  CSRF on all POST endpoints, page visibility wired to front-end nav,
  content/layout/colors persisted to DB and injected server-side as window.ROSALI,
  all front-end upload UI removed, gallery.php reads from media table,
  splat (gsplat.js) support ready-but-dormant behind splat_enabled flag

## Front-end data flow (Phase 5)
- Every front-end PHP page includes includes/front_init.php which emits
  `<script>window.ROSALI = {...}</script>` with theme, lang, content overrides,
  layout prefs, color overrides, page visibility, media slot URLs, splat flag.
- shared.jsx reads exclusively from window.ROSALI — no localStorage for content.
- Media table is the source of truth for all uploaded files. Slot assignments
  use `assigned_to = 'slot:<key>'`; rooms use `assigned_to = 'room_<key>_splat'` etc.
- Splat .splat/.ksplat files load gsplat.js from CDN on rooms.php only when
  splat_enabled = 1 AND that room has an assigned splat.

## Admin Credentials (local dev only)
- Username: admin
- Password: Admin@Rosali123
- URL: http://localhost:8088/rosalihotel/admin/

## Git Commit Rules
- Commit after every completed feature or logical unit of work
- Never batch unrelated changes into one commit
- Commit message format: "Phase X: short description of what was done"
- Examples:
  - "Phase 4A: admin theme switcher page"
  - "Phase 4B: rooms table migration"
  - "Phase 4B: rooms list and delete"
  - "Phase 4B: rooms add/edit form with photo upload"
- Always run `git add -A` then `git commit -m "message"` after each unit
- Do not wait until everything is done to commit — commit incrementally
- If a phase has multiple files, commit each logical group separately:
  - DB migration first
  - Backend logic second  
  - UI/view files third