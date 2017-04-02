<?php

namespace rdx\filemanager;

interface FileIdContract {

	/**
	 * @return Assoc array of [column => value] for usage storage.
	 */
	public function getUsageParams();

}
