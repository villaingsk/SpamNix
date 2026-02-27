<?php
if (! defined('ABSPATH')) {
    exit;
}

class SpamNix_Email_Filter
{
    private $disposable_domains = array();

    public function __construct($settings)
    {
        $this->disposable_domains = spamnix_get_disposable_domains($settings);
    }

    public function is_disposable($email)
    {
        $email = sanitize_email((string) $email);
        if (! $email || strpos($email, '@') === false) {
            return false;
        }

        $domain = strtolower(substr(strrchr($email, '@'), 1));
        return in_array($domain, $this->disposable_domains, true);
    }
}
