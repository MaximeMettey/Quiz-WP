<?php
/**
 * User dashboard template
 */

if (!defined('WPINC')) {
    die;
}

$user = wp_get_current_user();
?>

<div class="qlms-user-dashboard">
    <div class="qlms-dashboard-header">
        <h2><?php printf(__('Welcome back, %s!', 'quiz-lms-wc'), $user->display_name); ?></h2>
    </div>

    <div class="qlms-dashboard-stats">
        <div class="qlms-stat-box">
            <div class="qlms-stat-icon dashicons dashicons-book"></div>
            <div class="qlms-stat-content">
                <h3><?php echo esc_html($stats['enrolled_courses']); ?></h3>
                <p><?php _e('Enrolled Courses', 'quiz-lms-wc'); ?></p>
            </div>
        </div>

        <div class="qlms-stat-box">
            <div class="qlms-stat-icon dashicons dashicons-yes-alt"></div>
            <div class="qlms-stat-content">
                <h3><?php echo esc_html($stats['completed_courses']); ?></h3>
                <p><?php _e('Completed Courses', 'quiz-lms-wc'); ?></p>
            </div>
        </div>

        <div class="qlms-stat-box">
            <div class="qlms-stat-icon dashicons dashicons-welcome-learn-more"></div>
            <div class="qlms-stat-content">
                <h3><?php echo esc_html($stats['quiz_attempts']); ?></h3>
                <p><?php _e('Quiz Attempts', 'quiz-lms-wc'); ?></p>
            </div>
        </div>

        <div class="qlms-stat-box">
            <div class="qlms-stat-icon dashicons dashicons-chart-bar"></div>
            <div class="qlms-stat-content">
                <h3><?php echo esc_html(round($stats['average_score'], 1)); ?>%</h3>
                <p><?php _e('Average Score', 'quiz-lms-wc'); ?></p>
            </div>
        </div>
    </div>

    <div class="qlms-dashboard-courses">
        <h3><?php _e('My Courses', 'quiz-lms-wc'); ?></h3>
        <?php if (!empty($enrolled_courses)): ?>
            <div class="qlms-courses-grid">
                <?php foreach ($enrolled_courses as $enrolled_course):
                    $course = QLMS_Course::get($enrolled_course->course_id);
                    if (!$course) continue;
                    $course_post = get_post($course->post_id);
                    $progress = QLMS_Course::get_user_progress($course->id, $user_id);
                ?>
                    <div class="qlms-course-card">
                        <?php if (has_post_thumbnail($course->post_id)): ?>
                            <div class="qlms-course-thumbnail">
                                <?php echo get_the_post_thumbnail($course->post_id, 'medium'); ?>
                            </div>
                        <?php endif; ?>
                        <div class="qlms-course-card-content">
                            <h4>
                                <a href="<?php echo get_permalink($course->post_id); ?>">
                                    <?php echo esc_html($course_post->post_title); ?>
                                </a>
                            </h4>
                            <div class="qlms-course-progress">
                                <div class="qlms-progress-bar">
                                    <div class="qlms-progress-fill" style="width: <?php echo esc_attr($progress); ?>%;"></div>
                                </div>
                                <span><?php printf(__('%d%% Complete', 'quiz-lms-wc'), round($progress)); ?></span>
                            </div>
                            <a href="<?php echo get_permalink($course->post_id); ?>" class="button">
                                <?php _e('Continue Learning', 'quiz-lms-wc'); ?>
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p><?php _e('You are not enrolled in any courses yet.', 'quiz-lms-wc'); ?></p>
            <a href="<?php echo get_post_type_archive_link('qlms_course'); ?>" class="button">
                <?php _e('Browse Courses', 'quiz-lms-wc'); ?>
            </a>
        <?php endif; ?>
    </div>
</div>
