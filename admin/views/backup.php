<?php
if (!defined('ABSPATH')) exit;

if (!current_user_can('manage_options')) {
    wp_die(__('You do not have sufficient permissions.'));
}

$nonce = wp_create_nonce('wpsi_backup_export');
?>

<div class="wrap">
    <h1 class="wp-heading-inline">WP Site Backup & Restore</h1>
    <hr class="wp-header-end">

    <div class="postbox-container" style="max-width: 800px;">
        <div class="metabox-holder">

            <!-- Backup Card -->
            <div class="postbox">
                <h2 class="hndle"><span>Download Full Site Backup</span></h2>
                <div class="inside">
                    <p>This will export your entire WordPress site including database and files into a ZIP archive.</p>
                    <form method="post" action="<?php echo admin_url('admin-post.php'); ?>">
                        <input type="hidden" name="action" value="wpsi_export_backup">
                        <?php wp_nonce_field('wpsi_backup_export'); ?>
                        <p>
                            <input type="submit" class="button button-primary" value="⬇ Download Full Backup ZIP">
                        </p>
                    </form>
                </div>
            </div>

            <!-- Restore Card -->
            <div class="postbox">
                <h2 class="hndle"><span>Restore from Backup</span></h2>
                <div class="inside">

                    <?php if (isset($_GET['import']) && $_GET['import'] === 'success') : ?>
                        <div class="notice notice-success is-dismissible">
                            <p> Backup successfully imported!</p>
                        </div>
                    <?php endif; ?>

                    <p>Select a backup ZIP file to restore your site. This will overwrite your existing database and files.</p>
                    <form method="post" enctype="multipart/form-data" action="<?php echo admin_url('admin-post.php'); ?>">
                        <input type="hidden" name="action" value="wpsi_import_backup">
                        <?php wp_nonce_field('wpsi_import_backup'); ?>
                        <p>
                            <input type="file" name="backup_file" accept=".zip" required class="regular-text">
                        </p>
                        <p>
                            <input type="submit" class="button button-secondary" value="Import Backup ZIP">
                        </p>
                    </form>
                </div>
            </div>

        </div>
    </div>
</div>
