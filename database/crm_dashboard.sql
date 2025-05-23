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

-- Leads table
CREATE TABLE leads (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    phone VARCHAR(20),
    company VARCHAR(100),
    status ENUM('new', 'contacted', 'qualified', 'closed', 'lost') NOT NULL DEFAULT 'new',
    assigned_to INT,
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (assigned_to) REFERENCES users(id),
    FOREIGN KEY (created_by) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Lead sources table
CREATE TABLE IF NOT EXISTS lead_sources (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(50) NOT NULL UNIQUE,
    description TEXT,
    status ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_status (status)
) ENGINE=InnoDB;

-- Lead statuses table
CREATE TABLE IF NOT EXISTS lead_statuses (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(50) NOT NULL UNIQUE,
    color VARCHAR(7) NOT NULL DEFAULT '#6c757d',
    description TEXT,
    is_system BOOLEAN NOT NULL DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Lead notes table
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

-- Insert default lead statuses
INSERT INTO lead_statuses (name, color, description, is_system) VALUES
('New', '#007bff', 'New leads that need initial contact', TRUE),
('Contacted', '#17a2b8', 'Leads that have been contacted', TRUE),
('Qualified', '#28a745', 'Leads that have been qualified', TRUE),
('Proposal', '#ffc107', 'Leads that have received a proposal', TRUE),
('Negotiation', '#fd7e14', 'Leads in negotiation phase', TRUE),
('Converted', '#20c997', 'Successfully converted leads', TRUE),
('Lost', '#dc3545', 'Lost or unqualified leads', TRUE);

-- Insert default lead sources
INSERT INTO lead_sources (name, description) VALUES
('Website', 'Leads from company website'),
('Referral', 'Leads from customer referrals'),
('Social Media', 'Leads from social media platforms'),
('Email Campaign', 'Leads from email marketing campaigns'),
('Trade Show', 'Leads from trade shows and events'),
('Cold Call', 'Leads from cold calling'),
('Other', 'Leads from other sources');

-- Insert default tags
INSERT INTO tags (name, color) VALUES
('Hot Lead', '#dc3545'),
('VIP', '#ffc107'),
('Follow Up', '#17a2b8'),
('Potential', '#28a745'),
('Long Term', '#6c757d');

-- Insert some default settings
INSERT INTO settings (setting_key, setting_value, setting_group, is_public) VALUES
('company_name', 'CRM Dashboard', 'general', TRUE),
('company_email', 'contact@example.com', 'general', TRUE),
('company_phone', '+1 234 567 8900', 'general', TRUE),
('company_address', '123 Business St, City, Country', 'general', TRUE),
('default_lead_status', '1', 'leads', FALSE),
('default_lead_source', '1', 'leads', FALSE),
('items_per_page', '10', 'system', FALSE),
('date_format', 'Y-m-d', 'system', FALSE),
('time_format', 'H:i:s', 'system', FALSE);

-- Create lead statistics view
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

-- Create user performance view
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