-- =====================================================================
-- SocietyOS — Seed Data
-- Default roles, permissions, demo society, and one super admin login.
-- CHANGE THE DEFAULT PASSWORD IMMEDIATELY AFTER FIRST LOGIN.
-- =====================================================================

INSERT INTO society (name, registration_no, address, city, state, pincode, phone, email)
VALUES ('Demo Residency', 'REG-0001', '123 Main Road', 'Pune', 'Maharashtra', '411001', '9999999999', 'admin@demoresidency.local');

SET @society_id = LAST_INSERT_ID();

INSERT INTO financial_years (society_id, label, start_date, end_date, is_current)
VALUES (@society_id, '2026-2027', '2026-04-01', '2027-03-31', 1);

-- Roles

INSERT INTO roles (name, description, is_system) VALUES
('super_admin', 'Full system access', 1),
('society_admin', 'Society configuration, members, billing, reports', 1),
('accountant', 'Bills, receipts, expenses, financial reports', 1),
('committee_member', 'Complaints, notices, resident info, reports', 1),
('security_guard', 'Visitor entry, vehicle entry, delivery register, staff attendance', 1),
('resident', 'View bills, pay maintenance, complaints, notices, documents, visitor approval', 1),
('tenant', 'Limited resident access', 1);

-- Permissions (module.action)

INSERT INTO permissions (`key`, module, description) VALUES
('dashboard.view', 'dashboard', 'View dashboard'),
('society.manage', 'society', 'Manage society profile and setup'),
('members.view', 'members', 'View residents'),
('members.manage', 'members', 'Create/update/delete residents'),
('flats.manage', 'flats', 'Manage wings/floors/flats'),
('vehicles.manage', 'vehicles', 'Manage vehicles and parking'),
('billing.view', 'billing', 'View maintenance bills'),
('billing.manage', 'billing', 'Generate bills, record payments'),
('accounting.view', 'accounting', 'View accounting records'),
('accounting.manage', 'accounting', 'Manage income/expenses/ledger'),
('visitors.manage', 'visitors', 'Log and approve visitors'),
('complaints.view', 'complaints', 'View complaints'),
('complaints.manage', 'complaints', 'Assign/resolve complaints'),
('notices.manage', 'notices', 'Publish notices/circulars/events/polls'),
('staff.manage', 'staff', 'Manage staff, attendance, payroll'),
('assets.manage', 'assets', 'Manage assets, AMC, service records'),
('reports.view', 'reports', 'View and export reports'),
('users.manage', 'users', 'Manage users, roles, permissions'),
('settings.manage', 'settings', 'Manage system settings');

-- Role -> Permission mapping

INSERT INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id FROM roles r, permissions p WHERE r.name = 'super_admin';

INSERT INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id FROM roles r, permissions p
WHERE r.name = 'society_admin'
AND p.`key` IN ('dashboard.view','society.manage','members.view','members.manage','flats.manage','vehicles.manage','billing.view','billing.manage','reports.view','users.manage','settings.manage','notices.manage','complaints.view');

INSERT INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id FROM roles r, permissions p
WHERE r.name = 'accountant'
AND p.`key` IN ('dashboard.view','billing.view','billing.manage','accounting.view','accounting.manage','reports.view');

INSERT INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id FROM roles r, permissions p
WHERE r.name = 'committee_member'
AND p.`key` IN ('dashboard.view','complaints.view','complaints.manage','notices.manage','members.view','reports.view');

INSERT INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id FROM roles r, permissions p
WHERE r.name = 'security_guard'
AND p.`key` IN ('dashboard.view','visitors.manage','vehicles.manage','staff.manage');

INSERT INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id FROM roles r, permissions p
WHERE r.name = 'resident'
AND p.`key` IN ('dashboard.view','billing.view','complaints.view','notices.manage','visitors.manage');

INSERT INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id FROM roles r, permissions p
WHERE r.name = 'tenant'
AND p.`key` IN ('dashboard.view','billing.view','complaints.view');

-- Default super admin user
-- Email: admin@demoresidency.local | Password: ChangeMe@123 (bcrypt hash below)

INSERT INTO users (society_id, role_id, name, email, phone, password_hash, status, must_change_password)
SELECT @society_id, r.id, 'Super Admin', 'admin@demoresidency.local', '9999999999',
       '$2y$10$QzktOAmpbkoSzJc0sc8LMe6Www4qvFdPmGqac8mK7DQoCHqn5xgpy', 'active', 1
FROM roles r WHERE r.name = 'super_admin';

-- Demo structure: 1 wing, 3 floors, 2 flats per floor

INSERT INTO wings (society_id, name) VALUES (@society_id, 'A');
SET @wing_id = LAST_INSERT_ID();

INSERT INTO floors (wing_id, floor_number) VALUES (@wing_id, 1), (@wing_id, 2), (@wing_id, 3);

-- flat_number is bare (no wing prefix) — UI composes "wing-flat" for display, so the
-- wing letter must not be baked in here or listings show it twice (e.g. "A-A-101").
INSERT INTO flats (floor_id, flat_number, flat_type, carpet_area_sqft, occupancy_status)
SELECT f.id, CONCAT(f.floor_number, '01'), '2BHK', 850.00, 'vacant' FROM floors f WHERE f.wing_id = @wing_id
UNION ALL
SELECT f.id, CONCAT(f.floor_number, '02'), '2BHK', 850.00, 'vacant' FROM floors f WHERE f.wing_id = @wing_id;

-- Default maintenance heads
-- Amount now lives in maintenance_head_rates (effective-dated); each head gets an initial
-- rate effective from the financial year start so it's immediately current.

INSERT INTO maintenance_heads (society_id, name, calculation_type) VALUES (@society_id, 'General Maintenance', 'fixed');
SET @head_general = LAST_INSERT_ID();
INSERT INTO maintenance_heads (society_id, name, calculation_type) VALUES (@society_id, 'Sinking Fund', 'fixed');
SET @head_sinking = LAST_INSERT_ID();
INSERT INTO maintenance_heads (society_id, name, calculation_type) VALUES (@society_id, 'Water Charges', 'fixed');
SET @head_water = LAST_INSERT_ID();

INSERT INTO maintenance_head_rates (maintenance_head_id, amount, effective_from) VALUES
(@head_general, 2000.00, '2026-04-01'),
(@head_sinking, 500.00, '2026-04-01'),
(@head_water, 300.00, '2026-04-01');

-- Default complaint categories

INSERT INTO complaint_categories (society_id, name) VALUES
(@society_id, 'Plumbing'), (@society_id, 'Electrical'), (@society_id, 'Housekeeping'), (@society_id, 'Security'), (@society_id, 'Other');

-- Default asset categories

INSERT INTO asset_categories (society_id, name) VALUES
(@society_id, 'Lift'), (@society_id, 'Generator'), (@society_id, 'Fire Safety'), (@society_id, 'Water Pump'), (@society_id, 'CCTV');

-- Default cash account

INSERT INTO accounts (society_id, name, account_type, opening_balance) VALUES (@society_id, 'Cash in Hand', 'cash', 0.00);

-- Default settings

INSERT INTO settings (society_id, `key`, `value`) VALUES
(@society_id, 'currency_symbol', 'INR'),
(@society_id, 'maintenance_due_day', '10'),
(@society_id, 'penalty_interest_rate_percent', '18');
