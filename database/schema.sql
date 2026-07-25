CREATE DATABASE IF NOT EXISTS lam_shaml CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE lam_shaml;

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS audit_logs;
DROP TABLE IF EXISTS possible_match_reports;
DROP TABLE IF EXISTS notifications;
DROP TABLE IF EXISTS match_records;
DROP TABLE IF EXISTS documents;
DROP TABLE IF EXISTS locations;
DROP TABLE IF EXISTS family_members;
DROP TABLE IF EXISTS reunification_requests;
DROP TABLE IF EXISTS accounts;
SET FOREIGN_KEY_CHECKS = 1;

CREATE TABLE accounts (
  account_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  full_name VARCHAR(160) NOT NULL,
  phone VARCHAR(30) NOT NULL,
  email VARCHAR(190) NOT NULL,
  password_hash VARCHAR(255) NOT NULL,
  city VARCHAR(120) NOT NULL,
  role ENUM('user','organization','admin') NOT NULL DEFAULT 'user',
  status ENUM('active','blocked','pending') NOT NULL DEFAULT 'active',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_accounts_email (email),
  UNIQUE KEY uq_accounts_phone (phone),
  KEY idx_accounts_role_status (role, status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE reunification_requests (
  request_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  account_id INT UNSIGNED NOT NULL,
  request_type ENUM('missing','found') NOT NULL,
  status ENUM('pending','active','approved','rejected','more_info','resolved') NOT NULL DEFAULT 'pending',
  priority ENUM('low','normal','high','urgent') NOT NULL DEFAULT 'normal',
  description TEXT NOT NULL,
  contact_phone VARCHAR(30) NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  KEY idx_requests_account (account_id),
  KEY idx_requests_type_status (request_type, status),
  KEY idx_requests_created (created_at),
  CONSTRAINT fk_requests_account FOREIGN KEY (account_id) REFERENCES accounts(account_id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE family_members (
  member_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  request_id BIGINT UNSIGNED NOT NULL,
  full_name VARCHAR(180) NOT NULL,
  normalized_name VARCHAR(180) NOT NULL,
  age TINYINT UNSIGNED NULL,
  gender ENUM('male','female','unknown') NOT NULL DEFAULT 'unknown',
  original_city VARCHAR(120) NULL,
  relationship VARCHAR(80) NULL,
  health_status VARCHAR(255) NULL,
  distinctive_marks TEXT NULL,
  registered_by VARCHAR(160) NULL,
  KEY idx_family_request (request_id),
  KEY idx_family_normalized (normalized_name),
  KEY idx_family_age_gender (age, gender),
  CONSTRAINT fk_family_request FOREIGN KEY (request_id) REFERENCES reunification_requests(request_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE locations (
  location_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  request_id BIGINT UNSIGNED NOT NULL,
  city VARCHAR(120) NOT NULL,
  area VARCHAR(120) NULL,
  last_known_place VARCHAR(255) NULL,
  current_location VARCHAR(255) NULL,
  last_seen_date DATE NULL,
  KEY idx_locations_request (request_id),
  KEY idx_locations_city_area (city, area),
  CONSTRAINT fk_locations_request FOREIGN KEY (request_id) REFERENCES reunification_requests(request_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE documents (
  document_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  request_id BIGINT UNSIGNED NOT NULL,
  file_type VARCHAR(60) NOT NULL,
  file_path VARCHAR(255) NOT NULL,
  uploaded_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY idx_documents_request (request_id),
  CONSTRAINT fk_documents_request FOREIGN KEY (request_id) REFERENCES reunification_requests(request_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE match_records (
  match_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  request_id BIGINT UNSIGNED NOT NULL,
  matched_request_id BIGINT UNSIGNED NOT NULL,
  name_score DECIMAL(5,2) NOT NULL DEFAULT 0,
  location_score DECIMAL(5,2) NOT NULL DEFAULT 0,
  age_score DECIMAL(5,2) NOT NULL DEFAULT 0,
  gender_score DECIMAL(5,2) NOT NULL DEFAULT 0,
  place_score DECIMAL(5,2) NOT NULL DEFAULT 0,
  total_score DECIMAL(5,2) NOT NULL DEFAULT 0,
  status ENUM('pending','approved','rejected','resolved') NOT NULL DEFAULT 'pending',
  admin_notes TEXT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_match_pair (request_id, matched_request_id),
  KEY idx_matches_status_score (status, total_score),
  CONSTRAINT fk_matches_request FOREIGN KEY (request_id) REFERENCES reunification_requests(request_id) ON DELETE CASCADE,
  CONSTRAINT fk_matches_matched FOREIGN KEY (matched_request_id) REFERENCES reunification_requests(request_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE notifications (
  notification_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  account_id INT UNSIGNED NOT NULL,
  message VARCHAR(255) NOT NULL,
  type VARCHAR(60) NOT NULL DEFAULT 'info',
  is_read TINYINT(1) NOT NULL DEFAULT 0,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY idx_notifications_account_read (account_id, is_read, created_at),
  CONSTRAINT fk_notifications_account FOREIGN KEY (account_id) REFERENCES accounts(account_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE possible_match_reports (
  report_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  account_id INT UNSIGNED NOT NULL,
  request_id BIGINT UNSIGNED NOT NULL,
  matched_request_id BIGINT UNSIGNED NOT NULL,
  notes TEXT NULL,
  contact_phone VARCHAR(30) NOT NULL,
  status ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY idx_reports_status (status),
  CONSTRAINT fk_reports_account FOREIGN KEY (account_id) REFERENCES accounts(account_id) ON DELETE CASCADE,
  CONSTRAINT fk_reports_request FOREIGN KEY (request_id) REFERENCES reunification_requests(request_id) ON DELETE CASCADE,
  CONSTRAINT fk_reports_matched FOREIGN KEY (matched_request_id) REFERENCES reunification_requests(request_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE audit_logs (
  log_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  account_id INT UNSIGNED NULL,
  action VARCHAR(120) NOT NULL,
  table_name VARCHAR(120) NOT NULL,
  record_id BIGINT UNSIGNED NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY idx_audit_account_created (account_id, created_at),
  CONSTRAINT fk_audit_account FOREIGN KEY (account_id) REFERENCES accounts(account_id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
