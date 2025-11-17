<?php
/**
 * Lesson model class
 */

class QLMS_Lesson {

    /**
     * Get lesson by ID
     */
    public static function get($lesson_id) {
        global $wpdb;
        $table = $wpdb->prefix . 'qlms_lessons';

        return $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table WHERE id = %d",
            $lesson_id
        ));
    }

    /**
     * Get lesson by post ID
     */
    public static function get_by_post($post_id) {
        global $wpdb;
        $table = $wpdb->prefix . 'qlms_lessons';

        return $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table WHERE post_id = %d",
            $post_id
        ));
    }

    /**
     * Create lesson meta
     */
    public static function create($data) {
        global $wpdb;
        $table = $wpdb->prefix . 'qlms_lessons';

        $defaults = array(
            'post_id' => 0,
            'course_id' => 0,
            'sort_order' => 0,
            'duration' => 0,
        );

        $data = wp_parse_args($data, $defaults);

        $wpdb->insert($table, $data);
        return $wpdb->insert_id;
    }

    /**
     * Update lesson meta
     */
    public static function update($lesson_id, $data) {
        global $wpdb;
        $table = $wpdb->prefix . 'qlms_lessons';

        return $wpdb->update(
            $table,
            $data,
            array('id' => $lesson_id),
            null,
            array('%d')
        );
    }

    /**
     * Delete lesson meta
     */
    public static function delete($lesson_id) {
        global $wpdb;
        $table = $wpdb->prefix . 'qlms_lessons';

        return $wpdb->delete($table, array('id' => $lesson_id), array('%d'));
    }

    /**
     * Get lessons by course ID
     */
    public static function get_by_course($course_id) {
        global $wpdb;
        $table = $wpdb->prefix . 'qlms_lessons';

        return $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $table WHERE course_id = %d ORDER BY sort_order ASC",
            $course_id
        ));
    }

    /**
     * Get lesson quizzes
     */
    public static function get_quizzes($lesson_id) {
        return QLMS_Quiz::get_all(array('lesson_id' => $lesson_id));
    }

    /**
     * Mark lesson as completed for user
     */
    public static function mark_complete($lesson_id, $user_id = null) {
        if (!$user_id) {
            $user_id = get_current_user_id();
        }

        $lesson = self::get($lesson_id);
        if (!$lesson) {
            return false;
        }

        return QLMS_User_Progress::update_progress(
            $user_id,
            $lesson->course_id,
            $lesson_id,
            null,
            'completed'
        );
    }

    /**
     * Check if lesson is completed by user
     */
    public static function is_completed($lesson_id, $user_id = null) {
        if (!$user_id) {
            $user_id = get_current_user_id();
        }

        $progress = QLMS_User_Progress::get_lesson_progress($lesson_id, $user_id);
        return $progress && $progress->status === 'completed';
    }

    /**
     * Get next lesson in course
     */
    public static function get_next($lesson_id) {
        global $wpdb;
        $table = $wpdb->prefix . 'qlms_lessons';

        $current_lesson = self::get($lesson_id);
        if (!$current_lesson) {
            return null;
        }

        return $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table
            WHERE course_id = %d
            AND sort_order > %d
            ORDER BY sort_order ASC
            LIMIT 1",
            $current_lesson->course_id,
            $current_lesson->sort_order
        ));
    }

    /**
     * Get previous lesson in course
     */
    public static function get_previous($lesson_id) {
        global $wpdb;
        $table = $wpdb->prefix . 'qlms_lessons';

        $current_lesson = self::get($lesson_id);
        if (!$current_lesson) {
            return null;
        }

        return $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table
            WHERE course_id = %d
            AND sort_order < %d
            ORDER BY sort_order DESC
            LIMIT 1",
            $current_lesson->course_id,
            $current_lesson->sort_order
        ));
    }
}
