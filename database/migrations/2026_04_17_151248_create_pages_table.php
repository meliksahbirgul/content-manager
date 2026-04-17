<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('pages', static function (Blueprint $table): void {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('parent_id')
                ->nullable()
                ->constrained('pages')
                ->onDelete('cascade');
            $table->jsonb('title');
            $table->jsonb('content');
            $table->jsonb('slug');
            $table->integer('order')->default(0);
            $table->string('is_active')->default('passive');
            $table->timestamps();
            $table->softDeletes();

            $table->index('parent_id');
            $table->rawIndex('slug', 'pages_slug_gin_index', 'gin');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pages');
    }
};
