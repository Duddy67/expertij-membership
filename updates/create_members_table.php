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
            $table->integer('user_id')->unsigned()->index();
            $table->integer('profile_id')->unsigned()->index()->nullable()->default(null);
	    $table->char('status', 15)->default('pending');
	    $table->boolean('candidate')->default(true);
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
