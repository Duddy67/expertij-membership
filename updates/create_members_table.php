<?php namespace Codalia\Membership\Updates;

use Schema;
use October\Rain\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;

class CreateMembersTable extends Migration
{
    public function up()
    {
        Schema::create('codalia_membership_members', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->integer('profile_id')->unsigned()->index()->nullable()->default(null);
            $table->integer('appeal_court_id')->unsigned()->index()->nullable()->default(null);
	    $table->char('status', 20)->default('pending');
	    $table->boolean('member_list')->nullable();
	    $table->tinyInteger('email_sendings')->unsigned()->default(0);
	    $table->timestamp('member_since')->nullable();
	    $table->string('member_number', 30)->nullable();
	    $table->boolean('free_period')->nullable();
	    $table->integer('checked_out')->unsigned()->nullable()->index();
	    $table->timestamp('checked_out_time')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('codalia_membership_members');
    }
}
