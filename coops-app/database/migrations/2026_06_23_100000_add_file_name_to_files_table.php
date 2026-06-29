<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('files', 'file_name')) {
            Schema::table('files', function (Blueprint $table) {
                $table->string('file_name')->nullable()->after('file_id');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('files', 'file_name')) {
            Schema::table('files', function (Blueprint $table) {
                $table->dropColumn('file_name');
            });
        }
    }
};
