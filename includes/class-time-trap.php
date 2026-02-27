<?php
if (! defined('ABSPATH')) {
    exit;
}

class SpamNix_Time_Trap
{
    private $min_seconds;

    public function __construct($min_seconds = 5)
    {
        $this->min_seconds = max(1, (int) $min_seconds);
    }

    public function validate($post)
    {
        $ts = isset($post['spamnix_form_ts']) ? (int) $post['spamnix_form_ts'] : 0;
        if ($ts <= 0) {
            return 'missing_timestamp';
        }

        if ((time() - $ts) < $this->min_seconds) {
            return 'time_trap_triggered';
        }

        return false;
    }
}
