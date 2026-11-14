CREATE DATABASE IF NOT EXISTS project_hub
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;
USE project_hub;

CREATE TABLE IF NOT EXISTS departments (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  name VARCHAR(120) NOT NULL,
  description VARCHAR(255) NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_departments_name (name)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS positions (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  department_id INT UNSIGNED NULL,
  name VARCHAR(120) NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_positions_department_name (department_id, name),
  CONSTRAINT fk_positions_department FOREIGN KEY (department_id)
    REFERENCES departments (id) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS employees (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  employee_code VARCHAR(30) NOT NULL,
  first_name VARCHAR(100) NOT NULL,
  last_name VARCHAR(100) NOT NULL,
  email VARCHAR(190) NULL,
  phone VARCHAR(30) NULL,
  department_id INT UNSIGNED NULL,
  position_id INT UNSIGNED NULL,
  hire_date DATE NULL,
  status ENUM('Active', 'Resigned', 'Suspended') NOT NULL DEFAULT 'Active',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_employees_code (employee_code),
  UNIQUE KEY uq_employees_email (email),
  KEY idx_employees_department (department_id),
  KEY idx_employees_position (position_id),
  CONSTRAINT fk_employees_department FOREIGN KEY (department_id)
    REFERENCES departments (id) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT fk_employees_position FOREIGN KEY (position_id)
    REFERENCES positions (id) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS users (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  employee_id INT UNSIGNED NULL,
  username VARCHAR(80) NOT NULL,
  password_hash VARCHAR(255) NOT NULL,
  role ENUM('admin', 'hr', 'manager', 'employee') NOT NULL DEFAULT 'employee',
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_users_username (username),
  UNIQUE KEY uq_users_employee (employee_id),
  CONSTRAINT fk_users_employee FOREIGN KEY (employee_id)
    REFERENCES employees (id) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS attendances (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  employee_id INT UNSIGNED NOT NULL,
  attendance_date DATE NOT NULL,
  check_in TIME NULL,
  check_out TIME NULL,
  status ENUM('Present', 'Late', 'Absent', 'On Leave') NOT NULL DEFAULT 'Present',
  note VARCHAR(255) NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_attendance_employee_date (employee_id, attendance_date),
  CONSTRAINT fk_attendance_employee FOREIGN KEY (employee_id)
    REFERENCES employees (id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS leave_requests (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  employee_id INT UNSIGNED NOT NULL,
  leave_type ENUM('Annual', 'Sick', 'Personal', 'Other') NOT NULL,
  start_date DATE NOT NULL,
  end_date DATE NOT NULL,
  reason TEXT NULL,
  status ENUM('Pending', 'Approved', 'Rejected') NOT NULL DEFAULT 'Pending',
  reviewed_by INT UNSIGNED NULL,
  reviewed_at DATETIME NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_leave_employee_status (employee_id, status),
  CONSTRAINT fk_leave_employee FOREIGN KEY (employee_id)
    REFERENCES employees (id) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_leave_reviewer FOREIGN KEY (reviewed_by)
    REFERENCES users (id) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB;

INSERT IGNORE INTO departments (id, name, description) VALUES
  (1, 'ทรัพยากรบุคคล (HR)', 'บริหารทรัพยากรบุคคล'),
  (2, 'เทคโนโลยีสารสนเทศ (IT)', 'พัฒนาและดูแลระบบสารสนเทศ'),
  (3, 'บัญชีและการเงิน', 'ดูแลบัญชีและการเงิน');

INSERT IGNORE INTO positions (id, department_id, name) VALUES
  (1, 1, 'HR Manager'),
  (2, 2, 'IT Director'),
  (3, 2, 'Senior Developer'),
  (4, 3, 'Senior Accountant');

INSERT IGNORE INTO employees
  (id, employee_code, first_name, last_name, email, department_id, position_id, hire_date, status)
VALUES
  (1, 'EMP-001', 'สมชาย', 'ใจดี', 'somchai@company.co.th', 1, 1, '2023-01-15', 'Active'),
  (2, 'EMP-002', 'สุดา', 'รักงาน', 'suda@company.co.th', 2, 2, '2022-05-10', 'Active'),
  (3, 'EMP-008', 'ปรีชา', 'เก่งกล้า', 'preecha@company.co.th', 2, 3, '2024-03-01', 'Active'),
  (4, 'EMP-014', 'กัญญา', 'วัฒนา', 'kanya@company.co.th', 3, 4, '2023-08-20', 'Active');

-- Demo account: username admin, password admin123. Change it after first login.
INSERT IGNORE INTO users (username, password_hash, role)
VALUES ('admin', '$2y$10$ujQoMUvcdjcYOs5uhdh0j.SgJikbBHo.5TLKWKyUz59YT7N.ydoHy', 'admin');