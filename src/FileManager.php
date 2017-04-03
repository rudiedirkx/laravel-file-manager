<?php

namespace rdx\filemanager;

use Illuminate\Http\UploadedFile;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use rdx\filemanager\FileUsageContract;
use rdx\filemanager\FileManagerStorage;
use rdx\filemanager\ManagedFile;

class FileManager {

	protected $storage;
	protected $options = [];

	protected $storage_path = '';
	protected $public_path = '';

	protected $publishers = [];

	/**
	 *
	 */
	public function __construct(FileManagerStorage $storage, array $options = []) {
		$this->storage = $storage;
		$this->options = $options;

		$this->storage_path = storage_path($options['storage']) . '/';
		$this->public_path = public_path($options['public']) . '/';

		$this->addPublisher('original', function(FileManager $manager, ManagedFile $file) {
			$target = $manager->resolvePublicPath('original', $file->filepath);
			$manager->prepDir($manager->public_path, dirname($target));
			copy($file->fullpath, $target);
			$manager->chmodFile($target);
		});
	}

	/**
	 *
	 */
	public function addPublisher($name, callable $callback) {
		$this->publishers[$name] = $callback;
	}

	/**
	 *
	 */
	public function publish($publisher, ManagedFile ...$files) {
		if (!isset($this->publishers[$publisher])) {
			throw new \Exception("Invalid publisher '$publisher'.");
		}
		$publisher = $this->publishers[$publisher];

		usleep(50000);
		foreach ($files as $file) {
			$publisher($this, $file);
		}
	}

	/**
	 *
	 */
	public function resolveWebPath($publisher, $path) {
		$root = $this->public_path;
		$full = $this->resolvePublicPath($publisher, $path);
		return '/' . $this->options['public'] . '/' . substr($full, strlen($root));
	}

	/**
	 *
	 */
	public function resolvePublicPath($publisher, $path) {
		return rtrim($this->public_path . $publisher . '/' . trim($path, '/'), '/');
	}

	/**
	 *
	 */
	public function resolveStoragePath($path) {
		return rtrim($this->storage_path . trim($path, '/'), '/');
	}

	/**
	 *
	 */
	public function getFilePath($filename, $destination = null) {
		return $this->resolveStoragePath($destination) . '/' . $this->cleanFileName($filename);
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

		return substr($filepath, strlen($this->storage_path));
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
		$filename = strtolower($filename);

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

		$this->prepDir($this->storage_path, dirname($managed->fullpath));
		$this->chmodFile($managed->fullpath);

		return $moved;
	}

	/**
	 *
	 */
	public function chmodFile($path) {
		return chmod($path, $this->options['chmod_files']);
	}

	/**
	 *
	 */
	public function chmodDir($path) {
		return @chmod($path, $this->options['chmod_dirs']);
	}

	/**
	 *
	 */
	public function prepPublicDir($path) {
		return $this->prepDir($this->public_path, $path);
	}

	/**
	 *
	 */
	public function prepDir($root, $path) {
		$root = rtrim($root, '/');
		$path = rtrim($path, '/');

		@mkdir($path, $this->options['chmod_dirs'], true);

		while ($path != $root) {
			$this->chmodDir($path);
			$path = dirname($path);
		}
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
	public function getUsageCount(ManagedFile $file) {
		return $this->storage->getUsageCount($file);
	}

	/**
	 *
	 */
	public function getUsages(ManagedFile $file) {
		$usages = $this->storage->getUsages($file);
		return collect($usages)->map(function($usage) {
			return get_object_vars($usage);
		});
	}

	/**
	 *
	 */
	public function findByPath($path) {
		$file = $this->storage->getFileByPath($path);
		if ($file) {
			return $this->createManagedFileFromStorage(get_object_vars($file));
		}
	}

	/**
	 *
	 */
	public function findByPathOrFail($path) {
		$file = $this->findByPath($path);
		if (!$file) {
			throw new NotFoundHttpException("File '$path' doesn't exist.");
		}
		return $file;
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
	public function findAll(array $conditions = []) {
		$files = $this->storage->getFiles($conditions);
		return collect($files)->map(function($file) {
			return $this->createManagedFileFromStorage(get_object_vars($file));
		});
	}

	/**
	 *
	 */
	public function addUsage(FileUsageContract $usage, ManagedFile $file) {
		$this->storage->addUsage($file, $usage->getUsageParams());
		return $file;
	}

	/**
	 *
	 */
	public function removeUsage(FileUsageContract $usage, ManagedFile $file) {
		$this->storage->removeUsage($file, $usage->getUsageParams());
		return $file;
	}

	/**
	 *
	 */
	public function replaceUsage(FileUsageContract $usage, ManagedFile $file) {
		$params = $usage->getUsageParams();
		$this->storage->removeUsage(null, $params);
		$this->storage->addUsage($file, $params);
		return $file;
	}

	/**
	 *
	 */
	public function cleanUsage() {
		// @todo Find and remove all files without usage
	}

	/**
	 *
	 */
	public function addUsages(FileUsageContract $usage, ManagedFile ...$files) {
		return $files;
	}

	/**
	 *
	 */
	public function removeUsages(FileUsageContract $usage, ManagedFile ...$files) {
		return $files;
	}

	/**
	 *
	 */
	public function replaceUsages(FileUsageContract $usage, ManagedFile ...$files) {
		return $files;
	}

}
