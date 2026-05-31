<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void {
        DB::statement('
            CREATE TABLE controller_tiers (
                id INT UNSIGNED NOT NULL AUTO_INCREMENT,
                user_id INT UNSIGNED NOT NULL,
                tier ENUM("tier1","tier2","tier3","support","standby") NOT NULL,
                start_date DATE NOT NULL,
                end_date DATE NULL,
                notes VARCHAR(255) NULL,
                created_at TIMESTAMP NULL,
                updated_at TIMESTAMP NULL,
                PRIMARY KEY (id),
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=latin1
        ');
        DB::statement('
            CREATE TABLE controller_alerts (
                id INT UNSIGNED NOT NULL AUTO_INCREMENT,
                type ENUM("test","standby","callout") NOT NULL,
                title VARCHAR(255) NULL,
                message TEXT NULL,
                tier_scope TEXT NULL,
                raised_by INT UNSIGNED NOT NULL,
                raised_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                closed_at TIMESTAMP NULL,
                status ENUM("active","closed") NOT NULL DEFAULT "active",
                status_level_set INT NULL,
                created_at TIMESTAMP NULL,
                updated_at TIMESTAMP NULL,
                PRIMARY KEY (id),
                FOREIGN KEY (raised_by) REFERENCES users(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=latin1
        ');
        DB::statement('
            CREATE TABLE controller_alert_responses (
                id INT UNSIGNED NOT NULL AUTO_INCREMENT,
                alert_id INT UNSIGNED NOT NULL,
                user_id INT UNSIGNED NOT NULL,
                response ENUM("available","unavailable","no_response") NOT NULL DEFAULT "no_response",
                tier VARCHAR(20) NULL,
                responded_at TIMESTAMP NULL,
                token VARCHAR(64) NULL,
                created_at TIMESTAMP NULL,
                updated_at TIMESTAMP NULL,
                PRIMARY KEY (id),
                UNIQUE KEY token_unique (token),
                FOREIGN KEY (alert_id) REFERENCES controller_alerts(id) ON DELETE CASCADE,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=latin1
        ');
    }
    public function down(): void {
        DB::statement('DROP TABLE IF EXISTS controller_alert_responses');
        DB::statement('DROP TABLE IF EXISTS controller_alerts');
        DB::statement('DROP TABLE IF EXISTS controller_tiers');
    }
};
