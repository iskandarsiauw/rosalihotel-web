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