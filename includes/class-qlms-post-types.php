<?php
/**
 * Register custom post types and taxonomies
 */

class QLMS_Post_Types {

    /**
     * Register Course post type
     */
    public function register_course_post_type() {
        $labels = array(
            'name'                  => _x('Courses', 'Post Type General Name', 'quiz-lms-wc'),
            'singular_name'         => _x('Course', 'Post Type Singular Name', 'quiz-lms-wc'),
            'menu_name'             => __('Courses', 'quiz-lms-wc'),
            'name_admin_bar'        => __('Course', 'quiz-lms-wc'),
            'archives'              => __('Course Archives', 'quiz-lms-wc'),
            'attributes'            => __('Course Attributes', 'quiz-lms-wc'),
            'parent_item_colon'     => __('Parent Course:', 'quiz-lms-wc'),
            'all_items'             => __('All Courses', 'quiz-lms-wc'),
            'add_new_item'          => __('Add New Course', 'quiz-lms-wc'),
            'add_new'               => __('Add New', 'quiz-lms-wc'),
            'new_item'              => __('New Course', 'quiz-lms-wc'),
            'edit_item'             => __('Edit Course', 'quiz-lms-wc'),
            'update_item'           => __('Update Course', 'quiz-lms-wc'),
            'view_item'             => __('View Course', 'quiz-lms-wc'),
            'view_items'            => __('View Courses', 'quiz-lms-wc'),
            'search_items'          => __('Search Course', 'quiz-lms-wc'),
        );

        $args = array(
            'label'                 => __('Course', 'quiz-lms-wc'),
            'description'           => __('LMS Courses', 'quiz-lms-wc'),
            'labels'                => $labels,
            'supports'              => array('title', 'editor', 'thumbnail', 'excerpt', 'author', 'comments'),
            'taxonomies'            => array('qlms_course_category', 'qlms_course_tag'),
            'hierarchical'          => false,
            'public'                => true,
            'show_ui'               => true,
            'show_in_menu'          => true,
            'menu_position'         => 5,
            'menu_icon'             => 'dashicons-welcome-learn-more',
            'show_in_admin_bar'     => true,
            'show_in_nav_menus'     => true,
            'can_export'            => true,
            'has_archive'           => true,
            'exclude_from_search'   => false,
            'publicly_queryable'    => true,
            'capability_type'       => 'post',
            'show_in_rest'          => true,
        );

        register_post_type('qlms_course', $args);
    }

    /**
     * Register Lesson post type
     */
    public function register_lesson_post_type() {
        $labels = array(
            'name'                  => _x('Lessons', 'Post Type General Name', 'quiz-lms-wc'),
            'singular_name'         => _x('Lesson', 'Post Type Singular Name', 'quiz-lms-wc'),
            'menu_name'             => __('Lessons', 'quiz-lms-wc'),
            'name_admin_bar'        => __('Lesson', 'quiz-lms-wc'),
            'archives'              => __('Lesson Archives', 'quiz-lms-wc'),
            'attributes'            => __('Lesson Attributes', 'quiz-lms-wc'),
            'parent_item_colon'     => __('Parent Lesson:', 'quiz-lms-wc'),
            'all_items'             => __('All Lessons', 'quiz-lms-wc'),
            'add_new_item'          => __('Add New Lesson', 'quiz-lms-wc'),
            'add_new'               => __('Add New', 'quiz-lms-wc'),
            'new_item'              => __('New Lesson', 'quiz-lms-wc'),
            'edit_item'             => __('Edit Lesson', 'quiz-lms-wc'),
            'update_item'           => __('Update Lesson', 'quiz-lms-wc'),
            'view_item'             => __('View Lesson', 'quiz-lms-wc'),
            'view_items'            => __('View Lessons', 'quiz-lms-wc'),
            'search_items'          => __('Search Lesson', 'quiz-lms-wc'),
        );

        $args = array(
            'label'                 => __('Lesson', 'quiz-lms-wc'),
            'description'           => __('Course Lessons', 'quiz-lms-wc'),
            'labels'                => $labels,
            'supports'              => array('title', 'editor', 'thumbnail', 'excerpt', 'page-attributes'),
            'hierarchical'          => true,
            'public'                => true,
            'show_ui'               => true,
            'show_in_menu'          => 'edit.php?post_type=qlms_course',
            'show_in_admin_bar'     => true,
            'show_in_nav_menus'     => true,
            'can_export'            => true,
            'has_archive'           => false,
            'exclude_from_search'   => false,
            'publicly_queryable'    => true,
            'capability_type'       => 'post',
            'show_in_rest'          => true,
        );

        register_post_type('qlms_lesson', $args);
    }

    /**
     * Register taxonomies for courses and lessons
     */
    public function register_taxonomies() {
        // Course Categories
        $category_labels = array(
            'name'              => _x('Course Categories', 'taxonomy general name', 'quiz-lms-wc'),
            'singular_name'     => _x('Course Category', 'taxonomy singular name', 'quiz-lms-wc'),
            'search_items'      => __('Search Categories', 'quiz-lms-wc'),
            'all_items'         => __('All Categories', 'quiz-lms-wc'),
            'parent_item'       => __('Parent Category', 'quiz-lms-wc'),
            'parent_item_colon' => __('Parent Category:', 'quiz-lms-wc'),
            'edit_item'         => __('Edit Category', 'quiz-lms-wc'),
            'update_item'       => __('Update Category', 'quiz-lms-wc'),
            'add_new_item'      => __('Add New Category', 'quiz-lms-wc'),
            'new_item_name'     => __('New Category Name', 'quiz-lms-wc'),
            'menu_name'         => __('Categories', 'quiz-lms-wc'),
        );

        register_taxonomy('qlms_course_category', array('qlms_course'), array(
            'hierarchical'      => true,
            'labels'            => $category_labels,
            'show_ui'           => true,
            'show_admin_column' => true,
            'query_var'         => true,
            'rewrite'           => array('slug' => 'course-category'),
            'show_in_rest'      => true,
        ));

        // Course Tags
        $tag_labels = array(
            'name'              => _x('Course Tags', 'taxonomy general name', 'quiz-lms-wc'),
            'singular_name'     => _x('Course Tag', 'taxonomy singular name', 'quiz-lms-wc'),
            'search_items'      => __('Search Tags', 'quiz-lms-wc'),
            'all_items'         => __('All Tags', 'quiz-lms-wc'),
            'edit_item'         => __('Edit Tag', 'quiz-lms-wc'),
            'update_item'       => __('Update Tag', 'quiz-lms-wc'),
            'add_new_item'      => __('Add New Tag', 'quiz-lms-wc'),
            'new_item_name'     => __('New Tag Name', 'quiz-lms-wc'),
            'menu_name'         => __('Tags', 'quiz-lms-wc'),
        );

        register_taxonomy('qlms_course_tag', array('qlms_course'), array(
            'hierarchical'      => false,
            'labels'            => $tag_labels,
            'show_ui'           => true,
            'show_admin_column' => true,
            'query_var'         => true,
            'rewrite'           => array('slug' => 'course-tag'),
            'show_in_rest'      => true,
        ));

        // Quiz Categories
        $quiz_category_labels = array(
            'name'              => _x('Quiz Categories', 'taxonomy general name', 'quiz-lms-wc'),
            'singular_name'     => _x('Quiz Category', 'taxonomy singular name', 'quiz-lms-wc'),
            'search_items'      => __('Search Quiz Categories', 'quiz-lms-wc'),
            'all_items'         => __('All Quiz Categories', 'quiz-lms-wc'),
            'parent_item'       => __('Parent Quiz Category', 'quiz-lms-wc'),
            'parent_item_colon' => __('Parent Quiz Category:', 'quiz-lms-wc'),
            'edit_item'         => __('Edit Quiz Category', 'quiz-lms-wc'),
            'update_item'       => __('Update Quiz Category', 'quiz-lms-wc'),
            'add_new_item'      => __('Add New Quiz Category', 'quiz-lms-wc'),
            'new_item_name'     => __('New Quiz Category Name', 'quiz-lms-wc'),
            'menu_name'         => __('Quiz Categories', 'quiz-lms-wc'),
        );

        register_taxonomy('qlms_quiz_category', array(), array(
            'hierarchical'      => true,
            'labels'            => $quiz_category_labels,
            'show_ui'           => true,
            'show_admin_column' => false,
            'query_var'         => true,
            'rewrite'           => array('slug' => 'quiz-category'),
            'show_in_rest'      => true,
        ));
    }
}
