USE project_hub;

INSERT IGNORE INTO departments (id, name, description) VALUES
  (1, 'ทรัพยากรบุคคล (HR)', 'บริหารทรัพยากรบุคคล'),
  (2, 'เทคโนโลยีสารสนเทศ (IT)', 'พัฒนาและดูแลระบบสารสนเทศ'),
  (3, 'บัญชีและการเงิน', 'ดูแลบัญชีและการเงิน'),
  (4, 'ฝ่ายขาย', 'ดูแลลูกค้าและยอดขาย');

INSERT IGNORE INTO positions (id, department_id, name) VALUES
  (1, 1, 'HR Manager'),
  (2, 2, 'IT Director'),
  (3, 2, 'Senior Developer'),
  (4, 3, 'Senior Accountant'),
  (5, 4, 'Sales Executive'),
  (6, 4, 'Sales Manager');

INSERT IGNORE INTO employees
  (id, employee_code, first_name, last_name, email, phone, department_id, position_id, hire_date, status)
VALUES
  (1, 'EMP-001', 'สมชาย', 'ใจดี', 'somchai@company.co.th', '081-111-1111', 1, 1, '2023-01-15', 'Active'),
  (2, 'EMP-002', 'สุดา', 'รักงาน', 'suda@company.co.th', '082-222-2222', 2, 2, '2022-05-10', 'Active'),
  (3, 'EMP-008', 'ปรีชา', 'เก่งกล้า', 'preecha@company.co.th', '083-333-3333', 2, 3, '2024-03-01', 'Active'),
  (4, 'EMP-014', 'กัญญา', 'วัฒนา', 'kanya@company.co.th', '084-444-4444', 3, 4, '2023-08-20', 'Active'),
  (5, 'EMP-021', 'ธีรพงษ์', 'อุดม', 'teerapong@company.co.th', '085-555-5555', 4, 5, '2024-04-05', 'Active'),
  (6, 'EMP-027', 'นภัสสร', 'ชูชาติ', 'napatsorn@company.co.th', '086-666-6666', 4, 6, '2023-11-12', 'Active');

INSERT IGNORE INTO users (id, employee_id, username, password_hash, role, is_active) VALUES
  (1, NULL, 'admin', '$2y$10$ujQoMUvcdjcYOs5uhdh0j.SgJikbBHo.5TLKWKyUz59YT7N.ydoHy', 'admin', 1),
  (2, 1, 'somchai', '$2y$10$0h6i8v1JW9s3A1hS2Y0pSO8f0qyU3myQkDLrQCMo3XBde6VgV8hnu', 'employee', 1),
  (3, 2, 'suda', '$2y$10$LzMZ7N8vKZb5jzX8wksnW.4R8uV02pE8gX1mS7f7/1qJd7P7YQ2W', 'manager', 1),
  (4, 3, 'preecha', '$2y$10$B0N3xQyy6m1v4kP5gVb8heVx4zdWEs7M8H4sBd6d8hD2l0b5Q9Qm2', 'employee', 1),
  (5, 4, 'kanya', '$2y$10$5a4HqD0kr6iU4.KO6mPlR2ibP36dOQkMpxJTk6vZB7gJ2Pr06Mp3K', 'employee', 1),
  (6, 5, 'teerapong', '$2y$10$Y6qY0Y3rQjN8T2m4Pzq8ZudQp0M8A9O0M4ql2V5vNfOWT4XnB1gju', 'employee', 1),
  (7, 6, 'napatsorn', '$2y$10$4j9m3NXPQ3KQXo7mN7dAh.8g7r6Ud4eqJbNHZ7jP6vT7MZJ9eq3Mq', 'employee', 1);

INSERT IGNORE INTO attendances (id, employee_id, attendance_date, check_in, check_out, status, note) VALUES
  (1, 1, '2026-11-10', '08:30:00', '17:30:00', 'Present', 'เข้างานตามเวลา'),
  (2, 2, '2026-11-10', '08:45:00', '17:15:00', 'Late', 'มาสาย 15 นาที'),
  (3, 3, '2026-11-10', '08:15:00', '17:05:00', 'Present', 'ทำงานเต็มวัน'),
  (4, 4, '2026-11-10', '08:20:00', '17:40:00', 'Present', 'ทำงานปกติ'),
  (5, 5, '2026-11-10', '09:00:00', '18:00:00', 'Late', 'มาสาย 30 นาที');

INSERT IGNORE INTO leave_requests (id, employee_id, leave_type, start_date, end_date, reason, status) VALUES
  (1, 5, 'Annual', '2026-11-15', '2026-11-16', 'ไปพักผ่อนครอบครัว', 'Pending'),
  (2, 3, 'Sick', '2026-11-12', '2026-11-13', 'ป่วยต้องพักฟื้น', 'Approved'),
  (3, 4, 'Personal', '2026-11-20', '2026-11-21', 'เรื่องส่วนตัว', 'Rejected');

SELECT 'Demo data inserted successfully.' AS status;
