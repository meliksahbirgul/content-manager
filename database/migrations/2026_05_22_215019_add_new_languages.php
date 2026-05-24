<?php

use Carbon\CarbonImmutable;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Ramsey\Uuid\Uuid;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $languages = [
            [
                'name' => 'English',
                'code' => 'en',
                'status' => 'active',
            ],
            [
                'name' => 'Türkçe',
                'code' => 'tr',
                'status' => 'active',
            ],
            [
                'name' => 'Deutsch',
                'code' => 'de',
                'status' => 'passive',
            ],
        ];

        foreach ($languages as $language) {
            DB::table('languages')
                ->insert(
                    [
                        'uuid' => Uuid::uuid7()->toString(),
                        'name' => $language['name'],
                        'code' => $language['code'],
                        'status' => $language['status'],
                        'created_at' => CarbonImmutable::now('UTC')->format('Y-m-d H:i:s'),
                        'updated_at' => CarbonImmutable::now('UTC')->format('Y-m-d H:i:s'),
                    ],
                );
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('languages')->delete();
    }
};
