<?php
/**
 * The core plugin class
 */

class QLMS {

    /**
     * The loader that's responsible for maintaining and registering all hooks
     */
    protected $loader;

    /**
     * The unique identifier of this plugin
     */
    protected $plugin_name;

    /**
     * The current version of the plugin
     */
    protected $version;

    /**
     * Define the core functionality of the plugin
     */
    public function __construct() {
        $this->version = QLMS_VERSION;
        $this->plugin_name = 'quiz-lms-wc';

        $this->load_dependencies();
        $this->set_locale();
        $this->define_admin_hooks();
        $this->define_public_hooks();
        $this->register_post_types();
        $this->register_taxonomies();
    }

    /**
     * Load the required dependencies for this plugin
     */
    private function load_dependencies() {
        // Core classes
        require_once QLMS_PLUGIN_DIR . 'includes/class-qlms-loader.php';
        require_once QLMS_PLUGIN_DIR . 'includes/class-qlms-i18n.php';

        // Database classes
        require_once QLMS_PLUGIN_DIR . 'includes/class-qlms-quiz.php';
        require_once QLMS_PLUGIN_DIR . 'includes/class-qlms-question.php';
        require_once QLMS_PLUGIN_DIR . 'includes/class-qlms-course.php';
        require_once QLMS_PLUGIN_DIR . 'includes/class-qlms-lesson.php';
        require_once QLMS_PLUGIN_DIR . 'includes/class-qlms-user-progress.php';

        // Admin classes
        require_once QLMS_PLUGIN_DIR . 'admin/class-qlms-admin.php';
        require_once QLMS_PLUGIN_DIR . 'admin/class-qlms-quiz-admin.php';
        require_once QLMS_PLUGIN_DIR . 'admin/class-qlms-course-admin.php';

        // Public classes
        require_once QLMS_PLUGIN_DIR . 'public/class-qlms-public.php';
        require_once QLMS_PLUGIN_DIR . 'public/class-qlms-shortcodes.php';

        // WooCommerce integration
        require_once QLMS_PLUGIN_DIR . 'includes/class-qlms-woocommerce.php';

        // Post types
        require_once QLMS_PLUGIN_DIR . 'includes/class-qlms-post-types.php';

        $this->loader = new QLMS_Loader();
    }

    /**
     * Define the locale for this plugin for internationalization
     */
    private function set_locale() {
        $plugin_i18n = new QLMS_i18n();
        $this->loader->add_action('plugins_loaded', $plugin_i18n, 'load_plugin_textdomain');
    }

    /**
     * Register all of the hooks related to the admin area functionality
     */
    private function define_admin_hooks() {
        $plugin_admin = new QLMS_Admin($this->get_plugin_name(), $this->get_version());

        $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_styles');
        $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts');
        $this->loader->add_action('admin_menu', $plugin_admin, 'add_admin_menu');

        // Quiz admin
        $quiz_admin = new QLMS_Quiz_Admin();
        $this->loader->add_action('admin_init', $quiz_admin, 'register_settings');

        // Course admin
        $course_admin = new QLMS_Course_Admin();
        $this->loader->add_action('add_meta_boxes', $course_admin, 'add_meta_boxes');
        $this->loader->add_action('save_post', $course_admin, 'save_course_meta');
    }

    /**
     * Register all of the hooks related to the public-facing functionality
     */
    private function define_public_hooks() {
        $plugin_public = new QLMS_Public($this->get_plugin_name(), $this->get_version());

        $this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_styles');
        $this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_scripts');

        // Shortcodes
        $shortcodes = new QLMS_Shortcodes();
        $this->loader->add_action('init', $shortcodes, 'register_shortcodes');

        // AJAX handlers
        $this->loader->add_action('wp_ajax_qlms_submit_quiz', $plugin_public, 'handle_quiz_submission');
        $this->loader->add_action('wp_ajax_qlms_save_progress', $plugin_public, 'handle_save_progress');
        $this->loader->add_action('wp_ajax_qlms_get_quiz_data', $plugin_public, 'handle_get_quiz_data');
    }

    /**
     * Register custom post types
     */
    private function register_post_types() {
        $post_types = new QLMS_Post_Types();
        $this->loader->add_action('init', $post_types, 'register_course_post_type');
        $this->loader->add_action('init', $post_types, 'register_lesson_post_type');
    }

    /**
     * Register taxonomies
     */
    private function register_taxonomies() {
        $post_types = new QLMS_Post_Types();
        $this->loader->add_action('init', $post_types, 'register_taxonomies');
    }

    /**
     * Run the loader to execute all of the hooks with WordPress
     */
    public function run() {
        $this->loader->run();
    }

    /**
     * The name of the plugin used to uniquely identify it
     */
    public function get_plugin_name() {
        return $this->plugin_name;
    }

    /**
     * The reference to the class that orchestrates the hooks
     */
    public function get_loader() {
        return $this->loader;
    }

    /**
     * Retrieve the version number of the plugin
     */
    public function get_version() {
        return $this->version;
    }
}
