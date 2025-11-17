<?php
/**
 * Course settings meta box view
 */

if (!defined('WPINC')) {
    die;
}
?>

<table class="form-table">
    <tr>
        <th><label for="qlms_is_free"><?php esc_html_e('Free Course', 'quiz-lms-wc'); ?></label></th>
        <td>
            <input type="checkbox" id="qlms_is_free" name="qlms_is_free" value="1" <?php checked($is_free, 1); ?> />
            <p class="description"><?php esc_html_e('Check if this course is free for all users', 'quiz-lms-wc'); ?></p>
        </td>
    </tr>

    <?php if (class_exists('WooCommerce')): ?>
    <tr class="qlms_product_row" <?php echo $is_free ? 'style="display:none;"' : ''; ?>>
        <th><label for="qlms_product_id"><?php esc_html_e('WooCommerce Product', 'quiz-lms-wc'); ?></label></th>
        <td>
            <select id="qlms_product_id" name="qlms_product_id">
                <option value=""><?php esc_html_e('Select Product', 'quiz-lms-wc'); ?></option>
                <?php
                $products = get_posts(array(
                    'post_type' => 'product',
                    'posts_per_page' => -1,
                    'orderby' => 'title',
                    'order' => 'ASC',
                ));

                foreach ($products as $product) {
                    printf(
                        '<option value="%d" %s>%s</option>',
                        $product->ID,
                        selected($product_id, $product->ID, false),
                        esc_html($product->post_title)
                    );
                }
                ?>
            </select>
            <p class="description"><?php esc_html_e('Select the WooCommerce product required to access this course', 'quiz-lms-wc'); ?></p>
        </td>
    </tr>
    <?php endif; ?>

    <tr>
        <th><label for="qlms_duration"><?php esc_html_e('Duration', 'quiz-lms-wc'); ?></label></th>
        <td>
            <input type="text" id="qlms_duration" name="qlms_duration" value="<?php echo esc_attr($duration); ?>" class="regular-text" />
            <p class="description"><?php esc_html_e('Course duration (e.g., "4 weeks", "2 months")', 'quiz-lms-wc'); ?></p>
        </td>
    </tr>

    <tr>
        <th><label for="qlms_level"><?php esc_html_e('Level', 'quiz-lms-wc'); ?></label></th>
        <td>
            <select id="qlms_level" name="qlms_level">
                <option value="beginner" <?php selected($level, 'beginner'); ?>><?php esc_html_e('Beginner', 'quiz-lms-wc'); ?></option>
                <option value="intermediate" <?php selected($level, 'intermediate'); ?>><?php esc_html_e('Intermediate', 'quiz-lms-wc'); ?></option>
                <option value="advanced" <?php selected($level, 'advanced'); ?>><?php esc_html_e('Advanced', 'quiz-lms-wc'); ?></option>
            </select>
        </td>
    </tr>

    <tr>
        <th><label for="qlms_certificate_enabled"><?php esc_html_e('Enable Certificate', 'quiz-lms-wc'); ?></label></th>
        <td>
            <input type="checkbox" id="qlms_certificate_enabled" name="qlms_certificate_enabled" value="1" <?php checked($certificate_enabled, 1); ?> />
            <p class="description"><?php esc_html_e('Award a certificate upon course completion', 'quiz-lms-wc'); ?></p>
        </td>
    </tr>
</table>

<script>
jQuery(document).ready(function($) {
    $('#qlms_is_free').on('change', function() {
        if ($(this).is(':checked')) {
            $('.qlms_product_row').hide();
        } else {
            $('.qlms_product_row').show();
        }
    });
});
</script>
