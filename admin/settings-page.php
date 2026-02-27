<?php
if (! defined('ABSPATH')) {
    exit;
}
?>
<div class="wrap spamnix-admin">
    <h1><?php echo esc_html(spamnix_lang('SpamNix Settings', 'Pengaturan SpamNix')); ?></h1>
    <p><?php echo esc_html(spamnix_lang('Lightweight comment anti-spam configuration with no external service dependency.', 'Konfigurasi anti spam komentar ringan tanpa ketergantungan layanan eksternal.')); ?></p>

    <form method="post" action="options.php">
        <?php settings_fields('spamnix_settings_group'); ?>
        <?php do_settings_sections('spamnix'); ?>
        <?php submit_button(spamnix_lang('Save Settings', 'Simpan Pengaturan')); ?>
    </form>
</div>
