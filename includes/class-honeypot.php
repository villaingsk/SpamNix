<?php
if (! defined('ABSPATH')) {
    exit;
}

class SpamNix_Honeypot
{
    public function render_fields()
    {
        echo '<p class="spamnix-hp-wrap" style="position:absolute;left:-9999px;top:-9999px;">';
        echo '<label for="website_url_confirm">Leave this field empty</label>';
        echo '<input type="text" name="website_url_confirm" id="website_url_confirm" value="" autocomplete="off" />';
        echo '</p>';

        echo '<input type="hidden" name="spamnix_form_ts" value="' . esc_attr(time()) . '" />';
        echo '<input type="hidden" name="spamnix_js_token" id="spamnix_js_token" value="" />';
        wp_nonce_field('spamnix_comment', 'spamnix_nonce');

        echo '<script>(function(){var el=document.getElementById("spamnix_js_token");if(el){el.value="1";}})();</script>';
    }

    public function validate($post)
    {
        $nonce = isset($post['spamnix_nonce']) ? sanitize_text_field(wp_unslash($post['spamnix_nonce'])) : '';
        if (! wp_verify_nonce($nonce, 'spamnix_comment')) {
            return 'invalid_nonce';
        }

        $honeypot = isset($post['website_url_confirm']) ? trim((string) wp_unslash($post['website_url_confirm'])) : '';
        if ($honeypot !== '') {
            return 'honeypot_triggered';
        }

        $js_token = isset($post['spamnix_js_token']) ? sanitize_text_field(wp_unslash($post['spamnix_js_token'])) : '';
        if ($js_token !== '1') {
            return 'js_validation_failed';
        }

        return false;
    }
}
