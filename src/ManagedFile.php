<?php

namespace rdx\filemanager;

use rdx\filemanager\FileIdContract;
use rdx\filemanager\FileManager;

class ManagedFile {

	protected $manager;

	public $id = 0;
	public $filename;
	public $filepath;
	public $created_at;
	public $created_by;

	/**
	 *
	 */
	public function __construct(FileManager $manager, array $params) {
		$this->manager = $manager;

		foreach ($params as $property => $value) {
			$this->$property = $value;
		}
	}

	/**
	 *
	 */
	public function addUsage(FileIdContract $usage) {
		return $this->manager->addUsage($usage, $this);
	}

	/**
	 *
	 */
	public function addUsages(FileIdContract ...$usages) {

	}

	/**
	 *
	 */
	public function __get($name) {
		if ($name == 'fullpath') {
			return $this->manager->resolvePath($this->filepath);
		}
	}

}
