<?php

namespace rdx\filemanager;

use Illuminate\Http\File;
use rdx\filemanager\FileUsageContract;
use rdx\filemanager\FileManager;

/**
 * @property string $fullpath
 */
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
	public function getUsageCount() {
		return $this->manager->getUsageCount($this);
	}

	/**
	 *
	 */
	public function getUsages() {
		return $this->manager->getUsages($this);
	}

	/**
	 *
	 */
	public function addUsage(FileUsageContract $usage) {
		return $this->manager->addUsage($usage, $this);
	}

	/**
	 *
	 */
	public function replaceUsage(FileUsageContract $usage) {
		return $this->manager->replaceUsage($usage, $this);
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
