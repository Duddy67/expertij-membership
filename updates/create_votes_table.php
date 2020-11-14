<?php namespace Codalia\Membership\Updates;

use Schema;
use October\Rain\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;

class CreateVotesTable extends Migration
{
    public function up()
    {
        Schema::create('codalia_membership_votes', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->integer('member_id')->unsigned()->index()->nullable()->default(null);
            $table->integer('user_id')->unsigned()->index()->nullable()->default(null);
	    $table->char('choice', 3)->default(null);
	    $table->text('note')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('codalia_membership_votes');
    }
}
