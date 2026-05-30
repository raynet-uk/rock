<div align="center">

![RAYNET UK](https://www.raynet-uk.net/technical/graphics/raynet-uk.gif)

<br>

# ROCK
### RAYNET Operational Control Kernel
#### The complete web platform for RAYNET UK groups

<br>

[![Laravel](https://img.shields.io/badge/Laravel-12-FF2D20?style=flat-square&logo=laravel&logoColor=white)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.2%2B-777BB4?style=flat-square&logo=php&logoColor=white)](https://php.net)
[![License](https://img.shields.io/badge/License-GPL--2.0-003366?style=flat-square)](https://www.gnu.org/licenses/gpl-2.0.html)
[![RAYNET UK](https://img.shields.io/badge/RAYNET-UK%20Affiliated-C8102E?style=flat-square)](https://www.raynet-uk.net)
[![Modules](https://img.shields.io/badge/Module%20Repo-rock--modules-003366?style=flat-square&logo=github)](https://github.com/raynet-uk/rock-modules)

<br>

> **Built by RAYNET Liverpool — for every RAYNET UK group**
>
> *A professional, fully-featured website platform designed specifically for the needs of volunteer emergency communications groups. Replaces ageing static sites and generic WordPress installs with something purpose-built for RAYNET.*

<br>

---

</div>

## ✨ Features

<table>
<tr>
<td width="50%">

**🖥️ Content Management**
- Divi-style visual page builder
- Source code editor with syntax highlighting
- Automatic backups before every save
- Page URL management with auto-route generation
- One-click module install & update system

**👥 Member Management**
- Registration with admin approval workflow
- Callsign verification and formatting
- Role-based access control
- Member availability self-reporting
- Digital profile with avatar upload

</td>
<td width="50%">

**📅 Operations**
- Event scheduling with RSVPs
- Operator assignment and briefing system
- Live alert status widget
- Operational map (APRS, Meshtastic, weather, flood)
- DMR network dashboard and last heard log

**🎓 Training & Admin**
- Full LMS with courses, quizzes, certificates
- SCORM content support
- Committee readiness matrix & LRF
- SSO / OAuth 2.0 for connected tools
- Super admin panel with session management

</td>
</tr>
</table>

---

## 🧩 Module Ecosystem

ROCK uses a codename-based module system. Every module has a friendly operational name and a system code.

### Included Modules

| Code | Full Name | Purpose |
|------|-----------|---------|
| **BEACON** | Broadcast & Emergency Announcement Control & Notification | News, notices & announcements |
| **VAULT** | Verified Archive & Unified Library Tool | Document library & file sharing |
| **ECHO** | Electronic Communications History & Operations | Radio net logging |
| **DEBRIEF** | Debrief, Review, Intelligence & Evaluation for Future Operational Performance | After action reports |

### Planned Modules

| Code | Purpose |
|------|---------|
| **COMPASS** | Member management |
| **SENTINEL** | Incident management |
| **DEPOT** | Asset management |
| **ACADEMY** | Training system |
| **WATCH** | Duty roster |
| **OPSROOM** | Operations board |
| **ATLAS** | Mapping |
| **SIGNAL** | Messaging |
| **READYSTATE** | Readiness dashboard |
| **TRACKER** | Vehicle tracking |
| **TEMPEST** | Weather intelligence |
| **CASCADE** | Call-out system |

> ROCK powers the platform. COMPASS manages the members. WATCH tracks availability. CASCADE alerts operators. SENTINEL manages incidents. ECHO records radio traffic. DEBRIEF captures lessons. VAULT stores documents. ATLAS provides mapping. TEMPEST delivers weather intelligence.

---

## 📋 Requirements

| Requirement | Version | Notes |
|-------------|---------|-------|
| PHP | 8.2 or higher | 8.2 or 8.3 recommended |
| MySQL / MariaDB | 5.7+ / 10.3+ | |
| Composer | Any recent version | |
| Web Server | Apache or Nginx | |
| SSL Certificate | Required | Free via Let's Encrypt |
| **Hosting** | **VPS recommended** | **cPanel shared hosting also supported** |

> ✅ **Works on cPanel shared hosting** — a VPS is recommended for best performance and control, but not required.

---

## 🚀 Installation

### One command install

```bash
git clone https://github.com/raynet-uk/rock.git . && bash install.sh
```

Run this from your domain's web root directory via SSH. That's it — the interactive installer handles everything else.

### Option A — Automated (recommended)

```bash
# 1. Navigate to your domain's web root
cd /home/yourusername/public_html/yourdomain.com

# 2. Clone and install
git clone https://github.com/raynet-uk/rock.git . && bash install.sh
```

The install script handles everything interactively:

| Step | What it does |
|------|-------------|
| ✅ Pre-flight checks | PHP version, required extensions, writable directories |
| ⚙️ Environment setup | Asks for DB credentials, site URL, mail settings — writes `.env` |
| 📦 Composer | Detects or downloads `composer.phar` automatically |
| 🔑 Key generation | Generates your `APP_KEY` |
| 🗄️ Migrations | Runs all database migrations |
| 🔗 Storage link | Creates the `public/storage` symlink |
| 🔒 Permissions | Sets correct permissions on storage and cache |
| 🗑️ Cache clear | Clears all application caches |
| ⏰ Cron reminder | Shows the exact cron line to paste into cPanel |

When the script finishes, visit your domain — the web-based installation wizard will guide you through the final group setup.

---

### Option B — Manual

**1. Get the files**

```bash
git clone https://github.com/raynet-uk/rock.git .
```

**2. Install dependencies**

```bash
composer install --no-dev --optimize-autoloader
# cPanel shared hosting:
php composer.phar install --no-dev --optimize-autoloader
```

**3. Configure your environment**

```bash
cp .env.example .env
```

Edit `.env` with your details:

```env
APP_URL=https://yourgroup.net
DB_DATABASE=your_database
DB_USERNAME=your_db_user
DB_PASSWORD=your_db_password
MAIL_HOST=mail.yourgroup.net
MAIL_FROM_ADDRESS=noreply@yourgroup.net
```

**4. Finalise setup**

```bash
php artisan key:generate
php artisan migrate --force
php artisan storage:link
```

**5. Run the installation wizard**

Visit your domain — you'll be redirected to `/install` where the wizard guides you through group setup and creates your first admin account.

**That's it. Your ROCK site is live. 📻**

---

## 🏠 Hosting

**A VPS is recommended** for full control and performance. **cPanel shared hosting also works** — here's what to know:

Point your domain to the `public/` folder via cPanel → Domains. If you can't change the document root:

```apache
RewriteEngine On
RewriteRule ^(.*)$ public/$1 [L]
```

Use `php composer.phar` if `composer` isn't in PATH. Run artisan commands via SSH — not from a browser.

---

## 🔄 Updating

ROCK has a built-in update system. From the admin panel:

```
Admin → CMS Update → Check for Updates → Apply Update
```

Or via SSH:

```bash
php artisan cms:update
```

---

## ⚙️ Configuration

All group-specific settings live in **Admin → Settings** — no code editing required:

- Group name, callsign, number and region
- Logo upload (PNG, JPG, SVG, WebP)
- Contact and support emails
- QRZ XML credentials
- Telegram notifications
- Donations page & footer badge
- Email header code injection

---

## 📄 Default Pages

| Page | Description |
|------|-------------|
| **Home** | Landing page with alert status and upcoming events |
| **About** | Group information and history |
| **Training** | Training information and course links |
| **Event Support** | Public support request form |
| **Calendar** | Public events calendar with ICS export |
| **Privacy Notice** | GDPR-compliant template |
| **Cookie Policy** | Cookie consent policy |

---

## 👨‍💻 Developers

ROCK is developed and maintained by **RAYNET Liverpool** (Group 10/ME/179).

<table>
<tr>
<td align="center" width="50%">
<br>
<strong>Ian</strong><br>
<code>G4BDS</code><br>
<em>Developer</em><br>
<br>
</td>
<td align="center" width="50%">
<br>
<strong>Nathan</strong><br>
<code>M7NDN</code><br>
<em>Developer</em><br>
<br>
</td>
</tr>
</table>

---

## 🔗 Links

| | |
|--|--|
| 🌐 **RAYNET UK** | [raynet-uk.net](https://www.raynet-uk.net) |
| 📻 **RAYNET Liverpool** | [raynet-liverpool.net](https://raynet-liverpool.net) |
| 🔌 **Module Repository** | [github.com/raynet-uk/rock-modules](https://github.com/raynet-uk/rock-modules) |
| 🐛 **Issues & Support** | [github.com/raynet-uk/rock/issues](https://github.com/raynet-uk/rock/issues) |

---

## 📜 Licence

ROCK is open source software released under the **[GNU General Public Licence v2.0](https://www.gnu.org/licenses/gpl-2.0.html)**.

You are free to use, modify and distribute this software for any RAYNET UK group. We ask that improvements are contributed back to the project where possible.

---

<div align="center">

<br>

**ROCK** · RAYNET Operational Control Kernel · Developed by RAYNET Liverpool · For every RAYNET UK group

*Robust. Resilient. Radio.*

*RAYNET — the Radio Amateurs' Emergency Network · Affiliated with the RSGB*

<br>

`73 de G4BDS & M7NDN 📻`

</div>
