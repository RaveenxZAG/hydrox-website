<?php
/**
 * Plugin Name: Hydrox Booking
 * Description: Premium multiple service booking requests for Hydrox Facility Management.
 * Version: 2.1.2
 * Author: Hydrox Facility Management
 * Text Domain: hydrox-booking
 * Requires at least: 6.2
 * Requires PHP: 8.0
 */

defined('ABSPATH') || exit;

final class Hydrox_Booking_Plugin
{
    private const OPTION_KEY = 'hydrox_booking_settings';
    private const NONCE_ACTION = 'hydrox_booking_request';
    private const MAX_PHOTOS = 20;
    private const MAX_PHOTO_BYTES = 10485760;

    public static function boot(): void
    {
        add_action('admin_init', [self::class, 'register_settings']);
        add_action('admin_menu', [self::class, 'register_settings_page']);
        foreach (['create', 'upload_photo', 'finalize'] as $action) {
            add_action("wp_ajax_hydrox_booking_{$action}", [self::class, "ajax_{$action}"]);
            add_action("wp_ajax_nopriv_hydrox_booking_{$action}", [self::class, "ajax_{$action}"]);
        }
        add_shortcode('hydrox_booking_form', [self::class, 'render_form']);
    }

    public static function register_settings(): void
    {
        register_setting('hydrox_booking', self::OPTION_KEY, [
            'sanitize_callback' => static function ($input): array {
                $input = is_array($input) ? $input : [];
                return [
                    'endpoint' => esc_url_raw(trim((string) ($input['endpoint'] ?? ''))),
                    'token' => sanitize_text_field((string) ($input['token'] ?? '')),
                    'success_message' => sanitize_text_field((string) ($input['success_message'] ?? '')),
                ];
            },
        ]);
    }

    public static function register_settings_page(): void
    {
        add_options_page('Hydrox Booking', 'Hydrox Booking', 'manage_options', 'hydrox-booking', [self::class, 'render_settings_page']);
    }

    private static function settings(): array
    {
        return wp_parse_args((array) get_option(self::OPTION_KEY, []), [
            'endpoint' => 'https://portal.hydrox.au/api/bookings',
            'token' => '',
            'success_message' => 'Your request is safely with the Hydrox team.',
        ]);
    }

    private static function core_services(): array
    {
        return [
            'Regular home cleaning',
            'Deep or spring cleaning',
            'One off cleaning',
            'End of lease or move cleaning',
            'Office / commercial cleaning',
            'NDIS cleaning',
            'Aged care cleaning',
            'Carpet, rug & upholstery steam cleaning',
            'Pressure / concrete cleaning',
            'Lawn mowing / outdoor care',
            'Concreting',
            'Other facility request',
        ];
    }

    private static function extras(): array
    {
        return [
            'Oven cleaning',
            'Fridge cleaning',
            'Inside cabinets',
            'Inside windows',
            'Ground floor outside windows',
            'Wet-wipe blinds',
            'Wall cleaning',
            'Patio / balcony cleaning',
            'Focused kitchen cleaning',
            'Focused bathroom cleaning',
            'Eco-friendly products',
        ];
    }

    public static function render_settings_page(): void
    {
        if (! current_user_can('manage_options')) {
            return;
        }
        $settings = self::settings();
        ?>
        <div class="wrap">
            <h1>Hydrox Booking</h1>
            <p>Add <code>[hydrox_booking_form]</code> to the Hydrox booking page.</p>
            <form method="post" action="options.php">
                <?php settings_fields('hydrox_booking'); ?>
                <table class="form-table" role="presentation">
                    <tr><th><label for="hydrox-endpoint">Portal endpoint</label></th><td>
                        <input class="regular-text" id="hydrox-endpoint" type="url" name="<?php echo esc_attr(self::OPTION_KEY); ?>[endpoint]" value="<?php echo esc_attr($settings['endpoint']); ?>" required>
                        <p class="description">Use https://portal.hydrox.au/api/bookings</p>
                    </td></tr>
                    <tr><th><label for="hydrox-token">Integration token</label></th><td>
                        <input class="regular-text" id="hydrox-token" type="password" autocomplete="new-password" name="<?php echo esc_attr(self::OPTION_KEY); ?>[token]" value="<?php echo esc_attr($settings['token']); ?>" required>
                        <p class="description">Must match HYDROX_BOOKING_TOKEN in the Portal environment. It is never exposed to visitors.</p>
                    </td></tr>
                    <tr><th><label for="hydrox-success">Success message</label></th><td>
                        <input class="large-text" id="hydrox-success" type="text" name="<?php echo esc_attr(self::OPTION_KEY); ?>[success_message]" value="<?php echo esc_attr($settings['success_message']); ?>">
                    </td></tr>
                </table>
                <?php submit_button('Save Hydrox Booking Settings'); ?>
            </form>
        </div>
        <?php
    }

    public static function render_form($attributes = []): string
    {
        $attributes = shortcode_atts([
            'title' => 'Request a booking',
            'intro' => 'Choose the services you need. Our team will review your request and contact you to confirm availability and pricing.',
        ], $attributes, 'hydrox_booking_form');

        wp_enqueue_style('hydrox-booking', plugin_dir_url(__FILE__) . 'assets/booking.css', [], '2.1.2');
        wp_enqueue_style('hydrox-booking-fixes', plugin_dir_url(__FILE__) . 'assets/booking-fixes.css', ['hydrox-booking'], '2.1.2');
        wp_enqueue_script('hydrox-booking', plugin_dir_url(__FILE__) . 'assets/booking.js', [], '2.1.2', true);
        wp_localize_script('hydrox-booking', 'HydroxBooking', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce(self::NONCE_ACTION),
            'maxPhotos' => self::MAX_PHOTOS,
            'maxPhotoBytes' => self::MAX_PHOTO_BYTES,
            'successMessage' => self::settings()['success_message'],
        ]);

        ob_start();
        ?>
        <div class="hydrox-booking" id="hydrox-booking">
            <div class="hydrox-booking__shell">
                <header class="hydrox-booking__header">
                    <p class="hydrox-booking__eyebrow">Hydrox Facility Management</p>
                    <h2><?php echo esc_html($attributes['title']); ?></h2>
                    <p><?php echo esc_html($attributes['intro']); ?></p>
                </header>

                <div class="hydrox-booking__progress" aria-label="Booking progress">
                    <?php foreach (['Services', 'Location', 'Photos', 'Contact'] as $index => $label) : ?>
                        <div class="hydrox-booking__progress-item<?php echo $index === 0 ? ' is-active' : ''; ?>" data-progress="<?php echo esc_attr($index + 1); ?>">
                            <span><?php echo esc_html($index + 1); ?></span><small><?php echo esc_html($label); ?></small>
                        </div>
                    <?php endforeach; ?>
                </div>

                <form class="hydrox-booking__form" novalidate>
                    <input class="hydrox-booking__trap" name="company_website" tabindex="-1" autocomplete="off" aria-hidden="true">

                    <section class="hydrox-booking__step is-active" data-step="1">
                        <div class="hydrox-booking__section-heading"><span>01</span><div><h3>What can we help with?</h3><p>Select one or more main services, then add any extras.</p></div></div>
                        <div class="hydrox-booking__choice-grid">
                            <?php foreach (self::core_services() as $service) : ?>
                                <label class="hydrox-booking__choice"><input type="checkbox" name="services[]" value="<?php echo esc_attr($service); ?>"><span><b><?php echo esc_html($service); ?></b><i>✓</i></span></label>
                            <?php endforeach; ?>
                        </div>
                        <div class="hydrox-booking__subsection">
                            <h4>Optional extras</h4>
                            <div class="hydrox-booking__chips">
                                <?php foreach (self::extras() as $extra) : ?>
                                    <label><input type="checkbox" name="extras[]" value="<?php echo esc_attr($extra); ?>"><span><?php echo esc_html($extra); ?></span></label>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <div class="hydrox-booking__field">
                            <label for="hydrox-frequency">How often do you need the service?</label>
                            <select id="hydrox-frequency" name="frequency" required>
                                <option value="">Choose a frequency</option>
                                <option value="one-off">One off</option><option value="weekly">Weekly</option>
                                <option value="fortnightly">Fortnightly</option><option value="monthly">Monthly</option>
                                <option value="not-sure">Not sure yet</option>
                            </select>
                        </div>
                    </section>

                    <section class="hydrox-booking__step" data-step="2">
                        <div class="hydrox-booking__section-heading"><span>02</span><div><h3>Where and when?</h3><p>Tell us where the work is needed. Your preferred timing is not confirmed yet.</p></div></div>
                        <div class="hydrox-booking__fields">
                            <div class="hydrox-booking__field is-wide"><label for="hydrox-address">Service address *</label><input id="hydrox-address" name="address" required autocomplete="street-address" placeholder="Street address"></div>
                            <div class="hydrox-booking__field"><label for="hydrox-suburb">Suburb *</label><input id="hydrox-suburb" name="suburb" required autocomplete="address-level2"></div>
                            <div class="hydrox-booking__field"><label for="hydrox-postcode">Postcode *</label><input id="hydrox-postcode" name="postcode" required inputmode="numeric" pattern="[0-9]{4}" maxlength="4" autocomplete="postal-code"></div>
                        </div>
                        <label class="hydrox-booking__toggle"><input type="checkbox" name="schedule_flexible" value="1" checked><span></span><b>My date and time are flexible</b></label>
                        <div class="hydrox-booking__fields hydrox-booking__schedule is-hidden">
                            <div class="hydrox-booking__field"><label for="hydrox-date">Preferred date</label><input id="hydrox-date" type="date" name="preferred_date" min="<?php echo esc_attr(wp_date('Y-m-d')); ?>"></div>
                            <div class="hydrox-booking__field"><label for="hydrox-time">Preferred time</label><select id="hydrox-time" name="preferred_time"><option value="">Choose a time</option><option>Morning</option><option>Afternoon</option><option>Evening / after hours</option><option>Any time</option></select></div>
                        </div>
                    </section>

                    <section class="hydrox-booking__step" data-step="3">
                        <div class="hydrox-booking__section-heading"><span>03</span><div><h3>Show us the job</h3><p>Optional: add up to 20 photos from your phone camera or gallery, maximum 10 MB each.</p></div></div>
                        <label class="hydrox-booking__dropzone" for="hydrox-photos">
                            <input id="hydrox-photos" type="file" accept="image/jpeg,image/png,image/webp,image/heic,image/heif" multiple>
                            <span class="hydrox-booking__camera">＋</span><b>Add photos</b><small>Take photos or choose from your gallery</small>
                        </label>
                        <div class="hydrox-booking__photo-summary"><span>No photos selected</span><button type="button" class="hydrox-booking__clear">Clear all</button></div>
                        <div class="hydrox-booking__previews"></div>
                    </section>

                    <section class="hydrox-booking__step" data-step="4">
                        <div class="hydrox-booking__section-heading"><span>04</span><div><h3>Your contact details</h3><p>We will use these details to review and confirm your request.</p></div></div>
                        <div class="hydrox-booking__fields">
                            <div class="hydrox-booking__field is-wide"><label for="hydrox-name">Full name *</label><input id="hydrox-name" name="customer_name" required autocomplete="name"></div>
                            <div class="hydrox-booking__field"><label for="hydrox-phone">Phone number *</label><input id="hydrox-phone" type="tel" name="phone" required autocomplete="tel" placeholder="04XX XXX XXX"></div>
                            <div class="hydrox-booking__field"><label for="hydrox-email">Email address *</label><input id="hydrox-email" type="email" name="email" required autocomplete="email"></div>
                            <div class="hydrox-booking__field is-wide"><label for="hydrox-notes">Anything else we should know?</label><textarea id="hydrox-notes" name="notes" maxlength="5000" placeholder="Property details, access, parking, priorities or special requirements"></textarea></div>
                        </div>
                        <div class="hydrox-booking__review"></div>
                        <div class="hydrox-booking__request-note"><b>Request only, not a confirmed booking</b><p>Hydrox will review your request and contact you to confirm availability, scope and pricing.</p></div>
                    </section>

                    <div class="hydrox-booking__error" role="alert" hidden></div>
                    <div class="hydrox-booking__upload-status" hidden><div><span></span></div><p>Securely sending your request…</p></div>
                    <div class="hydrox-booking__actions">
                        <button class="hydrox-booking__back" type="button" hidden>Back</button>
                        <button class="hydrox-booking__next" type="button">Continue</button>
                        <button class="hydrox-booking__submit" type="submit" hidden>Submit booking request</button>
                    </div>
                </form>

                <section class="hydrox-booking__success" hidden>
                    <div class="hydrox-booking__success-icon"><span></span>✓</div>
                    <p class="hydrox-booking__eyebrow">Request received securely</p>
                    <h3>Your request is processing</h3>
                    <p class="hydrox-booking__success-copy"></p>
                    <div><small>BOOKING REQUEST CODE</small><strong></strong></div>
                    <p><b>This is not a confirmed booking.</b><br>Our team will review your request and contact you to confirm availability and pricing. Please wait for our confirmation.</p>
                    <p class="hydrox-booking__email-note">A receipt has been sent to your email. Keep your request code for reference.</p>
                </section>
            </div>
        </div>
        <?php
        return (string) ob_get_clean();
    }

    public static function ajax_create(): void
    {
        self::verify_ajax();
        if (! empty($_POST['company_website'])) {
            wp_send_json_error(['message' => 'Unable to submit this request.'], 400);
        }

        $services = array_values(array_intersect(self::text_array('services'), self::core_services()));
        $extras = array_values(array_intersect(self::text_array('extras'), self::extras()));
        $payload = [
            'source' => 'hydrox.au WordPress',
            'external_reference' => 'WP-' . wp_generate_uuid4(),
            'service' => implode(', ', $services),
            'services' => $services,
            'extras' => $extras,
            'frequency' => sanitize_key((string) ($_POST['frequency'] ?? '')),
            'schedule_flexible' => ! empty($_POST['schedule_flexible']),
            'preferred_date' => sanitize_text_field(wp_unslash((string) ($_POST['preferred_date'] ?? ''))),
            'preferred_time' => sanitize_text_field(wp_unslash((string) ($_POST['preferred_time'] ?? ''))),
            'address' => sanitize_text_field(wp_unslash((string) ($_POST['address'] ?? ''))),
            'suburb' => sanitize_text_field(wp_unslash((string) ($_POST['suburb'] ?? ''))),
            'postcode' => sanitize_text_field(wp_unslash((string) ($_POST['postcode'] ?? ''))),
            'customer_name' => sanitize_text_field(wp_unslash((string) ($_POST['customer_name'] ?? ''))),
            'email' => sanitize_email(wp_unslash((string) ($_POST['email'] ?? ''))),
            'phone' => sanitize_text_field(wp_unslash((string) ($_POST['phone'] ?? ''))),
            'notes' => sanitize_textarea_field(wp_unslash((string) ($_POST['notes'] ?? ''))),
        ];

        if (
            ! $services || ! in_array($payload['frequency'], ['one-off', 'weekly', 'fortnightly', 'monthly', 'not-sure'], true)
            || $payload['address'] === '' || $payload['suburb'] === '' || ! preg_match('/^\d{4}$/', $payload['postcode'])
            || $payload['customer_name'] === '' || $payload['phone'] === '' || ! is_email($payload['email'])
        ) {
            wp_send_json_error(['message' => 'Please complete all required booking details.'], 422);
        }

        $response = self::portal_json('', $payload);
        if (is_wp_error($response) || wp_remote_retrieve_response_code($response) !== 201) {
            self::portal_error($response);
        }

        $body = json_decode((string) wp_remote_retrieve_body($response), true);
        $reference = sanitize_text_field((string) ($body['reference'] ?? ''));
        $session = wp_generate_uuid4();
        set_transient('hydrox_booking_session_' . $session, $reference, 2 * HOUR_IN_SECONDS);

        wp_send_json_success(['session' => $session, 'reference' => $reference]);
    }

    public static function ajax_upload_photo(): void
    {
        self::verify_ajax();
        $reference = self::session_reference();

        if (empty($_FILES['photo']) || (int) $_FILES['photo']['error'] !== UPLOAD_ERR_OK) {
            wp_send_json_error(['message' => 'The selected photo could not be uploaded.'], 422);
        }
        $file = $_FILES['photo'];
        if ((int) $file['size'] > self::MAX_PHOTO_BYTES) {
            wp_send_json_error(['message' => 'Each photo must be 10 MB or smaller.'], 422);
        }
        $allowed = ['image/jpeg', 'image/png', 'image/webp', 'image/heic', 'image/heif'];
        $mime = function_exists('mime_content_type') ? mime_content_type($file['tmp_name']) : $file['type'];
        if (! in_array($mime, $allowed, true)) {
            wp_send_json_error(['message' => 'Please upload a JPG, PNG, WebP or supported phone photo.'], 422);
        }

        $response = self::portal_multipart('/' . rawurlencode($reference) . '/photos', $file, $mime);
        if (is_wp_error($response) || wp_remote_retrieve_response_code($response) !== 201) {
            self::portal_error($response);
        }
        wp_send_json_success(json_decode((string) wp_remote_retrieve_body($response), true));
    }

    public static function ajax_finalize(): void
    {
        self::verify_ajax();
        $session = sanitize_text_field(wp_unslash((string) ($_POST['session'] ?? '')));
        $reference = self::session_reference();
        $response = self::portal_json('/' . rawurlencode($reference) . '/finalize', []);

        if (is_wp_error($response) || wp_remote_retrieve_response_code($response) !== 200) {
            self::portal_error($response);
        }

        delete_transient('hydrox_booking_session_' . $session);
        set_transient('hydrox_booking_rate_' . md5(self::client_ip()), 1, MINUTE_IN_SECONDS);
        wp_send_json_success(json_decode((string) wp_remote_retrieve_body($response), true));
    }

    private static function verify_ajax(): void
    {
        check_ajax_referer(self::NONCE_ACTION, 'nonce');
        if (get_transient('hydrox_booking_rate_' . md5(self::client_ip()))) {
            wp_send_json_error(['message' => 'Please wait a moment before sending another request.'], 429);
        }
        $settings = self::settings();
        if ($settings['endpoint'] === '' || $settings['token'] === '') {
            wp_send_json_error(['message' => 'Booking requests are temporarily unavailable. Please call 0418 222 477.'], 503);
        }
    }

    private static function session_reference(): string
    {
        $session = sanitize_text_field(wp_unslash((string) ($_POST['session'] ?? '')));
        $reference = $session ? get_transient('hydrox_booking_session_' . $session) : false;
        if (! is_string($reference) || $reference === '') {
            wp_send_json_error(['message' => 'Your secure upload session expired. Please send the form again.'], 410);
        }
        return $reference;
    }

    private static function portal_json(string $suffix, array $payload)
    {
        $settings = self::settings();
        return wp_remote_post(rtrim($settings['endpoint'], '/') . $suffix, [
            'timeout' => 30,
            'redirection' => 0,
            'headers' => ['Accept' => 'application/json', 'Content-Type' => 'application/json', 'X-Booking-Token' => $settings['token']],
            'body' => wp_json_encode($payload),
        ]);
    }

    private static function portal_multipart(string $suffix, array $file, string $mime)
    {
        $settings = self::settings();
        $boundary = '----Hydrox' . wp_generate_password(24, false, false);
        $body = "--{$boundary}\r\nContent-Disposition: form-data; name=\"photo\"; filename=\"" . sanitize_file_name($file['name']) . "\"\r\nContent-Type: {$mime}\r\n\r\n";
        $body .= file_get_contents($file['tmp_name']);
        $body .= "\r\n--{$boundary}--\r\n";
        return wp_remote_post(rtrim($settings['endpoint'], '/') . $suffix, [
            'timeout' => 60,
            'redirection' => 0,
            'headers' => ['Accept' => 'application/json', 'Content-Type' => "multipart/form-data; boundary={$boundary}", 'X-Booking-Token' => $settings['token']],
            'body' => $body,
        ]);
    }

    private static function portal_error($response): void
    {
        $message = is_wp_error($response) ? $response->get_error_message() : '';
        if (! is_wp_error($response)) {
            $body = json_decode((string) wp_remote_retrieve_body($response), true);
            $message = is_array($body) ? (string) ($body['message'] ?? '') : '';
        }
        wp_send_json_error(['message' => $message ?: 'The Hydrox Portal could not accept this request. Please try again.'], 502);
    }

    private static function text_array(string $key): array
    {
        $values = isset($_POST[$key]) && is_array($_POST[$key]) ? wp_unslash($_POST[$key]) : [];
        return array_values(array_unique(array_filter(array_map('sanitize_text_field', $values))));
    }

    private static function client_ip(): string
    {
        return sanitize_text_field(wp_unslash((string) ($_SERVER['REMOTE_ADDR'] ?? 'unknown')));
    }
}

Hydrox_Booking_Plugin::boot();
