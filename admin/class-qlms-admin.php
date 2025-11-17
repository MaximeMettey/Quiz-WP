<?php
/**
 * The admin-specific functionality of the plugin
 */

class QLMS_Admin {

    private $plugin_name;
    private $version;

    public function __construct($plugin_name, $version) {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
    }

    /**
     * Register the stylesheets for the admin area
     */
    public function enqueue_styles() {
        wp_enqueue_style(
            $this->plugin_name,
            QLMS_PLUGIN_URL . 'assets/css/admin.css',
            array(),
            $this->version,
            'all'
        );
    }

    /**
     * Register the JavaScript for the admin area
     */
    public function enqueue_scripts() {
        wp_enqueue_script(
            $this->plugin_name,
            QLMS_PLUGIN_URL . 'assets/js/admin.js',
            array('jquery', 'jquery-ui-sortable'),
            $this->version,
            false
        );

        wp_localize_script($this->plugin_name, 'qlms_admin', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('qlms_admin_nonce'),
            'strings' => array(
                'confirm_delete' => __('Are you sure you want to delete this item?', 'quiz-lms-wc'),
                'saving' => __('Saving...', 'quiz-lms-wc'),
                'saved' => __('Saved!', 'quiz-lms-wc'),
                'error' => __('An error occurred', 'quiz-lms-wc'),
            ),
        ));
    }

    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        add_menu_page(
            __('Quiz & LMS', 'quiz-lms-wc'),
            __('Quiz & LMS', 'quiz-lms-wc'),
            'manage_options',
            'qlms',
            array($this, 'display_admin_page'),
            'dashicons-welcome-learn-more',
            30
        );

        add_submenu_page(
            'qlms',
            __('Dashboard', 'quiz-lms-wc'),
            __('Dashboard', 'quiz-lms-wc'),
            'manage_options',
            'qlms',
            array($this, 'display_admin_page')
        );

        add_submenu_page(
            'qlms',
            __('Quizzes', 'quiz-lms-wc'),
            __('Quizzes', 'quiz-lms-wc'),
            'manage_options',
            'qlms-quizzes',
            array($this, 'display_quizzes_page')
        );

        add_submenu_page(
            'qlms',
            __('Add New Quiz', 'quiz-lms-wc'),
            __('Add New Quiz', 'quiz-lms-wc'),
            'manage_options',
            'qlms-add-quiz',
            array($this, 'display_add_quiz_page')
        );

        add_submenu_page(
            'qlms',
            __('Quiz Results', 'quiz-lms-wc'),
            __('Quiz Results', 'quiz-lms-wc'),
            'manage_options',
            'qlms-results',
            array($this, 'display_results_page')
        );

        add_submenu_page(
            'qlms',
            __('Settings', 'quiz-lms-wc'),
            __('Settings', 'quiz-lms-wc'),
            'manage_options',
            'qlms-settings',
            array($this, 'display_settings_page')
        );
    }

    /**
     * Display admin dashboard page
     */
    public function display_admin_page() {
        global $wpdb;

        // Get statistics
        $quizzes_count = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}qlms_quizzes");
        $courses_count = wp_count_posts('qlms_course')->publish;
        $lessons_count = wp_count_posts('qlms_lesson')->publish;
        $attempts_count = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}qlms_attempts WHERE completed_at IS NOT NULL");

        include QLMS_PLUGIN_DIR . 'admin/views/dashboard.php';
    }

    /**
     * Display quizzes page
     */
    public function display_quizzes_page() {
        $quizzes = QLMS_Quiz::get_all();
        include QLMS_PLUGIN_DIR . 'admin/views/quizzes-list.php';
    }

    /**
     * Display add/edit quiz page
     */
    public function display_add_quiz_page() {
        $quiz_id = isset($_GET['quiz_id']) ? intval($_GET['quiz_id']) : 0;
        $quiz = $quiz_id ? QLMS_Quiz::get($quiz_id) : null;
        $questions = $quiz_id ? QLMS_Quiz::get_questions($quiz_id) : array();

        include QLMS_PLUGIN_DIR . 'admin/views/quiz-edit.php';
    }

    /**
     * Display results page
     */
    public function display_results_page() {
        global $wpdb;

        $attempts = $wpdb->get_results("
            SELECT a.*, q.title as quiz_title, u.display_name as user_name
            FROM {$wpdb->prefix}qlms_attempts a
            LEFT JOIN {$wpdb->prefix}qlms_quizzes q ON a.quiz_id = q.id
            LEFT JOIN {$wpdb->users} u ON a.user_id = u.ID
            WHERE a.completed_at IS NOT NULL
            ORDER BY a.completed_at DESC
            LIMIT 100
        ");

        include QLMS_PLUGIN_DIR . 'admin/views/results.php';
    }

    /**
     * Display settings page
     */
    public function display_settings_page() {
        include QLMS_PLUGIN_DIR . 'admin/views/settings.php';
    }
}
