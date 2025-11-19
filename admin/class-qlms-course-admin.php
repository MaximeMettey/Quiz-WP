<?php
/**
 * Course admin functionality
 */

class QLMS_Course_Admin {

    public function __construct() {
        add_action('save_post_qlms_course', array($this, 'save_course_meta'), 10, 2);
        add_action('save_post_qlms_lesson', array($this, 'save_lesson_meta'), 10, 2);
    }

    /**
     * Add meta boxes for courses
     */
    public function add_meta_boxes() {
        // Course settings meta box
        add_meta_box(
            'qlms_course_settings',
            __('Course Settings', 'quiz-lms-wc'),
            array($this, 'render_course_settings_meta_box'),
            'qlms_course',
            'normal',
            'high'
        );

        // Lessons meta box
        add_meta_box(
            'qlms_course_lessons',
            __('Course Lessons', 'quiz-lms-wc'),
            array($this, 'render_course_lessons_meta_box'),
            'qlms_course',
            'normal',
            'high'
        );

        // Lesson settings meta box
        add_meta_box(
            'qlms_lesson_settings',
            __('Lesson Settings', 'quiz-lms-wc'),
            array($this, 'render_lesson_settings_meta_box'),
            'qlms_lesson',
            'side',
            'default'
        );
    }

    /**
     * Render course settings meta box
     */
    public function render_course_settings_meta_box($post) {
        wp_nonce_field('qlms_course_meta', 'qlms_course_meta_nonce');

        $course = QLMS_Course::get_by_post($post->ID);
        $is_free = $course ? $course->is_free : 1;
        $product_id = $course ? $course->product_id : '';
        $duration = $course ? $course->duration : '';
        $level = $course ? $course->level : 'beginner';
        $certificate_enabled = $course ? $course->certificate_enabled : 0;

        include QLMS_PLUGIN_DIR . 'admin/views/course-settings-meta-box.php';
    }

    /**
     * Render course lessons meta box
     */
    public function render_course_lessons_meta_box($post) {
        $course = QLMS_Course::get_by_post($post->ID);
        $course_id = $course ? $course->id : 0;

        $lessons = $course_id ? QLMS_Course::get_lessons($course_id) : array();

        // Get all lessons for selection
        $all_lessons = get_posts(array(
            'post_type' => 'qlms_lesson',
            'posts_per_page' => -1,
            'orderby' => 'title',
            'order' => 'ASC',
        ));

        include QLMS_PLUGIN_DIR . 'admin/views/course-lessons-meta-box.php';
    }

    /**
     * Render lesson settings meta box
     */
    public function render_lesson_settings_meta_box($post) {
        wp_nonce_field('qlms_lesson_meta', 'qlms_lesson_meta_nonce');

        $lesson = QLMS_Lesson::get_by_post($post->ID);

        // Get all courses for selection
        $courses = get_posts(array(
            'post_type' => 'qlms_course',
            'posts_per_page' => -1,
            'orderby' => 'title',
            'order' => 'ASC',
        ));

        // Get the course post_id from the course table ID
        $course_post_id = '';
        if ($lesson && $lesson->course_id) {
            $course_meta = QLMS_Course::get($lesson->course_id);
            $course_post_id = $course_meta ? $course_meta->post_id : '';
        }

        $course_id = $course_post_id;
        $duration = $lesson ? $lesson->duration : '';
        $sort_order = $lesson ? $lesson->sort_order : 0;

        include QLMS_PLUGIN_DIR . 'admin/views/lesson-settings-meta-box.php';
    }

    /**
     * Save course meta
     */
    public function save_course_meta($post_id, $post = null) {
        // Check nonce
        if (!isset($_POST['qlms_course_meta_nonce']) || !wp_verify_nonce($_POST['qlms_course_meta_nonce'], 'qlms_course_meta')) {
            return;
        }

        // Check autosave
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        // Check permissions
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        $course = QLMS_Course::get_by_post($post_id);

        $data = array(
            'post_id' => $post_id,
            'is_free' => isset($_POST['qlms_is_free']) ? 1 : 0,
            'product_id' => isset($_POST['qlms_product_id']) ? intval($_POST['qlms_product_id']) : null,
            'duration' => sanitize_text_field($_POST['qlms_duration']),
            'level' => sanitize_text_field($_POST['qlms_level']),
            'certificate_enabled' => isset($_POST['qlms_certificate_enabled']) ? 1 : 0,
        );

        if ($course) {
            QLMS_Course::update($course->id, $data);
        } else {
            QLMS_Course::create($data);
        }
    }

    /**
     * Save lesson meta
     */
    public function save_lesson_meta($post_id, $post) {
        // Check nonce
        if (!isset($_POST['qlms_lesson_meta_nonce']) || !wp_verify_nonce($_POST['qlms_lesson_meta_nonce'], 'qlms_lesson_meta')) {
            return;
        }

        // Check autosave
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        // Check permissions
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        $lesson = QLMS_Lesson::get_by_post($post_id);

        // Get the actual course_id from the courses table
        $course_post_id = isset($_POST['qlms_course_id']) ? intval($_POST['qlms_course_id']) : 0;
        $course_meta = $course_post_id ? QLMS_Course::get_by_post($course_post_id) : null;
        $course_id = $course_meta ? $course_meta->id : 0;

        $data = array(
            'post_id' => $post_id,
            'course_id' => $course_id,
            'duration' => isset($_POST['qlms_duration']) ? intval($_POST['qlms_duration']) : 0,
            'sort_order' => isset($_POST['qlms_sort_order']) ? intval($_POST['qlms_sort_order']) : 0,
        );

        if ($lesson) {
            QLMS_Lesson::update($lesson->id, $data);
        } else {
            QLMS_Lesson::create($data);
        }
    }
}
