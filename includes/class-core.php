<?php
if (! defined('ABSPATH')) {
    exit;
}

class SpamNix_Core
{
    private $settings;
    private $honeypot;
    private $time_trap;
    private $rate_limit;
    private $scanner;
    private $email_filter;

    public function __construct()
    {
        $this->settings = spamnix_get_settings();
        $this->honeypot = new SpamNix_Honeypot();
        $this->time_trap = new SpamNix_Time_Trap($this->settings['time_trap_min_seconds']);
        $this->rate_limit = new SpamNix_IP_Rate_Limit($this->settings);
        $this->scanner = new SpamNix_Keyword_Scanner($this->settings);
        $this->email_filter = new SpamNix_Email_Filter($this->settings);

        add_action('comment_form_after_fields', array($this, 'render_protection_fields'));
        add_action('comment_form_logged_in_after', array($this, 'render_protection_fields'));
        add_filter('preprocess_comment', array($this, 'guard_comment'), 1);
    }

    public function render_protection_fields()
    {
        if (! empty($this->settings['honeypot_enabled']) || ! empty($this->settings['time_trap_enabled'])) {
            $this->honeypot->render_fields();
        }
    }

    public function guard_comment($commentdata)
    {
        $reason = false;
        $spamnix_post = array();

        if (isset($_POST) && is_array($_POST)) {
            $spamnix_post = wp_unslash($_POST);
        }

        if (! empty($this->settings['ip_rate_limit_enabled'])) {
            $ip = spamnix_get_client_ip();
            if (! $this->rate_limit->check_and_track($ip)) {
                $reason = 'ip_rate_limited';
            }
        }

        if (
            ! $reason &&
            (! empty($this->settings['honeypot_enabled']) || ! empty($this->settings['time_trap_enabled']))
        ) {
            $nonce = isset($spamnix_post['spamnix_nonce']) ? sanitize_text_field($spamnix_post['spamnix_nonce']) : '';
            if (! wp_verify_nonce($nonce, 'spamnix_comment')) {
                $reason = 'invalid_nonce';
            }
        }

        if (! $reason && ! empty($this->settings['honeypot_enabled'])) {
            $reason = $this->honeypot->validate($spamnix_post);
        }

        if (! $reason && ! empty($this->settings['time_trap_enabled'])) {
            $reason = $this->time_trap->validate($spamnix_post);
        }

        if (! $reason && ! empty($this->settings['email_filter_enabled'])) {
            $email = $commentdata['comment_author_email'] ?? '';
            if ($this->email_filter->is_disposable($email)) {
                $reason = 'disposable_email';
            }
        }

        if (! $reason && ! empty($this->settings['keyword_scanner_enabled'])) {
            $reason = $this->scanner->scan($commentdata['comment_content'] ?? '');
        }

        if ($reason) {
            $commentdata['comment_approved'] = 'spam';
            $agent = isset($commentdata['comment_agent']) ? $commentdata['comment_agent'] . ' ' : '';
            $commentdata['comment_agent'] = trim($agent . 'SpamNix/' . $reason);
            spamnix_increment_stat($reason);
        }

        return $commentdata;
    }
}
