<?php

namespace rdx\filemanager;

use rdx\filemanager\FileUsageContract;

class FileUsage implements FileUsageContract {

	protected $type = '';
	protected $components = [];

	/**
	 *
	 */
	public function __construct($type, ...$components) {
		$this->type = $type;
		$this->components = $components;
	}

	/**
	 *
	 */
	public function getUsageParams() {
		return [
			'used_type' => $this->type,
			'used_id' => implode(':', $this->components),
		];
	}

}
