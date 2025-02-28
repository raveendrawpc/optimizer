<?php

/**
 * Plugin Name: Quiz Data to Word Exporter
 * Description: A WordPress plugin to export quiz data to a Word document.
 * Version: 1.0
 * Author: Your Name
 */

if (!defined('ABSPATH')) {
    exit; // Prevent direct access
}

require_once __DIR__ . '/vendor/autoload.php'; // Include PHPWord via Composer

use PhpOffice\PhpWord\PhpWord;

class QuizDataToWordExporter {
    public function __construct() {
        // Create the table and insert dummy data
        $this->generate_quiz_data_table();

        // Handle the export action for the Word document
        add_action('admin_post_export_quiz_data', [$this, 'export_quiz_data_to_word']);
        add_action('admin_post_nopriv_export_quiz_data', [$this, 'export_quiz_data_to_word']); // For non-logged-in users

        // Register the shortcode to display the download link
        add_shortcode('quiz_export_link', [$this, 'render_export_link']);
    }

    public function generate_quiz_data_table() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'quiz_data';

        // Create table if it doesn't exist
        if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
            $charset_collate = $wpdb->get_charset_collate();
            $sql = "CREATE TABLE $table_name (
                id BIGINT(20) UNSIGNED AUTO_INCREMENT,
                question VARCHAR(255) NOT NULL,
                answer VARCHAR(255) NOT NULL,
                PRIMARY KEY (id)
            ) $charset_collate;";

            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
            dbDelta($sql);
        }

        // Insert dummy data if the table is empty
        if ($wpdb->get_var("SELECT COUNT(*) FROM $table_name") == 0) {
            $dummy_data = [
                ['question' => 'What is the capital of France?', 'answer' => 'Paris'],
                ['question' => 'What is 2 + 2?', 'answer' => '4'],
                ['question' => 'Who wrote Romeo and Juliet?', 'answer' => 'William Shakespeare'],
                ['question' => 'What is the color of the sky?', 'answer' => 'Blue'],
                ['question' => 'What is the boiling point of water?', 'answer' => '100°C']
            ];

            foreach ($dummy_data as $data) {
                $wpdb->insert($table_name, $data);
            }
        }
    }

    public function export_quiz_data_to_word() {
        // Verify nonce for security
        if (!isset($_REQUEST['export_quiz_nonce']) || !wp_verify_nonce($_REQUEST['export_quiz_nonce'], 'export_quiz_data_action')) {
            wp_die('Invalid request. Please try again.');
        }

        global $wpdb;

        $table_name = $wpdb->prefix . 'quiz_data'; // Adjust based on your database
        $results = $wpdb->get_results("SELECT * FROM $table_name", ARRAY_A);

        if (!$results) {
            wp_die('No quiz data found.');
        }

        $phpWord = new PhpWord();
        $section = $phpWord->addSection();

        $depth = 1;
        $fontStyle = ['name' => 'Arial', 'size' => 16, 'bold' => true];
        $paragraphStyle = ['align' => 'center'];
        $phpWord->addTitleStyle($depth, $fontStyle, $paragraphStyle);
        $section->addTitle('Quiz Data', 1);
        $section->addTextBreak();

        foreach ($results as $row) {
            $section->addText("ID: " . $row['id']);
            $section->addText("Question: " . $row['question'], ['bold' => true]);
            $section->addText("Answer: " . $row['answer']);
            $section->addTextBreak();
        }

        $upload_dir = wp_upload_dir();
        $unique_file_name = 'quiz-data-' . uniqid() . '-' . time() . '.docx'; // Generate unique file name
        $file_path = $upload_dir['path'] . '/' . $unique_file_name;

        $phpWord->save($file_path, 'Word2007');

        // Trigger file download
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . basename($unique_file_name) . '"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($file_path));
        readfile($file_path);

        unlink($file_path); // Remove the file after download
        exit;
    }

    public function render_export_link($atts) {
        $defaults = [
            'text' => 'Download Quiz Data',
        ];
        $atts = shortcode_atts($defaults, $atts, 'quiz_export_link');

        // Generate a nonce for the link
        $nonce = wp_create_nonce('export_quiz_data_action');
        $link = admin_url('admin-post.php?action=export_quiz_data&export_quiz_nonce=' . $nonce);

        return sprintf('<a href="%s" class="quiz-export-link">%s</a>', esc_url($link), esc_html($atts['text']));
    }
}

new QuizDataToWordExporter();
