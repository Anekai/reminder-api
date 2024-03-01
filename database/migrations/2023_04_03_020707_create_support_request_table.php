<?php

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
        Schema::create('support_requests', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description');
            $table->text('response')->nullable();
            $table->text('reason_refusal')->nullable();
            $table->string('type'); // ERRO, DUVIDA, SUGESTAO
            $table->string('priority'); // BAIXA, MEDIA, ALTA, URGENTE
            $table->string('status'); // ABERTA, EM ANDAMENTO, CONCLUIDA, CANCELADA, RECUSADA

            $table->bigInteger('user_id')->unsigned();
            $table->foreign('user_id')->references('id')->on('users');

            $table->bigInteger('support_user_id')->unsigned()->nullable();
            $table->foreign('support_user_id')->references('id')->on('users');

            $table->date('start_date')->nullable();
            $table->date('conclusion_date')->nullable();
            $table->date('cancellation_date')->nullable();
            $table->date('refusal_date')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('support_request');
    }
};
