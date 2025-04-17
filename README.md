# Web-attacks-FYP-2025
An educational web platform for learning cybersecurity through interactive levels on SQL injection, XSS, and more. Download for local use; modifications are permitted locally but not to the original repository.

# Cybersecurity Educational Web Platform

## Overview
This project is an educational web platform designed to teach cybersecurity concepts through hands-on, interactive levels. It simulates common web vulnerabilities, including SQL injection, cross-site scripting (XSS), brute-force, and dictionary attacks. Key features include:
- Seven interactive levels with deliberate vulnerabilities.
- Integration with third-party tools like Kali Linux, Hydra, and SQLmap.
- A custom admin tool for logging user interactions, visualizing data, and exporting logs as CSV.
- SQLite/MariaDB database and PHP-based backend.
- Educational explanation pages to enhance learning.

The platform is intended for students, educators, and cybersecurity enthusiasts to download and use locally. You may modify the code locally for personal use, but contributions or modifications to this repository are not accepted to preserve the original project.

This project was created for a final year project and is not fully polished, do not enter this project with an expectation everything described runs well. It all depends on the configuration process.

## Prerequisites
To run this project locally, ensure you have:
- **PHP** (version 7.4 or higher) for the backend.
- **SQLite** or **MariaDB** for the database.
- **Web server** (e.g., Apache, Nginx) for local hosting.
- **Kali Linux** (optional, for levels using Hydra or SQLmap).
- A modern web browser (e.g., Chrome, Firefox).
- Made use of PHPstorm in this project (Recommended to run latest version of PHPstorm - optional)

## Installation
1. **Download the Repository**:
   - Clone or download the ZIP file:
     ```bash
     git clone https://github.com/Aujree/Web-attacks-FYP.git
     
2. Set Up the Web Server:

Configure your web server (e.g., Apache) to point to the project directory.
Ensure PHP is enabled.

3. Database Configuration:
Import the provided database.sql-tables file into SQLite
Update config.php with your database credentials.

4. Network Configuration (for Kali Linux tools):
Set your network to Bridged Adapter in Kali Linux.
Assign an unused IP address to avoid conflicts (see documentation for details).

3. Run the Project:
Access via http://localhost/your-repo-name in your browser.
