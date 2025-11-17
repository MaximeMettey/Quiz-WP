/**
 * Admin JavaScript for Quiz & LMS
 */

(function($) {
    'use strict';

    $(document).ready(function() {
        // Initialize sortable for questions
        if ($('.qlms-question-list').length) {
            $('.qlms-question-list').sortable({
                handle: '.qlms-question-handle',
                placeholder: 'qlms-sortable-placeholder',
                update: function(event, ui) {
                    updateQuestionOrder();
                }
            });
        }

        // Delete quiz
        $('.qlms-delete-quiz').on('click', function(e) {
            e.preventDefault();

            if (!confirm(qlms_admin.strings.confirm_delete)) {
                return;
            }

            const quizId = $(this).data('quiz-id');
            deleteQuiz(quizId);
        });

        // Delete question
        $('.qlms-delete-question').on('click', function(e) {
            e.preventDefault();

            if (!confirm(qlms_admin.strings.confirm_delete)) {
                return;
            }

            const questionId = $(this).data('question-id');
            deleteQuestion(questionId);
        });

        // Add answer field
        $('.qlms-add-answer').on('click', function(e) {
            e.preventDefault();
            addAnswerField($(this).closest('.qlms-question-item'));
        });

        // Remove answer field
        $(document).on('click', '.qlms-remove-answer', function(e) {
            e.preventDefault();
            $(this).closest('.qlms-answer-field').remove();
        });
    });

    /**
     * Update question order
     */
    function updateQuestionOrder() {
        $('.qlms-question-item').each(function(index) {
            $(this).find('.qlms-question-order').val(index);
        });
    }

    /**
     * Delete quiz
     */
    function deleteQuiz(quizId) {
        $.ajax({
            url: qlms_admin.ajax_url,
            type: 'POST',
            data: {
                action: 'qlms_delete_quiz',
                quiz_id: quizId,
                nonce: qlms_admin.nonce
            },
            success: function(response) {
                if (response.success) {
                    location.reload();
                } else {
                    alert(response.data.message || qlms_admin.strings.error);
                }
            },
            error: function() {
                alert(qlms_admin.strings.error);
            }
        });
    }

    /**
     * Delete question
     */
    function deleteQuestion(questionId) {
        $.ajax({
            url: qlms_admin.ajax_url,
            type: 'POST',
            data: {
                action: 'qlms_delete_question',
                question_id: questionId,
                nonce: qlms_admin.nonce
            },
            success: function(response) {
                if (response.success) {
                    $('[data-question-id="' + questionId + '"]').fadeOut(300, function() {
                        $(this).remove();
                    });
                } else {
                    alert(response.data.message || qlms_admin.strings.error);
                }
            },
            error: function() {
                alert(qlms_admin.strings.error);
            }
        });
    }

    /**
     * Add answer field
     */
    function addAnswerField($question) {
        const $answerList = $question.find('.qlms-answers-list');
        const answerCount = $answerList.find('.qlms-answer-field').length;

        const answerHtml = `
            <div class="qlms-answer-field">
                <input type="text" name="answers[${answerCount}][text]" placeholder="${qlms_admin.strings.answer_text || 'Answer text'}" />
                <label>
                    <input type="checkbox" name="answers[${answerCount}][is_correct]" value="1" />
                    ${qlms_admin.strings.correct || 'Correct'}
                </label>
                <button type="button" class="button qlms-remove-answer">${qlms_admin.strings.remove || 'Remove'}</button>
            </div>
        `;

        $answerList.append(answerHtml);
    }

})(jQuery);
