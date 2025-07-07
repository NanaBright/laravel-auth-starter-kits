<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('otp_verifications', function (Blueprint $table) {
            $table->id();
            $table->string('phone');
            $table->string('otp', 6);
            $table->timestamp('expires_at');
            $table->timestamps();

            $table->index(['phone', 'otp']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('otp_verifications');
    }
};
