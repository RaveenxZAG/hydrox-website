=== Hydrox Booking ===
Contributors: hydrox
Tags: booking, cleaning, facility management
Requires at least: 6.2
Tested up to: 6.6
Requires PHP: 8.0
Stable tag: 2.1.2
License: GPLv2 or later

Adds a guided multiple service booking request with secure photo uploads and Hydrox Portal integration.

== Installation ==

1. In WordPress, open Plugins > Add New Plugin > Upload Plugin.
2. Upload hydrox-booking.zip, install it, and activate it.
3. Open Settings > Hydrox Booking.
4. Keep the endpoint as https://portal.hydrox.au/api/bookings.
5. Enter the same private token used for HYDROX_BOOKING_TOKEN in the Hydrox Portal.
6. Save the settings.
7. Add the shortcode [hydrox_booking_form] to the booking page.

The token is used only by the WordPress server and is not included in the public page.

The form supports multiple core services, optional extras, flexible scheduling and up to 20 photos. Photos are uploaded one at a time to reduce failures on mobile and shared hosting.

== Shortcode ==

[hydrox_booking_form]

Optional heading text:

[hydrox_booking_form title="Book a Hydrox service" intro="Tell us what you need and we will contact you."]

== Changelog ==

= 2.1.2 =
* Added single service compatibility data for older Portal deployments.

= 2.1.1 =
* Fixed form values being omitted when a booking request is submitted.

= 2.1.0 =
* Removed the outer card border, rounded corners and shadow.
* Enforced one action button per step and neutral button styling.
* Added cache safe asset versions.

= 2.0.0 =
* Added a premium five step booking flow.
* Added multiple core services and extras.
* Added up to 20 private camera or gallery photos.
* Added sequential upload progress and retry.
* Added processing confirmation and booking request code.

= 1.0.0 =
* Initial Hydrox booking form and Portal integration.
