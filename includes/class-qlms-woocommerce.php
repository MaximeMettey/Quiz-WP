<?php
/**
 * WooCommerce integration class
 */

class QLMS_WooCommerce {

    public function __construct() {
        if (!class_exists('WooCommerce')) {
            return;
        }

        // Add hooks for WooCommerce integration
        add_action('woocommerce_order_status_completed', array($this, 'grant_access_on_purchase'));
        add_action('woocommerce_subscription_status_active', array($this, 'grant_access_on_subscription'));
        add_action('woocommerce_subscription_status_cancelled', array($this, 'revoke_access_on_subscription_cancel'));
        add_action('woocommerce_subscription_status_expired', array($this, 'revoke_access_on_subscription_expire'));

        // Add custom product type
        add_filter('product_type_selector', array($this, 'add_course_product_type'));
        add_filter('woocommerce_product_data_tabs', array($this, 'add_course_product_tab'));
        add_action('woocommerce_product_data_panels', array($this, 'add_course_product_panel'));
        add_action('woocommerce_process_product_meta', array($this, 'save_course_product_data'));
    }

    /**
     * Grant access to course/quiz on purchase
     */
    public function grant_access_on_purchase($order_id) {
        $order = wc_get_order($order_id);
        $user_id = $order->get_user_id();

        if (!$user_id) {
            return;
        }

        foreach ($order->get_items() as $item) {
            $product_id = $item->get_product_id();
            $this->grant_access_by_product($product_id, $user_id);
        }
    }

    /**
     * Grant access on active subscription
     */
    public function grant_access_on_subscription($subscription) {
        $user_id = $subscription->get_user_id();

        if (!$user_id) {
            return;
        }

        foreach ($subscription->get_items() as $item) {
            $product_id = $item->get_product_id();
            $this->grant_access_by_product($product_id, $user_id);
        }
    }

    /**
     * Revoke access on subscription cancellation
     */
    public function revoke_access_on_subscription_cancel($subscription) {
        $this->revoke_access_on_subscription($subscription);
    }

    /**
     * Revoke access on subscription expiration
     */
    public function revoke_access_on_subscription_expire($subscription) {
        $this->revoke_access_on_subscription($subscription);
    }

    /**
     * Revoke access for subscription
     */
    private function revoke_access_on_subscription($subscription) {
        $user_id = $subscription->get_user_id();

        if (!$user_id) {
            return;
        }

        foreach ($subscription->get_items() as $item) {
            $product_id = $item->get_product_id();
            $this->revoke_access_by_product($product_id, $user_id);
        }
    }

    /**
     * Grant access by product ID
     */
    private function grant_access_by_product($product_id, $user_id) {
        global $wpdb;

        // Check if product is linked to a course
        $course = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}qlms_courses WHERE product_id = %d",
            $product_id
        ));

        if ($course) {
            QLMS_Course::enroll_user($course->id, $user_id);
        }

        // Check if product is linked to a quiz
        $quizzes = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}qlms_quizzes WHERE product_id = %d",
            $product_id
        ));

        foreach ($quizzes as $quiz) {
            // Mark quiz as accessible (via user meta or custom logic)
            update_user_meta($user_id, 'qlms_quiz_access_' . $quiz->id, 1);
        }
    }

    /**
     * Revoke access by product ID
     */
    private function revoke_access_by_product($product_id, $user_id) {
        global $wpdb;

        // Revoke course access
        $course = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}qlms_courses WHERE product_id = %d",
            $product_id
        ));

        if ($course) {
            // Mark course as inactive for user
            update_user_meta($user_id, 'qlms_course_access_' . $course->id, 0);
        }

        // Revoke quiz access
        $quizzes = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}qlms_quizzes WHERE product_id = %d",
            $product_id
        ));

        foreach ($quizzes as $quiz) {
            delete_user_meta($user_id, 'qlms_quiz_access_' . $quiz->id);
        }
    }

    /**
     * Add course product type to WooCommerce
     */
    public function add_course_product_type($types) {
        $types['qlms_course'] = __('QLMS Course', 'quiz-lms-wc');
        return $types;
    }

    /**
     * Add course tab to product data
     */
    public function add_course_product_tab($tabs) {
        $tabs['qlms_course'] = array(
            'label'    => __('QLMS Course', 'quiz-lms-wc'),
            'target'   => 'qlms_course_product_data',
            'class'    => array('show_if_qlms_course'),
            'priority' => 21,
        );
        return $tabs;
    }

    /**
     * Add course product panel
     */
    public function add_course_product_panel() {
        global $post;
        ?>
        <div id="qlms_course_product_data" class="panel woocommerce_options_panel">
            <div class="options_group">
                <?php
                // Get all courses
                $courses = get_posts(array(
                    'post_type' => 'qlms_course',
                    'posts_per_page' => -1,
                    'orderby' => 'title',
                    'order' => 'ASC',
                ));

                $course_options = array('' => __('Select a course', 'quiz-lms-wc'));
                foreach ($courses as $course) {
                    $course_options[$course->ID] = $course->post_title;
                }

                woocommerce_wp_select(array(
                    'id' => '_qlms_linked_course',
                    'label' => __('Linked Course', 'quiz-lms-wc'),
                    'options' => $course_options,
                    'desc_tip' => true,
                    'description' => __('Select the course that this product grants access to', 'quiz-lms-wc'),
                ));

                woocommerce_wp_checkbox(array(
                    'id' => '_qlms_grant_certificate',
                    'label' => __('Grant Certificate', 'quiz-lms-wc'),
                    'description' => __('Award a certificate upon course completion', 'quiz-lms-wc'),
                ));
                ?>
            </div>
        </div>
        <?php
    }

    /**
     * Save course product data
     */
    public function save_course_product_data($post_id) {
        $linked_course = isset($_POST['_qlms_linked_course']) ? sanitize_text_field($_POST['_qlms_linked_course']) : '';
        $grant_certificate = isset($_POST['_qlms_grant_certificate']) ? 'yes' : 'no';

        update_post_meta($post_id, '_qlms_linked_course', $linked_course);
        update_post_meta($post_id, '_qlms_grant_certificate', $grant_certificate);

        // Update course meta to link back to this product
        if ($linked_course) {
            $course_meta = QLMS_Course::get_by_post($linked_course);
            if ($course_meta) {
                QLMS_Course::update($course_meta->id, array(
                    'product_id' => $post_id,
                    'is_free' => 0,
                ));
            }
        }
    }

    /**
     * Check if user has purchased product
     */
    public static function user_has_purchased($product_id, $user_id = null) {
        if (!$user_id) {
            $user_id = get_current_user_id();
        }

        if (!function_exists('wc_customer_bought_product')) {
            return false;
        }

        $user = get_user_by('id', $user_id);
        if (!$user) {
            return false;
        }

        return wc_customer_bought_product($user->user_email, $user_id, $product_id);
    }

    /**
     * Check if user has active subscription for product
     */
    public static function user_has_active_subscription($product_id, $user_id = null) {
        if (!$user_id) {
            $user_id = get_current_user_id();
        }

        if (!function_exists('wcs_user_has_subscription')) {
            return false;
        }

        return wcs_user_has_subscription($user_id, $product_id, 'active');
    }
}

// Initialize WooCommerce integration
new QLMS_WooCommerce();
