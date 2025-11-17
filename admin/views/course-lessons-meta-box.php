<?php
/**
 * Course lessons meta box view
 */

if (!defined('WPINC')) {
    die;
}
?>

<div class="qlms-course-lessons-meta-box">
    <p><em><?php _e('Lessons are managed separately. Create lessons and link them to this course using the Lesson Settings.', 'quiz-lms-wc'); ?></em></p>

    <?php if (!empty($lessons)): ?>
        <h4><?php _e('Current Lessons', 'quiz-lms-wc'); ?></h4>
        <ol>
            <?php foreach ($lessons as $lesson):
                $lesson_post = get_post($lesson->post_id);
            ?>
                <li>
                    <a href="<?php echo get_edit_post_link($lesson->post_id); ?>">
                        <?php echo esc_html($lesson_post->post_title); ?>
                    </a>
                    (<?php printf(__('Order: %d', 'quiz-lms-wc'), $lesson->sort_order); ?>)
                </li>
            <?php endforeach; ?>
        </ol>
    <?php else: ?>
        <p><?php _e('No lessons linked to this course yet.', 'quiz-lms-wc'); ?></p>
    <?php endif; ?>

    <p>
        <a href="<?php echo admin_url('post-new.php?post_type=qlms_lesson'); ?>" class="button">
            <?php _e('Add New Lesson', 'quiz-lms-wc'); ?>
        </a>
    </p>
</div>
