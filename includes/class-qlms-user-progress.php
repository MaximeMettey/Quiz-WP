<?php
/**
 * User Progress model class
 */

class QLMS_User_Progress {

    /**
     * Update user progress
     */
    public static function update_progress($user_id, $course_id = null, $lesson_id = null, $quiz_id = null, $status = 'in_progress') {
        global $wpdb;
        $table = $wpdb->prefix . 'qlms_user_progress';

        // Check if progress entry exists
        $existing = self::get_progress($user_id, $course_id, $lesson_id, $quiz_id);

        $data = array(
            'status' => $status,
        );

        if ($status === 'completed') {
            $data['completed_at'] = current_time('mysql');
        }

        if ($existing) {
            return $wpdb->update(
                $table,
                $data,
                array('id' => $existing->id),
                null,
                array('%d')
            );
        } else {
            $insert_data = array_merge($data, array(
                'user_id' => $user_id,
                'course_id' => $course_id,
                'lesson_id' => $lesson_id,
                'quiz_id' => $quiz_id,
            ));

            $wpdb->insert($table, $insert_data);
            return $wpdb->insert_id;
        }
    }

    /**
     * Get user progress
     */
    public static function get_progress($user_id, $course_id = null, $lesson_id = null, $quiz_id = null) {
        global $wpdb;
        $table = $wpdb->prefix . 'qlms_user_progress';

        $where = array('user_id = %d');
        $values = array($user_id);

        if ($course_id !== null) {
            $where[] = 'course_id = %d';
            $values[] = $course_id;
        }

        if ($lesson_id !== null) {
            $where[] = 'lesson_id = %d';
            $values[] = $lesson_id;
        }

        if ($quiz_id !== null) {
            $where[] = 'quiz_id = %d';
            $values[] = $quiz_id;
        }

        $where_clause = implode(' AND ', $where);

        return $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table WHERE $where_clause",
            $values
        ));
    }

    /**
     * Get course progress for user
     */
    public static function get_course_progress($course_id, $user_id) {
        return self::get_progress($user_id, $course_id, null, null);
    }

    /**
     * Get lesson progress for user
     */
    public static function get_lesson_progress($lesson_id, $user_id) {
        return self::get_progress($user_id, null, $lesson_id, null);
    }

    /**
     * Get quiz progress for user
     */
    public static function get_quiz_progress($quiz_id, $user_id) {
        return self::get_progress($user_id, null, null, $quiz_id);
    }

    /**
     * Get all user courses
     */
    public static function get_user_courses($user_id) {
        global $wpdb;
        $table = $wpdb->prefix . 'qlms_user_progress';

        return $wpdb->get_results($wpdb->prepare(
            "SELECT DISTINCT course_id, status, completed_at
            FROM $table
            WHERE user_id = %d AND course_id IS NOT NULL
            ORDER BY updated_at DESC",
            $user_id
        ));
    }

    /**
     * Get user statistics
     */
    public static function get_user_stats($user_id) {
        global $wpdb;
        $progress_table = $wpdb->prefix . 'qlms_user_progress';
        $attempts_table = $wpdb->prefix . 'qlms_attempts';

        $stats = array();

        // Total enrolled courses
        $stats['enrolled_courses'] = (int) $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(DISTINCT course_id) FROM $progress_table WHERE user_id = %d AND course_id IS NOT NULL",
            $user_id
        ));

        // Completed courses
        $stats['completed_courses'] = (int) $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(DISTINCT course_id) FROM $progress_table WHERE user_id = %d AND course_id IS NOT NULL AND status = 'completed'",
            $user_id
        ));

        // Total quiz attempts
        $stats['quiz_attempts'] = (int) $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $attempts_table WHERE user_id = %d AND completed_at IS NOT NULL",
            $user_id
        ));

        // Passed quizzes
        $stats['passed_quizzes'] = (int) $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $attempts_table WHERE user_id = %d AND passed = 1",
            $user_id
        ));

        // Average score
        $stats['average_score'] = (float) $wpdb->get_var($wpdb->prepare(
            "SELECT AVG(score) FROM $attempts_table WHERE user_id = %d AND completed_at IS NOT NULL",
            $user_id
        ));

        return $stats;
    }

    /**
     * Get recent activity for user
     */
    public static function get_recent_activity($user_id, $limit = 10) {
        global $wpdb;
        $table = $wpdb->prefix . 'qlms_user_progress';

        return $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $table WHERE user_id = %d ORDER BY updated_at DESC LIMIT %d",
            $user_id,
            $limit
        ));
    }
}
