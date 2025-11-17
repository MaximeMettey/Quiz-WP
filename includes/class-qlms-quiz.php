<?php
/**
 * Quiz model class
 */

class QLMS_Quiz {

    /**
     * Get quiz by ID
     */
    public static function get($quiz_id) {
        global $wpdb;
        $table = $wpdb->prefix . 'qlms_quizzes';

        return $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table WHERE id = %d",
            $quiz_id
        ));
    }

    /**
     * Create a new quiz
     */
    public static function create($data) {
        global $wpdb;
        $table = $wpdb->prefix . 'qlms_quizzes';

        $defaults = array(
            'title' => '',
            'description' => '',
            'course_id' => null,
            'lesson_id' => null,
            'time_limit' => 0,
            'pass_percentage' => 70,
            'is_free' => 1,
            'product_id' => null,
            'attempts_allowed' => -1,
            'show_correct_answers' => 1,
            'randomize_questions' => 0,
            'status' => 'draft',
        );

        $data = wp_parse_args($data, $defaults);

        $wpdb->insert($table, $data);
        return $wpdb->insert_id;
    }

    /**
     * Update quiz
     */
    public static function update($quiz_id, $data) {
        global $wpdb;
        $table = $wpdb->prefix . 'qlms_quizzes';

        return $wpdb->update(
            $table,
            $data,
            array('id' => $quiz_id),
            null,
            array('%d')
        );
    }

    /**
     * Delete quiz
     */
    public static function delete($quiz_id) {
        global $wpdb;
        $table = $wpdb->prefix . 'qlms_quizzes';

        // Delete associated questions and answers
        QLMS_Question::delete_by_quiz($quiz_id);

        return $wpdb->delete($table, array('id' => $quiz_id), array('%d'));
    }

    /**
     * Get all quizzes
     */
    public static function get_all($args = array()) {
        global $wpdb;
        $table = $wpdb->prefix . 'qlms_quizzes';

        $defaults = array(
            'status' => '',
            'course_id' => null,
            'lesson_id' => null,
            'orderby' => 'created_at',
            'order' => 'DESC',
            'limit' => -1,
            'offset' => 0,
        );

        $args = wp_parse_args($args, $defaults);

        $where = array('1=1');

        if (!empty($args['status'])) {
            $where[] = $wpdb->prepare("status = %s", $args['status']);
        }

        if (!empty($args['course_id'])) {
            $where[] = $wpdb->prepare("course_id = %d", $args['course_id']);
        }

        if (!empty($args['lesson_id'])) {
            $where[] = $wpdb->prepare("lesson_id = %d", $args['lesson_id']);
        }

        $where_clause = implode(' AND ', $where);
        $order_clause = sprintf("%s %s", $args['orderby'], $args['order']);

        $sql = "SELECT * FROM $table WHERE $where_clause ORDER BY $order_clause";

        if ($args['limit'] > 0) {
            $sql .= $wpdb->prepare(" LIMIT %d OFFSET %d", $args['limit'], $args['offset']);
        }

        return $wpdb->get_results($sql);
    }

    /**
     * Get quiz questions
     */
    public static function get_questions($quiz_id, $randomize = false) {
        return QLMS_Question::get_by_quiz($quiz_id, $randomize);
    }

    /**
     * Check if user has access to quiz
     */
    public static function user_has_access($quiz_id, $user_id = null) {
        if (!$user_id) {
            $user_id = get_current_user_id();
        }

        $quiz = self::get($quiz_id);

        if (!$quiz) {
            return false;
        }

        // Free quizzes are accessible to all logged-in users
        if ($quiz->is_free) {
            return is_user_logged_in();
        }

        // Check if user has purchased the product
        if ($quiz->product_id && function_exists('wc_customer_bought_product')) {
            $user = get_user_by('id', $user_id);
            if ($user) {
                return wc_customer_bought_product($user->user_email, $user_id, $quiz->product_id);
            }
        }

        return false;
    }

    /**
     * Get user attempts count for a quiz
     */
    public static function get_user_attempts_count($quiz_id, $user_id = null) {
        if (!$user_id) {
            $user_id = get_current_user_id();
        }

        global $wpdb;
        $table = $wpdb->prefix . 'qlms_attempts';

        return (int) $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $table WHERE quiz_id = %d AND user_id = %d AND completed_at IS NOT NULL",
            $quiz_id,
            $user_id
        ));
    }

    /**
     * Check if user can take quiz
     */
    public static function user_can_take($quiz_id, $user_id = null) {
        if (!$user_id) {
            $user_id = get_current_user_id();
        }

        $quiz = self::get($quiz_id);

        if (!$quiz) {
            return false;
        }

        // Check access
        if (!self::user_has_access($quiz_id, $user_id)) {
            return false;
        }

        // Check attempts limit
        if ($quiz->attempts_allowed > 0) {
            $attempts = self::get_user_attempts_count($quiz_id, $user_id);
            if ($attempts >= $quiz->attempts_allowed) {
                return false;
            }
        }

        return true;
    }

    /**
     * Start quiz attempt
     */
    public static function start_attempt($quiz_id, $user_id = null) {
        if (!$user_id) {
            $user_id = get_current_user_id();
        }

        global $wpdb;
        $table = $wpdb->prefix . 'qlms_attempts';

        $questions = self::get_questions($quiz_id);

        $wpdb->insert($table, array(
            'user_id' => $user_id,
            'quiz_id' => $quiz_id,
            'total_questions' => count($questions),
            'started_at' => current_time('mysql'),
        ));

        return $wpdb->insert_id;
    }

    /**
     * Complete quiz attempt
     */
    public static function complete_attempt($attempt_id, $data) {
        global $wpdb;
        $table = $wpdb->prefix . 'qlms_attempts';

        $update_data = array(
            'score' => $data['score'],
            'correct_answers' => $data['correct_answers'],
            'time_taken' => $data['time_taken'],
            'passed' => $data['passed'],
            'completed_at' => current_time('mysql'),
        );

        return $wpdb->update(
            $table,
            $update_data,
            array('id' => $attempt_id),
            null,
            array('%d')
        );
    }
}
