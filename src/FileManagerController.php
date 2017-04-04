<?php

namespace rdx\filemanager;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use rdx\filemanager\FileManager;

class FileManagerController extends Controller {

	/**
	 *
	 */
	public function getPublish(Request $request, FileManager $files, $publisher, $path) {
		$file = $files->findByPathOrFail($path);
		$mime = $file->file->getMimeType();

		$_time = microtime(1);
		$files->publish($publisher, $file);
		$_time = microtime(1) - $_time;

		header("Content-type: $mime");
		header("X-publishing-time: " . round($_time * 1000));
		readfile($file->publicPath($publisher));
		exit;
	}

}
