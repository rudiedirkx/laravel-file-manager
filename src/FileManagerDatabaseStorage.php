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
	public function getUsageCount(ManagedFile $file) {
		return $this->connection
			->table($this->options['usage_table'])
			->where('file_id', $file->id)
			->count();
	}

	/**
	 *
	 */
	public function getUsages(ManagedFile $file) {
		return $this->connection
			->table($this->options['usage_table'])
			->where('file_id', $file->id)
			->get();
	}

	/**
	 *
	 */
	public function getFiles(array $conditions = [], array $options = []) {
		$query = $this->connection->table($this->options['files_table']);

		foreach ($conditions as $column => $value) {
			$query->where($column, $value);
		}

		return $query->get();
	}

	/**
	 *
	 */
	public function getFileByPath($path) {
		return $this->connection
			->table($this->options['files_table'])
			->where('filepath', $path)
			->first();
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

		return $this->connection->table($this->options['usage_table'])->insert($params);
	}

	/**
	 *
	 */
	public function removeUsage(ManagedFile $file = null, array $params) {
		$query = $this->connection->table($this->options['usage_table']);

		if ($file) {
			$query->where('file_id', $file->id);
		}

		foreach ($params as $column => $value) {
			$query->where($column, $value);
		}

		return $query->delete();
	}

}
