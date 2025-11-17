<?php
/**
 * Quizzes list view
 */

if (!defined('WPINC')) {
    die;
}
?>

<div class="wrap">
    <h1 class="wp-heading-inline"><?php echo esc_html(get_admin_page_title()); ?></h1>
    <a href="<?php echo admin_url('admin.php?page=qlms-add-quiz'); ?>" class="page-title-action">
        <?php _e('Add New', 'quiz-lms-wc'); ?>
    </a>

    <?php if (empty($quizzes)): ?>
        <p><?php _e('No quizzes found. Create your first quiz!', 'quiz-lms-wc'); ?></p>
    <?php else: ?>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th><?php _e('Title', 'quiz-lms-wc'); ?></th>
                    <th><?php _e('Questions', 'quiz-lms-wc'); ?></th>
                    <th><?php _e('Time Limit', 'quiz-lms-wc'); ?></th>
                    <th><?php _e('Pass %', 'quiz-lms-wc'); ?></th>
                    <th><?php _e('Status', 'quiz-lms-wc'); ?></th>
                    <th><?php _e('Created', 'quiz-lms-wc'); ?></th>
                    <th><?php _e('Actions', 'quiz-lms-wc'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($quizzes as $quiz):
                    $questions = QLMS_Quiz::get_questions($quiz->id);
                ?>
                    <tr>
                        <td><strong><?php echo esc_html($quiz->title); ?></strong></td>
                        <td><?php echo count($questions); ?></td>
                        <td><?php echo $quiz->time_limit > 0 ? $quiz->time_limit . ' ' . __('min', 'quiz-lms-wc') : '—'; ?></td>
                        <td><?php echo $quiz->pass_percentage; ?>%</td>
                        <td><?php echo esc_html(ucfirst($quiz->status)); ?></td>
                        <td><?php echo date_i18n(get_option('date_format'), strtotime($quiz->created_at)); ?></td>
                        <td>
                            <a href="<?php echo admin_url('admin.php?page=qlms-add-quiz&quiz_id=' . $quiz->id); ?>">
                                <?php _e('Edit', 'quiz-lms-wc'); ?>
                            </a> |
                            <a href="#" class="qlms-delete-quiz" data-quiz-id="<?php echo $quiz->id; ?>" style="color: #b32d2e;">
                                <?php _e('Delete', 'quiz-lms-wc'); ?>
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
