<?php namespace Codalia\Membership\Updates;

use Schema;
use October\Rain\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;

class CreateInsurancesTable extends Migration
{
    public function up()
    {
        Schema::create('codalia_membership_insurances', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->integer('member_id')->unsigned()->index()->nullable()->default(null);
	    $table->char('status', 20)->default('disabled');
	    $table->char('code', 3)->default(null);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('codalia_membership_insurances');
    }
}
