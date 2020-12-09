<?php namespace Codalia\Membership\Updates;

use Schema;
use October\Rain\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;

class UpdateDocumentsTable extends Migration
{
    public function up()
    {
        Schema::table('codalia_membership_documents', function (Blueprint $table) {
	    $table->string('title')->nullable()->after('id');
            $table->text('description')->nullable()->after('title');
            $table->char('status', 15)->default('unpublished')->after('description');
	    $table->timestamp('last_email_sending')->nullable()->after('status');
	    $table->integer('created_by')->unsigned()->nullable()->index()->after('last_email_sending');
	    $table->integer('updated_by')->unsigned()->nullable()->after('created_by');
	    $table->timestamp('published_up')->nullable()->after('updated_by');
	    $table->timestamp('published_down')->nullable()->after('published_up');
	    $table->integer('checked_out')->unsigned()->nullable()->index()->after('published_down');
	    $table->timestamp('checked_out_time')->nullable()->after('checked_out');
        });
    }

    public function down()
    {
        if (Schema::hasColumn('codalia_membership_documents', 'title')) {
            Schema::table('codalia_membership_documents', function($table)
            {
                $table->dropColumn('title');
            });
        }

        if (Schema::hasColumn('codalia_membership_documents', 'description')) {
            Schema::table('codalia_membership_documents', function($table)
            {
                $table->dropColumn('description');
            });
        }

        if (Schema::hasColumn('codalia_membership_documents', 'status')) {
            Schema::table('codalia_membership_documents', function($table)
            {
                $table->dropColumn('status');
            });
        }

        if (Schema::hasColumn('codalia_membership_documents', 'last_email_sending')) {
            Schema::table('codalia_membership_documents', function($table)
            {
                $table->dropColumn('last_email_sending');
            });
        }

        if (Schema::hasColumn('codalia_membership_documents', 'created_by')) {
            Schema::table('codalia_membership_documents', function($table)
            {
                $table->dropColumn('created_by');
            });
        }

        if (Schema::hasColumn('codalia_membership_documents', 'updated_by')) {
            Schema::table('codalia_membership_documents', function($table)
            {
                $table->dropColumn('updated_by');
            });
        }

        if (Schema::hasColumn('codalia_membership_documents', 'published_up')) {
            Schema::table('codalia_membership_documents', function($table)
            {
                $table->dropColumn('published_up');
            });
        }

        if (Schema::hasColumn('codalia_membership_documents', 'published_down')) {
            Schema::table('codalia_membership_documents', function($table)
            {
                $table->dropColumn('published_down');
            });
        }

        if (Schema::hasColumn('codalia_membership_documents', 'checked_out')) {
            Schema::table('codalia_membership_documents', function($table)
            {
                $table->dropColumn('checked_out');
            });
        }

        if (Schema::hasColumn('codalia_membership_documents', 'checked_out_time')) {
            Schema::table('codalia_membership_documents', function($table)
            {
                $table->dropColumn('checked_out_time');
            });
        }
    }
}
