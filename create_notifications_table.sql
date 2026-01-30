-- Create vtiger_notifications table for Modern Notification System
CREATE TABLE IF NOT EXISTS `vtiger_notifications` (
  `id` int(19) NOT NULL AUTO_INCREMENT,
  `userid` int(19) NOT NULL,
  `module` varchar(100) NOT NULL,
  `recordid` int(19) NOT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_userid` (`userid`),
  KEY `idx_is_read` (`is_read`),
  KEY `idx_created_at` (`created_at`),
  KEY `idx_userid_is_read` (`userid`, `is_read`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


