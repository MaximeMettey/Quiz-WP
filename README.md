# Quiz & LMS for WooCommerce

A comprehensive WordPress plugin for creating quizzes, courses, and a complete Learning Management System (LMS) with WooCommerce integration for monetizing your educational content.

## Features

### Quiz Management
- **Multiple Question Types**: Support for single-choice and multiple-choice questions
- **Flexible Answer Options**: Configure one or multiple correct answers per question
- **Timer Functionality**: Set time limits for quizzes with visual countdown
- **Quiz History**: Track all user attempts and results
- **Quiz Categories**: Organize quizzes with custom taxonomies
- **Pass Percentage**: Set custom passing scores for each quiz
- **Attempt Limits**: Control how many times users can take a quiz
- **Randomization**: Option to randomize questions and answers

### Course & Lesson Management
- **Hierarchical Structure**: Courses contain lessons, lessons can contain quizzes
- **Progress Tracking**: Monitor user progress through courses and lessons
- **Course Categories**: Organize courses with categories and tags
- **Course Levels**: Beginner, Intermediate, Advanced
- **Duration Settings**: Set estimated completion times
- **Sequential Learning**: Control lesson order and progression

### WooCommerce Integration
- **Paid Content**: Sell courses and quizzes through WooCommerce
- **Flexible Pricing**: Support for one-time payments and subscriptions
- **Free & Premium Mix**: Offer both free and paid content
- **Access Control**: Automatic access management based on purchases
- **Product Linking**: Link WooCommerce products to courses/quizzes

### User Experience
- **User Dashboard**: Personalized dashboard showing enrolled courses, progress, and statistics
- **Responsive Design**: Mobile-friendly interface
- **AJAX-Powered**: Smooth user experience without page reloads
- **Progress Bars**: Visual progress indicators for courses
- **Quiz Results**: Instant feedback with detailed results

### Multilingual Support
- **English & French**: Built-in translations
- **Translation Ready**: Easy to add more languages
- **WPML Compatible**: Works with popular translation plugins

## Installation

1. Upload the `quiz-lms-woocommerce` folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Make sure WooCommerce is installed and activated (optional, only for paid content)
4. Go to Quiz & LMS > Settings to configure the plugin

## Requirements

- WordPress 5.8 or higher
- PHP 7.4 or higher
- WooCommerce 5.0 or higher (optional, for paid content features)
- MySQL 5.6 or higher

## Quick Start

### Creating Your First Quiz

1. Go to **Quiz & LMS > Add New Quiz**
2. Enter the quiz title and description
3. Set quiz settings:
   - Time limit (optional)
   - Pass percentage
   - Number of attempts allowed
   - Whether to show correct answers after submission
4. Add questions with single or multiple correct answers
5. Use the shortcode `[qlms_quiz id="X"]` to display it on any page

### Creating a Course

1. Go to **Courses > Add New**
2. Enter course title and description
3. Set course as free or link to a WooCommerce product
4. Create lessons and link them to the course
5. Use the shortcode `[qlms_course id="X"]` to display the course

## Shortcodes

- `[qlms_quiz id="1"]` - Display a quiz
- `[qlms_course id="1"]` - Display a course
- `[qlms_courses]` - Display courses list
- `[qlms_user_dashboard]` - Display user dashboard
- `[qlms_lesson id="1"]` - Display a lesson

## Database Structure

The plugin creates 8 custom tables for managing quizzes, courses, lessons, and user progress.

## Support

For support, please create an issue on [GitHub](https://github.com/MaximeMettey/Quiz-WP/issues).

## License

GPL v2 or later

## Author

Maxime Mettey