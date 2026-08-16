# Vakt - Health & Security Quality Assurance Monitor

Vakt is a centralized, premium Security Quality Assurance (SQA) and health monitoring platform designed to oversee the stability, security, and integrity of multiple web applications and servers.

## Core Features

- **Continuous Uptime & Health Monitoring:** Automatically monitor the uptime and health of client projects.
- **File Integrity Scanning:** Detect unauthorized file modifications, malware injections, and tampering on remote servers via lightweight, self-contained agent scripts.
- **Incident Management:** Full lifecycle incident tracking (Detection, Investigation, Containment, Resolution) with dedicated Kanban boards and client alerts.
- **Security Quality Assurance (SQA) Reports:** Generate professional, printable, automated monthly security and health reports for clients.
- **Vulnerability Tracking:** Keep track of outdated dependencies and unpatched vulnerabilities across the portfolio.
- **Client Dashboard:** A dedicated portal for clients to review reports, approve improvement proposals, and track incidents.
- **Agent Architecture:** Monitor remote servers securely without requiring SSH access. Vakt deploys a highly secure, cryptographically signed standalone PHP agent to remote servers that communicates back to the central hub.

## Technology Stack

- **Framework:** Laravel 13
- **Frontend Logic:** Livewire 4 + Alpine.js
- **Styling:** Vanilla CSS (Custom Design System with Dark/Light Mode support)
- **Database:** MySQL / SQLite
- **PHP:** 8.3+

## Installation & Setup

1. **Clone the repository:**
   ```bash
   git clone https://github.com/Kunnatta-ehf/health.kunnatta.is.git
   cd health.kunnatta.is
   ```

2. **Install Dependencies:**
   ```bash
   composer install
   npm install && npm run build
   ```

3. **Environment Setup:**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```
   *Configure your database settings in the `.env` file.*

4. **Database Migration & Seeding:**
   ```bash
   php artisan migrate --seed
   ```
   *Note: This will populate the system with the default operator accounts and sample data.*

5. **Run the Application:**
   ```bash
   php artisan serve
   ```

## Agent Deployment (Remote Monitoring)

To monitor a remote website (e.g., a standard PHP or WordPress site):
1. In the Vakt dashboard, create a new Project.
2. Click **Download Agent** on the project page.
3. Upload the generated `vakt-agent.php` to the public root of the target website.
4. Vakt will now communicate with this agent securely using project-specific cryptographic signatures to check file integrity and server health.

## License

Proprietary Software. All rights reserved &copy; Kunnatta ehf.
