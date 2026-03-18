# Hospital Management System - Deployment Guide

## Architecture

This application uses a **separated frontend + API** architecture:

- **Frontend** (`/frontend/`) - Static HTML/CSS/JS that runs in browser
- **API Backend** (`/api/`) - PHP REST API to be deployed on Hostinger

## Quick Start (Local Testing)

### Option 1: Just Open the Frontend
1. Open `frontend/index.html` directly in your browser
2. The app will load but API calls will fail until you deploy the backend

### Option 2: Deploy API to Hostinger First
1. Upload the `/api/` folder to Hostinger
2. Update `frontend/js/config.js` with your API URL
3. Open `frontend/index.html` in browser

## Hostinger Deployment

### Step 1: Upload API Backend

1. Log into Hostinger hPanel
2. Go to **File Manager**
3. Navigate to `public_html` (or your domain folder)
4. Create folder: `api`
5. Upload all contents from `/api/` folder:
   - `index.php`
   - `config.php`
   - `helpers.php`
   - `.htaccess`
   - `classes/` folder
   - `endpoints/` folder
   - `data/` folder (will be created automatically)

### Step 2: Configure API

Edit `api/config.php` on Hostinger:

```php
// Change the JWT secret (IMPORTANT for security!)
define('JWT_SECRET', 'your-unique-random-secret-key-here');

// Add your domain to allowed origins
define('ALLOWED_ORIGINS', [
    'https://yourdomain.com',
    'https://www.yourdomain.com'
]);
```

### Step 3: Set Permissions

In Hostinger File Manager:
- `api/data/` folder: 755
- JSON files in `api/data/`: 644

### Step 4: Configure Frontend

Edit `frontend/js/config.js`:

```javascript
// Change this to your Hostinger API URL
API_URL: 'https://yourdomain.com/api'
```

### Step 5: Upload Frontend

1. Upload contents of `/frontend/` folder to `public_html`
2. Or use a subdomain/folder for the frontend

## File Structure After Deployment

```
public_html/
├── index.html          (frontend entry)
├── css/
│   └── style.css
├── js/
│   ├── config.js       (API URL configured here)
│   ├── api.js
│   ├── auth.js
│   ├── router.js
│   ├── app.js
│   └── pages/
│       └── *.js
└── api/
    ├── index.php       (API entry point)
    ├── config.php      (API configuration)
    ├── .htaccess
    ├── classes/
    ├── endpoints/
    └── data/           (JSON storage - auto created)
```

## Default Login Credentials

| Role | Username | Password |
|------|----------|----------|
| Admin | admin | admin123 |
| Doctor | doctor | doctor123 |
| Receptionist | receptionist | reception123 |

**Important:** Change these passwords after first login!

## Security Checklist

- [ ] Change JWT_SECRET in `api/config.php`
- [ ] Update ALLOWED_ORIGINS with your actual domain
- [ ] Change default user passwords
- [ ] Ensure `api/data/` folder is not publicly accessible
- [ ] Enable HTTPS on your domain

## Troubleshooting

### CORS Errors
- Check that your domain is in ALLOWED_ORIGINS in `api/config.php`
- Make sure the API URL in `frontend/js/config.js` is correct

### 404 on API Routes
- Ensure `.htaccess` is uploaded and mod_rewrite is enabled
- Check that all PHP files are uploaded correctly

### Login Not Working
- Check browser console for errors
- Verify API URL is accessible
- Check that `api/data/users.json` exists

### Data Not Persisting
- Check write permissions on `api/data/` folder (755)
- Ensure PHP has write access to the directory

## API Endpoints Reference

Base URL: `https://yourdomain.com/api`

| Endpoint | Method | Description |
|----------|--------|-------------|
| /auth/login | POST | User login |
| /dashboard/stats | GET | Dashboard statistics |
| /patients | GET/POST | List/Create patients |
| /patients/:id | GET/PUT/DELETE | Patient CRUD |
| /doctors | GET/POST | List/Create doctors |
| /appointments | GET/POST | List/Create appointments |
| /opd | GET/POST | OPD visits |
| /ipd | GET/POST | IPD admissions |
| /surgery | GET/POST | Surgery management |
| /pharmacy/medicines | GET/POST | Medicine inventory |
| /pharmacy/dispense | POST | Dispense medicine |
| /laboratory/orders | GET/POST | Lab orders |
| /billing/invoices | GET/POST | Invoices |
| /reports/revenue | GET | Revenue report |
| /users | GET/POST | User management |
| /settings | GET/PUT | System settings |
