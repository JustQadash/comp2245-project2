# Security Policy

## Supported Versions

This project is an academic coursework repository (`comp2245-project2`) for a helpdesk application. Only the active `main` branch is maintained during development.

| Version | Supported |
| ------- | ----------------- |
| main    | :white_check_mark: |
| older commits / branches | :x: |

## Reporting a Vulnerability

Please do not open a public GitHub Issue for security vulnerabilities (such as SQL injection, cross-site scripting, authentication/authorization bypasses, or exposed credentials).

Instead, report issues privately via GitHub Security Advisories (if enabled) or contact the repository maintainer directly.

- **Response & Updates:** You can expect an initial acknowledgment and status update within a few days (or as soon as practicable during active coursework).
- **Resolution Process:** Confirmed vulnerabilities will be reviewed, patched on `main`, and verified before being disclosed. If a report is out of scope or cannot be reproduced, you will be notified with a brief explanation.

## Security Notes for This Project

- Do not commit real credentials, API keys, or `.env` files to the repository.
- Use unique passwords rather than shared demo credentials outside local testing.
