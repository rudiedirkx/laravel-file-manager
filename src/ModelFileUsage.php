<?php

namespace rdx\filemanager;

use Illuminate\Database\Eloquent\Model;
use rdx\filemanager\FileUsageContract;

class ModelFileUsage implements FileUsageContract {

	protected $model;
	protected $field = '';

	/**
	 *
	 */
	public function __construct(Model $model, $field) {
		$this->model = $model;
		$this->field = $field;
	}

	/**
	 *
	 */
	public function getUsageParams() {
		return [
			'used_type' => get_class($this->model),
			'used_id' => $this->model->getKey() . ':' . $this->field,
		];
	}

}
