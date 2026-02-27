<?php
if (! defined('ABSPATH')) {
    exit;
}

class SpamNix_Admin
{
    public function __construct()
    {
        add_action('admin_menu', array($this, 'register_menu'));
        add_action('admin_init', array($this, 'register_settings'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_assets'));
    }

    public function register_menu()
    {
        add_options_page('SpamNix', 'SpamNix', 'manage_options', 'spamnix', array($this, 'render_settings_page'));
    }

    public function enqueue_assets($hook)
    {
        $allowed = array('settings_page_spamnix', 'settings_page_spamnix-stats');
        if (! in_array($hook, $allowed, true)) {
            return;
        }

        wp_enqueue_style('spamnix-admin', SPAMNIX_URL . 'assets/css/admin.css', array(), SPAMNIX_VERSION);
        wp_enqueue_script('spamnix-admin', SPAMNIX_URL . 'assets/js/admin.js', array(), SPAMNIX_VERSION, true);
    }

    public function register_settings()
    {
        register_setting('spamnix_settings_group', 'spamnix_settings', array($this, 'sanitize_settings'));

        add_settings_section(
            'spamnix_honeypot_section',
            spamnix_lang('Service: Honeypot + JS Validation', 'Layanan: Honeypot + Validasi JS'),
            array($this, 'render_section_description'),
            'spamnix',
            array('description' => spamnix_lang('Detects basic bots that fill hidden fields or do not execute JavaScript.', 'Mendeteksi bot klasik yang mengisi field tersembunyi atau tidak menjalankan JavaScript.'))
        );
        add_settings_field('honeypot_enabled', spamnix_lang('Enable Honeypot', 'Aktifkan Honeypot'), array($this, 'render_checkbox_field'), 'spamnix', 'spamnix_honeypot_section', array('key' => 'honeypot_enabled', 'desc' => spamnix_lang('If enabled, SpamNix adds hidden field, nonce, and JS token to the comment form.', 'Jika aktif, SpamNix menambah hidden field, nonce, dan JS token pada form komentar.')));

        add_settings_section(
            'spamnix_time_trap_section',
            spamnix_lang('Service: Time Trap', 'Layanan: Time Trap'),
            array($this, 'render_section_description'),
            'spamnix',
            array('description' => spamnix_lang('Rejects comments submitted too quickly after the form is loaded.', 'Menolak submit komentar yang terlalu cepat setelah form dibuka.'))
        );
        add_settings_field('time_trap_enabled', spamnix_lang('Enable Time Trap', 'Aktifkan Time Trap'), array($this, 'render_checkbox_field'), 'spamnix', 'spamnix_time_trap_section', array('key' => 'time_trap_enabled', 'desc' => spamnix_lang('Prevents instant bot submissions before minimum waiting time.', 'Mencegah bot submit instan sebelum batas waktu minimum.')));
        add_settings_field('time_trap_min_seconds', spamnix_lang('Minimum Seconds Before Submit', 'Minimum Detik Submit'), array($this, 'render_number_field'), 'spamnix', 'spamnix_time_trap_section', array('key' => 'time_trap_min_seconds', 'min' => 1, 'desc' => spamnix_lang('Example 5 seconds: submissions below this value are marked as spam.', 'Contoh 5 detik: submit di bawah nilai ini akan ditandai spam.')));

        add_settings_section(
            'spamnix_scanner_section',
            spamnix_lang('Service: Link & Keyword Scanner', 'Layanan: Scanner Link & Keyword'),
            array($this, 'render_section_description'),
            'spamnix',
            array('description' => spamnix_lang('Scans comments by link count, banned keywords, and suspicious text patterns.', 'Memindai isi komentar berdasarkan jumlah link, keyword, dan pola teks mencurigakan.'))
        );
        add_settings_field('keyword_scanner_enabled', spamnix_lang('Enable Scanner', 'Aktifkan Scanner'), array($this, 'render_checkbox_field'), 'spamnix', 'spamnix_scanner_section', array('key' => 'keyword_scanner_enabled', 'desc' => spamnix_lang('Enables excessive-link detection, banned keywords, repeated characters, and uppercase abuse checks.', 'Mengaktifkan deteksi link berlebih, keyword terlarang, karakter berulang, dan uppercase berlebihan.')));
        add_settings_field('max_links', spamnix_lang('Maximum Links', 'Maksimum Link'), array($this, 'render_number_field'), 'spamnix', 'spamnix_scanner_section', array('key' => 'max_links', 'min' => 0, 'desc' => spamnix_lang('If comment links exceed this value, the comment is marked as spam.', 'Jika jumlah link melebihi nilai ini, komentar akan ditandai sebagai spam.')));
        add_settings_field('banned_keywords', spamnix_lang('Banned Keywords', 'Keyword Terlarang'), array($this, 'render_textarea_field'), 'spamnix', 'spamnix_scanner_section', array('key' => 'banned_keywords', 'desc' => spamnix_lang('One keyword per line. Matching comments are marked as spam.', 'Satu keyword per baris. Jika ditemukan dalam komentar, komentar akan ditandai spam.')));

        add_settings_section(
            'spamnix_rate_limit_section',
            spamnix_lang('Service: IP Rate Limit', 'Layanan: Rate Limit IP'),
            array($this, 'render_section_description'),
            'spamnix',
            array('description' => spamnix_lang('Limits comment frequency from the same IP to stop burst spam.', 'Membatasi frekuensi komentar dari IP yang sama untuk mencegah spam beruntun.'))
        );
        add_settings_field('ip_rate_limit_enabled', spamnix_lang('Enable IP Rate Limit', 'Aktifkan IP Rate Limit'), array($this, 'render_checkbox_field'), 'spamnix', 'spamnix_rate_limit_section', array('key' => 'ip_rate_limit_enabled', 'desc' => spamnix_lang('Enables throttling based on short and long time windows.', 'Mengaktifkan pembatasan request berdasarkan jendela waktu pendek dan panjang.')));
        add_settings_field('ip_limit_short', spamnix_lang('Short Window Limit', 'Batas Short Window'), array($this, 'render_number_field'), 'spamnix', 'spamnix_rate_limit_section', array('key' => 'ip_limit_short', 'min' => 1, 'desc' => spamnix_lang('Maximum comments allowed in the short window.', 'Maksimum jumlah komentar pada short window.')));
        add_settings_field('ip_window_short', spamnix_lang('Short Window Duration (seconds)', 'Durasi Short Window (detik)'), array($this, 'render_number_field'), 'spamnix', 'spamnix_rate_limit_section', array('key' => 'ip_window_short', 'min' => 10, 'desc' => spamnix_lang('Example: 60 seconds for rapid burst protection.', 'Contoh 60 detik untuk pembatasan cepat.')));
        add_settings_field('ip_limit_long', spamnix_lang('Long Window Limit', 'Batas Long Window'), array($this, 'render_number_field'), 'spamnix', 'spamnix_rate_limit_section', array('key' => 'ip_limit_long', 'min' => 1, 'desc' => spamnix_lang('Maximum comments allowed in the long window.', 'Maksimum jumlah komentar pada long window.')));
        add_settings_field('ip_window_long', spamnix_lang('Long Window Duration (seconds)', 'Durasi Long Window (detik)'), array($this, 'render_number_field'), 'spamnix', 'spamnix_rate_limit_section', array('key' => 'ip_window_long', 'min' => 30, 'desc' => spamnix_lang('Example: 600 seconds (10 minutes).', 'Contoh 600 detik (10 menit).')));
        add_settings_field('ip_block_duration', spamnix_lang('IP Block Duration (seconds)', 'Durasi Block IP (detik)'), array($this, 'render_number_field'), 'spamnix', 'spamnix_rate_limit_section', array('key' => 'ip_block_duration', 'min' => 60, 'desc' => spamnix_lang('How long an IP is blocked after hitting limits.', 'Lama IP diblok saat melewati limit.')));

        add_settings_section(
            'spamnix_email_section',
            spamnix_lang('Service: Disposable Email Block', 'Layanan: Blokir Email Disposable'),
            array($this, 'render_section_description'),
            'spamnix',
            array('description' => spamnix_lang('Blocks comments using temporary/disposable email domains.', 'Memblokir komentar dari domain email sementara/disposable.'))
        );
        add_settings_field('email_filter_enabled', spamnix_lang('Enable Email Filter', 'Aktifkan Email Filter'), array($this, 'render_checkbox_field'), 'spamnix', 'spamnix_email_section', array('key' => 'email_filter_enabled', 'desc' => spamnix_lang('Useful to reduce spam from one-time email accounts.', 'Cocok untuk menekan spam akun sekali pakai.')));
        add_settings_field('disposable_domains', spamnix_lang('Disposable Domain List', 'Daftar Domain Disposable'), array($this, 'render_textarea_field'), 'spamnix', 'spamnix_email_section', array('key' => 'disposable_domains', 'desc' => spamnix_lang('One domain per line. Domains in this list are blocked.', 'Satu domain per baris. Domain di daftar ini akan ditolak.')));
    }

    public function sanitize_settings($input)
    {
        $defaults = spamnix_default_settings();
        $output = array();

        foreach ($defaults as $key => $default) {
            if (is_int($default)) {
                $output[$key] = isset($input[$key]) ? max(0, (int) $input[$key]) : 0;
                continue;
            }

            if (is_string($default)) {
                $output[$key] = isset($input[$key]) ? sanitize_textarea_field($input[$key]) : '';
                continue;
            }

            $output[$key] = ! empty($input[$key]) ? 1 : 0;
        }

        return wp_parse_args($output, $defaults);
    }

    public function render_checkbox_field($args)
    {
        $key = $args['key'];
        $settings = spamnix_get_settings();
        $value = ! empty($settings[$key]) ? 1 : 0;

        echo '<label>';
        echo '<input type="checkbox" name="spamnix_settings[' . esc_attr($key) . ']" value="1" ' . checked(1, $value, false) . ' />';
        echo ' ' . esc_html(spamnix_lang('enabled', 'aktif'));
        echo '</label>';
        $this->render_field_description($args);
    }

    public function render_number_field($args)
    {
        $key = $args['key'];
        $min = isset($args['min']) ? (int) $args['min'] : 0;
        $settings = spamnix_get_settings();
        $value = isset($settings[$key]) ? (int) $settings[$key] : 0;

        echo '<input type="number" min="' . esc_attr($min) . '" name="spamnix_settings[' . esc_attr($key) . ']" value="' . esc_attr($value) . '" />';
        $this->render_field_description($args);
    }

    public function render_textarea_field($args)
    {
        $key = $args['key'];
        $settings = spamnix_get_settings();
        $value = isset($settings[$key]) ? $settings[$key] : '';

        echo '<textarea name="spamnix_settings[' . esc_attr($key) . ']" rows="6" cols="50">' . esc_textarea($value) . '</textarea>';
        $this->render_field_description($args);
    }

    public function render_section_description($args)
    {
        if (! empty($args['description'])) {
            echo '<p class="spamnix-section-desc">' . esc_html($args['description']) . '</p>';
        }
    }

    private function render_field_description($args)
    {
        if (! empty($args['desc'])) {
            echo '<p class="description spamnix-field-desc">' . esc_html($args['desc']) . '</p>';
        }
    }

    public function render_settings_page()
    {
        include SPAMNIX_PATH . 'admin/settings-page.php';
    }

    public function render_stats_page()
    {
        include SPAMNIX_PATH . 'admin/stats-page.php';
    }
}
