# New Laptop Setup Guide

Complete migration guide for setting up the development workspace on a new laptop.

## 1. Software to Install

### Core Runtimes
| Software | Version | Download |
|----------|---------|----------|
| Node.js | v24.x | https://nodejs.org |
| PHP | 8.2+ | https://windows.php.net or via XAMPP/Laragon |
| Python | 3.12+ | https://python.org |
| Git | Latest | https://git-scm.com |
| Docker Desktop | Latest | https://docker.com |
| VS Code | Latest | https://code.visualstudio.com |

### Package Managers
```bash
# pnpm (for AbsorbLMSMgmt, AdherenceManagement)
npm i -g pnpm@10

# Composer (for MediStock Pro / Laravel)
# Download from https://getcomposer.org
```

### Databases
| Database | Version | Needed For |
|----------|---------|------------|
| MySQL 8.0 | Latest | MediStock Pro |
| PostgreSQL 14+ | Latest | AbsorbLMSMgmt, InnoVerse (or SQLite for dev) |

### Azure & Cloud Tools
```bash
npm i -g azure-functions-core-tools@4
npm i -g @azure/static-web-apps-cli
npm i -g @marp-team/marp-cli
# Also install Azure CLI from https://learn.microsoft.com/en-us/cli/azure/install-azure-cli
```

### Global Python Packages
```bash
pip install bcrypt openpyxl paramiko pillow PyMuPDF PyPDF2 python-docx python-pptx xlsxwriter invoke lxml
```

---

## 2. Files to Manually Copy (NOT in any git repo)

> **These folders have no git remote and MUST be copied manually via USB/cloud drive.**

| Folder | Contents |
|--------|----------|
| `c:/Working/Powerpoint/` | Business docs (RFP scoring, brand guidelines, PowerPoint decks), Node.js utility scripts |
| `c:/Working/func-deploy/` | Azure Functions deployment artifact |

### Environment / Secret Files (.env)
These are git-ignored. Export from old laptop and import to new:

| File Path | Contains |
|-----------|----------|
| `MediStock Pro/.env` | Anthropic API key, MySQL creds, Mailgun SMTP |
| `AbsorbLMSMgmt/packages/api/.env` | Absorb LMS API key, DATABASE_URL |
| `AbsorbLMSMgmt/packages/api/local.settings.json` | Azure Functions local config |
| `AbsorbLMSMgmt/packages/web/.env` | VITE_API_URL |
| `YogaStudioMgmt/.env` | Supabase credentials |
| `Innovtion Tracker/innovation-platform/.env` | DB URL, NextAuth secrets (if exists) |

---

## 3. Git Configuration

```bash
git config --global user.name "Amrendra Dubey"
git config --global user.email "amrendra.dubey@ynvgroup.com"
```

---

## 4. Clone All Repositories

### GitHub (personal - github.com/amar4h)
```bash
mkdir c:/Working && cd c:/Working

git clone https://github.com/amar4h/HospitalManagement.git "Hospital Management"
git clone https://github.com/amar4h/MediStock-Pro.git "MediStock Pro"
git clone https://github.com/amar4h/RituYog-Studio.git YogaStudioMgmt
git clone https://github.com/amar4h/RituYog-Studio-Monorepo.git YogaStudio
```

### Azure DevOps (enterprise - YNV-Engineering)
```bash
git clone https://YNV-Engineering@dev.azure.com/YNV-Engineering/AdherenceManagement/_git/AdherenceManagement
git clone https://dev.azure.com/YNV-Engineering/absorb-management/_git/InnoVerse "Innovtion Tracker"
git clone https://YNV-Engineering@dev.azure.com/YNV-Engineering/absorb-management/_git/MeetingCostCalculator
```

> You'll be prompted to authenticate via Git Credential Manager for both GitHub and Azure DevOps.

---

## 5. Post-Clone Setup (per project)

### Hospital Management
```bash
cd "Hospital Management"
php -S localhost:8000
# No dependencies to install - uses CDN libraries
```

### MediStock Pro (Laravel)
```bash
cd "MediStock Pro"
composer install
npm install
cp .env.example .env
# Paste saved .env secrets
php artisan key:generate
php artisan migrate
npm run build
php artisan serve
```

### AbsorbLMSMgmt
```bash
cd AbsorbLMSMgmt
pnpm install
# Copy .env files to packages/api/ and packages/web/
# Copy local.settings.json to packages/api/
cd packages/api && npx prisma generate && npx prisma migrate dev
cd ../..
pnpm dev
```

### YogaStudioMgmt
```bash
cd YogaStudioMgmt
npm install
# Copy .env with Supabase credentials
npm run dev
```

### YogaStudio (Astro website)
```bash
cd YogaStudio
npm install
npm run website
```

### Innovation Tracker
```bash
cd "Innovtion Tracker/innovation-platform"
npm install
npx prisma generate && npx prisma migrate dev
npm run dev
```

### MeetingCostCalculator (Outlook Add-in)
```bash
cd MeetingCostCalculator
npm install
npm run dev
```

### AdherenceManagement
```bash
cd AdherenceManagement
pnpm install
pnpm dev
```

---

## 6. Verify Everything Works

After setup, verify each project runs:
- [ ] Hospital Management - `http://localhost:8000`
- [ ] MediStock Pro - `http://localhost:8000` (artisan serve)
- [ ] AbsorbLMSMgmt - Frontend: `http://localhost:3000`, API: `http://localhost:7071`
- [ ] YogaStudioMgmt - `http://localhost:5173`
- [ ] YogaStudio - `http://localhost:4321`
- [ ] Innovation Tracker - `http://localhost:3000`
- [ ] MeetingCostCalculator - Outlook sideload
