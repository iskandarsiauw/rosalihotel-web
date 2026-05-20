-- Analytics: visitor tracking schema
-- Idempotent migration — safe to run multiple times.

CREATE TABLE IF NOT EXISTS visitor_logs (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  page VARCHAR(100) NOT NULL,
  ip_address VARCHAR(45) NOT NULL,
  country VARCHAR(100) NULL,
  city VARCHAR(100) NULL,
  device_type VARCHAR(20) NULL,
  browser VARCHAR(50) NULL,
  os VARCHAR(50) NULL,
  referrer VARCHAR(500) NULL,
  visit_date DATE NOT NULL,
  visit_time TIME NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_visit_date_page (visit_date, page),
  INDEX idx_ip_address (ip_address)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS visitor_daily_summary (
  id INT AUTO_INCREMENT PRIMARY KEY,
  page VARCHAR(100) NOT NULL,
  visit_date DATE NOT NULL,
  total_visits INT DEFAULT 0,
  unique_visits INT DEFAULT 0,
  UNIQUE KEY unique_page_date (page, visit_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS visitor_ip_cache (
  ip_address VARCHAR(45) PRIMARY KEY,
  country VARCHAR(100) NULL,
  city VARCHAR(100) NULL,
  cached_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
