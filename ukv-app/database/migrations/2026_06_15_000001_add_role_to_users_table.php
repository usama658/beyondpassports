<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // from: WP capability — "eligible owner" = can edit orders.
            // string + app-level validation/cast (enum: admin/agent/viewer).
            $table->string('role', 20)->default('agent')
                ->comment('enum: admin|agent|viewer')
                ->after('password');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('role');
        });
    }
};
