<?php
    if (! defined('ABSPATH')) {
        exit;
    }

    if (! current_user_can('manage_options')) {
        wp_die(esc_html__('You do not have sufficient permissions.', 'wp-site-inspector'));
    }

    $nonce = wp_create_nonce('wpsi_backup_export');
?>

<div class="wrap">
    <h1 class="wp-heading-inline"><?php echo esc_html__('WP Site Backup & Restore', 'wp-site-inspector'); ?></h1>
    <hr class="wp-header-end">

    <div class="postbox-container" style="max-width: 800px;">
        <div class="metabox-holder">

            <!-- Backup Card -->
            <div class="postbox">
                <h2 class="hndle"><span><?php echo esc_html__('Download Full Site Backup', 'wp-site-inspector'); ?></span></h2>
                <div class="inside">
                    <p><?php echo esc_html__('This will export your entire WordPress site including database and files into a ZIP archive.', 'wp-site-inspector'); ?></p>
                    <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                        <input type="hidden" name="action" value="wpsi_export_backup">
                        <?php wp_nonce_field('wpsi_backup_export'); ?>
                        <p>
                            <input type="submit" class="button button-primary" value="<?php echo esc_attr__('⬇ Download Full Backup ZIP', 'wp-site-inspector'); ?>">
                        </p>
                    </form>
                </div>
            </div>

            <!-- Restore Card -->
            <!-- <div class="postbox">
                <h2 class="hndle"><span><?php echo esc_html__('Restore from Backup', 'wp-site-inspector'); ?></span></h2>
                <div class="inside">

                    <?php if (isset($_GET['import']) && $_GET['import'] === 'success'): ?>
                        <div class="notice notice-success is-dismissible">
                            <p><?php echo esc_html__('Backup successfully imported!', 'wp-site-inspector'); ?></p>
                        </div>
                    <?php endif; ?>

                    <p><?php echo esc_html__('Select a backup ZIP file to restore your site. This will overwrite your existing database and files.', 'wp-site-inspector'); ?></p>
                    <form method="post" enctype="multipart/form-data" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                        <input type="hidden" name="action" value="wpsi_import_backup">
                        <?php wp_nonce_field('wpsi_import_backup'); ?>
                        <p>
                            <input type="file" name="backup_file" accept=".zip" required class="regular-text">
                        </p>
                        <p>
                            <input type="submit" class="button button-secondary" value="<?php echo esc_attr__('Import Backup ZIP', 'wp-site-inspector'); ?>">
                        </p>
                    </form>
                </div>
            </div> -->

        </div>
    </div>
</div>
