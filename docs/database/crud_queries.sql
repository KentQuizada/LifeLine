-- LifeLine: documented SQL CRUD queries
-- Run against the seeded `lifeline` database:
--   C:/xampp/mysql/bin/mysql.exe -u lifeline -p lifeline < docs/database/crud_queries.sql
--   Reset first (run in lifeline/): php artisan migrate:fresh --seed. This file inserts a demo request and changes sample rows, so a second run fails with duplicate entry 1062.
-- Statements marked "EXPECTED ERROR" are demonstrations of database validation;
-- run them one at a time in a MySQL client to see the error message.

-- Laravel's connection runs in strict mode; XAMPP's default mode is not strict, so an invalid
-- ENUM value would be saved as '' with only a warning. Match the app's behaviour:
SET SESSION sql_mode = 'STRICT_ALL_TABLES';

-- =========================================================
-- CREATE (INSERT)
-- =========================================================

-- 1. Submit an emergency blood request with a unique reference number.
INSERT INTO blood_requests
    (reference_number, requester_id, hospital_id, patient_name, blood_type, units, urgency, status, created_at, updated_at)
VALUES
    ('LL-261113-DEMAA2',
     (SELECT id FROM users WHERE role = 'requester' ORDER BY id LIMIT 1),
     (SELECT id FROM hospitals ORDER BY id LIMIT 1),
     'Demo Patient', 'O+', 2, 'critical', 'open', NOW(), NOW());

-- 2. Record the first history entry for that request.
INSERT INTO request_history (blood_request_id, changed_by, old_status, new_status, remarks)
SELECT id, requester_id, NULL, 'open', 'Request submitted'
FROM blood_requests
WHERE reference_number = 'LL-261113-DEMAA2';

-- 3. EXPECTED ERROR 1062 (Duplicate entry): the same reference number cannot be saved twice.
-- INSERT INTO blood_requests
--     (reference_number, requester_id, hospital_id, patient_name, blood_type, units, urgency, created_at, updated_at)
-- VALUES
--     ('LL-261113-DEMAA2',
--      (SELECT id FROM users WHERE role = 'requester' ORDER BY id LIMIT 1),
--      (SELECT id FROM hospitals ORDER BY id LIMIT 1),
--      'Duplicate Patient', 'O+', 1, 'urgent', NOW(), NOW());

-- 4. EXPECTED ERROR 4025 (CONSTRAINT chk_blood_requests_units failed): units must be 1 to 10.
-- INSERT INTO blood_requests
--     (reference_number, requester_id, hospital_id, patient_name, blood_type, units, urgency, created_at, updated_at)
-- VALUES
--     ('LL-261113-DEMAA3',
--      (SELECT id FROM users WHERE role = 'requester' ORDER BY id LIMIT 1),
--      (SELECT id FROM hospitals ORDER BY id LIMIT 1),
--      'Demo Patient', 'O+', 0, 'urgent', NOW(), NOW());

-- =========================================================
-- READ (SELECT)
-- =========================================================

-- 5. Track a request by reference number (public view: status only, no patient name).
SELECT br.reference_number, br.blood_type, br.units, br.urgency, br.status, h.name AS hospital, br.created_at
FROM blood_requests br
JOIN hospitals h ON h.id = br.hospital_id
WHERE br.reference_number = 'LL-261113-DEMAA2';

-- 6. Available, verified O+ donors with their last donation date.
SELECT d.id, u.first_name, u.last_name, d.blood_type, d.city, MAX(dn.donated_on) AS last_donation
FROM donors d
JOIN users u ON u.id = d.user_id
LEFT JOIN donations dn ON dn.donor_id = d.id
WHERE d.blood_type = 'O+'
  AND d.is_available = 1
  AND d.verification_status = 'verified'
GROUP BY d.id, u.first_name, u.last_name, d.blood_type, d.city
ORDER BY last_donation;

-- 7. Status history of a request, oldest first.
SELECT rh.old_status, rh.new_status, rh.remarks, rh.created_at, u.first_name AS changed_by
FROM request_history rh
JOIN blood_requests br ON br.id = rh.blood_request_id
LEFT JOIN users u ON u.id = rh.changed_by
WHERE br.reference_number = 'LL-261113-DEMAA2'
ORDER BY rh.created_at, rh.id;

-- 8. Admin statistics: requests per status.
SELECT status, COUNT(*) AS total
FROM blood_requests
GROUP BY status
ORDER BY total DESC;

-- 9. Admin statistics: available verified donors per blood type.
SELECT blood_type, COUNT(*) AS available_donors
FROM donors
WHERE is_available = 1 AND verification_status = 'verified'
GROUP BY blood_type
ORDER BY blood_type;

-- 10. The 5 donors with the most unread notifications.
SELECT u.email, COUNT(n.id) AS unread
FROM users u
LEFT JOIN notifications n ON n.user_id = u.id AND n.read_at IS NULL
WHERE u.role = 'donor'
GROUP BY u.id, u.email
ORDER BY unread DESC
LIMIT 5;

-- =========================================================
-- UPDATE
-- =========================================================

-- 11. Mark a donor unavailable (availability toggle).
UPDATE donors
SET is_available = 0, updated_at = NOW()
WHERE id = (SELECT id FROM (SELECT id FROM donors ORDER BY id LIMIT 1) AS first_donor);

-- 12. Change a request's status and log it, as one transaction.
START TRANSACTION;
UPDATE blood_requests
SET status = 'donor_found', updated_at = NOW()
WHERE reference_number = 'LL-261113-DEMAA2';
INSERT INTO request_history (blood_request_id, changed_by, old_status, new_status, remarks)
SELECT id, NULL, 'open', 'donor_found', 'A donor accepted the request'
FROM blood_requests
WHERE reference_number = 'LL-261113-DEMAA2';
COMMIT;

-- 13. Mark all of a user's notifications as read.
UPDATE notifications
SET read_at = NOW()
WHERE user_id = (SELECT id FROM users WHERE role = 'donor' ORDER BY id LIMIT 1)
  AND read_at IS NULL;

-- =========================================================
-- DELETE
-- =========================================================

-- 14. Delete read notifications older than 30 days.
DELETE FROM notifications
WHERE read_at IS NOT NULL
  AND read_at < NOW() - INTERVAL 30 DAY;

-- 15. EXPECTED ERROR 1451 (foreign key constraint fails): a request with history cannot be deleted,
--     so no record of an emergency is ever lost.
-- DELETE FROM blood_requests WHERE reference_number = 'LL-261113-DEMAA2';
