-- Sticky Notes table for Lead Management System

-- Create the sticky_notes table if it doesn't exist
CREATE TABLE IF NOT EXISTS sticky_notes (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    content TEXT NOT NULL,
    color VARCHAR(7) DEFAULT '#ebf8ff', -- Default light blue color
    is_pinned BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Add sample data (optional, can be removed for production)
INSERT INTO sticky_notes (user_id, content, created_at) VALUES
(1, 'Welcome to the Lead Management System! This is a sample sticky note.', NOW()),
(1, 'Remember to follow up with new leads within 24 hours.', NOW()),
(1, 'Team meeting scheduled for Monday at 10 AM.', NOW());
