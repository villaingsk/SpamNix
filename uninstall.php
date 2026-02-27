<?php
if (! defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

delete_option('spamnix_settings');
delete_option('spamnix_stats');
