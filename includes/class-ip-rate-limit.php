<?php
if (! defined('ABSPATH')) {
    exit;
}

class SpamNix_IP_Rate_Limit
{
    private $short_limit;
    private $short_window;
    private $long_limit;
    private $long_window;
    private $block_duration;

    public function __construct($settings)
    {
        $this->short_limit = max(1, (int) ($settings['ip_limit_short'] ?? 3));
        $this->short_window = max(10, (int) ($settings['ip_window_short'] ?? 60));
        $this->long_limit = max($this->short_limit, (int) ($settings['ip_limit_long'] ?? 10));
        $this->long_window = max($this->short_window, (int) ($settings['ip_window_long'] ?? 600));
        $this->block_duration = max(60, (int) ($settings['ip_block_duration'] ?? 1800));
    }

    public function check_and_track($ip)
    {
        $hash = md5((string) $ip);
        $block_key = 'spamnix_blk_' . $hash;
        $rate_key = 'spamnix_rl_' . $hash;

        if (get_transient($block_key)) {
            return false;
        }

        $now = time();
        $events = get_transient($rate_key);

        if (! is_array($events)) {
            $events = array();
        }

        $events[] = $now;

        $events = array_values(array_filter($events, function ($ts) use ($now) {
            return ($now - (int) $ts) <= $this->long_window;
        }));

        $short_count = 0;
        $long_count = count($events);

        foreach ($events as $ts) {
            if (($now - (int) $ts) <= $this->short_window) {
                $short_count++;
            }
        }

        set_transient($rate_key, $events, $this->long_window);

        if ($short_count > $this->short_limit || $long_count > $this->long_limit) {
            set_transient($block_key, 1, $this->block_duration);
            return false;
        }

        return true;
    }
}
