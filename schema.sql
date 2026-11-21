DROP DATABASE IF EXISTS helpdesk;

CREATE DATABASE helpdesk
CHARACTER SET utf8mb4;

USE helpdesk;


CREATE TABLE users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    firstname VARCHAR(50) NOT NULL,
    lastname VARCHAR(50) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'member') NOT NULL DEFAULT 'member',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);


CREATE TABLE tickets (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(150) NOT NULL,
    description TEXT NOT NULL,
    priority ENUM('Low', 'Medium', 'High') NOT NULL DEFAULT 'Medium',
    status ENUM('Open', 'In progress', 'Closed') NOT NULL DEFAULT 'Open',
    created_by INT UNSIGNED NOT NULL,
    assigned_to INT UNSIGNED DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_tickets_creator
        FOREIGN KEY (created_by)
        REFERENCES users(id),

    CONSTRAINT fk_tickets_assignee
        FOREIGN KEY (assigned_to)
        REFERENCES users(id)
        ON DELETE SET NULL
);


CREATE TABLE notes (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    ticket_id INT UNSIGNED NOT NULL,
    body TEXT NOT NULL,
    created_by INT UNSIGNED NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_notes_ticket
        FOREIGN KEY (ticket_id)
        REFERENCES tickets(id)
        ON DELETE CASCADE,

    CONSTRAINT fk_notes_creator
        FOREIGN KEY (created_by)
        REFERENCES users(id)
);


INSERT INTO users (firstname, lastname, email, password, role)
VALUES
(
    'Admin',
    'User',
    'admin@fixit.test',
    '$2y$12$kUM5AO2QVQL9eHY02mahu.pGwpU.kxe.I1DebAPtJLtCWETv7R0SG',
    'admin'
),
(
    'John',
    'Smith',
    'member1@fixit.test',
    '$2y$12$kUM5AO2QVQL9eHY02mahu.pGwpU.kxe.I1DebAPtJLtCWETv7R0SG',
    'member'
),
(
    'Jane',
    'Brown',
    'member2@fixit.test',
    '$2y$12$kUM5AO2QVQL9eHY02mahu.pGwpU.kxe.I1DebAPtJLtCWETv7R0SG',
    'member'
);


INSERT INTO tickets
(title, description, priority, status, created_by, assigned_to)
VALUES
(
    'Computer will not start',
    'The office computer does not power on when the power button is pressed.',
    'High',
    'Open',
    2,
    3
),
(
    'Printer connection issue',
    'The printer cannot connect to the office network.',
    'Medium',
    'In progress',
    3,
    2
),
(
    'Forgotten password',
    'A staff member needs assistance resetting their account password.',
    'Low',
    'Closed',
    2,
    2
),
(
    'Slow internet connection',
    'Internet speeds have become unusually slow in the office.',
    'High',
    'In progress',
    3,
    NULL
),
(
    'Software installation request',
    'A staff member requires assistance installing approved office software.',
    'Low',
    'Open',
    2,
    NULL
);


INSERT INTO notes (ticket_id, body, created_by)
VALUES
(
    1,
    'Initial inspection started. Checking the power supply.',
    3
),
(
    1,
    'Power cable was loose. Further testing is required.',
    2
),
(
    2,
    'Network settings are being checked.',
    2
);


CREATE USER IF NOT EXISTS 'comp2245'@'localhost'
IDENTIFIED BY 'comp2245-demo';

ALTER USER 'comp2245'@'localhost'
IDENTIFIED BY 'comp2245-demo';

GRANT SELECT, INSERT, UPDATE, DELETE
ON helpdesk.*
TO 'comp2245'@'localhost';
