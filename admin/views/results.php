<?php
/**
 * Results page view
 */

if (!defined('WPINC')) {
    die;
}
?>

<div class="wrap">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>

    <?php if (empty($attempts)): ?>
        <p><?php _e('No quiz attempts found.', 'quiz-lms-wc'); ?></p>
    <?php else: ?>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th><?php _e('User', 'quiz-lms-wc'); ?></th>
                    <th><?php _e('Quiz', 'quiz-lms-wc'); ?></th>
                    <th><?php _e('Score', 'quiz-lms-wc'); ?></th>
                    <th><?php _e('Correct', 'quiz-lms-wc'); ?></th>
                    <th><?php _e('Total', 'quiz-lms-wc'); ?></th>
                    <th><?php _e('Time Taken', 'quiz-lms-wc'); ?></th>
                    <th><?php _e('Status', 'quiz-lms-wc'); ?></th>
                    <th><?php _e('Date', 'quiz-lms-wc'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($attempts as $attempt): ?>
                    <tr>
                        <td><?php echo esc_html($attempt->user_name); ?></td>
                        <td><?php echo esc_html($attempt->quiz_title); ?></td>
                        <td><strong><?php echo round($attempt->score, 2); ?>%</strong></td>
                        <td><?php echo $attempt->correct_answers; ?></td>
                        <td><?php echo $attempt->total_questions; ?></td>
                        <td><?php echo gmdate('i:s', $attempt->time_taken); ?></td>
                        <td>
                            <?php if ($attempt->passed): ?>
                                <span style="color: #46b450;">✓ <?php _e('Passed', 'quiz-lms-wc'); ?></span>
                            <?php else: ?>
                                <span style="color: #dc3232;">✗ <?php _e('Failed', 'quiz-lms-wc'); ?></span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($attempt->completed_at)); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
