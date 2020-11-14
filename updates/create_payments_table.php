<?php namespace Codalia\Membership\Updates;

use Schema;
use October\Rain\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;

class CreatePaymentsTable extends Migration
{
    public function up()
    {
        Schema::create('codalia_membership_payments', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->integer('member_id')->unsigned()->index()->nullable()->default(null);
	    $table->char('status', 15)->default('pending');
	    $table->char('mode', 15)->default(null);
	    $table->char('item', 10)->default(null);
            $table->decimal('amount', 5, 2)->unsigned()->nullable()->default(null);
	    $table->text('note')->nullable();
	    $table->text('data')->nullable();
	    $table->boolean('last')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('codalia_membership_payments');
    }
}
