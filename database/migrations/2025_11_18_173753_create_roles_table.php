<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('operator_roles')) Schema::create('operator_roles', function (Blueprint $table) {
            $table->id();
            $table->string('name');              // Human label – e.g. Group Controller
            $table->string('slug')->unique();    // Internal key – e.g. group-controller
            $table->integer('sort_order')->default(0);
            $table->string('colour', 7)->nullable(); // Optional hex badge colour
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // Seed my initial role set so I’ve got something usable straight away
        DB::table('operator_roles')->insert([
            [
                'name'       => 'Group Controller',
                'slug'       => 'group-controller',
                'sort_order' => 10,
                'colour'     => '#22c55e',
                'description'=> 'Overall lead for RAYNET group operations and governance.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name'       => 'Deputy Controller',
                'slug'       => 'deputy-controller',
                'sort_order' => 20,
                'colour'     => '#38bdf8',
                'description'=> 'Deputises for the Group Controller and supports operational leadership.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name'       => 'Secretary',
                'slug'       => 'secretary',
                'sort_order' => 30,
                'colour'     => '#a855f7',
                'description'=> 'Handles minutes, correspondence and records.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name'       => 'Treasurer',
                'slug'       => 'treasurer',
                'sort_order' => 40,
                'colour'     => '#f97316',
                'description'=> 'Looks after money, budgets and financial reporting.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name'       => 'Training Officer',
                'slug'       => 'training-officer',
                'sort_order' => 50,
                'colour'     => '#eab308',
                'description'=> 'Owns the training plan and competence tracking.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name'       => 'Operator',
                'slug'       => 'operator',
                'sort_order' => 60,
                'colour'     => '#0ea5e9',
                'description'=> 'Regular RAYNET operator for events and incidents.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name'       => 'Non-active member',
                'slug'       => 'non-active-member',
                'sort_order' => 70,
                'colour'     => '#6b7280',
                'description'=> 'Member who is not currently active on nets or events.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('operator_roles');
    }
};