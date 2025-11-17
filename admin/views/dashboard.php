<?php
/**
 * Admin dashboard view
 */

if (!defined('WPINC')) {
    die;
}
?>

<div class="wrap">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>

    <div class="qlms-dashboard">
        <div class="qlms-stats-grid">
            <div class="qlms-stat-box">
                <div class="qlms-stat-icon dashicons dashicons-welcome-learn-more"></div>
                <div class="qlms-stat-content">
                    <h3><?php echo esc_html($quizzes_count); ?></h3>
                    <p><?php esc_html_e('Total Quizzes', 'quiz-lms-wc'); ?></p>
                </div>
            </div>

            <div class="qlms-stat-box">
                <div class="qlms-stat-icon dashicons dashicons-book"></div>
                <div class="qlms-stat-content">
                    <h3><?php echo esc_html($courses_count); ?></h3>
                    <p><?php esc_html_e('Total Courses', 'quiz-lms-wc'); ?></p>
                </div>
            </div>

            <div class="qlms-stat-box">
                <div class="qlms-stat-icon dashicons dashicons-media-document"></div>
                <div class="qlms-stat-content">
                    <h3><?php echo esc_html($lessons_count); ?></h3>
                    <p><?php esc_html_e('Total Lessons', 'quiz-lms-wc'); ?></p>
                </div>
            </div>

            <div class="qlms-stat-box">
                <div class="qlms-stat-icon dashicons dashicons-yes-alt"></div>
                <div class="qlms-stat-content">
                    <h3><?php echo esc_html($attempts_count); ?></h3>
                    <p><?php esc_html_e('Quiz Attempts', 'quiz-lms-wc'); ?></p>
                </div>
            </div>
        </div>

        <div class="qlms-quick-links">
            <h2><?php esc_html_e('Quick Actions', 'quiz-lms-wc'); ?></h2>
            <div class="qlms-links-grid">
                <a href="<?php echo admin_url('admin.php?page=qlms-add-quiz'); ?>" class="qlms-quick-link">
                    <span class="dashicons dashicons-plus-alt"></span>
                    <?php esc_html_e('Create New Quiz', 'quiz-lms-wc'); ?>
                </a>
                <a href="<?php echo admin_url('post-new.php?post_type=qlms_course'); ?>" class="qlms-quick-link">
                    <span class="dashicons dashicons-plus-alt"></span>
                    <?php esc_html_e('Create New Course', 'quiz-lms-wc'); ?>
                </a>
                <a href="<?php echo admin_url('admin.php?page=qlms-results'); ?>" class="qlms-quick-link">
                    <span class="dashicons dashicons-chart-bar"></span>
                    <?php esc_html_e('View Results', 'quiz-lms-wc'); ?>
                </a>
                <a href="<?php echo admin_url('admin.php?page=qlms-settings'); ?>" class="qlms-quick-link">
                    <span class="dashicons dashicons-admin-settings"></span>
                    <?php esc_html_e('Settings', 'quiz-lms-wc'); ?>
                </a>
            </div>
        </div>
    </div>
</div>
