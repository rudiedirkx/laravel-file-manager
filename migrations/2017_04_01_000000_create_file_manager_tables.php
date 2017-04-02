<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFileManagerTables extends Migration {

	/**
	 * Run the migrations.
	 */
	public function up() {
		Schema::create('files', function(Blueprint $table) {
			$table->increments('id');
            $table->string('filepath')->collation('utf8_bin');
            $table->string('filename');
            $table->datetime('created_at');
            $table->integer('created_by')->nullable();
		});

		Schema::create('files_usage', function(Blueprint $table) {
			$table->integer('file_id')->unsigned()->index();
            $table->string('used_type')->index();
            $table->string('used_id')->index();
            $table->datetime('created_at');
            $table->integer('created_by')->nullable();

			$table->foreign('file_id')->references('id')->on('files')->onDelete('cascade');
		});
	}

	/**
	 * Reverse the migrations.
	 */
	public function down() {
		Schema::table('files_usage', function(Blueprint $table) {
			$table->drop();
		});

		Schema::table('files', function(Blueprint $table) {
			$table->drop();
		});
	}

}
