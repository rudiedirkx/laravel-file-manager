<?php

namespace rdx\filemanager;

use Illuminate\Http\UploadedFile;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use rdx\filemanager\FileIdContract;
use rdx\filemanager\FileManagerStorage;
use rdx\filemanager\ManagedFile;

class FileManager {

	protected $storage;
	protected $options = [];

	protected $base = '';

	/**
	 *
	 */
	public function __construct(FileManagerStorage $storage, array $options = []) {
		$this->storage = $storage;
		$this->options = $options;

		$this->base = storage_path() . '/';
	}

	/**
	 *
	 */
	public function resolvePath($path) {
		if (!$path) {
			$path = $this->options['destination'];
		}

		return $this->base . trim($path, '/');
	}

	/**
	 *
	 */
	public function getFilePath($filename, $destination = null) {
		return $this->resolvePath($destination) . '/' . $this->cleanFileName($filename);
	}

	/**
	 *
	 */
	public function makeNewFilePath($filename, $destination = null) {
		$original = $this->getFilePath($filename, $destination);

		$i = 0;
		$filepath = $original;
		while (file_exists($filepath)) {
			$filepath = $this->appendFilePathUniqueness($original, $i++);
		}

		return substr($filepath, strlen($this->base));
	}

	/**
	 *
	 */
	public function appendFilePathUniqueness($filepath, $index) {
		$ext = $this->takeExtension($filepath);
		return $filepath . '_' . $index . $ext;
	}

	/**
	 *
	 */
	public function cleanFileName($filename) {
		$ext = $this->takeExtension($filename);

		$filename = preg_replace('#[^a-z0-9\-_]#i', '_', $filename);
		$filename = preg_replace('#[_\-]+#', '_', $filename);
		$filename = trim($filename, '_');

		return $filename . $ext;
	}

	/**
	 *
	 */
	public function takeExtension(&$filename) {
		$ext = strtolower($this->getExtension($filename));
		$filename = preg_replace('#' . preg_quote($ext, '#') . '$#i', '', $filename);
		if ($ext && trim($ext, '.')) {
			return $ext;
		}
	}

	/**
	 *
	 */
	public function getExtension($filename) {
		if (strpos($filename, '/') !== false) {
			$filename = basename($filename);
		}

		$pos = strrpos($filename, '.');
		if ($pos !== false) {
			return substr($filename, $pos);
		}
	}

	/**
	 *
	 */
	public function saveFile(UploadedFile $uploaded, $destination = null) {
		$managed = $this->createManagedFileFromUpload($uploaded, $destination);
		$this->persistFile($managed, $uploaded);

		// @todo Customizable filename by App

		$this->storage->addFile($managed);
		return $managed;
	}

	/**
	 *
	 */
	protected function persistFile(ManagedFile $managed, UploadedFile $uploaded) {
		$moved = $uploaded->move(
			dirname($managed->fullpath),
			basename($managed->fullpath)
		);

		$this->chmodFile($managed->fullpath);

		$path = dirname($managed->fullpath);
		while ("$path/" != $this->base) {
			$this->chmodDir($path);
			$path = dirname($path);
		}

		return $moved;
	}

	/**
	 *
	 */
	protected function chmodFile($path) {
		return @chmod($path, 0666);
	}

	/**
	 *
	 */
	protected function chmodDir($path) {
		return @chmod($path, 0777);
	}

	/**
	 *
	 */
	protected function createManagedFileFromUpload(UploadedFile $file, $destination) {
		return new ManagedFile($this, [
			'filename' => $file->getClientOriginalName(),
			'filepath' => $this->makeNewFilePath($file->getClientOriginalName(), $destination),
		]);
	}

	/**
	 *
	 */
	protected function createManagedFileFromStorage(array $params) {
		return new ManagedFile($this, $params);
	}

	/**
	 *
	 */
	public function find($id) {
		$file = $this->storage->getFile($id);
		if ($file) {
			return $this->createManagedFileFromStorage(get_object_vars($file));
		}
	}

	/**
	 *
	 */
	public function findOrFail($id) {
		$file = $this->find($id);
		if (!$file) {
			throw new NotFoundHttpException("File '$id' doesn't exist.");
		}
		return $file;
	}

	/**
	 *
	 */
	public function addUsage(FileIdContract $usage, ManagedFile $file) {
		$this->storage->addUsage($file, $usage->getUsageParams());
		return $file;
	}

	/**
	 *
	 */
	public function addUsages(FileIdContract $usage, ManagedFile ...$files) {
		return $files;
	}

	/**
	 *
	 */
	public function removeUsage(FileIdContract $usage, ManagedFile $file) {
		return $file;
	}

	/**
	 *
	 */
	public function removeUsages(FileIdContract $usage, ManagedFile ...$files) {
		return $files;
	}

	/**
	 *
	 */
	public function replaceUsage(FileIdContract $usage, ManagedFile $file) {
		return $file;
	}

	/**
	 *
	 */
	public function replaceUsages(FileIdContract $usage, ManagedFile ...$files) {
		return $files;
	}

}
