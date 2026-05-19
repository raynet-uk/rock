<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('member_applications', function (Blueprint $table) {
            $table->string('doc_a_file', 500)->nullable()->after('doc_a_ref');
            $table->string('doc_b_file', 500)->nullable()->after('doc_b_ref');
            $table->string('pdf_path', 500)->nullable()->after('signature_data');
        });
    }

    public function down(): void
    {
        Schema::table('member_applications', function (Blueprint $table) {
            $table->dropColumn(['doc_a_file', 'doc_b_file', 'pdf_path']);
        });
    }
};
