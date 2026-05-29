<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::dropIfExists('event_generated_risks');
        Schema::dropIfExists('event_approvals');
        Schema::dropIfExists('event_pack_documents');
        Schema::dropIfExists('event_operators');
        Schema::dropIfExists('event_posts');
        Schema::dropIfExists('event_user_services');
        Schema::dropIfExists('event_support_packs');

        Schema::create('event_support_packs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('group_ref')->default('');
            $table->string('event_name');
            $table->date('event_date');
            $table->integer('duration_days')->default(1);
            $table->string('location');
            $table->string('town_area')->nullable();
            $table->text('event_description')->nullable();
            $table->string('organiser_name')->nullable();
            $table->string('organiser_contact')->nullable();
            $table->string('organiser_phone')->nullable();
            $table->string('organiser_email')->nullable();
            $table->string('event_type')->nullable();
            $table->string('controller_callsign')->nullable();
            $table->string('primary_frequency')->nullable();
            $table->boolean('frequency_public')->default(false);
            $table->string('talkthrough_used')->default('Unknown');
            $table->boolean('talkthrough_public')->default(false);
            $table->boolean('assistance_visible')->default(false);
            $table->string('assistance_contact')->nullable();
            $table->string('assistance_phone_email')->nullable();
            $table->string('duty_type')->nullable();
            $table->string('outstations')->nullable();
            $table->text('duty_description')->nullable();
            $table->string('message_type')->nullable();
            $table->string('data_comms')->nullable();
            $table->string('skill_level')->nullable();
            $table->string('traffic_level')->nullable();
            $table->text('equipment_power')->nullable();
            $table->text('operating_environment')->nullable();
            $table->text('welfare_food')->nullable();
            $table->text('welfare_other')->nullable();
            $table->json('raynet_roles')->nullable();
            $table->string('scope_traffic')->default('No');
            $table->string('scope_marshalling')->default('No');
            $table->string('scope_children')->default('No');
            $table->string('scope_first_aid')->default('No');
            $table->string('scope_transport')->default('No');
            $table->string('scope_casualties')->default('No');
            $table->string('secondary_frequency')->nullable();
            $table->string('repeater_details')->nullable();
            $table->string('control_callsign')->nullable();
            $table->string('event_controller')->nullable();
            $table->string('deputy_controller')->nullable();
            $table->string('net_control_location')->nullable();
            $table->string('call_round_interval')->nullable();
            $table->text('fallback_methods')->nullable();
            $table->json('terrain')->nullable();
            $table->json('access_conditions')->nullable();
            $table->string('weather_exposure')->nullable();
            $table->string('road_exposure')->nullable();
            $table->string('operator_movement')->nullable();
            $table->json('equipment')->nullable();
            $table->string('power_source')->nullable();
            $table->string('vehicles_operating')->default('No');
            $table->string('deployment_duration')->nullable();
            $table->json('facilities')->nullable();
            $table->json('welfare_risks')->nullable();
            $table->string('lone_working')->default('No');
            $table->string('night_operation')->default('No');
            $table->string('under_18')->default('No');
            $table->string('start_time')->nullable();
            $table->string('finish_time')->nullable();
            $table->json('other_agencies')->nullable();
            $table->enum('rag_status', ['green','amber','red'])->nullable();
            $table->enum('status', ['draft','awaiting_review','approved','approved_with_controls','escalated','returned','closed','cancelled'])->default('draft');
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->string('approval_statement')->nullable();
            $table->text('notes')->nullable();
            $table->integer('version')->default(1);
            $table->string('template_type')->nullable();
            $table->unsignedBigInteger('cloned_from')->nullable();
            $table->timestamps();
        });

        Schema::create('event_user_services', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('event_support_pack_id');
            $table->string('service_name');
            $table->timestamps();
        });

        Schema::create('event_posts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('event_support_pack_id');
            $table->string('tactical_callsign')->nullable();
            $table->string('post_name');
            $table->text('description')->nullable();
            $table->string('post_type')->nullable();
            $table->string('location')->nullable();
            $table->string('grid_ref')->nullable();
            $table->string('what3words')->nullable();
            $table->text('access_notes')->nullable();
            $table->string('start_time')->nullable();
            $table->string('finish_time')->nullable();
            $table->integer('minimum_operators')->default(1);
            $table->boolean('vehicle_required')->default(false);
            $table->boolean('remote_post')->default(false);
            $table->boolean('lone_working_possible')->default(false);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('event_operators', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('event_support_pack_id');
            $table->unsignedBigInteger('post_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('name')->nullable();
            $table->string('callsign')->nullable();
            $table->string('vehicle_reg')->nullable();
            $table->string('mobile')->nullable();
            $table->string('start_time')->nullable();
            $table->string('finish_time')->nullable();
            $table->text('equipment')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('event_generated_risks', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('event_support_pack_id');
            $table->string('hazard');
            $table->string('cause')->nullable();
            $table->string('persons_at_risk')->nullable();
            $table->text('controls');
            $table->string('likelihood');
            $table->string('severity');
            $table->string('residual');
            $table->string('rag')->nullable();
            $table->boolean('escalation_required')->default(false);
            $table->text('briefing_note')->nullable();
            $table->boolean('accepted')->default(true);
            $table->unsignedBigInteger('overridden_by')->nullable();
            $table->string('override_reason')->nullable();
            $table->timestamps();
        });

        Schema::create('event_pack_documents', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('event_support_pack_id');
            $table->string('document_type');
            $table->string('filename');
            $table->integer('version')->default(1);
            $table->timestamp('generated_at')->nullable();
            $table->unsignedBigInteger('generated_by')->nullable();
            $table->timestamps();
        });

        Schema::create('event_approvals', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('event_support_pack_id');
            $table->string('status');
            $table->unsignedBigInteger('approver_id')->nullable();
            $table->text('statement')->nullable();
            $table->text('comments')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('event_approvals');
        Schema::dropIfExists('event_pack_documents');
        Schema::dropIfExists('event_generated_risks');
        Schema::dropIfExists('event_operators');
        Schema::dropIfExists('event_posts');
        Schema::dropIfExists('event_user_services');
        Schema::dropIfExists('event_support_packs');
    }
};
