<?php
/**
 * Quiz edit view
 */

if (!defined('WPINC')) {
    die;
}

$quiz_id = isset($_GET['quiz_id']) ? intval($_GET['quiz_id']) : 0;
$quiz = $quiz_id ? QLMS_Quiz::get($quiz_id) : null;
$questions = $quiz_id ? QLMS_Quiz::get_questions($quiz_id) : array();

// Get all courses for linking
$courses = get_posts(array(
    'post_type' => 'qlms_course',
    'posts_per_page' => -1,
    'orderby' => 'title',
    'order' => 'ASC',
));

// Get all lessons for linking
$lessons = get_posts(array(
    'post_type' => 'qlms_lesson',
    'posts_per_page' => -1,
    'orderby' => 'title',
    'order' => 'ASC',
));

// Get WooCommerce products if available
$products = array();
if (class_exists('WooCommerce')) {
    $products = get_posts(array(
        'post_type' => 'product',
        'posts_per_page' => -1,
        'orderby' => 'title',
        'order' => 'ASC',
    ));
}
?>

<div class="wrap">
    <h1><?php echo $quiz_id ? __('Edit Quiz', 'quiz-lms-wc') : __('Add New Quiz', 'quiz-lms-wc'); ?></h1>

    <?php if (isset($_GET['message']) && $_GET['message'] === 'saved'): ?>
        <div class="notice notice-success is-dismissible">
            <p><?php _e('Quiz saved successfully!', 'quiz-lms-wc'); ?></p>
        </div>
    <?php endif; ?>

    <form method="post" id="qlms-quiz-form" action="">
        <?php wp_nonce_field('qlms_save_quiz', 'qlms_quiz_nonce'); ?>
        <input type="hidden" name="action" value="qlms_save_quiz" />
        <input type="hidden" name="quiz_id" value="<?php echo esc_attr($quiz_id); ?>" />

        <div id="poststuff">
            <div id="post-body" class="metabox-holder columns-2">
                <div id="post-body-content">
                    <!-- Title -->
                    <div class="qlms-field-group">
                        <label for="quiz_title"><strong><?php _e('Quiz Title', 'quiz-lms-wc'); ?></strong></label>
                        <input type="text" id="quiz_title" name="title" value="<?php echo $quiz ? esc_attr($quiz->title) : ''; ?>" class="widefat" required />
                    </div>

                    <!-- Description -->
                    <div class="qlms-field-group">
                        <label for="quiz_description"><strong><?php _e('Description', 'quiz-lms-wc'); ?></strong></label>
                        <?php
                        wp_editor(
                            $quiz ? $quiz->description : '',
                            'quiz_description',
                            array(
                                'textarea_name' => 'description',
                                'media_buttons' => false,
                                'textarea_rows' => 5,
                            )
                        );
                        ?>
                    </div>

                    <!-- Questions Section -->
                    <div class="qlms-questions-section">
                        <h2><?php _e('Questions', 'quiz-lms-wc'); ?></h2>
                        <div id="qlms-questions-container">
                            <?php if (!empty($questions)): ?>
                                <?php foreach ($questions as $index => $question): ?>
                                    <?php include 'quiz-question-item.php'; ?>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                        <button type="button" id="qlms-add-question-btn" class="button button-secondary">
                            <?php _e('Add Question', 'quiz-lms-wc'); ?>
                        </button>
                    </div>
                </div>

                <div id="postbox-container-1" class="postbox-container">
                    <div class="postbox">
                        <h2 class="hndle"><?php _e('Quiz Settings', 'quiz-lms-wc'); ?></h2>
                        <div class="inside">
                            <!-- Time Limit -->
                            <div class="qlms-field-group">
                                <label for="time_limit"><?php _e('Time Limit (minutes)', 'quiz-lms-wc'); ?></label>
                                <input type="number" id="time_limit" name="time_limit" value="<?php echo $quiz ? esc_attr($quiz->time_limit) : '0'; ?>" min="0" style="width: 100%;" />
                                <p class="description"><?php _e('0 for no time limit', 'quiz-lms-wc'); ?></p>
                            </div>

                            <!-- Pass Percentage -->
                            <div class="qlms-field-group">
                                <label for="pass_percentage"><?php _e('Pass Percentage', 'quiz-lms-wc'); ?></label>
                                <input type="number" id="pass_percentage" name="pass_percentage" value="<?php echo $quiz ? esc_attr($quiz->pass_percentage) : '70'; ?>" min="0" max="100" style="width: 100%;" />
                            </div>

                            <!-- Attempts Allowed -->
                            <div class="qlms-field-group">
                                <label for="attempts_allowed"><?php _e('Attempts Allowed', 'quiz-lms-wc'); ?></label>
                                <input type="number" id="attempts_allowed" name="attempts_allowed" value="<?php echo $quiz ? esc_attr($quiz->attempts_allowed) : '-1'; ?>" min="-1" style="width: 100%;" />
                                <p class="description"><?php _e('-1 for unlimited attempts', 'quiz-lms-wc'); ?></p>
                            </div>

                            <!-- Show Correct Answers -->
                            <div class="qlms-field-group">
                                <label>
                                    <input type="checkbox" name="show_correct_answers" value="1" <?php echo ($quiz && $quiz->show_correct_answers) ? 'checked' : 'checked'; ?> />
                                    <?php _e('Show correct answers after submission', 'quiz-lms-wc'); ?>
                                </label>
                            </div>

                            <!-- Randomize Questions -->
                            <div class="qlms-field-group">
                                <label>
                                    <input type="checkbox" name="randomize_questions" value="1" <?php echo ($quiz && $quiz->randomize_questions) ? 'checked' : ''; ?> />
                                    <?php _e('Randomize questions', 'quiz-lms-wc'); ?>
                                </label>
                            </div>

                            <!-- Free/Paid -->
                            <div class="qlms-field-group">
                                <label>
                                    <input type="checkbox" id="is_free" name="is_free" value="1" <?php echo (!$quiz || $quiz->is_free) ? 'checked' : ''; ?> />
                                    <?php _e('Free Quiz', 'quiz-lms-wc'); ?>
                                </label>
                            </div>

                            <!-- WooCommerce Product -->
                            <?php if (!empty($products)): ?>
                            <div class="qlms-field-group qlms-product-field" style="<?php echo (!$quiz || $quiz->is_free) ? 'display:none;' : ''; ?>">
                                <label for="product_id"><?php _e('WooCommerce Product', 'quiz-lms-wc'); ?></label>
                                <select id="product_id" name="product_id" style="width: 100%;">
                                    <option value=""><?php _e('Select Product', 'quiz-lms-wc'); ?></option>
                                    <?php foreach ($products as $product): ?>
                                        <option value="<?php echo $product->ID; ?>" <?php echo ($quiz && $quiz->product_id == $product->ID) ? 'selected' : ''; ?>>
                                            <?php echo esc_html($product->post_title); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <?php endif; ?>

                            <!-- Link to Course -->
                            <div class="qlms-field-group">
                                <label for="course_id"><?php _e('Link to Course', 'quiz-lms-wc'); ?></label>
                                <select id="course_id" name="course_id" style="width: 100%;">
                                    <option value=""><?php _e('None', 'quiz-lms-wc'); ?></option>
                                    <?php foreach ($courses as $course): ?>
                                        <option value="<?php echo $course->ID; ?>" <?php echo ($quiz && $quiz->course_id == $course->ID) ? 'selected' : ''; ?>>
                                            <?php echo esc_html($course->post_title); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <!-- Link to Lesson -->
                            <div class="qlms-field-group">
                                <label for="lesson_id"><?php _e('Link to Lesson', 'quiz-lms-wc'); ?></label>
                                <select id="lesson_id" name="lesson_id" style="width: 100%;">
                                    <option value=""><?php _e('None', 'quiz-lms-wc'); ?></option>
                                    <?php foreach ($lessons as $lesson): ?>
                                        <option value="<?php echo $lesson->ID; ?>" <?php echo ($quiz && $quiz->lesson_id == $lesson->ID) ? 'selected' : ''; ?>>
                                            <?php echo esc_html($lesson->post_title); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <!-- Status -->
                            <div class="qlms-field-group">
                                <label for="status"><?php _e('Status', 'quiz-lms-wc'); ?></label>
                                <select id="status" name="status" style="width: 100%;">
                                    <option value="draft" <?php echo ($quiz && $quiz->status == 'draft') ? 'selected' : ''; ?>><?php _e('Draft', 'quiz-lms-wc'); ?></option>
                                    <option value="published" <?php echo ($quiz && $quiz->status == 'published') ? 'selected' : ''; ?>><?php _e('Published', 'quiz-lms-wc'); ?></option>
                                </select>
                            </div>

                            <!-- Submit Button -->
                            <div class="qlms-field-group">
                                <button type="submit" class="button button-primary button-large" style="width: 100%;">
                                    <?php echo $quiz_id ? __('Update Quiz', 'quiz-lms-wc') : __('Create Quiz', 'quiz-lms-wc'); ?>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<script type="text/html" id="qlms-question-template">
    <div class="qlms-question-item postbox" data-question-index="__INDEX__">
        <div class="postbox-header">
            <h2 class="hndle"><?php _e('Question', 'quiz-lms-wc'); ?> <span class="question-number"></span></h2>
            <div class="handle-actions">
                <button type="button" class="qlms-remove-question button-link-delete"><?php _e('Remove', 'quiz-lms-wc'); ?></button>
            </div>
        </div>
        <div class="inside">
            <div class="qlms-field-group">
                <label><?php _e('Question Text', 'quiz-lms-wc'); ?></label>
                <textarea name="questions[__INDEX__][question_text]" rows="3" style="width: 100%;" required></textarea>
            </div>

            <div class="qlms-field-group">
                <label><?php _e('Question Type', 'quiz-lms-wc'); ?></label>
                <select name="questions[__INDEX__][question_type]" class="question-type-select" style="width: 100%;">
                    <option value="single"><?php _e('Single Choice', 'quiz-lms-wc'); ?></option>
                    <option value="multiple"><?php _e('Multiple Choice', 'quiz-lms-wc'); ?></option>
                </select>
            </div>

            <div class="qlms-field-group">
                <label><?php _e('Points', 'quiz-lms-wc'); ?></label>
                <input type="number" name="questions[__INDEX__][points]" value="1" min="1" style="width: 100px;" />
            </div>

            <div class="qlms-field-group">
                <label><?php _e('Answers', 'quiz-lms-wc'); ?></label>
                <div class="qlms-answers-list">
                    <div class="qlms-answer-item">
                        <input type="text" name="questions[__INDEX__][answers][0][text]" placeholder="<?php _e('Answer 1', 'quiz-lms-wc'); ?>" style="width: 70%;" />
                        <label style="display: inline-block; margin-left: 10px;">
                            <input type="checkbox" name="questions[__INDEX__][answers][0][is_correct]" value="1" class="answer-correct-checkbox" />
                            <?php _e('Correct', 'quiz-lms-wc'); ?>
                        </label>
                    </div>
                    <div class="qlms-answer-item">
                        <input type="text" name="questions[__INDEX__][answers][1][text]" placeholder="<?php _e('Answer 2', 'quiz-lms-wc'); ?>" style="width: 70%;" />
                        <label style="display: inline-block; margin-left: 10px;">
                            <input type="checkbox" name="questions[__INDEX__][answers][1][is_correct]" value="1" class="answer-correct-checkbox" />
                            <?php _e('Correct', 'quiz-lms-wc'); ?>
                        </label>
                    </div>
                </div>
                <button type="button" class="qlms-add-answer-btn button button-small" style="margin-top: 10px;">
                    <?php _e('Add Answer', 'quiz-lms-wc'); ?>
                </button>
            </div>
        </div>
    </div>
</script>

<script>
jQuery(document).ready(function($) {
    let questionIndex = <?php echo count($questions); ?>;

    // Toggle product field based on is_free checkbox
    $('#is_free').on('change', function() {
        if ($(this).is(':checked')) {
            $('.qlms-product-field').hide();
        } else {
            $('.qlms-product-field').show();
        }
    });

    // Add question
    $('#qlms-add-question-btn').on('click', function() {
        const template = $('#qlms-question-template').html();
        const html = template.replace(/__INDEX__/g, questionIndex);
        $('#qlms-questions-container').append(html);
        questionIndex++;
        updateQuestionNumbers();
    });

    // Remove question
    $(document).on('click', '.qlms-remove-question', function() {
        if (confirm('<?php _e('Are you sure you want to remove this question?', 'quiz-lms-wc'); ?>')) {
            $(this).closest('.qlms-question-item').remove();
            updateQuestionNumbers();
        }
    });

    // Add answer
    $(document).on('click', '.qlms-add-answer-btn', function() {
        const $questionItem = $(this).closest('.qlms-question-item');
        const questionIdx = $questionItem.data('question-index');
        const answerCount = $questionItem.find('.qlms-answer-item').length;

        const answerHtml = `
            <div class="qlms-answer-item">
                <input type="text" name="questions[${questionIdx}][answers][${answerCount}][text]" placeholder="<?php _e('Answer', 'quiz-lms-wc'); ?> ${answerCount + 1}" style="width: 70%;" />
                <label style="display: inline-block; margin-left: 10px;">
                    <input type="checkbox" name="questions[${questionIdx}][answers][${answerCount}][is_correct]" value="1" class="answer-correct-checkbox" />
                    <?php _e('Correct', 'quiz-lms-wc'); ?>
                </label>
                <button type="button" class="qlms-remove-answer-btn button-link-delete" style="margin-left: 5px;"><?php _e('Remove', 'quiz-lms-wc'); ?></button>
            </div>
        `;

        $(this).prev('.qlms-answers-list').append(answerHtml);
    });

    // Remove answer
    $(document).on('click', '.qlms-remove-answer-btn', function() {
        $(this).closest('.qlms-answer-item').remove();
    });

    // Handle question type change (single/multiple)
    $(document).on('change', '.question-type-select', function() {
        const $question = $(this).closest('.qlms-question-item');
        const type = $(this).val();

        if (type === 'single') {
            // Convert checkboxes to radio-like behavior
            $question.find('.answer-correct-checkbox').on('change', function() {
                if ($(this).is(':checked')) {
                    $question.find('.answer-correct-checkbox').not(this).prop('checked', false);
                }
            });
        } else {
            // Remove the single-selection restriction
            $question.find('.answer-correct-checkbox').off('change');
        }
    });

    function updateQuestionNumbers() {
        $('.qlms-question-item').each(function(index) {
            $(this).find('.question-number').text(index + 1);
        });
    }

    updateQuestionNumbers();
});
</script>

<style>
.qlms-field-group {
    margin-bottom: 20px;
}
.qlms-field-group label {
    display: block;
    margin-bottom: 5px;
    font-weight: 600;
}
.qlms-question-item {
    margin-bottom: 20px;
}
.qlms-answer-item {
    margin-bottom: 10px;
    padding: 10px;
    background: #f9f9f9;
    border-radius: 3px;
}
.qlms-questions-section {
    margin-top: 30px;
}
#qlms-questions-container {
    margin-bottom: 15px;
}
</style>
