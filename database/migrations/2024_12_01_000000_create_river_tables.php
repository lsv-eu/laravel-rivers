<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRiverTables extends Migration
{
    public function up(): void
    {
        Schema::create('rivers', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('title');
            $table->string('status')->default('draft');
            $table->foreignIdFor(\LsvEu\Rivers\Models\RiverVersion::class, 'current_version_id')->nullable();
            $table->longText('map');
            $table->longText('listeners');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('river_versions', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignIdFor(\LsvEu\Rivers\Models\River::class)->constrained()->cascadeOnDelete();
            $table->boolean('is_autosave')->default(false);
            $table->longText('map');
            $table->timestamps();
        });

        Schema::create('river_runs', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignIdFor(\LsvEu\Rivers\Models\River::class)->constrained()->cascadeOnDelete();
            $table->uuid('job_id')->nullable();
            $table->string('location')->nullable();
            $table->longText('listeners');
            $table->longText('raft');
            $table->timestamps();
        });

        Schema::create('river_interrupts', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignIdFor(\LsvEu\Rivers\Models\RiverRun::class)->constrained()->cascadeOnDelete();
            $table->string('event');
            $table->longText('details');
            $table->timestamps();
        });
    }
}
