/**
 * Public-facing JavaScript for Quiz & LMS
 */

(function($) {
    'use strict';

    let quizTimer = null;
    let timeRemaining = 0;
    let startTime = null;

    $(document).ready(function() {
        // Start quiz button
        $('.qlms-start-quiz-btn').on('click', function(e) {
            e.preventDefault();
            startQuiz($(this).closest('.qlms-quiz-container'));
        });

        // Quiz form submission
        $('#qlms-quiz-form').on('submit', function(e) {
            e.preventDefault();

            if (confirm(qlms_public.strings.confirm_submit)) {
                submitQuiz($(this));
            }
        });

        // Mark lesson as complete
        $('.qlms-mark-complete-btn').on('click', function(e) {
            e.preventDefault();
            markLessonComplete($(this));
        });
    });

    /**
     * Start quiz
     */
    function startQuiz($container) {
        const quizId = $container.data('quiz-id');

        // Show loading
        $container.find('.qlms-quiz-start-screen').html('<p>' + qlms_public.strings.loading + '</p>');

        // Get quiz data
        $.ajax({
            url: qlms_public.ajax_url,
            type: 'POST',
            data: {
                action: 'qlms_get_quiz_data',
                quiz_id: quizId,
                nonce: qlms_public.nonce
            },
            success: function(response) {
                if (response.success) {
                    // Hide start screen
                    $container.find('.qlms-quiz-start-screen').hide();

                    // Show quiz content
                    $container.find('.qlms-quiz-content').show();

                    // Set attempt ID
                    $('#qlms-attempt-id').val(response.data.attempt_id);

                    // Start timer if time limit is set
                    const timeLimit = $('#qlms-quiz-form').data('time-limit');
                    if (timeLimit > 0) {
                        startTimer(timeLimit);
                    }

                    // Record start time
                    startTime = new Date();
                } else {
                    alert(response.data.message || qlms_public.strings.error);
                    $container.find('.qlms-quiz-start-screen').html(
                        '<p>' + (response.data.message || qlms_public.strings.error) + '</p>'
                    );
                }
            },
            error: function() {
                alert(qlms_public.strings.error);
                $container.find('.qlms-quiz-start-screen').html(
                    '<button class="qlms-start-quiz-btn button button-primary button-large">' +
                    qlms_public.strings.submit_quiz + '</button>'
                );
            }
        });
    }

    /**
     * Start quiz timer
     */
    function startTimer(minutes) {
        timeRemaining = minutes * 60; // Convert to seconds

        quizTimer = setInterval(function() {
            timeRemaining--;

            // Update timer display
            const mins = Math.floor(timeRemaining / 60);
            const secs = timeRemaining % 60;
            const display = String(mins).padStart(2, '0') + ':' + String(secs).padStart(2, '0');

            $('.qlms-timer-text').text(display);

            // Change color when time is running low
            if (timeRemaining <= 60) {
                $('.qlms-quiz-timer').addClass('danger');
            } else if (timeRemaining <= 300) {
                $('.qlms-quiz-timer').addClass('warning');
            }

            // Time's up
            if (timeRemaining <= 0) {
                clearInterval(quizTimer);
                alert(qlms_public.strings.time_up);
                submitQuiz($('#qlms-quiz-form'));
            }
        }, 1000);
    }

    /**
     * Submit quiz
     */
    function submitQuiz($form) {
        // Stop timer
        if (quizTimer) {
            clearInterval(quizTimer);
        }

        // Calculate time taken
        const timeTaken = startTime ? Math.floor((new Date() - startTime) / 1000) : 0;

        // Get form data
        const formData = $form.serializeArray();

        // Convert answers to proper format
        const answers = {};
        formData.forEach(function(item) {
            if (item.name.startsWith('answers[')) {
                const match = item.name.match(/answers\[(\d+)\]/);
                if (match) {
                    const questionId = match[1];
                    if (!answers[questionId]) {
                        answers[questionId] = [];
                    }
                    answers[questionId].push(item.value);
                }
            }
        });

        // Convert single answers from array to string
        Object.keys(answers).forEach(function(questionId) {
            if (answers[questionId].length === 1) {
                answers[questionId] = answers[questionId][0];
            }
        });

        const quizId = $form.data('quiz-id');
        const attemptId = $('#qlms-attempt-id').val();

        // Disable form
        $form.find(':input').prop('disabled', true);
        $form.find('button[type="submit"]').html(qlms_public.strings.loading);

        // Submit via AJAX
        $.ajax({
            url: qlms_public.ajax_url,
            type: 'POST',
            data: {
                action: 'qlms_submit_quiz',
                quiz_id: quizId,
                attempt_id: attemptId,
                answers: answers,
                time_taken: timeTaken,
                nonce: qlms_public.nonce
            },
            success: function(response) {
                if (response.success) {
                    displayResults(response.data);
                } else {
                    alert(response.data.message || qlms_public.strings.error);
                    $form.find(':input').prop('disabled', false);
                    $form.find('button[type="submit"]').html(qlms_public.strings.submit_quiz);
                }
            },
            error: function() {
                alert(qlms_public.strings.error);
                $form.find(':input').prop('disabled', false);
                $form.find('button[type="submit"]').html(qlms_public.strings.submit_quiz);
            }
        });
    }

    /**
     * Display quiz results
     */
    function displayResults(data) {
        // Hide quiz content
        $('.qlms-quiz-content').hide();

        // Build results HTML
        const resultClass = data.passed ? 'passed' : 'failed';
        const resultsHtml = `
            <div class="qlms-result-score ${resultClass}">
                ${data.score}%
            </div>
            <div class="qlms-result-details">
                <p><strong>${qlms_public.strings.correct_answers || 'Correct Answers'}:</strong> ${data.correct_answers} / ${data.total_questions}</p>
                <p><strong>${qlms_public.strings.pass_score || 'Pass Score'}:</strong> ${data.pass_percentage}%</p>
                <p><strong>${qlms_public.strings.status || 'Status'}:</strong> ${data.passed ? '<span style="color: #28a745;">✓ Passed</span>' : '<span style="color: #dc3545;">✗ Failed</span>'}</p>
            </div>
            <p class="qlms-result-message">${data.message}</p>
        `;

        // Show results
        $('.qlms-results-content').html(resultsHtml);
        $('.qlms-quiz-results').show();

        // Scroll to results
        $('html, body').animate({
            scrollTop: $('.qlms-quiz-results').offset().top - 100
        }, 500);
    }

    /**
     * Mark lesson as complete
     */
    function markLessonComplete($button) {
        const lessonId = $button.data('lesson-id');

        $button.prop('disabled', true).html(qlms_public.strings.loading);

        $.ajax({
            url: qlms_public.ajax_url,
            type: 'POST',
            data: {
                action: 'qlms_save_progress',
                lesson_id: lessonId,
                status: 'completed',
                nonce: qlms_public.nonce
            },
            success: function(response) {
                if (response.success) {
                    $button.html('✓ ' + (qlms_public.strings.completed || 'Completed'));
                    $button.addClass('completed');

                    // Reload page after 1 second
                    setTimeout(function() {
                        location.reload();
                    }, 1000);
                } else {
                    alert(response.data.message || qlms_public.strings.error);
                    $button.prop('disabled', false).html(qlms_public.strings.mark_complete || 'Mark as Complete');
                }
            },
            error: function() {
                alert(qlms_public.strings.error);
                $button.prop('disabled', false).html(qlms_public.strings.mark_complete || 'Mark as Complete');
            }
        });
    }

})(jQuery);
