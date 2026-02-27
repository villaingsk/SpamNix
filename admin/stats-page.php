<?php
if (! defined('ABSPATH')) {
    exit;
}

$spamnix_stats = get_option('spamnix_stats', array('total_blocked' => 0));
?>
<div class="wrap spamnix-admin">
    <h1><?php echo esc_html(spamnix_lang('SpamNix Stats', 'Statistik SpamNix')); ?></h1>

    <div class="spamnix-card">
        <h2><?php echo esc_html(spamnix_lang('Total Blocked', 'Total Diblokir')); ?></h2>
        <p class="spamnix-big"><?php echo esc_html((string) ($spamnix_stats['total_blocked'] ?? 0)); ?></p>
    </div>

    <h2><?php echo esc_html(spamnix_lang('By Reason', 'Berdasarkan Alasan')); ?></h2>
    <table class="widefat striped">
        <thead>
            <tr>
                <th><?php echo esc_html(spamnix_lang('Reason', 'Alasan')); ?></th>
                <th><?php echo esc_html(spamnix_lang('Count', 'Jumlah')); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($spamnix_stats as $spamnix_reason => $spamnix_count) : ?>
                <?php if ($spamnix_reason === 'total_blocked') { continue; } ?>
                <tr>
                    <td><?php echo esc_html($spamnix_reason); ?></td>
                    <td><?php echo esc_html((string) $spamnix_count); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <h2><?php echo esc_html(spamnix_lang('PRO Features', 'Fitur PRO')); ?></h2>
    <ul>
        <li><?php echo esc_html(spamnix_lang('Advanced spam statistics dashboard', 'Dashboard statistik spam lanjutan')); ?></li>
        <li><?php echo esc_html(spamnix_lang('Auto IP block intelligence', 'Auto IP block intelligence')); ?></li>
        <li><?php echo esc_html(spamnix_lang('Country filter', 'Filter negara')); ?></li>
        <li><?php echo esc_html(spamnix_lang('Detailed log viewer', 'Log viewer detail')); ?></li>
        <li><?php echo esc_html(spamnix_lang('Cloudflare API integration for automatic IP blocking', 'Integrasi Cloudflare API untuk auto block IP')); ?></li>
    </ul>

    <?php if (! spamnix_is_pro()) : ?>
        <p><strong><?php echo esc_html(spamnix_lang('Status:', 'Status:')); ?></strong> <?php echo esc_html(spamnix_lang('PRO is not active yet (placeholder).', 'PRO belum aktif (placeholder).')); ?></p>
    <?php endif; ?>
</div>
