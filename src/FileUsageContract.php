<?php

namespace rdx\filemanager;

interface FileUsageContract {

	/**
	 * @return array Assoc array of [column => value] for usage storage.
	 */
	public function getUsageParams();

}
