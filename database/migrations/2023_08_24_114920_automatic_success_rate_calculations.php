<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    public static function up(): void
    {
        DB::unprepared('
            CREATE TRIGGER calculate_ip_addresses_success_rate
            BEFORE UPDATE ON ip_addresses FOR EACH ROW
            BEGIN
                IF NEW.attempts > 0 THEN
                    SET NEW.success_rate = NEW.successful_attempts / NEW.attempts;
                ELSE
                    SET NEW.success_rate = 0;
                END IF;
            END;
        ');

        DB::unprepared('
            CREATE TRIGGER calculate_location_success_rate
            BEFORE UPDATE ON locations FOR EACH ROW
            BEGIN
                IF NEW.attempts > 0 THEN
                    SET NEW.success_rate = NEW.successful_attempts / NEW.attempts;
                ELSE
                    SET NEW.success_rate = 0;
                END IF;
            END;
        ');
    }

    public static function down(): void
    {
        DB::unprepared('DROP TRIGGER IF EXISTS calculate_ip_addresses_success_rate');
        DB::unprepared('DROP TRIGGER IF EXISTS calculate_locations_success_rate');
    }
};
