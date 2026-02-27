<?php
if (! defined('ABSPATH')) {
    exit;
}

function spamnix_default_settings()
{
    return array(
        'honeypot_enabled' => 0,
        'time_trap_enabled' => 0,
        'time_trap_min_seconds' => 5,
        'keyword_scanner_enabled' => 0,
        'max_links' => 2,
        'banned_keywords' => "casino\nviagra\ncrypto",
        'ip_rate_limit_enabled' => 0,
        'ip_limit_short' => 3,
        'ip_window_short' => 60,
        'ip_limit_long' => 10,
        'ip_window_long' => 600,
        'ip_block_duration' => 1800,
        'email_filter_enabled' => 0,
        'disposable_domains' => "mailinator.com\nyopmail.com\nguerrillamail.com",
    );
}

function spamnix_get_settings()
{
    $saved = get_option('spamnix_settings', array());
    return wp_parse_args($saved, spamnix_default_settings());
}

function spamnix_is_pro()
{
    return (bool) apply_filters('spamnix_is_pro', false);
}

function spamnix_increment_stat($reason)
{
    $stats = get_option('spamnix_stats', array('total_blocked' => 0));

    if (! isset($stats['total_blocked'])) {
        $stats['total_blocked'] = 0;
    }

    $stats['total_blocked']++;

    if (! isset($stats[$reason])) {
        $stats[$reason] = 0;
    }
    $stats[$reason]++;

    update_option('spamnix_stats', $stats);
}

function spamnix_get_client_ip()
{
    $candidates = array(
        'HTTP_CF_CONNECTING_IP',
        'HTTP_X_FORWARDED_FOR',
        'REMOTE_ADDR',
    );

    foreach ($candidates as $key) {
        if (! empty($_SERVER[$key])) {
            $raw = sanitize_text_field(wp_unslash($_SERVER[$key]));
            $ip = trim(explode(',', $raw)[0]);
            if (filter_var($ip, FILTER_VALIDATE_IP)) {
                return $ip;
            }
        }
    }

    return '0.0.0.0';
}

function spamnix_get_disposable_domains($settings)
{
    $default = array(
        'mailinator.com',
        'yopmail.com',
        'guerrillamail.com',
        '10minutemail.com',
        'temp-mail.org',
    );

    $custom = preg_split('/[\r\n,]+/', (string) ($settings['disposable_domains'] ?? ''), -1, PREG_SPLIT_NO_EMPTY);
    $all = array_unique(array_map('strtolower', array_merge($default, $custom)));

    return array_values($all);
}

function spamnix_lang($en_text, $id_text = '')
{
    $locale = function_exists('determine_locale') ? determine_locale() : get_locale();
    if (strpos((string) $locale, 'id') === 0) {
        return $id_text !== '' ? $id_text : $en_text;
    }

    return $en_text;
}
