-- Create database if not exists
CREATE DATABASE IF NOT EXISTS crm_dashboard DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Select the database
USE crm_dashboard;

-- Drop tables in correct order (respecting foreign key constraints)
DROP TABLE IF EXISTS lead_tags;
DROP TABLE IF EXISTS lead_notes;
DROP TABLE IF EXISTS lead_activities;
DROP TABLE IF EXISTS role_permissions;
DROP TABLE IF EXISTS user_roles_map;
DROP TABLE IF EXISTS user_permissions;
DROP TABLE IF EXISTS user_roles;
DROP TABLE IF EXISTS calendar_events;
DROP TABLE IF EXISTS reminders;
DROP TABLE IF EXISTS tasks;
DROP TABLE IF EXISTS notes;
DROP TABLE IF EXISTS notifications;
DROP TABLE IF EXISTS leads;
DROP TABLE IF EXISTS lead_sources;
DROP TABLE IF EXISTS lead_status_types;
DROP TABLE IF EXISTS sticky_notes;
DROP TABLE IF EXISTS user_sessions;
DROP TABLE IF EXISTS login_history;
DROP TABLE IF EXISTS tags;
DROP TABLE IF EXISTS settings;
DROP TABLE IF EXISTS users;

-- Drop views
DROP VIEW IF EXISTS lead_statistics;
DROP VIEW IF EXISTS user_performance;
DROP VIEW IF EXISTS vw_todays_leads_summary;
DROP VIEW IF EXISTS vw_todays_leads_by_source;

-- Create users table first (since other tables reference it)
CREATE TABLE IF NOT EXISTS users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50),
    email VARCHAR(100) NOT NULL UNIQUE,
    phone VARCHAR(20),
    password VARCHAR(255) NOT NULL,
    timezone VARCHAR(100) DEFAULT '(GMT+05:30) Kolkata',
    company_name VARCHAR(100),
    gst_number VARCHAR(50),
    package ENUM('basic', 'silver', 'gold', 'platinum', 'diamond', 'diamond_pro') DEFAULT 'basic',
    status ENUM('trial', 'active', 'expired', 'suspended') DEFAULT 'trial',
    trial_end_date DATETIME,
    profile_image VARCHAR(255) DEFAULT NULL,
    profile_image_updated_at TIMESTAMP NULL DEFAULT NULL,
    last_login DATETIME,
    login_attempts INT DEFAULT 0,
    is_locked BOOLEAN DEFAULT FALSE,
    lock_until DATETIME,
    reset_token VARCHAR(100),
    reset_token_expiry DATETIME,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_status (status),
    INDEX idx_trial_end (trial_end_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create sticky_notes table if it doesn't exist
CREATE TABLE IF NOT EXISTS `sticky_notes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `content` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `sticky_notes_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- User sessions for tracking active sessions
CREATE TABLE IF NOT EXISTS user_sessions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    session_token VARCHAR(255) NOT NULL,
    ip_address VARCHAR(45),
    user_agent TEXT,
    last_activity DATETIME DEFAULT CURRENT_TIMESTAMP,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_session_token (session_token),
    INDEX idx_user_id (user_id)
) ENGINE=InnoDB;

-- Login history for security tracking
CREATE TABLE IF NOT EXISTS login_history (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    login_time DATETIME DEFAULT CURRENT_TIMESTAMP,
    ip_address VARCHAR(45),
    user_agent TEXT,
    status ENUM('success', 'failed') NOT NULL,
    failure_reason VARCHAR(255),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_login (user_id, login_time)
) ENGINE=InnoDB;

-- Drop existing tables if they exist
DROP TABLE IF EXISTS `leads`;
DROP TABLE IF EXISTS `lead_sources`;
DROP TABLE IF EXISTS `lead_status_types`;

-- Create lead_status_types table
CREATE TABLE `lead_status_types` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `color_code` varchar(7) NOT NULL,
  `display_order` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert default status types
INSERT INTO `lead_status_types` (`name`, `color_code`, `display_order`) VALUES
('New', '#0d6efd', 1),
('Processing', '#6610f2', 2),
('Close-by', '#ffc107', 3),
('Confirm', '#198754', 4),
('Cancel', '#dc3545', 5);

-- Create lead_sources table
CREATE TABLE `lead_sources` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `color_code` varchar(7) NOT NULL,
  `display_order` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert default sources
INSERT INTO `lead_sources` (`name`, `color_code`, `display_order`) VALUES
('Online', '#0d6efd', 1),
('Offline', '#6c757d', 2),
('Website', '#198754', 3),
('Whatsapp', '#ffc107', 4),
('Customer Reminder', '#6610f2', 5),
('Indiamart', '#0dcaf0', 6),
('Facebook', '#20c997', 7),
('Google Form', '#fd7e14', 8);

-- Create leads table
CREATE TABLE `leads` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `phone` varchar(20) NOT NULL,
  `company` varchar(100) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `source_id` int(11) NOT NULL,
  `status_id` int(11) NOT NULL,
  `assigned_to` int(11) NOT NULL,
  `notes` text DEFAULT NULL,
  `reference` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `submission_id` VARCHAR(50) UNIQUE,
  PRIMARY KEY (`id`),
  KEY `source_id` (`source_id`),
  KEY `status_id` (`status_id`),
  KEY `assigned_to` (`assigned_to`),
  CONSTRAINT `leads_source_fk` FOREIGN KEY (`source_id`) REFERENCES `lead_sources` (`id`),
  CONSTRAINT `leads_status_fk` FOREIGN KEY (`status_id`) REFERENCES `lead_status_types` (`id`),
  CONSTRAINT `leads_user_fk` FOREIGN KEY (`assigned_to`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Update existing leads with a default submission_id
UPDATE leads SET submission_id = CONCAT('legacy_', id) WHERE submission_id IS NULL;

-- Create view for today's leads summary
CREATE OR REPLACE VIEW `vw_todays_leads_summary` AS
SELECT 
    lst.name as status_name,
    lst.color_code as status_color,
    COUNT(l.id) as lead_count,
    ROUND((COUNT(l.id) * 100.0) / NULLIF((SELECT COUNT(*) FROM leads WHERE DATE(created_at) = CURDATE()), 0), 1) as percentage
FROM lead_status_types lst
LEFT JOIN leads l ON lst.id = l.status_id AND DATE(l.created_at) = CURDATE()
GROUP BY lst.id, lst.name, lst.color_code
ORDER BY lst.display_order;

-- Create view for today's leads by source
CREATE OR REPLACE VIEW `vw_todays_leads_by_source` AS
SELECT 
    ls.name as source_name,
    ls.color_code as source_color,
    COUNT(l.id) as lead_count,
    ROUND((COUNT(l.id) * 100.0) / NULLIF((SELECT COUNT(*) FROM leads WHERE DATE(created_at) = CURDATE()), 0), 1) as percentage
FROM lead_sources ls
LEFT JOIN leads l ON ls.id = l.source_id AND DATE(l.created_at) = CURDATE()
GROUP BY ls.id, ls.name, ls.color_code
ORDER BY ls.display_order;

-- Lead notes table (existing structure is fine for comments)
CREATE TABLE IF NOT EXISTS lead_notes (
    id INT PRIMARY KEY AUTO_INCREMENT,
    lead_id INT NOT NULL,
    user_id INT NOT NULL,
    note TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (lead_id) REFERENCES leads(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id),
    INDEX idx_lead (lead_id),
    INDEX idx_user (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Lead activities table
CREATE TABLE IF NOT EXISTS lead_activities (
    id INT PRIMARY KEY AUTO_INCREMENT,
    lead_id INT NOT NULL,
    user_id INT NOT NULL,
    activity_type ENUM('call', 'email', 'meeting', 'note', 'status_change', 'other') NOT NULL,
    description TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (lead_id) REFERENCES leads(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id),
    INDEX idx_lead (lead_id),
    INDEX idx_user (user_id),
    INDEX idx_activity_type (activity_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Lead tags table
CREATE TABLE IF NOT EXISTS tags (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(50) NOT NULL UNIQUE,
    color VARCHAR(7) NOT NULL DEFAULT '#6c757d',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Lead-tag relationships
CREATE TABLE IF NOT EXISTS lead_tags (
    lead_id INT NOT NULL,
    tag_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (lead_id, tag_id),
    FOREIGN KEY (lead_id) REFERENCES leads(id) ON DELETE CASCADE,
    FOREIGN KEY (tag_id) REFERENCES tags(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Settings table
CREATE TABLE IF NOT EXISTS settings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    setting_key VARCHAR(50) NOT NULL UNIQUE,
    setting_value TEXT,
    setting_group VARCHAR(50) NOT NULL DEFAULT 'general',
    is_public BOOLEAN NOT NULL DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_key (setting_key),
    INDEX idx_group (setting_group)
) ENGINE=InnoDB;

-- Create lead statistics view
CREATE OR REPLACE VIEW lead_statistics AS
SELECT
    lst.name as status,
    COUNT(*) as total_leads,
    COUNT(CASE WHEN l.created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY) THEN 1 END) as new_leads,
    COUNT(CASE WHEN lst.name = 'New' THEN 1 END) as new_status_count,
    COUNT(CASE WHEN lst.name = 'Processing' THEN 1 END) as processing_count,
    COUNT(CASE WHEN lst.name = 'Close-by' THEN 1 END) as close_by_count,
    COUNT(CASE WHEN lst.name = 'Confirm' THEN 1 END) as confirm_count,
    COUNT(CASE WHEN lst.name = 'Cancel' THEN 1 END) as cancel_count,
    COUNT(CASE WHEN l.assigned_to IS NOT NULL THEN 1 END) as assigned_leads,
    COUNT(CASE WHEN l.assigned_to IS NULL THEN 1 END) as unassigned_leads
FROM leads l
JOIN lead_status_types lst ON l.status_id = lst.id
GROUP BY lst.name;

-- Create user performance view
CREATE OR REPLACE VIEW user_performance AS
SELECT
    u.id as user_id,
    u.first_name as user_name,
    u.email as user_email,
    u.package as user_package,
    COUNT(DISTINCT l.id) as total_leads,
    COUNT(DISTINCT CASE WHEN lst.name = 'Confirm' THEN l.id END) as confirmed_leads,
    COUNT(DISTINCT CASE WHEN lst.name = 'Cancel' THEN l.id END) as cancelled_leads,
    COUNT(DISTINCT CASE WHEN lst.name = 'New' THEN l.id END) as new_leads,
    COUNT(DISTINCT CASE WHEN lst.name = 'Processing' THEN l.id END) as processing_leads,
    COUNT(DISTINCT CASE WHEN lst.name = 'Close-by' THEN l.id END) as close_by_leads,
    MAX(l.created_at) as last_lead_created
FROM users u
LEFT JOIN leads l ON u.id = l.assigned_to
LEFT JOIN lead_status_types lst ON l.status_id = lst.id
GROUP BY u.id, u.first_name, u.email, u.package;

-- Additional tables for expanded functionality

-- Tasks table
CREATE TABLE IF NOT EXISTS tasks (
    id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    due_date DATETIME,
    priority ENUM('low', 'medium', 'high') DEFAULT 'medium',
    status ENUM('pending', 'in_progress', 'completed', 'cancelled') DEFAULT 'pending',
    related_to ENUM('lead', 'general') DEFAULT 'general',
    lead_id INT NULL,
    assigned_to INT NULL,
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (lead_id) REFERENCES leads(id) ON DELETE CASCADE,
    FOREIGN KEY (assigned_to) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_status (status),
    INDEX idx_due_date (due_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Reminders table
CREATE TABLE IF NOT EXISTS reminders (
    id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    reminder_date DATETIME NOT NULL,
    is_recurring BOOLEAN DEFAULT FALSE,
    recurrence_pattern VARCHAR(50) NULL, -- daily, weekly, monthly, etc.
    related_to ENUM('lead', 'task', 'general') DEFAULT 'general',
    lead_id INT NULL,
    task_id INT NULL,
    user_id INT NOT NULL,
    is_completed BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (lead_id) REFERENCES leads(id) ON DELETE CASCADE,
    FOREIGN KEY (task_id) REFERENCES tasks(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_reminder_date (reminder_date),
    INDEX idx_user_id (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Notes table (for general notes, separate from lead notes)
CREATE TABLE IF NOT EXISTS notes (
    id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,
    color VARCHAR(7) DEFAULT '#ffffff',
    is_pinned BOOLEAN DEFAULT FALSE,
    user_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Calendar events table
CREATE TABLE IF NOT EXISTS calendar_events (
    id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    start_date DATETIME NOT NULL,
    end_date DATETIME NOT NULL,
    all_day BOOLEAN DEFAULT FALSE,
    location VARCHAR(255),
    color VARCHAR(7) DEFAULT '#4f46e5',
    related_to ENUM('lead', 'task', 'general') DEFAULT 'general',
    lead_id INT NULL,
    task_id INT NULL,
    user_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (lead_id) REFERENCES leads(id) ON DELETE CASCADE,
    FOREIGN KEY (task_id) REFERENCES tasks(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_dates (start_date, end_date),
    INDEX idx_user_id (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Notifications table
CREATE TABLE IF NOT EXISTS notifications (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    type ENUM('info', 'success', 'warning', 'danger') DEFAULT 'info',
    is_read BOOLEAN DEFAULT FALSE,
    related_to ENUM('lead', 'task', 'reminder', 'system') DEFAULT 'system',
    related_id INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_read (user_id, is_read)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- User roles table
CREATE TABLE IF NOT EXISTS user_roles (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(50) NOT NULL UNIQUE,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- User permissions table
CREATE TABLE IF NOT EXISTS user_permissions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL UNIQUE,
    description TEXT,
    module VARCHAR(50) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Role-permission relationship table
CREATE TABLE IF NOT EXISTS role_permissions (
    role_id INT NOT NULL,
    permission_id INT NOT NULL,
    PRIMARY KEY (role_id, permission_id),
    FOREIGN KEY (role_id) REFERENCES user_roles(id) ON DELETE CASCADE,
    FOREIGN KEY (permission_id) REFERENCES user_permissions(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- User-role relationship table
CREATE TABLE IF NOT EXISTS user_roles_map (
    user_id INT NOT NULL,
    role_id INT NOT NULL,
    PRIMARY KEY (user_id, role_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (role_id) REFERENCES user_roles(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert default roles
INSERT IGNORE INTO user_roles (name, description) VALUES
('Administrator', 'Full system access'),
('Manager', 'Can manage leads, tasks, and users'),
('Staff', 'Can manage assigned leads and tasks'),
('Viewer', 'Can only view data, no edit permissions');

-- Insert default permissions
INSERT IGNORE INTO user_permissions (name, description, module) VALUES
('view_leads', 'Can view leads', 'leads'),
('add_leads', 'Can add new leads', 'leads'),
('edit_leads', 'Can edit leads', 'leads'),
('delete_leads', 'Can delete leads', 'leads'),
('assign_leads', 'Can assign leads to users', 'leads'),
('view_tasks', 'Can view tasks', 'tasks'),
('add_tasks', 'Can add new tasks', 'tasks'),
('edit_tasks', 'Can edit tasks', 'tasks'),
('delete_tasks', 'Can delete tasks', 'tasks'),
('view_reports', 'Can view reports', 'reports'),
('manage_users', 'Can manage users', 'users'),
('manage_settings', 'Can manage system settings', 'settings');

-- Assign permissions to roles
-- Administrator role (all permissions)
INSERT IGNORE INTO role_permissions (role_id, permission_id)
SELECT 1, id FROM user_permissions;

-- Manager role permissions
INSERT IGNORE INTO role_permissions (role_id, permission_id)
SELECT 2, id FROM user_permissions WHERE name NOT IN ('manage_settings');

-- Staff role permissions
INSERT IGNORE INTO role_permissions (role_id, permission_id)
SELECT 3, id FROM user_permissions WHERE name IN ('view_leads', 'add_leads', 'edit_leads', 'view_tasks', 'add_tasks', 'edit_tasks', 'view_reports');

-- Viewer role permissions
INSERT IGNORE INTO role_permissions (role_id, permission_id)
SELECT 4, id FROM user_permissions WHERE name IN ('view_leads', 'view_tasks', 'view_reports');