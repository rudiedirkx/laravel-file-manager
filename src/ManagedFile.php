<?php

namespace rdx\filemanager;

use Illuminate\Http\File;
use rdx\filemanager\FileIdContract;
use rdx\filemanager\FileManager;

class ManagedFile {

	protected $manager;

	public $id = 0;
	public $filename;
	public $filepath;
	public $created_at;
	public $created_by;
	public $file;

	/**
	 *
	 */
	public function __construct(FileManager $manager, array $params) {
		$this->manager = $manager;

		foreach ($params as $property => $value) {
			$this->$property = $value;
		}

		if ($this->filepath) {
			$this->file = new File($this->fullpath, false);
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
	public function webPath($publisher) {
		return $this->manager->resolveWebPath($publisher, $this->filepath);
	}

	/**
	 *
	 */
	public function publicPath($publisher) {
		return $this->manager->resolvePublicPath($publisher, $this->filepath);
	}

	/**
	 *
	 */
	public function __get($name) {
		if ($name == 'fullpath') {
			return $this->manager->resolveStoragePath($this->filepath);
		}
	}

}