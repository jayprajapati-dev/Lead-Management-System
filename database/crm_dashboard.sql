-- Create database if not exists
CREATE DATABASE IF NOT EXISTS crm_dashboard DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE crm_dashboard;

-- Users table with trial system
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
) ENGINE=InnoDB;

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

-- Lead sources table (defined before leads, as it's referenced by leads)
CREATE TABLE IF NOT EXISTS lead_sources (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(50) NOT NULL UNIQUE,
    description TEXT,
    status ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_status (status)
) ENGINE=InnoDB;

-- Lead statuses table (defined before leads, as its values are used in leads ENUM)
CREATE TABLE IF NOT EXISTS lead_statuses (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(50) NOT NULL UNIQUE,
    color VARCHAR(7) NOT NULL DEFAULT '#6c757d',
    description TEXT,
    is_system BOOLEAN NOT NULL DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Leads table
CREATE TABLE leads (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    phone VARCHAR(20),
    country_code VARCHAR(10) NULL,
    company VARCHAR(100),
    lead_date DATE NULL,
    reference VARCHAR(255) NULL,
    address TEXT NULL,
    comment TEXT NULL,
    status ENUM('new', 'processing', 'close-by', 'confirm', 'cancel', 'contacted', 'qualified', 'closed', 'lost', 'proposal', 'negotiation', 'converted') NOT NULL DEFAULT 'new',
    source_id INT NULL, -- Foreign key column
    assigned_to INT NULL, -- Foreign key column (made NULLable to match modal)
    created_by INT NOT NULL, -- Foreign key column
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (source_id) REFERENCES lead_sources(id) ON DELETE SET NULL, -- Added ON DELETE rule
    FOREIGN KEY (assigned_to) REFERENCES users(id) ON DELETE SET NULL, -- Added ON DELETE rule
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE -- Added ON DELETE rule
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

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

-- Insert or ignore default lead statuses (combining original and modal statuses)
INSERT IGNORE INTO lead_statuses (name, color, description, is_system) VALUES
('New', '#007bff', 'New leads that need initial contact', TRUE),
('Processing', '#17a2b8', 'Leads currently being processed', FALSE),
('Close-by', '#ffc107', 'Leads close to conversion', FALSE),
('Confirm', '#28a745', 'Confirmed leads', FALSE), -- Using success color
('Cancel', '#dc3545', 'Cancelled leads', FALSE), -- Using danger color
('Contacted', '#17a2b8', 'Leads that have been contacted', TRUE),
('Qualified', '#28a745', 'Leads that have been qualified', TRUE),
('Proposal', '#ffc107', 'Leads that have received a proposal', TRUE),
('Negotiation', '#fd7e14', 'Leads in negotiation phase', TRUE),
('Converted', '#20c997', 'Successfully converted leads', TRUE),
('Lost', '#dc3545', 'Lost or unqualified leads', TRUE);

-- Insert or ignore default lead sources (combining original and modal sources)
INSERT IGNORE INTO lead_sources (name, description) VALUES
('Online', 'Leads from online channels'),
('Offline', 'Leads from offline activities'),
('Website', 'Leads from company website'),
('Whatsapp', 'Leads from Whatsapp'),
('Customer Reminder', 'Leads from customer reminders'),
('Indiamart', 'Leads from Indiamart'),
('Facebook', 'Leads from Facebook'),
('Google Form', 'Leads from Google Forms'),
('Referral', 'Leads from customer referrals'),
('Social Media', 'Leads from social media platforms'),
('Email Campaign', 'Leads from email marketing campaigns'),
('Trade Show', 'Leads from trade shows and events'),
('Cold Call', 'Leads from cold calling'),
('Other', 'Leads from other sources');

-- Insert default tags (existing)
INSERT IGNORE INTO tags (name, color) VALUES
('Hot Lead', '#dc3545'),
('VIP', '#ffc107'),
('Follow Up', '#17a2b8'),
('Potential', '#28a745'),
('Long Term', '#6c757d');

-- Insert some default settings (existing)
INSERT IGNORE INTO settings (setting_key, setting_value, setting_group, is_public) VALUES
('company_name', 'CRM Dashboard', 'general', TRUE),
('company_email', 'contact@example.com', 'general', TRUE),
('company_phone', '+1 234 567 8900', 'general', TRUE),
('company_address', '123 Business St, City, Country', 'general', TRUE),
('default_lead_status', '1', 'leads', FALSE),
('default_lead_source', '1', 'leads', FALSE),
('items_per_page', '10', 'system', FALSE),
('date_format', 'Y-m-d', 'system', FALSE),
('time_format', 'H:i:s', 'system', FALSE);

-- Create lead statistics view (existing)
CREATE OR REPLACE VIEW lead_statistics AS
SELECT
    l.status,
    COUNT(*) as total_leads,
    COUNT(CASE WHEN l.created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY) THEN 1 END) as new_leads,
    COUNT(CASE WHEN l.status = 'new' THEN 1 END) as new_status_count,
    COUNT(CASE WHEN l.status = 'contacted' THEN 1 END) as contacted_count,
    COUNT(CASE WHEN l.status = 'qualified' THEN 1 END) as qualified_count,
    COUNT(CASE WHEN l.status = 'closed' THEN 1 END) as closed_count,
    COUNT(CASE WHEN l.status = 'lost' THEN 1 END) as lost_count,
    COUNT(CASE WHEN l.assigned_to IS NOT NULL THEN 1 END) as assigned_leads,
    COUNT(CASE WHEN l.assigned_to IS NULL THEN 1 END) as unassigned_leads
FROM leads l
GROUP BY l.status;

-- Create user performance view (existing)
CREATE OR REPLACE VIEW user_performance AS
SELECT
    u.id as user_id,
    u.first_name as user_name,
    u.email as user_email,
    u.package as user_package,
    COUNT(DISTINCT l.id) as total_leads,
    COUNT(DISTINCT CASE WHEN l.status = 'closed' THEN l.id END) as closed_leads,
    COUNT(DISTINCT CASE WHEN l.status = 'lost' THEN l.id END) as lost_leads,
    COUNT(DISTINCT CASE WHEN l.status = 'new' THEN l.id END) as new_leads,
    COUNT(DISTINCT CASE WHEN l.status = 'contacted' THEN l.id END) as contacted_leads,
    COUNT(DISTINCT CASE WHEN l.status = 'qualified' THEN l.id END) as qualified_leads,
    MAX(l.created_at) as last_lead_created
FROM users u
LEFT JOIN leads l ON u.id = l.assigned_to
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