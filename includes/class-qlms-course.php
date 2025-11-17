<?php
/**
 * Course model class
 */

class QLMS_Course {

    /**
     * Get course by ID
     */
    public static function get($course_id) {
        global $wpdb;
        $table = $wpdb->prefix . 'qlms_courses';

        return $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table WHERE id = %d",
            $course_id
        ));
    }

    /**
     * Get course by post ID
     */
    public static function get_by_post($post_id) {
        global $wpdb;
        $table = $wpdb->prefix . 'qlms_courses';

        return $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table WHERE post_id = %d",
            $post_id
        ));
    }

    /**
     * Create course meta
     */
    public static function create($data) {
        global $wpdb;
        $table = $wpdb->prefix . 'qlms_courses';

        $defaults = array(
            'post_id' => 0,
            'is_free' => 1,
            'product_id' => null,
            'duration' => '',
            'level' => 'beginner',
            'certificate_enabled' => 0,
        );

        $data = wp_parse_args($data, $defaults);

        $wpdb->insert($table, $data);
        return $wpdb->insert_id;
    }

    /**
     * Update course meta
     */
    public static function update($course_id, $data) {
        global $wpdb;
        $table = $wpdb->prefix . 'qlms_courses';

        return $wpdb->update(
            $table,
            $data,
            array('id' => $course_id),
            null,
            array('%d')
        );
    }

    /**
     * Delete course meta
     */
    public static function delete($course_id) {
        global $wpdb;
        $table = $wpdb->prefix . 'qlms_courses';

        return $wpdb->delete($table, array('id' => $course_id), array('%d'));
    }

    /**
     * Get course lessons
     */
    public static function get_lessons($course_id) {
        return QLMS_Lesson::get_by_course($course_id);
    }

    /**
     * Get course quizzes
     */
    public static function get_quizzes($course_id) {
        return QLMS_Quiz::get_all(array('course_id' => $course_id));
    }

    /**
     * Check if user has access to course
     */
    public static function user_has_access($course_id, $user_id = null) {
        if (!$user_id) {
            $user_id = get_current_user_id();
        }

        $course = self::get($course_id);

        if (!$course) {
            return false;
        }

        // Free courses are accessible to all logged-in users
        if ($course->is_free) {
            return is_user_logged_in();
        }

        // Check if user has purchased the product
        if ($course->product_id && function_exists('wc_customer_bought_product')) {
            $user = get_user_by('id', $user_id);
            if ($user) {
                return wc_customer_bought_product($user->user_email, $user_id, $course->product_id);
            }
        }

        return false;
    }

    /**
     * Get course progress for user
     */
    public static function get_user_progress($course_id, $user_id = null) {
        if (!$user_id) {
            $user_id = get_current_user_id();
        }

        $lessons = self::get_lessons($course_id);
        $total_lessons = count($lessons);

        if ($total_lessons === 0) {
            return 0;
        }

        $completed_lessons = 0;
        foreach ($lessons as $lesson) {
            $progress = QLMS_User_Progress::get_lesson_progress($lesson->id, $user_id);
            if ($progress && $progress->status === 'completed') {
                $completed_lessons++;
            }
        }

        return ($completed_lessons / $total_lessons) * 100;
    }

    /**
     * Check if course is completed by user
     */
    public static function is_completed($course_id, $user_id = null) {
        if (!$user_id) {
            $user_id = get_current_user_id();
        }

        return self::get_user_progress($course_id, $user_id) >= 100;
    }

    /**
     * Get all courses with meta
     */
    public static function get_all_courses($args = array()) {
        $defaults = array(
            'post_type' => 'qlms_course',
            'posts_per_page' => -1,
            'post_status' => 'publish',
        );

        $args = wp_parse_args($args, $defaults);

        $posts = get_posts($args);
        $courses = array();

        foreach ($posts as $post) {
            $course_meta = self::get_by_post($post->ID);
            $course = (object) array(
                'post' => $post,
                'meta' => $course_meta,
            );
            $courses[] = $course;
        }

        return $courses;
    }

    /**
     * Enroll user in course
     */
    public static function enroll_user($course_id, $user_id = null) {
        if (!$user_id) {
            $user_id = get_current_user_id();
        }

        return QLMS_User_Progress::update_progress(
            $user_id,
            $course_id,
            null,
            null,
            'in_progress'
        );
    }
}
