<?php
/**
 * Shortcodes class
 */

class QLMS_Shortcodes {

    /**
     * Register all shortcodes
     */
    public function register_shortcodes() {
        add_shortcode('qlms_quiz', array($this, 'quiz_shortcode'));
        add_shortcode('qlms_course', array($this, 'course_shortcode'));
        add_shortcode('qlms_courses', array($this, 'courses_list_shortcode'));
        add_shortcode('qlms_user_dashboard', array($this, 'user_dashboard_shortcode'));
        add_shortcode('qlms_lesson', array($this, 'lesson_shortcode'));
    }

    /**
     * Quiz shortcode
     * Usage: [qlms_quiz id="1"]
     */
    public function quiz_shortcode($atts) {
        $atts = shortcode_atts(array(
            'id' => 0,
        ), $atts);

        $quiz_id = intval($atts['id']);

        if (!$quiz_id) {
            return '<p>' . __('Invalid quiz ID', 'quiz-lms-wc') . '</p>';
        }

        $quiz = QLMS_Quiz::get($quiz_id);

        if (!$quiz) {
            return '<p>' . __('Quiz not found', 'quiz-lms-wc') . '</p>';
        }

        if (!is_user_logged_in()) {
            return '<p>' . __('Please log in to take this quiz.', 'quiz-lms-wc') . ' <a href="' . wp_login_url(get_permalink()) . '">' . __('Log in', 'quiz-lms-wc') . '</a></p>';
        }

        $user_id = get_current_user_id();

        if (!QLMS_Quiz::user_can_take($quiz_id, $user_id)) {
            if (!$quiz->is_free && $quiz->product_id) {
                $product = wc_get_product($quiz->product_id);
                return '<p>' . __('You need to purchase this quiz to access it.', 'quiz-lms-wc') . ' <a href="' . get_permalink($quiz->product_id) . '">' . __('Purchase Now', 'quiz-lms-wc') . '</a></p>';
            }
            return '<p>' . __('You do not have access to this quiz.', 'quiz-lms-wc') . '</p>';
        }

        ob_start();
        include QLMS_PLUGIN_DIR . 'templates/quiz.php';
        return ob_get_clean();
    }

    /**
     * Course shortcode
     * Usage: [qlms_course id="1"]
     */
    public function course_shortcode($atts) {
        $atts = shortcode_atts(array(
            'id' => 0,
        ), $atts);

        $post_id = intval($atts['id']);

        if (!$post_id) {
            $post_id = get_the_ID();
        }

        $course = QLMS_Course::get_by_post($post_id);

        if (!$course) {
            return '<p>' . __('Course not found', 'quiz-lms-wc') . '</p>';
        }

        if (!is_user_logged_in()) {
            return '<p>' . __('Please log in to access this course.', 'quiz-lms-wc') . ' <a href="' . wp_login_url(get_permalink()) . '">' . __('Log in', 'quiz-lms-wc') . '</a></p>';
        }

        $user_id = get_current_user_id();

        if (!QLMS_Course::user_has_access($course->id, $user_id)) {
            if (!$course->is_free && $course->product_id) {
                return '<p>' . __('You need to purchase this course to access it.', 'quiz-lms-wc') . ' <a href="' . get_permalink($course->product_id) . '">' . __('Purchase Now', 'quiz-lms-wc') . '</a></p>';
            }
            return '<p>' . __('You do not have access to this course.', 'quiz-lms-wc') . '</p>';
        }

        $lessons = QLMS_Course::get_lessons($course->id);
        $progress = QLMS_Course::get_user_progress($course->id, $user_id);

        ob_start();
        include QLMS_PLUGIN_DIR . 'templates/course.php';
        return ob_get_clean();
    }

    /**
     * Courses list shortcode
     * Usage: [qlms_courses category="beginner" limit="10"]
     */
    public function courses_list_shortcode($atts) {
        $atts = shortcode_atts(array(
            'category' => '',
            'limit' => -1,
            'orderby' => 'date',
            'order' => 'DESC',
        ), $atts);

        $args = array(
            'post_type' => 'qlms_course',
            'posts_per_page' => intval($atts['limit']),
            'orderby' => $atts['orderby'],
            'order' => $atts['order'],
            'post_status' => 'publish',
        );

        if (!empty($atts['category'])) {
            $args['tax_query'] = array(
                array(
                    'taxonomy' => 'qlms_course_category',
                    'field' => 'slug',
                    'terms' => $atts['category'],
                ),
            );
        }

        $courses = get_posts($args);

        ob_start();
        include QLMS_PLUGIN_DIR . 'templates/courses-list.php';
        return ob_get_clean();
    }

    /**
     * User dashboard shortcode
     * Usage: [qlms_user_dashboard]
     */
    public function user_dashboard_shortcode($atts) {
        if (!is_user_logged_in()) {
            return '<p>' . __('Please log in to view your dashboard.', 'quiz-lms-wc') . ' <a href="' . wp_login_url(get_permalink()) . '">' . __('Log in', 'quiz-lms-wc') . '</a></p>';
        }

        $user_id = get_current_user_id();
        $stats = QLMS_User_Progress::get_user_stats($user_id);
        $enrolled_courses = QLMS_User_Progress::get_user_courses($user_id);
        $recent_activity = QLMS_User_Progress::get_recent_activity($user_id);

        ob_start();
        include QLMS_PLUGIN_DIR . 'templates/user-dashboard.php';
        return ob_get_clean();
    }

    /**
     * Lesson shortcode
     * Usage: [qlms_lesson id="1"]
     */
    public function lesson_shortcode($atts) {
        $atts = shortcode_atts(array(
            'id' => 0,
        ), $atts);

        $post_id = intval($atts['id']);

        if (!$post_id) {
            $post_id = get_the_ID();
        }

        $lesson = QLMS_Lesson::get_by_post($post_id);

        if (!$lesson) {
            return '<p>' . __('Lesson not found', 'quiz-lms-wc') . '</p>';
        }

        $course = QLMS_Course::get($lesson->course_id);
        $user_id = get_current_user_id();

        if (!QLMS_Course::user_has_access($lesson->course_id, $user_id)) {
            return '<p>' . __('You do not have access to this lesson.', 'quiz-lms-wc') . '</p>';
        }

        $next_lesson = QLMS_Lesson::get_next($lesson->id);
        $prev_lesson = QLMS_Lesson::get_previous($lesson->id);
        $is_completed = QLMS_Lesson::is_completed($lesson->id, $user_id);

        ob_start();
        include QLMS_PLUGIN_DIR . 'templates/lesson.php';
        return ob_get_clean();
    }
}
