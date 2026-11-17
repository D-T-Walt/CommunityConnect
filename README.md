# CommunityConnect

> *Connecting People. Strengthening Communities.*

CommunityConnect is a PHP/MySQL web application that lets community members register for volunteer activities (clean-ups, sports days, tutoring, donation drives and more), manage their registrations, and receive email and PDF confirmations. An administrator dashboard provides an overview of registrations and a downloadable PDF report.

## Table of Contents

- [Features](#features)
- [Prerequisites](#prerequisites)
- [Installation & Setup](#installation--setup)
  - [1. Install a local server stack](#1-install-a-local-server-stack)
  - [2. Place the project in the correct folder](#2-place-the-project-in-the-correct-folder)
  - [3. Start Apache and MySQL](#3-start-apache-and-mysql)
  - [4. Import the database in phpMyAdmin](#4-import-the-database-in-phpmyadmin)
  - [5. Check the database connection](#5-check-the-database-connection)
  - [6. Open the application](#6-open-the-application)
  - [7. (Optional) Configure email](#7-optional-configure-email)
- [Usage](#usage)
- [Project Structure](#project-structure)
- [Security Notes](#security-notes)
- [Authors](#authors)

---

## Features

- Registrant sign-up and login (passwords stored hashed with `password_hash` and verified with `password_verify`)
- Register for a community activity by parish and preferred participation date
- View, update and cancel/delete your registrations from the registrant portal
- Email confirmation on registration (PHPMailer via Gmail SMTP)
- Downloadable PDF registration confirmation (Dompdf)
- Password-protected administrator dashboard with a downloadable PDF report

## Prerequisites

- One of the following local server stacks (Apache + PHP + MySQL + phpMyAdmin):
  - **XAMPP** (Windows/ macOS/ Linux) – https://www.apachefriends.org
  - **WAMP** (Windows) – https://www.wampserver.com
  - **LAMP** (Linux) – install Apache, MySQL/MariaDB, PHP and phpMyAdmin through your package manager
- PHP 8.1 or newer is recommended (the SQL dump was generated on PHP 8.3/ MySQL 8.4)
- A modern web browser
- *(Optional, for email)* A Gmail account with an App Password

## Installation & Setup

### 1. Install a local server stack

Install XAMPP, WAMP or LAMP using the default options. Make sure Apache, MySQL and phpMyAdmin are included.

### 2. Place the project in the correct folder

Copy (or unzip) the **`CommunityConnect`** folder into your server's web root. The folder must keep the name `CommunityConnect` so the URL below works.

| Stack  | Web root folder                                              | Final project path                               |
|--------|--------------------------------------------------------------|--------------------------------------------------|
| XAMPP (Windows) | `C:\xampp\htdocs\`                                  | `C:\xampp\htdocs\CommunityConnect\`              |
| XAMPP (macOS)   | `/Applications/XAMPP/htdocs/`                       | `/Applications/XAMPP/htdocs/CommunityConnect/`   |
| XAMPP (Linux)   | `/opt/lampp/htdocs/`                                | `/opt/lampp/htdocs/CommunityConnect/`            |
| WAMP (Windows)  | `C:\wamp64\www\`                                    | `C:\wamp64\www\CommunityConnect\`                |
| LAMP (Linux)    | `/var/www/html/`                                    | `/var/www/html/CommunityConnect/`                |

> On LAMP you may need to grant permissions, e.g.
> `sudo chown -R www-data:www-data /var/www/html/CommunityConnect`

Make sure `index.php` sits directly inside `CommunityConnect/` (not in a nested `CommunityConnect/CommunityConnect/` folder, which can happen when unzipping).

### 3. Start Apache and MySQL

**XAMPP**
1. Open the **XAMPP Control Panel**.
2. Click **Start** next to **Apache**.
3. Click **Start** next to **MySQL**.
4. Both modules should turn green.

**WAMP**
1. Launch **WampServer** (Start menu → *WampServer64*).
2. Wait for the tray icon (bottom-right of the taskbar) to turn **green**. Orange means one service is down; red means both are.
3. If needed, left-click the icon → **Start All Services**.

**LAMP (Linux)**
```bash
sudo systemctl start apache2     # or: httpd
sudo systemctl start mysql       # or: mariadb
```
For XAMPP on Linux: `sudo /opt/lampp/lampp start`

### 4. Import the database in phpMyAdmin

1. In your browser, go to phpMyAdmin:

   ```
   http://localhost/phpmyadmin
   ```
   - **XAMPP:** username `root`, password blank (leave empty).
   - **WAMP:** username `root`, password blank (leave empty).
   - **LAMP:** use the MySQL credentials you configured during installation.
2. Click the **Import** tab in the top menu. *(Stay on the server home page, you do not need to create or select a database first.)*
3. Under *File to import*, click **Choose File** and select:

   ```
   CommunityConnect/community_connectdb.sql
   ```
4. Leave the format as **SQL** and the other defaults unchanged.
5. Scroll down and click **Import** (or **Go**).
6. You should see a green success message, and a new database named **`community_connectdb`** will appear in the left sidebar.

The script automatically creates the database and these tables:

| Table                | Purpose                                         |
|----------------------|-------------------------------------------------|
| `activities`         | List of activities (pre-filled with 6 entries)  |
| `registrants`        | User accounts (email, name, hashed password…)   |
| `registrants_skills` | Skills associated with each registrant          |
| `registrations`      | Activity registrations                          |

> Re-importing the file will **drop and recreate** the tables, erasing any existing data.

### 5. Check the database connection

The app connects using `connectDatabase.php`. The defaults match a fresh XAMPP/WAMP install:

```php
$host     = "localhost";
$username = "root";
$password = "";                    // empty by default
$database = "community_connectdb";
```

If your MySQL `root` user has a password (common on LAMP), or MySQL runs on a different port, update these values.

### 6. Open the application

With Apache and MySQL running, type this into your browser's address bar:

```
http://localhost/CommunityConnect/index.php
```

You should see the **CommunityConnect Log-In** page. Click **"New user? Click Here"** to create an account.

If Apache is using a non-default port (e.g. 8080), use `http://localhost:8080/CommunityConnect/index.php`.

### 7. (Optional) Configure email

Registration confirmation emails are sent through Gmail SMTP using PHPMailer. Without valid credentials the app still works, but emails won't be delivered.

1. Use a Gmail account and enable **2-Step Verification**.
2. Generate an **App Password** at https://myaccount.google.com/apppasswords
3. Edit `mailConfiguration.php`:

   ```php
   return [
       'host'       => 'smtp.gmail.com',
       'port'       => 587,
       'username'   => 'your.address@gmail.com',
       'password'   => 'your-16-character-app-password',
       'from_email' => 'your.address@gmail.com',
       'from_name'  => 'CommunityConnect',
   ];
   ```
## Usage

**Registrants**
1. Open `http://localhost/CommunityConnect/index.php`.
2. Create an account and register via **New user? Click Here**, then log in.
3. From the portal, register for more activities, choosing a parish and participation date.
4. View, update or delete your registrations, and download your PDF confirmation.

**Administrators**
1. Click the **Admin** link, or go to `http://localhost/CommunityConnect/admin.php`.
2. Enter the admin password (see [Security Notes](#security-notes)).
3. View the dashboard and download the **Administrative PDF Report**.

## Project Structure

```
CommunityConnect/
├── index.php                 # Registrant login page (entry point)
├── registration.php          # New-user sign-up
├── registration1.php         # Activity registration step
├── confirmation.php          # Registration confirmation
├── registrantPortal.php      # Registrant portal
├── registrantView.php        # View registrations
├── registrantUpdate.php      # Update a registration
├── registrantDelete.php      # Delete a registration
├── registrationPDF.php       # PDF confirmation (Dompdf)
├── admin.php                 # Administrator dashboard
├── adminReportPDF.php        # Admin PDF report
├── sendRegistrationEmail.php # Email sender (PHPMailer)
├── mailConfiguration.php     # SMTP settings
├── connectDatabase.php       # MySQLi connection settings
├── community_connectdb.sql   # Database schema + initial data
├── mainStyle.css             # Shared stylesheet
├── img/                      # Background images
├── composer.json / .lock     # PHP dependencies
└── vendor/                   # Installed dependencies (PHPMailer, Dompdf)
```

## Security Notes

This project is intended for coursework/ local development. Before any real deployment:

- The admin password is currently hard-coded in `admin.php`. Change it, or move it into configuration/environment variables.
- Do **not** commit real SMTP credentials. Replace the values in `mailConfiguration.php` with your own, and consider revoking any App Password that has been shared or pushed to a public repository.
- Set a strong MySQL `root` password and use a dedicated database user instead of `root` with a blank password.
- Serve the site over HTTPS.

## Authors

- Diwani Walters – 2303848
- Kevon Hedman – 2203313
- Olivia McFarlane – 2301555
- Shemael Deslandes – 2306823
