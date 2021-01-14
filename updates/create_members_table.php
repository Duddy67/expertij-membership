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
	    $table->char('status', 20)->default('pending');
	    $table->boolean('member_list')->nullable();
	    $table->tinyInteger('email_sendings')->unsigned()->default(0);
	    $table->timestamp('member_since')->nullable();
	    $table->string('member_number', 30)->nullable();
	    $table->boolean('free_period')->nullable();
	    $table->char('pro_status', 20)->nullable();
	    $table->string('pro_status_info', 30)->nullable();
            $table->smallInteger('since')->unsigned()->nullable();
	    $table->char('siret_number', 14)->nullable();
	    $table->char('naf_code', 5)->nullable();
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
