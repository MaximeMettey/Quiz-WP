<?php
/**
 * The public-facing functionality of the plugin
 */

class QLMS_Public {

    private $plugin_name;
    private $version;

    public function __construct($plugin_name, $version) {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
    }

    /**
     * Register the stylesheets for the public-facing side of the site
     */
    public function enqueue_styles() {
        wp_enqueue_style(
            $this->plugin_name,
            QLMS_PLUGIN_URL . 'assets/css/public.css',
            array(),
            $this->version,
            'all'
        );
    }

    /**
     * Register the JavaScript for the public-facing side of the site
     */
    public function enqueue_scripts() {
        wp_enqueue_script(
            $this->plugin_name,
            QLMS_PLUGIN_URL . 'assets/js/public.js',
            array('jquery'),
            $this->version,
            false
        );

        wp_localize_script($this->plugin_name, 'qlms_public', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('qlms_public_nonce'),
            'strings' => array(
                'submit_quiz' => __('Submit Quiz', 'quiz-lms-wc'),
                'time_up' => __('Time is up!', 'quiz-lms-wc'),
                'confirm_submit' => __('Are you sure you want to submit?', 'quiz-lms-wc'),
                'select_answer' => __('Please select an answer for all questions', 'quiz-lms-wc'),
                'loading' => __('Loading...', 'quiz-lms-wc'),
                'error' => __('An error occurred', 'quiz-lms-wc'),
            ),
        ));
    }

    /**
     * Handle quiz submission via AJAX
     */
    public function handle_quiz_submission() {
        check_ajax_referer('qlms_public_nonce', 'nonce');

        if (!is_user_logged_in()) {
            wp_send_json_error(array('message' => __('You must be logged in to take quizzes', 'quiz-lms-wc')));
        }

        $quiz_id = intval($_POST['quiz_id']);
        $attempt_id = intval($_POST['attempt_id']);
        $answers = isset($_POST['answers']) ? $_POST['answers'] : array();
        $time_taken = isset($_POST['time_taken']) ? intval($_POST['time_taken']) : 0;

        $user_id = get_current_user_id();

        // Verify user can take this quiz
        if (!QLMS_Quiz::user_can_take($quiz_id, $user_id)) {
            wp_send_json_error(array('message' => __('You do not have access to this quiz', 'quiz-lms-wc')));
        }

        // Get quiz and questions
        $quiz = QLMS_Quiz::get($quiz_id);
        $questions = QLMS_Quiz::get_questions($quiz_id);

        $correct_answers = 0;
        $total_points = 0;
        $earned_points = 0;

        global $wpdb;
        $user_answers_table = $wpdb->prefix . 'qlms_user_answers';

        // Process each answer
        foreach ($questions as $question) {
            $total_points += $question->points;
            $question_id = $question->id;

            if (!isset($answers[$question_id])) {
                continue;
            }

            $user_answer = $answers[$question_id];

            // Check if answer is correct
            $is_correct = QLMS_Question::check_answer($question_id, $user_answer);

            if ($is_correct) {
                $correct_answers++;
                $earned_points += $question->points;
            }

            // Save user answer
            if (is_array($user_answer)) {
                foreach ($user_answer as $answer_id) {
                    $wpdb->insert($user_answers_table, array(
                        'attempt_id' => $attempt_id,
                        'question_id' => $question_id,
                        'answer_id' => intval($answer_id),
                        'is_correct' => $is_correct ? 1 : 0,
                    ));
                }
            } else {
                $wpdb->insert($user_answers_table, array(
                    'attempt_id' => $attempt_id,
                    'question_id' => $question_id,
                    'answer_id' => intval($user_answer),
                    'is_correct' => $is_correct ? 1 : 0,
                ));
            }
        }

        // Calculate score
        $score = $total_points > 0 ? ($earned_points / $total_points) * 100 : 0;
        $passed = $score >= $quiz->pass_percentage;

        // Update attempt
        QLMS_Quiz::complete_attempt($attempt_id, array(
            'score' => $score,
            'correct_answers' => $correct_answers,
            'time_taken' => $time_taken,
            'passed' => $passed ? 1 : 0,
        ));

        // Update user progress
        if ($quiz->quiz_id) {
            QLMS_User_Progress::update_progress(
                $user_id,
                null,
                null,
                $quiz_id,
                $passed ? 'completed' : 'failed'
            );
        }

        wp_send_json_success(array(
            'score' => round($score, 2),
            'correct_answers' => $correct_answers,
            'total_questions' => count($questions),
            'passed' => $passed,
            'pass_percentage' => $quiz->pass_percentage,
            'message' => $passed
                ? __('Congratulations! You passed the quiz.', 'quiz-lms-wc')
                : __('You did not pass this time. Please try again.', 'quiz-lms-wc'),
        ));
    }

    /**
     * Save user progress via AJAX
     */
    public function handle_save_progress() {
        check_ajax_referer('qlms_public_nonce', 'nonce');

        if (!is_user_logged_in()) {
            wp_send_json_error(array('message' => __('You must be logged in', 'quiz-lms-wc')));
        }

        $user_id = get_current_user_id();
        $course_id = isset($_POST['course_id']) ? intval($_POST['course_id']) : null;
        $lesson_id = isset($_POST['lesson_id']) ? intval($_POST['lesson_id']) : null;
        $quiz_id = isset($_POST['quiz_id']) ? intval($_POST['quiz_id']) : null;
        $status = isset($_POST['status']) ? sanitize_text_field($_POST['status']) : 'in_progress';

        QLMS_User_Progress::update_progress($user_id, $course_id, $lesson_id, $quiz_id, $status);

        wp_send_json_success(array('message' => __('Progress saved', 'quiz-lms-wc')));
    }

    /**
     * Get quiz data via AJAX
     */
    public function handle_get_quiz_data() {
        check_ajax_referer('qlms_public_nonce', 'nonce');

        if (!is_user_logged_in()) {
            wp_send_json_error(array('message' => __('You must be logged in', 'quiz-lms-wc')));
        }

        $quiz_id = intval($_POST['quiz_id']);
        $user_id = get_current_user_id();

        // Verify access
        if (!QLMS_Quiz::user_can_take($quiz_id, $user_id)) {
            wp_send_json_error(array('message' => __('You do not have access to this quiz', 'quiz-lms-wc')));
        }

        $quiz = QLMS_Quiz::get($quiz_id);
        $questions = QLMS_Quiz::get_questions($quiz_id, $quiz->randomize_questions);

        // Start new attempt
        $attempt_id = QLMS_Quiz::start_attempt($quiz_id, $user_id);

        wp_send_json_success(array(
            'quiz' => $quiz,
            'questions' => $questions,
            'attempt_id' => $attempt_id,
        ));
    }
}
