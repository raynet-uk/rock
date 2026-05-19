<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('member_applications', function (Blueprint $table) {
            $table->id();
            $table->string('callsign', 20)->nullable();
            $table->string('title', 20)->nullable();
            $table->string('surname', 100);
            $table->string('forenames', 100);
            $table->string('known_as', 100)->nullable();
            $table->date('dob');
            $table->string('email', 255);
            $table->string('home_tel', 30)->nullable();
            $table->boolean('home_tel_ex')->default(false);
            $table->string('mobile', 30)->nullable();
            $table->boolean('mobile_ex')->default(false);
            $table->string('nationality', 80)->nullable();
            $table->string('former_nationality', 80)->nullable();
            $table->string('place_of_birth', 100)->nullable();
            $table->text('address');
            $table->string('doc_a_type', 200)->nullable();
            $table->string('doc_a_date', 30)->nullable();
            $table->string('doc_a_ref', 100)->nullable();
            $table->string('doc_b_type', 200)->nullable();
            $table->string('doc_b_date', 30)->nullable();
            $table->string('doc_b_ref', 100)->nullable();
            $table->string('criminal_1', 3)->nullable();
            $table->text('criminal_1_detail')->nullable();
            $table->string('criminal_2', 3)->nullable();
            $table->text('criminal_2_detail')->nullable();
            $table->string('criminal_3', 3)->nullable();
            $table->text('criminal_3_detail')->nullable();
            $table->boolean('comms_national_email')->default(false);
            $table->boolean('comms_group_email')->default(false);
            $table->boolean('comms_national_tel')->default(false);
            $table->boolean('comms_group_tel')->default(false);
            $table->boolean('comms_national_sms')->default(false);
            $table->boolean('comms_group_sms')->default(false);
            $table->boolean('comms_national_post')->default(false);
            $table->boolean('comms_group_post')->default(false);
            $table->text('signature_data')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->string('invite_token', 64)->nullable()->unique();
            $table->timestamp('invite_sent_at')->nullable();
            $table->unsignedInteger('converted_user_id')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('member_applications');
    }
};
