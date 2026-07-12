-- =====================================================================
-- SocietyOS — Database Schema
-- Single-society installation. One DB per society. MySQL 8.x / InnoDB / utf8mb4.
-- Last verified against the live database: 2026-07-12 (55 tables, column-by-column,
-- including all in-place ALTER TABLE migrations documented in docs/DECISIONS.md) — no drift.
-- =====================================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- =====================================================================
-- 1. SOCIETY & ADMINISTRATION
-- =====================================================================

CREATE TABLE IF NOT EXISTS society (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    registration_no VARCHAR(100) NULL,
    address VARCHAR(255) NULL,
    city VARCHAR(100) NULL,
    state VARCHAR(100) NULL,
    pincode VARCHAR(20) NULL,
    phone VARCHAR(20) NULL,
    email VARCHAR(150) NULL,
    logo_path VARCHAR(255) NULL,
    gstin VARCHAR(20) NULL,
    pan VARCHAR(20) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS financial_years (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    society_id INT UNSIGNED NOT NULL,
    label VARCHAR(20) NOT NULL,              -- e.g. 2026-2027
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    is_current TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (society_id) REFERENCES society(id) ON DELETE CASCADE,
    UNIQUE KEY uq_fy_label (society_id, label)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS bank_details (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    society_id INT UNSIGNED NOT NULL,
    bank_name VARCHAR(150) NOT NULL,
    account_name VARCHAR(150) NOT NULL,
    account_number VARCHAR(50) NOT NULL,
    ifsc_code VARCHAR(20) NULL,
    branch VARCHAR(150) NULL,
    is_primary TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (society_id) REFERENCES society(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS settings (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    society_id INT UNSIGNED NOT NULL,
    `key` VARCHAR(100) NOT NULL,
    `value` TEXT NULL,
    FOREIGN KEY (society_id) REFERENCES society(id) ON DELETE CASCADE,
    UNIQUE KEY uq_setting_key (society_id, `key`)
) ENGINE=InnoDB;

-- Roles / Permissions (RBAC)

CREATE TABLE IF NOT EXISTS roles (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE,        -- super_admin, society_admin, accountant, committee_member, security_guard, resident, tenant
    description VARCHAR(255) NULL,
    is_system TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS permissions (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `key` VARCHAR(100) NOT NULL UNIQUE,      -- e.g. billing.view, complaints.manage
    module VARCHAR(50) NOT NULL,
    description VARCHAR(255) NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS role_permissions (
    role_id INT UNSIGNED NOT NULL,
    permission_id INT UNSIGNED NOT NULL,
    PRIMARY KEY (role_id, permission_id),
    FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE,
    FOREIGN KEY (permission_id) REFERENCES permissions(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    society_id INT UNSIGNED NOT NULL,
    role_id INT UNSIGNED NOT NULL,
    member_id INT UNSIGNED NULL,             -- linked resident/member, nullable for staff-only roles
    name VARCHAR(150) NOT NULL,
    email VARCHAR(150) NOT NULL,
    phone VARCHAR(20) NULL,
    password_hash VARCHAR(255) NOT NULL,
    status ENUM('active','inactive','locked') NOT NULL DEFAULT 'active',
    must_change_password TINYINT(1) NOT NULL DEFAULT 0,
    last_login_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (society_id) REFERENCES society(id) ON DELETE CASCADE,
    FOREIGN KEY (role_id) REFERENCES roles(id),
    UNIQUE KEY uq_user_email (society_id, email)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS password_resets (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    token_hash VARCHAR(255) NOT NULL,
    expires_at DATETIME NOT NULL,
    used_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS user_sessions (
    id VARCHAR(128) PRIMARY KEY,             -- session id
    user_id INT UNSIGNED NOT NULL,
    ip_address VARCHAR(45) NULL,
    user_agent VARCHAR(255) NULL,
    last_activity TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS login_history (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NULL,
    email_attempted VARCHAR(150) NULL,
    ip_address VARCHAR(45) NULL,
    user_agent VARCHAR(255) NULL,
    status ENUM('success','failed') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS activity_logs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    society_id INT UNSIGNED NOT NULL,
    user_id INT UNSIGNED NULL,
    module VARCHAR(50) NOT NULL,
    action VARCHAR(100) NOT NULL,
    description VARCHAR(255) NULL,
    ip_address VARCHAR(45) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (society_id) REFERENCES society(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_activity_module (module),
    INDEX idx_activity_created (created_at)
) ENGINE=InnoDB;

-- =====================================================================
-- 2. SOCIETY STRUCTURE (Wings / Floors / Flats / Parking)
-- =====================================================================

CREATE TABLE IF NOT EXISTS wings (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    society_id INT UNSIGNED NOT NULL,
    name VARCHAR(50) NOT NULL,               -- A, B, Tower-1
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (society_id) REFERENCES society(id) ON DELETE CASCADE,
    UNIQUE KEY uq_wing_name (society_id, name)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS floors (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    wing_id INT UNSIGNED NOT NULL,
    floor_number INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (wing_id) REFERENCES wings(id) ON DELETE CASCADE,
    UNIQUE KEY uq_floor (wing_id, floor_number)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS flats (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    floor_id INT UNSIGNED NOT NULL,
    flat_number VARCHAR(20) NOT NULL,
    flat_type VARCHAR(20) NULL,              -- 1BHK, 2BHK...
    carpet_area_sqft DECIMAL(10,2) NULL,
    occupancy_status ENUM('owner_occupied','tenant_occupied','vacant') NOT NULL DEFAULT 'vacant',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (floor_id) REFERENCES floors(id) ON DELETE CASCADE,
    UNIQUE KEY uq_flat (floor_id, flat_number)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS flat_ownership_history (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    flat_id INT UNSIGNED NOT NULL,
    owner_name VARCHAR(150) NOT NULL,
    from_date DATE NOT NULL,
    to_date DATE NULL,
    transfer_reason VARCHAR(150) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (flat_id) REFERENCES flats(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS parking_slots (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    society_id INT UNSIGNED NOT NULL,
    slot_number VARCHAR(20) NOT NULL,
    slot_type ENUM('two_wheeler','four_wheeler') NOT NULL DEFAULT 'four_wheeler',
    is_allocated TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (society_id) REFERENCES society(id) ON DELETE CASCADE,
    UNIQUE KEY uq_slot (society_id, slot_number)
) ENGINE=InnoDB;

-- Effective-dated parking fee per slot type, same pattern as maintenance_head_rates:
-- append-only once effective, billing looks up the rate for the bill period's start date.
-- No rate configured for a type = that type isn't charged (parking stays free until priced).
CREATE TABLE IF NOT EXISTS parking_rates (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    society_id INT UNSIGNED NOT NULL,
    slot_type ENUM('two_wheeler','four_wheeler') NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    effective_from DATE NOT NULL,
    created_by INT UNSIGNED NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (society_id) REFERENCES society(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    UNIQUE KEY uq_parking_rate_effective (society_id, slot_type, effective_from),
    INDEX idx_parking_rate_lookup (society_id, slot_type, effective_from)
) ENGINE=InnoDB;

-- =====================================================================
-- 3. RESIDENTS / MEMBERS
-- =====================================================================

CREATE TABLE IF NOT EXISTS members (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    society_id INT UNSIGNED NOT NULL,
    flat_id INT UNSIGNED NOT NULL,
    member_type ENUM('owner','tenant') NOT NULL DEFAULT 'owner',
    name VARCHAR(150) NOT NULL,
    email VARCHAR(150) NULL,
    phone VARCHAR(20) NOT NULL,
    alternate_phone VARCHAR(20) NULL,
    move_in_date DATE NULL,
    move_out_date DATE NULL,
    status ENUM('active','inactive') NOT NULL DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (society_id) REFERENCES society(id) ON DELETE CASCADE,
    FOREIGN KEY (flat_id) REFERENCES flats(id) ON DELETE CASCADE,
    INDEX idx_member_flat (flat_id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS family_members (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    member_id INT UNSIGNED NOT NULL,
    name VARCHAR(150) NOT NULL,
    relation VARCHAR(50) NULL,
    date_of_birth DATE NULL,                 -- preferred: age is computed live from this, never stored stale
    age INT NULL,                            -- manual fallback, only used when date_of_birth is not known
    phone VARCHAR(20) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (member_id) REFERENCES members(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS tenants (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    flat_id INT UNSIGNED NOT NULL,
    member_id INT UNSIGNED NOT NULL,          -- the tenant's own member record
    owner_member_id INT UNSIGNED NOT NULL,    -- the flat owner's member record
    lease_start DATE NULL,
    lease_end DATE NULL,
    agreement_doc_path VARCHAR(255) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (flat_id) REFERENCES flats(id) ON DELETE CASCADE,
    FOREIGN KEY (member_id) REFERENCES members(id) ON DELETE CASCADE,
    FOREIGN KEY (owner_member_id) REFERENCES members(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS emergency_contacts (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    member_id INT UNSIGNED NOT NULL,
    name VARCHAR(150) NOT NULL,
    relation VARCHAR(50) NULL,
    phone VARCHAR(20) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (member_id) REFERENCES members(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS documents (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    society_id INT UNSIGNED NOT NULL,
    member_id INT UNSIGNED NULL,
    title VARCHAR(150) NOT NULL,
    file_path VARCHAR(255) NOT NULL,
    file_type VARCHAR(50) NULL,
    uploaded_by INT UNSIGNED NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (society_id) REFERENCES society(id) ON DELETE CASCADE,
    FOREIGN KEY (member_id) REFERENCES members(id) ON DELETE CASCADE,
    FOREIGN KEY (uploaded_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- =====================================================================
-- 4. VEHICLES / PARKING ALLOCATION
-- =====================================================================

CREATE TABLE IF NOT EXISTS vehicles (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    member_id INT UNSIGNED NOT NULL,
    vehicle_type ENUM('two_wheeler','four_wheeler') NOT NULL,
    registration_number VARCHAR(20) NOT NULL,
    make VARCHAR(50) NULL,
    model VARCHAR(50) NULL,
    color VARCHAR(30) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (member_id) REFERENCES members(id) ON DELETE CASCADE,
    UNIQUE KEY uq_vehicle_reg (registration_number)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS parking_allocations (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    parking_slot_id INT UNSIGNED NOT NULL,
    vehicle_id INT UNSIGNED NULL,
    flat_id INT UNSIGNED NOT NULL,
    allocated_from DATE NOT NULL,
    allocated_to DATE NULL,
    -- Per-allocation, not per-slot: the same physical slot can be a paid allocation for one
    -- occupant and a free/courtesy one for the next. 1 = billed at the slot type's rate
    -- (subject to a rate actually being configured), 0 = never billed regardless of rate.
    is_chargeable TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (parking_slot_id) REFERENCES parking_slots(id) ON DELETE CASCADE,
    FOREIGN KEY (vehicle_id) REFERENCES vehicles(id) ON DELETE SET NULL,
    FOREIGN KEY (flat_id) REFERENCES flats(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- =====================================================================
-- 5. MAINTENANCE BILLING
-- =====================================================================

CREATE TABLE IF NOT EXISTS maintenance_heads (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    society_id INT UNSIGNED NOT NULL,
    name VARCHAR(100) NOT NULL,              -- Sinking Fund, Water Charges...
    calculation_type ENUM('fixed','per_sqft') NOT NULL DEFAULT 'fixed',
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (society_id) REFERENCES society(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Effective-dated rate history for a maintenance head. A head's "current" amount is the
-- row with the latest effective_from <= the date in question (today for display, the bill
-- period's start date for bill generation). Rows are append-only once effective — see
-- MaintenanceHeadRate::deleteIfFuture() — so past/current rates are never silently rewritten;
-- corrections are made by adding a new dated rate, matching how societies actually announce
-- rate changes (a resolution effective from a future date).
CREATE TABLE IF NOT EXISTS maintenance_head_rates (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    maintenance_head_id INT UNSIGNED NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    effective_from DATE NOT NULL,
    created_by INT UNSIGNED NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (maintenance_head_id) REFERENCES maintenance_heads(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    UNIQUE KEY uq_head_effective (maintenance_head_id, effective_from),
    INDEX idx_head_effective (maintenance_head_id, effective_from)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS maintenance_bills (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    society_id INT UNSIGNED NOT NULL,
    flat_id INT UNSIGNED NOT NULL,
    financial_year_id INT UNSIGNED NOT NULL,
    bill_number VARCHAR(30) NOT NULL,
    bill_period_start DATE NOT NULL,
    bill_period_end DATE NOT NULL,
    due_date DATE NOT NULL,
    total_amount DECIMAL(10,2) NOT NULL DEFAULT 0,
    paid_amount DECIMAL(10,2) NOT NULL DEFAULT 0,
    status ENUM('unpaid','partially_paid','paid','overdue') NOT NULL DEFAULT 'unpaid',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (society_id) REFERENCES society(id) ON DELETE CASCADE,
    FOREIGN KEY (flat_id) REFERENCES flats(id) ON DELETE CASCADE,
    FOREIGN KEY (financial_year_id) REFERENCES financial_years(id),
    UNIQUE KEY uq_bill_number (society_id, bill_number),
    INDEX idx_bill_flat_status (flat_id, status)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS bill_items (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    maintenance_bill_id INT UNSIGNED NOT NULL,
    maintenance_head_id INT UNSIGNED NULL,   -- exactly one of these two is set — see CHECK below
    parking_slot_id INT UNSIGNED NULL,
    description VARCHAR(150) NULL,
    amount DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (maintenance_bill_id) REFERENCES maintenance_bills(id) ON DELETE CASCADE,
    FOREIGN KEY (maintenance_head_id) REFERENCES maintenance_heads(id),
    FOREIGN KEY (parking_slot_id) REFERENCES parking_slots(id),
    CONSTRAINT chk_bill_item_source CHECK (
        (maintenance_head_id IS NOT NULL AND parking_slot_id IS NULL) OR
        (maintenance_head_id IS NULL AND parking_slot_id IS NOT NULL)
    )
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS penalties (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    maintenance_bill_id INT UNSIGNED NOT NULL,
    interest_rate_percent DECIMAL(5,2) NOT NULL,
    days_overdue INT NOT NULL DEFAULT 0,
    penalty_amount DECIMAL(10,2) NOT NULL DEFAULT 0,
    calculated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (maintenance_bill_id) REFERENCES maintenance_bills(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS payments (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    maintenance_bill_id INT UNSIGNED NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    payment_mode ENUM('cash','cheque','upi','bank_transfer','card') NOT NULL,
    reference_number VARCHAR(100) NULL,
    paid_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    received_by INT UNSIGNED NULL,
    FOREIGN KEY (maintenance_bill_id) REFERENCES maintenance_bills(id) ON DELETE CASCADE,
    FOREIGN KEY (received_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS receipts (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    payment_id INT UNSIGNED NOT NULL,
    receipt_number VARCHAR(30) NOT NULL,
    pdf_path VARCHAR(255) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (payment_id) REFERENCES payments(id) ON DELETE CASCADE,
    UNIQUE KEY uq_receipt_number (receipt_number)
) ENGINE=InnoDB;

-- =====================================================================
-- 6. ACCOUNTING
-- =====================================================================

CREATE TABLE IF NOT EXISTS accounts (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    society_id INT UNSIGNED NOT NULL,
    name VARCHAR(100) NOT NULL,              -- Cash, Bank - HDFC, etc.
    account_type ENUM('cash','bank') NOT NULL DEFAULT 'cash',
    opening_balance DECIMAL(12,2) NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (society_id) REFERENCES society(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS vendors (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    society_id INT UNSIGNED NOT NULL,
    name VARCHAR(150) NOT NULL,
    contact_person VARCHAR(150) NULL,
    phone VARCHAR(20) NULL,
    email VARCHAR(150) NULL,
    category VARCHAR(50) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (society_id) REFERENCES society(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS income (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    society_id INT UNSIGNED NOT NULL,
    account_id INT UNSIGNED NOT NULL,
    category VARCHAR(100) NOT NULL,
    amount DECIMAL(12,2) NOT NULL,
    description VARCHAR(255) NULL,
    income_date DATE NOT NULL,
    created_by INT UNSIGNED NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (society_id) REFERENCES society(id) ON DELETE CASCADE,
    FOREIGN KEY (account_id) REFERENCES accounts(id),
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS expenses (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    society_id INT UNSIGNED NOT NULL,
    account_id INT UNSIGNED NOT NULL,
    vendor_id INT UNSIGNED NULL,
    category VARCHAR(100) NOT NULL,
    amount DECIMAL(12,2) NOT NULL,
    description VARCHAR(255) NULL,
    expense_date DATE NOT NULL,
    bill_document_path VARCHAR(255) NULL,
    created_by INT UNSIGNED NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (society_id) REFERENCES society(id) ON DELETE CASCADE,
    FOREIGN KEY (account_id) REFERENCES accounts(id),
    FOREIGN KEY (vendor_id) REFERENCES vendors(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS ledger_entries (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    society_id INT UNSIGNED NOT NULL,
    account_id INT UNSIGNED NOT NULL,
    entry_type ENUM('debit','credit') NOT NULL,
    amount DECIMAL(12,2) NOT NULL,
    reference_type ENUM('income','expense','payment') NOT NULL,
    reference_id BIGINT UNSIGNED NOT NULL,
    entry_date DATE NOT NULL,
    narration VARCHAR(255) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (society_id) REFERENCES society(id) ON DELETE CASCADE,
    FOREIGN KEY (account_id) REFERENCES accounts(id),
    INDEX idx_ledger_account_date (account_id, entry_date)
) ENGINE=InnoDB;

-- =====================================================================
-- 7. VISITORS / SECURITY
-- =====================================================================

CREATE TABLE IF NOT EXISTS visitors (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    society_id INT UNSIGNED NOT NULL,
    flat_id INT UNSIGNED NOT NULL,
    name VARCHAR(150) NOT NULL,
    phone VARCHAR(20) NULL,
    purpose VARCHAR(150) NULL,
    photo_path VARCHAR(255) NULL,
    check_in_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    check_out_at TIMESTAMP NULL,
    approval_status ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending',
    approved_by_member_id INT UNSIGNED NULL,
    logged_by INT UNSIGNED NULL,             -- security guard user
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (society_id) REFERENCES society(id) ON DELETE CASCADE,
    FOREIGN KEY (flat_id) REFERENCES flats(id) ON DELETE CASCADE,
    FOREIGN KEY (approved_by_member_id) REFERENCES members(id) ON DELETE SET NULL,
    FOREIGN KEY (logged_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_visitor_flat (flat_id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS visitor_passes (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    flat_id INT UNSIGNED NOT NULL,
    visitor_name VARCHAR(150) NOT NULL,
    qr_token VARCHAR(100) NOT NULL,
    valid_from DATETIME NOT NULL,
    valid_until DATETIME NOT NULL,
    used_at TIMESTAMP NULL,
    created_by INT UNSIGNED NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (flat_id) REFERENCES flats(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    UNIQUE KEY uq_pass_token (qr_token)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS deliveries (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    society_id INT UNSIGNED NOT NULL,
    flat_id INT UNSIGNED NOT NULL,
    courier_company VARCHAR(100) NULL,
    recipient_name VARCHAR(150) NULL,
    received_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    collected_at TIMESTAMP NULL,
    logged_by INT UNSIGNED NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (society_id) REFERENCES society(id) ON DELETE CASCADE,
    FOREIGN KEY (flat_id) REFERENCES flats(id) ON DELETE CASCADE,
    FOREIGN KEY (logged_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- =====================================================================
-- 8. COMPLAINTS
-- =====================================================================

CREATE TABLE IF NOT EXISTS complaint_categories (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    society_id INT UNSIGNED NOT NULL,
    name VARCHAR(100) NOT NULL,
    FOREIGN KEY (society_id) REFERENCES society(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS complaints (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    society_id INT UNSIGNED NOT NULL,
    flat_id INT UNSIGNED NOT NULL,
    member_id INT UNSIGNED NOT NULL,
    category_id INT UNSIGNED NOT NULL,
    subject VARCHAR(150) NOT NULL,
    description TEXT NULL,
    priority ENUM('low','medium','high') NOT NULL DEFAULT 'medium',
    status ENUM('open','in_progress','resolved','closed') NOT NULL DEFAULT 'open',
    assigned_to INT UNSIGNED NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    resolved_at TIMESTAMP NULL,
    FOREIGN KEY (society_id) REFERENCES society(id) ON DELETE CASCADE,
    FOREIGN KEY (flat_id) REFERENCES flats(id) ON DELETE CASCADE,
    FOREIGN KEY (member_id) REFERENCES members(id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES complaint_categories(id),
    FOREIGN KEY (assigned_to) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_complaint_status (status)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS complaint_updates (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    complaint_id INT UNSIGNED NOT NULL,
    updated_by INT UNSIGNED NULL,
    status ENUM('open','in_progress','resolved','closed') NOT NULL,
    remarks VARCHAR(255) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (complaint_id) REFERENCES complaints(id) ON DELETE CASCADE,
    FOREIGN KEY (updated_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- =====================================================================
-- 9. COMMUNICATION (Notices / Events / Polls)
-- =====================================================================

CREATE TABLE IF NOT EXISTS notices (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    society_id INT UNSIGNED NOT NULL,
    title VARCHAR(150) NOT NULL,
    body TEXT NOT NULL,
    notice_type ENUM('notice','circular') NOT NULL DEFAULT 'notice',
    attachment_path VARCHAR(255) NULL,
    published_by INT UNSIGNED NULL,
    published_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expires_at TIMESTAMP NULL,
    FOREIGN KEY (society_id) REFERENCES society(id) ON DELETE CASCADE,
    FOREIGN KEY (published_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS events (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    society_id INT UNSIGNED NOT NULL,
    title VARCHAR(150) NOT NULL,
    description TEXT NULL,
    venue VARCHAR(150) NULL,
    starts_at DATETIME NOT NULL,
    ends_at TIMESTAMP NULL,
    created_by INT UNSIGNED NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (society_id) REFERENCES society(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS polls (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    society_id INT UNSIGNED NOT NULL,
    question VARCHAR(255) NOT NULL,
    closes_at TIMESTAMP NULL,
    created_by INT UNSIGNED NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (society_id) REFERENCES society(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS poll_options (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    poll_id INT UNSIGNED NOT NULL,
    option_text VARCHAR(150) NOT NULL,
    FOREIGN KEY (poll_id) REFERENCES polls(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS poll_votes (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    poll_option_id INT UNSIGNED NOT NULL,
    member_id INT UNSIGNED NOT NULL,
    voted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (poll_option_id) REFERENCES poll_options(id) ON DELETE CASCADE,
    FOREIGN KEY (member_id) REFERENCES members(id) ON DELETE CASCADE,
    UNIQUE KEY uq_vote (poll_option_id, member_id)
) ENGINE=InnoDB;

-- =====================================================================
-- 10. STAFF
-- =====================================================================

CREATE TABLE IF NOT EXISTS staff (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    society_id INT UNSIGNED NOT NULL,
    name VARCHAR(150) NOT NULL,
    designation VARCHAR(100) NULL,           -- watchman, plumber, sweeper...
    phone VARCHAR(20) NULL,
    address VARCHAR(255) NULL,
    date_of_birth DATE NULL,
    id_proof_path VARCHAR(255) NULL,
    photo_path VARCHAR(255) NULL,
    joining_date DATE NULL,
    status ENUM('active','inactive') NOT NULL DEFAULT 'active',
    -- Verification is a real 3-state workflow in practice, not a yes/no: staff are commonly
    -- hired before their police verification clears, so "pending" needs to exist as its own
    -- state, not be conflated with either "verified" or "not verified".
    police_verification_status ENUM('pending','verified','not_verified') NOT NULL DEFAULT 'pending',
    police_verification_date DATE NULL,
    police_verification_doc_path VARCHAR(255) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (society_id) REFERENCES society(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS attendance (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    staff_id INT UNSIGNED NOT NULL,
    attendance_date DATE NOT NULL,
    status ENUM('present','absent','half_day','leave') NOT NULL,
    marked_by INT UNSIGNED NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (staff_id) REFERENCES staff(id) ON DELETE CASCADE,
    FOREIGN KEY (marked_by) REFERENCES users(id) ON DELETE SET NULL,
    UNIQUE KEY uq_attendance (staff_id, attendance_date)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS leave_requests (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    staff_id INT UNSIGNED NOT NULL,
    from_date DATE NOT NULL,
    to_date DATE NOT NULL,
    reason VARCHAR(255) NULL,
    status ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (staff_id) REFERENCES staff(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS payroll (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    staff_id INT UNSIGNED NOT NULL,
    pay_period VARCHAR(20) NOT NULL,         -- 2026-07
    basic_amount DECIMAL(10,2) NOT NULL DEFAULT 0,
    deductions DECIMAL(10,2) NOT NULL DEFAULT 0,
    net_amount DECIMAL(10,2) NOT NULL DEFAULT 0,
    paid_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (staff_id) REFERENCES staff(id) ON DELETE CASCADE,
    UNIQUE KEY uq_payroll_period (staff_id, pay_period)
) ENGINE=InnoDB;

-- =====================================================================
-- 11. ASSETS
-- =====================================================================

CREATE TABLE IF NOT EXISTS asset_categories (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    society_id INT UNSIGNED NOT NULL,
    name VARCHAR(100) NOT NULL,
    FOREIGN KEY (society_id) REFERENCES society(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS assets (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    society_id INT UNSIGNED NOT NULL,
    category_id INT UNSIGNED NOT NULL,
    name VARCHAR(150) NOT NULL,
    purchase_date DATE NULL,
    purchase_cost DECIMAL(10,2) NULL,
    warranty_expiry DATE NULL,
    location VARCHAR(150) NULL,
    status ENUM('active','under_repair','disposed') NOT NULL DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (society_id) REFERENCES society(id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES asset_categories(id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS asset_amc (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    asset_id INT UNSIGNED NOT NULL,
    vendor_id INT UNSIGNED NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    cost DECIMAL(10,2) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (asset_id) REFERENCES assets(id) ON DELETE CASCADE,
    FOREIGN KEY (vendor_id) REFERENCES vendors(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS asset_services (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    asset_id INT UNSIGNED NOT NULL,
    service_date DATE NOT NULL,
    description VARCHAR(255) NULL,
    cost DECIMAL(10,2) NULL,
    next_due_date DATE NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (asset_id) REFERENCES assets(id) ON DELETE CASCADE
) ENGINE=InnoDB;

SET FOREIGN_KEY_CHECKS = 1;
