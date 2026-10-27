# Security hardening

This project applies a baseline protection layer for production deployments.

## HTTP security headers

The application adds the following response headers automatically for all web requests:

- `X-Frame-Options: DENY`
- `X-Content-Type-Options: nosniff`
- `Referrer-Policy: strict-origin-when-cross-origin`
- `Permissions-Policy: camera=(), microphone=(), geolocation=()`
- `Cross-Origin-Resource-Policy: same-origin`
- `X-Permitted-Cross-Domain-Policies: none`
- `Strict-Transport-Security: max-age=31536000; includeSubDomains` when served via HTTPS

## Additional controls

- Use secure session cookies in production (`SESSION_SECURE_COOKIE=true`) and keep `SESSION_HTTP_ONLY=true` enabled.
- Keep `APP_ENV=production` and `APP_DEBUG=false`.
- Rate-limit checkout and login flows with Laravel’s throttle middleware.
- Restrict admin access to trusted IP ranges or a VPN when possible.
- Keep Laravel packages and PHP versions updated.
- Validate uploaded media files and enforce a strict file size and MIME-type policy.

## Operational reminders

- Review queue workers and exception logs at least once per day.
- Rotate secrets regularly, especially SMTP credentials, payment API keys, and application keys.
- Take regular database backups and store them outside the application runtime path.
