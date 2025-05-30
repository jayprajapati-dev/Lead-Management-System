# Lead Management System

<p align="center">
  A comprehensive <strong>Customer Relationship Management (CRM)</strong> solution designed to streamline lead tracking, follow-ups and conversions for businesses of all sizes.
</p>

<p align="center">
  <img src="https://img.shields.io/badge/PHP-8.0+-777BB4?style=flat-square&logo=php&logoColor=white" />
  <img src="https://img.shields.io/badge/MySQL-8.0+-4479A1?style=flat-square&logo=mysql&logoColor=white" />
  <img src="https://img.shields.io/badge/Bootstrap-5.3-7952B3?style=flat-square&logo=bootstrap&logoColor=white" />
  <img src="https://img.shields.io/badge/jQuery-3.6-0769AD?style=flat-square&logo=jquery&logoColor=white" />
  <img src="https://img.shields.io/badge/License-MIT-green.svg?style=flat-square" />
</p>

## 📋 Overview

The **Lead Management System** is a powerful web application built for sales teams, marketing agencies and businesses to efficiently manage their customer acquisition pipeline. With a focus on usability and performance, this CRM helps you convert more leads into customers through organized tracking and timely follow-ups.

## 🚀 Key Features

- **Lead Management**
  - Capture and organize leads from multiple sources
  - Track lead status through customizable sales pipelines
  - Assign leads to team members with role-based permissions
  - Add notes, attachments and follow-up activities

- **User Management**
  - Secure authentication with password strength enforcement
  - Profile customization with image uploads
  - Role-based access control (Admin, Manager, Staff, Viewer)
  - Session tracking and security logs

- **Dashboard & Analytics**
  - Real-time overview of sales pipeline
  - Performance metrics and conversion rates
  - Visual charts for lead sources, statuses and team performance
  - Customizable date ranges for reporting
- **Task & Reminder Management**
  - Create and assign tasks with priorities and due dates
  - Set up recurring reminders for follow-ups
  - Calendar integration for scheduling
  - Email and notification alerts

- **Notes & Communication**
  - Centralized note-taking system
  - Internal communication tools
  - Email templates for consistent messaging
  - Activity logging for all interactions

## Technology Stack

| Component | Technology |
|-----------|------------|
| Frontend | HTML5, CSS3, JavaScript, Bootstrap 5, jQuery |
| Backend | PHP 8.0+ |
| Database | MySQL 8.0+ |
| Server | Apache |
| Development Environment | XAMPP / WAMP / MAMP |

## Requirements

- PHP 8.0 or higher
- MySQL 8.0 or higher
- Apache web server
- mod_rewrite enabled
- GD Library (for image processing)

## Installation

1. **Clone the repository**
   ```bash
   git clone https://github.com/yourusername/Lead-Management-System.git
   ```

2. **Set up your local environment**
   - Install XAMPP, WAMP, or MAMP
   - Start Apache and MySQL services

3. **Database setup**
   - Create a new MySQL database named `crm_dashboard`
   - Import the SQL file from `database/crm_dashboard.sql`

4. **Configuration**
   - Navigate to `includes/config.php`
   - Update database credentials and site settings

5. **File permissions**
   - Ensure the `uploads` directory is writable
   ```bash
   chmod 755 uploads/
   chmod 755 uploads/profile_images/
   ```

6. **Access the application**
   - Open your browser and navigate to `http://localhost/Lead-Management-System/public/`
   - Default login: admin@example.com / password123 (change immediately)

## Project Structure

```bash
Lead-Management-System/
├── dashboard/         # Dashboard pages and functionality
├── database/          # Database schema and migrations
├── includes/          # Reusable components and configuration
├── public/            # Public-facing pages and assets
├── uploads/           # User-uploaded files
│   └── profile_images/# User profile images
├── css/               # Stylesheets
└── logs/              # System logs
```

## Security Features

- Password hashing using bcrypt
- Protection against SQL injection
- CSRF token verification
- Session management and timeout
- Input validation and sanitization
- Login attempt limiting

## Roadmap

- [ ] API integration for third-party services
- [ ] Advanced reporting and export options
- [ ] Email marketing campaign integration
- [ ] Mobile application development
- [ ] AI-powered lead scoring

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add some amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## Contact

For support or inquiries, please contact us at support@leadmanagementsystem.com
