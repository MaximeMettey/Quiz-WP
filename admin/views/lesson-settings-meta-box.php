<?php
/**
 * Lesson settings meta box view
 */

if (!defined('WPINC')) {
    die;
}
?>

<p>
    <label for="qlms_course_id"><strong><?php esc_html_e('Course', 'quiz-lms-wc'); ?></strong></label><br>
    <select id="qlms_course_id" name="qlms_course_id" style="width: 100%;">
        <option value=""><?php esc_html_e('Select Course', 'quiz-lms-wc'); ?></option>
        <?php foreach ($courses as $course): ?>
            <option value="<?php echo esc_attr($course->ID); ?>" <?php selected($course_id, $course->ID); ?>>
                <?php echo esc_html($course->post_title); ?>
            </option>
        <?php endforeach; ?>
    </select>
</p>

<p>
    <label for="qlms_duration"><strong><?php esc_html_e('Duration (minutes)', 'quiz-lms-wc'); ?></strong></label><br>
    <input type="number" id="qlms_duration" name="qlms_duration" value="<?php echo esc_attr($duration); ?>" style="width: 100%;" min="0" />
</p>

<p>
    <label for="qlms_sort_order"><strong><?php esc_html_e('Order', 'quiz-lms-wc'); ?></strong></label><br>
    <input type="number" id="qlms_sort_order" name="qlms_sort_order" value="<?php echo esc_attr($sort_order); ?>" style="width: 100%;" min="0" />
    <span class="description"><?php esc_html_e('Lower numbers appear first', 'quiz-lms-wc'); ?></span>
</p>
