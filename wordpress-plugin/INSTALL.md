# Hydrox Booking WordPress Plugin

The installable file is `hydrox-booking.zip`.

## WordPress setup

1. Sign in to the WordPress dashboard for hydrox.au.
2. Go to **Plugins → Add New Plugin → Upload Plugin**.
3. Upload `hydrox-booking.zip`, then select **Install Now** and **Activate**.
4. Go to **Settings → Hydrox Booking**.
5. Confirm the endpoint is `https://portal.hydrox.au/api/bookings`.
6. Enter the same secret value configured as `HYDROX_BOOKING_TOKEN` in the Hydrox Portal.
7. Save the settings.
8. Add `[hydrox_booking_form]` to the WordPress booking page.

The form is responsive and lets customers select several services, add optional extras and upload up to 20 camera or gallery photos.

## Portal setup

The production Portal `.env` must contain a long private token:

```env
HYDROX_BOOKING_TOKEN=use-the-same-private-random-token-as-wordpress
```

After changing the production `.env`, clear and rebuild the Laravel configuration cache.

Configure production SMTP so both booking emails are delivered:

```env
MAIL_MAILER=smtp
MAIL_HOST=your-mail-server
MAIL_PORT=587
MAIL_USERNAME=admin@hydrox.au
MAIL_PASSWORD=your-mailbox-password
MAIL_SCHEME=tls
MAIL_FROM_ADDRESS=admin@hydrox.au
MAIL_FROM_NAME="Hydrox Facility Management"
```

Because photos upload sequentially, WordPress only needs to accept one 10 MB image at a time. Set PHP `upload_max_filesize` above 10 MB and `post_max_size` above 12 MB.

Never place this token inside the WordPress page, shortcode, theme code, or browser JavaScript.
