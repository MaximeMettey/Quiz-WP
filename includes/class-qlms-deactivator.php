<?php
/**
 * Fired during plugin deactivation
 */

class QLMS_Deactivator {

    /**
     * Deactivation tasks
     */
    public static function deactivate() {
        // Flush rewrite rules
        flush_rewrite_rules();
    }
}
