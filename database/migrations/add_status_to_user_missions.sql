ALTER TABLE user_missions
ADD COLUMN status ENUM('pending', 'approved', 'rejected') NOT NULL DEFAULT 'pending';