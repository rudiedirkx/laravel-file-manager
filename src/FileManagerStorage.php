<?php

namespace rdx\filemanager;

use rdx\filemanager\ManagedFile;

interface FileManagerStorage {

	public function getUsageCount(ManagedFile $file);
	public function getUsages(ManagedFile $file);

	public function getFiles(array $conditions = [], array $options = []);

	public function getFileByPath($path);
	public function getFile($id);

	public function addFile(ManagedFile $file);
	public function removeFile(ManagedFile $file);

	public function addUsage(ManagedFile $file, array $params);
	public function removeUsage(ManagedFile $file, array $params);

}
