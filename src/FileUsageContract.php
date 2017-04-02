<?php

namespace rdx\filemanager;

interface FileUsageContract {

	/**
	 * @return Assoc array of [column => value] for usage storage.
	 */
	public function getUsageParams();

}
