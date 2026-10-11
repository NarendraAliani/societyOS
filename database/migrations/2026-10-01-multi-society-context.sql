-- SocietyOS multi-society context foundation
-- Run after taking a production backup.
-- Additive and backward-compatible for the existing single-society deployment.

ALTER TABLE society
    ADD COLUMN code VARCHAR(30) NULL AFTER id;

UPDATE society
SET code = CONCAT('SOC-', LPAD(id, 3, '0'))
WHERE code IS NULL OR code = '';

ALTER TABLE society
    MODIFY COLUMN code VARCHAR(30) NOT NULL,
    ADD UNIQUE KEY uq_society_code (code);
