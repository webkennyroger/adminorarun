<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clubs', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('city');
            $table->string('state');
            $table->string('image')->nullable(); // Cover image
            $table->string('avatar')->nullable(); // Avatar image
            $table->string('category');
            $table->boolean('is_public')->default(true);
            $table->foreignId('creator_id')->constrained('users')->onDelete('cascade');
            $table->string('creator_name');
            $table->integer('members_count')->default(0);
            $table->integer('followers_count')->default(0);
            $table->timestamps();
        });

        Schema::create('club_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('club_id')->constrained()->onDelete('cascade');
            $table->string('role')->default('member'); // member, admin, creator
            $table->timestamps();
            $table->unique(['user_id', 'club_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('club_user');
        Schema::dropIfExists('clubs');
    }
};
