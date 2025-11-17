<?php
/**
 * Quiz template
 */

if (!defined('WPINC')) {
    die;
}

$questions = QLMS_Quiz::get_questions($quiz_id, $quiz->randomize_questions);
$attempts_count = QLMS_Quiz::get_user_attempts_count($quiz_id, $user_id);
?>

<div class="qlms-quiz-container" data-quiz-id="<?php echo esc_attr($quiz_id); ?>">
    <div class="qlms-quiz-header">
        <h2><?php echo esc_html($quiz->title); ?></h2>
        <?php if ($quiz->description): ?>
            <div class="qlms-quiz-description">
                <?php echo wp_kses_post($quiz->description); ?>
            </div>
        <?php endif; ?>

        <div class="qlms-quiz-meta">
            <?php if ($quiz->time_limit > 0): ?>
                <div class="qlms-quiz-time-limit">
                    <span class="dashicons dashicons-clock"></span>
                    <?php printf(__('Time Limit: %d minutes', 'quiz-lms-wc'), $quiz->time_limit); ?>
                </div>
            <?php endif; ?>

            <div class="qlms-quiz-pass-percentage">
                <span class="dashicons dashicons-yes-alt"></span>
                <?php printf(__('Pass Score: %d%%', 'quiz-lms-wc'), $quiz->pass_percentage); ?>
            </div>

            <div class="qlms-quiz-questions-count">
                <span class="dashicons dashicons-list-view"></span>
                <?php printf(_n('%d Question', '%d Questions', count($questions), 'quiz-lms-wc'), count($questions)); ?>
            </div>

            <?php if ($quiz->attempts_allowed > 0): ?>
                <div class="qlms-quiz-attempts">
                    <span class="dashicons dashicons-controls-repeat"></span>
                    <?php printf(__('Attempts: %d / %d', 'quiz-lms-wc'), $attempts_count, $quiz->attempts_allowed); ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="qlms-quiz-start-screen">
        <button class="qlms-start-quiz-btn button button-primary button-large">
            <?php _e('Start Quiz', 'quiz-lms-wc'); ?>
        </button>
    </div>

    <div class="qlms-quiz-content" style="display: none;">
        <?php if ($quiz->time_limit > 0): ?>
            <div class="qlms-quiz-timer">
                <span class="dashicons dashicons-clock"></span>
                <span class="qlms-timer-text"><?php echo sprintf('%02d:00', $quiz->time_limit); ?></span>
            </div>
        <?php endif; ?>

        <form id="qlms-quiz-form" data-quiz-id="<?php echo esc_attr($quiz_id); ?>" data-time-limit="<?php echo esc_attr($quiz->time_limit); ?>">
            <?php wp_nonce_field('qlms_quiz_' . $quiz_id, 'qlms_quiz_nonce'); ?>
            <input type="hidden" name="attempt_id" id="qlms-attempt-id" value="" />

            <?php foreach ($questions as $index => $question): ?>
                <div class="qlms-question" data-question-id="<?php echo esc_attr($question->id); ?>">
                    <div class="qlms-question-header">
                        <span class="qlms-question-number"><?php echo ($index + 1); ?>.</span>
                        <div class="qlms-question-text"><?php echo wp_kses_post($question->question_text); ?></div>
                        <?php if ($question->points > 1): ?>
                            <span class="qlms-question-points"><?php printf(__('(%d points)', 'quiz-lms-wc'), $question->points); ?></span>
                        <?php endif; ?>
                    </div>

                    <div class="qlms-question-answers">
                        <?php foreach ($question->answers as $answer): ?>
                            <label class="qlms-answer">
                                <?php if ($question->question_type === 'multiple'): ?>
                                    <input type="checkbox" name="answers[<?php echo esc_attr($question->id); ?>][]" value="<?php echo esc_attr($answer->id); ?>" />
                                <?php else: ?>
                                    <input type="radio" name="answers[<?php echo esc_attr($question->id); ?>]" value="<?php echo esc_attr($answer->id); ?>" />
                                <?php endif; ?>
                                <span class="qlms-answer-text"><?php echo esc_html($answer->answer_text); ?></span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>

            <div class="qlms-quiz-submit">
                <button type="submit" class="button button-primary button-large">
                    <?php _e('Submit Quiz', 'quiz-lms-wc'); ?>
                </button>
            </div>
        </form>
    </div>

    <div class="qlms-quiz-results" style="display: none;">
        <div class="qlms-results-container">
            <h3><?php _e('Quiz Results', 'quiz-lms-wc'); ?></h3>
            <div class="qlms-results-content"></div>
        </div>
    </div>
</div>
