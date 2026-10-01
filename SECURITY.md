# BloodNet V3 security notes

This project is intended for local XAMPP demonstration and educational use.

## Packaging
The distributed project contains PHP, SQL, CSS, JavaScript, Markdown and text files only. No Windows executable, installer, batch, PowerShell, VBS, DLL, JAR or binary program is included.

## Source review
A static source scan was performed for common high-risk PHP execution/backdoor patterns, including `eval`, `base64_decode`, `shell_exec`, `system`, `passthru`, `exec`, `proc_open`, `popen`, raw socket creation and remote PHP includes. No such pattern was found in the V3 project source at packaging time.

## Existing protections
- Prepared database statements are used on sensitive state-changing paths.
- CSRF tokens protect important POST actions.
- Session ID is regenerated after successful login.
- Session cookies use HttpOnly and SameSite settings.
- Demo one-click login is restricted to localhost/127.0.0.1.
- Public pages do not expose donor telephone numbers.
- Database errors are not printed to public users.

## Important limitations
This is a university/demo application, not a production medical system. Before Internet deployment, use HTTPS, environment-based database secrets, a production web server configuration, rate limiting, email/phone verification, a real authorization review, dependency security review, logging/monitoring and penetration testing.
