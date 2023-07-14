<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Enums\MessageType;
use App\Enums\ReactType;
return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('message__recipients', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('recipient_id');
            $table->foreign('recipient_id')->references('id')->on('users')->onDelete('cascade');
            $table->unsignedBigInteger('recipient_group_id');
            $table->foreign('recipient_group_id')->references('id')->on('conversations')->onDelete('cascade');
            $table->unsignedBigInteger('message_id');
            $table->foreign('message_id')->references('id')->on('messages')->onDelete('cascade');
            $table->integer('read_status')->default(MessageType::UNREAD);
            $table->integer('react_status')->default(ReactType::NONE)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('message__recipients');
    }
};
