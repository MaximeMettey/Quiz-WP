<?php
/**
 * Course template
 */

if (!defined('WPINC')) {
    die;
}
?>

<div class="qlms-course-container" data-course-id="<?php echo esc_attr($course->id); ?>">
    <div class="qlms-course-header">
        <div class="qlms-course-meta">
            <?php if ($course->duration): ?>
                <span class="qlms-course-duration">
                    <span class="dashicons dashicons-clock"></span>
                    <?php echo esc_html($course->duration); ?>
                </span>
            <?php endif; ?>

            <span class="qlms-course-level">
                <span class="dashicons dashicons-awards"></span>
                <?php echo esc_html(ucfirst($course->level)); ?>
            </span>

            <span class="qlms-course-lessons-count">
                <span class="dashicons dashicons-list-view"></span>
                <?php printf(_n('%d Lesson', '%d Lessons', count($lessons), 'quiz-lms-wc'), count($lessons)); ?>
            </span>
        </div>

        <div class="qlms-course-progress">
            <div class="qlms-progress-bar">
                <div class="qlms-progress-fill" style="width: <?php echo esc_attr($progress); ?>%;"></div>
            </div>
            <span class="qlms-progress-text"><?php printf(__('%d%% Complete', 'quiz-lms-wc'), round($progress)); ?></span>
        </div>
    </div>

    <div class="qlms-course-lessons">
        <h3><?php _e('Course Lessons', 'quiz-lms-wc'); ?></h3>
        <div class="qlms-lessons-list">
            <?php foreach ($lessons as $index => $lesson):
                $lesson_post = get_post($lesson->post_id);
                $is_completed = QLMS_Lesson::is_completed($lesson->id, $user_id);
            ?>
                <div class="qlms-lesson-item <?php echo $is_completed ? 'completed' : ''; ?>">
                    <div class="qlms-lesson-number"><?php echo ($index + 1); ?></div>
                    <div class="qlms-lesson-content">
                        <h4 class="qlms-lesson-title">
                            <a href="<?php echo get_permalink($lesson->post_id); ?>">
                                <?php echo esc_html($lesson_post->post_title); ?>
                            </a>
                        </h4>
                        <?php if ($lesson->duration > 0): ?>
                            <span class="qlms-lesson-duration">
                                <?php printf(__('%d min', 'quiz-lms-wc'), $lesson->duration); ?>
                            </span>
                        <?php endif; ?>
                    </div>
                    <div class="qlms-lesson-status">
                        <?php if ($is_completed): ?>
                            <span class="dashicons dashicons-yes-alt"></span>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <?php
    $quizzes = QLMS_Course::get_quizzes($course->id);
    if (!empty($quizzes)):
    ?>
        <div class="qlms-course-quizzes">
            <h3><?php _e('Course Quizzes', 'quiz-lms-wc'); ?></h3>
            <div class="qlms-quizzes-list">
                <?php foreach ($quizzes as $quiz): ?>
                    <div class="qlms-quiz-item">
                        <h4><?php echo esc_html($quiz->title); ?></h4>
                        <p><?php echo esc_html($quiz->description); ?></p>
                        <a href="?quiz_id=<?php echo esc_attr($quiz->id); ?>" class="button">
                            <?php _e('Take Quiz', 'quiz-lms-wc'); ?>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>
</div>
