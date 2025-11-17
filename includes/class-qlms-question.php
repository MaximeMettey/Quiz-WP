<?php
/**
 * Question model class
 */

class QLMS_Question {

    /**
     * Get question by ID
     */
    public static function get($question_id) {
        global $wpdb;
        $table = $wpdb->prefix . 'qlms_questions';

        $question = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table WHERE id = %d",
            $question_id
        ));

        if ($question) {
            $question->answers = self::get_answers($question_id);
        }

        return $question;
    }

    /**
     * Create a new question
     */
    public static function create($data) {
        global $wpdb;
        $table = $wpdb->prefix . 'qlms_questions';

        $defaults = array(
            'quiz_id' => 0,
            'question_text' => '',
            'question_type' => 'single',
            'points' => 1,
            'sort_order' => 0,
        );

        $data = wp_parse_args($data, $defaults);

        $wpdb->insert($table, array(
            'quiz_id' => $data['quiz_id'],
            'question_text' => $data['question_text'],
            'question_type' => $data['question_type'],
            'points' => $data['points'],
            'sort_order' => $data['sort_order'],
        ));

        return $wpdb->insert_id;
    }

    /**
     * Update question
     */
    public static function update($question_id, $data) {
        global $wpdb;
        $table = $wpdb->prefix . 'qlms_questions';

        return $wpdb->update(
            $table,
            $data,
            array('id' => $question_id),
            null,
            array('%d')
        );
    }

    /**
     * Delete question
     */
    public static function delete($question_id) {
        global $wpdb;
        $table = $wpdb->prefix . 'qlms_questions';
        $answers_table = $wpdb->prefix . 'qlms_answers';

        // Delete associated answers
        $wpdb->delete($answers_table, array('question_id' => $question_id), array('%d'));

        return $wpdb->delete($table, array('id' => $question_id), array('%d'));
    }

    /**
     * Delete all questions for a quiz
     */
    public static function delete_by_quiz($quiz_id) {
        global $wpdb;
        $questions_table = $wpdb->prefix . 'qlms_questions';
        $answers_table = $wpdb->prefix . 'qlms_answers';

        // Get all question IDs
        $question_ids = $wpdb->get_col($wpdb->prepare(
            "SELECT id FROM $questions_table WHERE quiz_id = %d",
            $quiz_id
        ));

        if (!empty($question_ids)) {
            $placeholders = implode(',', array_fill(0, count($question_ids), '%d'));
            $wpdb->query($wpdb->prepare(
                "DELETE FROM $answers_table WHERE question_id IN ($placeholders)",
                $question_ids
            ));
        }

        return $wpdb->delete($questions_table, array('quiz_id' => $quiz_id), array('%d'));
    }

    /**
     * Get questions by quiz ID
     */
    public static function get_by_quiz($quiz_id, $randomize = false) {
        global $wpdb;
        $table = $wpdb->prefix . 'qlms_questions';

        $order_by = $randomize ? 'RAND()' : 'sort_order ASC';

        $questions = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $table WHERE quiz_id = %d ORDER BY $order_by",
            $quiz_id
        ));

        // Load answers for each question
        foreach ($questions as $question) {
            $question->answers = self::get_answers($question->id);
        }

        return $questions;
    }

    /**
     * Get answers for a question
     */
    public static function get_answers($question_id, $randomize = false) {
        global $wpdb;
        $table = $wpdb->prefix . 'qlms_answers';

        $order_by = $randomize ? 'RAND()' : 'sort_order ASC';

        return $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $table WHERE question_id = %d ORDER BY $order_by",
            $question_id
        ));
    }

    /**
     * Add answer to question
     */
    public static function add_answer($question_id, $answer_text, $is_correct = false, $sort_order = 0) {
        global $wpdb;
        $table = $wpdb->prefix . 'qlms_answers';

        $wpdb->insert($table, array(
            'question_id' => $question_id,
            'answer_text' => $answer_text,
            'is_correct' => $is_correct ? 1 : 0,
            'sort_order' => $sort_order,
        ));

        return $wpdb->insert_id;
    }

    /**
     * Update answer
     */
    public static function update_answer($answer_id, $data) {
        global $wpdb;
        $table = $wpdb->prefix . 'qlms_answers';

        return $wpdb->update(
            $table,
            $data,
            array('id' => $answer_id),
            null,
            array('%d')
        );
    }

    /**
     * Delete answer
     */
    public static function delete_answer($answer_id) {
        global $wpdb;
        $table = $wpdb->prefix . 'qlms_answers';

        return $wpdb->delete($table, array('id' => $answer_id), array('%d'));
    }

    /**
     * Check if answer is correct
     */
    public static function check_answer($question_id, $answer_ids) {
        global $wpdb;
        $table = $wpdb->prefix . 'qlms_answers';

        if (!is_array($answer_ids)) {
            $answer_ids = array($answer_ids);
        }

        // Get all correct answers for this question
        $correct_answers = $wpdb->get_col($wpdb->prepare(
            "SELECT id FROM $table WHERE question_id = %d AND is_correct = 1",
            $question_id
        ));

        // Compare arrays
        sort($correct_answers);
        sort($answer_ids);

        return $correct_answers === array_map('intval', $answer_ids);
    }

    /**
     * Get correct answers for a question
     */
    public static function get_correct_answers($question_id) {
        global $wpdb;
        $table = $wpdb->prefix . 'qlms_answers';

        return $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $table WHERE question_id = %d AND is_correct = 1",
            $question_id
        ));
    }
}
