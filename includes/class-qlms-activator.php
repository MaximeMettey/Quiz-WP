<?php
/**
 * Fired during plugin activation
 */

class QLMS_Activator {

    /**
     * Activation tasks
     */
    public static function activate() {
        global $wpdb;

        $charset_collate = $wpdb->get_charset_collate();

        // Table for quizzes
        $table_quizzes = $wpdb->prefix . 'qlms_quizzes';
        $sql_quizzes = "CREATE TABLE IF NOT EXISTS $table_quizzes (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            title varchar(255) NOT NULL,
            description text,
            course_id bigint(20),
            lesson_id bigint(20),
            time_limit int(11) DEFAULT 0,
            pass_percentage int(11) DEFAULT 70,
            is_free tinyint(1) DEFAULT 1,
            product_id bigint(20),
            attempts_allowed int(11) DEFAULT -1,
            show_correct_answers tinyint(1) DEFAULT 1,
            randomize_questions tinyint(1) DEFAULT 0,
            status varchar(20) DEFAULT 'draft',
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY course_id (course_id),
            KEY lesson_id (lesson_id)
        ) $charset_collate;";

        // Table for questions
        $table_questions = $wpdb->prefix . 'qlms_questions';
        $sql_questions = "CREATE TABLE IF NOT EXISTS $table_questions (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            quiz_id bigint(20) NOT NULL,
            question_text text NOT NULL,
            question_type varchar(50) DEFAULT 'single',
            points int(11) DEFAULT 1,
            sort_order int(11) DEFAULT 0,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY quiz_id (quiz_id)
        ) $charset_collate;";

        // Table for answers
        $table_answers = $wpdb->prefix . 'qlms_answers';
        $sql_answers = "CREATE TABLE IF NOT EXISTS $table_answers (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            question_id bigint(20) NOT NULL,
            answer_text text NOT NULL,
            is_correct tinyint(1) DEFAULT 0,
            sort_order int(11) DEFAULT 0,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY question_id (question_id)
        ) $charset_collate;";

        // Table for user quiz attempts
        $table_attempts = $wpdb->prefix . 'qlms_attempts';
        $sql_attempts = "CREATE TABLE IF NOT EXISTS $table_attempts (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            user_id bigint(20) NOT NULL,
            quiz_id bigint(20) NOT NULL,
            score float DEFAULT 0,
            total_questions int(11) DEFAULT 0,
            correct_answers int(11) DEFAULT 0,
            time_taken int(11) DEFAULT 0,
            passed tinyint(1) DEFAULT 0,
            started_at datetime DEFAULT CURRENT_TIMESTAMP,
            completed_at datetime,
            PRIMARY KEY (id),
            KEY user_id (user_id),
            KEY quiz_id (quiz_id)
        ) $charset_collate;";

        // Table for user answers
        $table_user_answers = $wpdb->prefix . 'qlms_user_answers';
        $sql_user_answers = "CREATE TABLE IF NOT EXISTS $table_user_answers (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            attempt_id bigint(20) NOT NULL,
            question_id bigint(20) NOT NULL,
            answer_id bigint(20),
            answer_text text,
            is_correct tinyint(1) DEFAULT 0,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY attempt_id (attempt_id),
            KEY question_id (question_id)
        ) $charset_collate;";

        // Table for courses
        $table_courses = $wpdb->prefix . 'qlms_courses';
        $sql_courses = "CREATE TABLE IF NOT EXISTS $table_courses (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            post_id bigint(20) NOT NULL,
            is_free tinyint(1) DEFAULT 1,
            product_id bigint(20),
            duration varchar(100),
            level varchar(50),
            certificate_enabled tinyint(1) DEFAULT 0,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY post_id (post_id)
        ) $charset_collate;";

        // Table for lessons
        $table_lessons = $wpdb->prefix . 'qlms_lessons';
        $sql_lessons = "CREATE TABLE IF NOT EXISTS $table_lessons (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            post_id bigint(20) NOT NULL,
            course_id bigint(20) NOT NULL,
            sort_order int(11) DEFAULT 0,
            duration int(11) DEFAULT 0,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY post_id (post_id),
            KEY course_id (course_id)
        ) $charset_collate;";

        // Table for user progress
        $table_progress = $wpdb->prefix . 'qlms_user_progress';
        $sql_progress = "CREATE TABLE IF NOT EXISTS $table_progress (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            user_id bigint(20) NOT NULL,
            course_id bigint(20),
            lesson_id bigint(20),
            quiz_id bigint(20),
            status varchar(50) DEFAULT 'not_started',
            completed_at datetime,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY user_id (user_id),
            KEY course_id (course_id)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

        dbDelta($sql_quizzes);
        dbDelta($sql_questions);
        dbDelta($sql_answers);
        dbDelta($sql_attempts);
        dbDelta($sql_user_answers);
        dbDelta($sql_courses);
        dbDelta($sql_lessons);
        dbDelta($sql_progress);

        // Create default roles and capabilities
        self::create_roles_and_capabilities();

        // Flush rewrite rules
        flush_rewrite_rules();
    }

    /**
     * Create custom roles and capabilities
     */
    private static function create_roles_and_capabilities() {
        // Add capabilities to administrator
        $admin = get_role('administrator');
        if ($admin) {
            $admin->add_cap('manage_qlms');
            $admin->add_cap('edit_quizzes');
            $admin->add_cap('edit_courses');
            $admin->add_cap('view_quiz_results');
        }

        // Add instructor role
        add_role('qlms_instructor', __('QLMS Instructor', 'quiz-lms-wc'), [
            'read' => true,
            'edit_posts' => true,
            'edit_published_posts' => true,
            'publish_posts' => true,
            'upload_files' => true,
            'edit_quizzes' => true,
            'edit_courses' => true,
            'view_quiz_results' => true
        ]);

        // Add student role capabilities
        $subscriber = get_role('subscriber');
        if ($subscriber) {
            $subscriber->add_cap('take_quizzes');
            $subscriber->add_cap('view_own_results');
        }
    }
}
