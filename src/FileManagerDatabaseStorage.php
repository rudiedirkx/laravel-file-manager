<?php

namespace rdx\filemanager;

use Carbon\Carbon;
use Illuminate\Database\ConnectionInterface;
use rdx\filemanager\ManagedFile;

class FileManagerDatabaseStorage implements FileManagerStorage {

	protected $connection;
	protected $options = [];

	/**
	 *
	 */
	public function __construct(ConnectionInterface $connection, array $options = []) {
		$this->connection = $connection;
		$this->options = $options;
	}

	/**
	 *
	 */
	public function getFile($id) {
		return $this->connection
			->table($this->options['files_table'])
			->where('id', $id)
			->first();
	}

	/**
	 *
	 */
	public function addFile(ManagedFile $file) {
		$file->id = $this->connection->table($this->options['files_table'])->insertGetId([
			'filepath' => $file->filepath,
			'filename' => $file->filename,
			'created_at' => new Carbon,
			'created_by' => \Auth::id(),
		]);
		return $file;
	}

	/**
	 *
	 */
	public function removeFile(ManagedFile $file) {

	}

	/**
	 *
	 */
	public function addUsage(ManagedFile $file, array $params) {
		$params = [
			'file_id' => $file->id,
			'created_at' => new Carbon,
			'created_by' => \Auth::id(),
		] + $params;
		$this->connection->table($this->options['usage_table'])->insert($params);
		return $file;
	}

	/**
	 *
	 */
	public function removeUsage(ManagedFile $file, array $params) {

	}

}
