<?php
/**
 * Quiz admin functionality
 */

class QLMS_Quiz_Admin {

    public function __construct() {
        add_action('wp_ajax_qlms_save_quiz', array($this, 'save_quiz'));
        add_action('wp_ajax_qlms_delete_quiz', array($this, 'delete_quiz'));
        add_action('wp_ajax_qlms_add_question', array($this, 'add_question'));
        add_action('wp_ajax_qlms_save_question', array($this, 'save_question'));
        add_action('wp_ajax_qlms_delete_question', array($this, 'delete_question'));
    }

    /**
     * Register settings
     */
    public function register_settings() {
        register_setting('qlms_settings', 'qlms_options');

        add_settings_section(
            'qlms_general_section',
            __('General Settings', 'quiz-lms-wc'),
            array($this, 'general_section_callback'),
            'qlms_settings'
        );

        add_settings_field(
            'qlms_default_pass_percentage',
            __('Default Pass Percentage', 'quiz-lms-wc'),
            array($this, 'pass_percentage_callback'),
            'qlms_settings',
            'qlms_general_section'
        );

        add_settings_field(
            'qlms_enable_certificates',
            __('Enable Certificates', 'quiz-lms-wc'),
            array($this, 'certificates_callback'),
            'qlms_settings',
            'qlms_general_section'
        );
    }

    public function general_section_callback() {
        echo '<p>' . esc_html__('Configure general settings for quizzes and courses.', 'quiz-lms-wc') . '</p>';
    }

    public function pass_percentage_callback() {
        $options = get_option('qlms_options');
        $value = isset($options['default_pass_percentage']) ? $options['default_pass_percentage'] : 70;
        echo '<input type="number" name="qlms_options[default_pass_percentage]" value="' . esc_attr($value) . '" min="0" max="100" />';
    }

    public function certificates_callback() {
        $options = get_option('qlms_options');
        $value = isset($options['enable_certificates']) ? $options['enable_certificates'] : 0;
        echo '<input type="checkbox" name="qlms_options[enable_certificates]" value="1" ' . checked($value, 1, false) . ' />';
    }

    /**
     * Save quiz via AJAX
     */
    public function save_quiz() {
        check_ajax_referer('qlms_admin_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Permission denied', 'quiz-lms-wc')));
        }

        $quiz_id = isset($_POST['quiz_id']) ? intval($_POST['quiz_id']) : 0;
        $data = array(
            'title' => sanitize_text_field($_POST['title']),
            'description' => wp_kses_post($_POST['description']),
            'time_limit' => intval($_POST['time_limit']),
            'pass_percentage' => intval($_POST['pass_percentage']),
            'is_free' => isset($_POST['is_free']) ? 1 : 0,
            'product_id' => isset($_POST['product_id']) ? intval($_POST['product_id']) : null,
            'attempts_allowed' => intval($_POST['attempts_allowed']),
            'show_correct_answers' => isset($_POST['show_correct_answers']) ? 1 : 0,
            'randomize_questions' => isset($_POST['randomize_questions']) ? 1 : 0,
            'status' => sanitize_text_field($_POST['status']),
        );

        if ($quiz_id) {
            QLMS_Quiz::update($quiz_id, $data);
        } else {
            $quiz_id = QLMS_Quiz::create($data);
        }

        wp_send_json_success(array(
            'message' => __('Quiz saved successfully', 'quiz-lms-wc'),
            'quiz_id' => $quiz_id,
        ));
    }

    /**
     * Delete quiz via AJAX
     */
    public function delete_quiz() {
        check_ajax_referer('qlms_admin_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Permission denied', 'quiz-lms-wc')));
        }

        $quiz_id = intval($_POST['quiz_id']);
        QLMS_Quiz::delete($quiz_id);

        wp_send_json_success(array('message' => __('Quiz deleted successfully', 'quiz-lms-wc')));
    }

    /**
     * Save question via AJAX
     */
    public function save_question() {
        check_ajax_referer('qlms_admin_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Permission denied', 'quiz-lms-wc')));
        }

        $question_id = isset($_POST['question_id']) ? intval($_POST['question_id']) : 0;
        $quiz_id = intval($_POST['quiz_id']);

        $question_data = array(
            'quiz_id' => $quiz_id,
            'question_text' => wp_kses_post($_POST['question_text']),
            'question_type' => sanitize_text_field($_POST['question_type']),
            'points' => intval($_POST['points']),
            'sort_order' => intval($_POST['sort_order']),
        );

        if ($question_id) {
            QLMS_Question::update($question_id, $question_data);
        } else {
            $question_id = QLMS_Question::create($question_data);
        }

        // Save answers
        if (isset($_POST['answers']) && is_array($_POST['answers'])) {
            // Delete existing answers
            $existing_answers = QLMS_Question::get_answers($question_id);
            foreach ($existing_answers as $answer) {
                QLMS_Question::delete_answer($answer->id);
            }

            // Add new answers
            foreach ($_POST['answers'] as $index => $answer) {
                if (!empty($answer['text'])) {
                    QLMS_Question::add_answer(
                        $question_id,
                        sanitize_text_field($answer['text']),
                        isset($answer['is_correct']) ? 1 : 0,
                        $index
                    );
                }
            }
        }

        wp_send_json_success(array(
            'message' => __('Question saved successfully', 'quiz-lms-wc'),
            'question_id' => $question_id,
        ));
    }

    /**
     * Delete question via AJAX
     */
    public function delete_question() {
        check_ajax_referer('qlms_admin_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Permission denied', 'quiz-lms-wc')));
        }

        $question_id = intval($_POST['question_id']);
        QLMS_Question::delete($question_id);

        wp_send_json_success(array('message' => __('Question deleted successfully', 'quiz-lms-wc')));
    }
}
