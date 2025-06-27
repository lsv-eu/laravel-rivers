<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use LsvEu\Rivers\Models\River;
use LsvEu\Rivers\Models\RiverRun;
use LsvEu\Rivers\Models\RiverVersion;

class CreateRiverTables extends Migration
{
    public function up(): void
    {
        Schema::create('rivers', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('title');
            $table->string('status')->default('draft');
            $table->foreignIdFor(RiverVersion::class, 'current_version_id')->nullable();
            $table->longText('map');
            $table->longText('listeners');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('river_versions', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignIdFor(River::class)->constrained()->cascadeOnDelete();
            $table->boolean('is_autosave')->default(false);
            $table->longText('map');
            $table->timestamps();
        });

        Schema::create('river_runs', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignIdFor(River::class)->constrained()->cascadeOnDelete();
            $table->string('status')->default('created');
            $table->string('location')->nullable();
            $table->longText('listeners');
            $table->longText('raft');
            $table->timestamps();
            $table->timestamp('completed_at')->nullable();
        });

        Schema::create('river_interrupts', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignIdFor(RiverRun::class)->constrained()->cascadeOnDelete();
            $table->string('event');
            $table->boolean('checked')->default(false);
            $table->longText('details');
            $table->timestamps();
        });

        Schema::create('river_timed_bridges', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignIdFor(RiverRun::class)->constrained()->cascadeOnDelete();
            $table->string('location');
            $table->timestamp('resume_at');
            $table->boolean('paused');
            $table->timestamps();
        });
    }
}
