<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * M3 — plaintext passwords: widen logins.password (bcrypt is 60 chars,
     * the old column was varchar(25)) and one-time-hash any plaintext rows.
     * Idempotent: rows already starting with a bcrypt prefix are skipped.
     */
    public function up(): void
    {
        Schema::table('logins', function (Blueprint $table) {
            $table->string('password', 255)->change();
        });

        DB::table('logins')->orderBy('id')->chunkById(200, function ($logins) {
            foreach ($logins as $login) {
                $password = (string) $login->password;

                if ($password === '' || str_starts_with($password, '$2')) {
                    continue; // already hashed (or empty)
                }

                DB::table('logins')->where('id', $login->id)->update([
                    'password' => Hash::make($password),
                ]);
            }
        });
    }

    /**
     * Hashing is not reversible — nothing to roll back.
     */
    public function down(): void
    {
        // no-op
    }
};
