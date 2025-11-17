<?php
/**
 * Settings page view
 */

if (!defined('WPINC')) {
    die;
}
?>

<div class="wrap">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>

    <form method="post" action="options.php">
        <?php
        settings_fields('qlms_settings');
        do_settings_sections('qlms_settings');
        submit_button();
        ?>
    </form>
</div>
