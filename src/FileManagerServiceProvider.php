<?php

namespace rdx\filemanager;

use Illuminate\Database\ConnectionInterface;
use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;
use rdx\filemanager\FileManager;
use rdx\filemanager\FileManagerDatabaseStorage;

class FileManagerServiceProvider extends ServiceProvider {

	/**
	 *
	 */
	public function register() {
		$this->_registerService();
	}

	/**
	 *
	 */
	public function boot() {
		$this->_publishConfig();

		$this->_loadMigrations();

		$this->_defineRouteParams($this->app->make('router'));
	}

	/**
	 *
	 */
	protected function _registerService() {
		$this->app->singleton(FileManager::class, function($app) {
			$connection = $app->make(ConnectionInterface::class);

			$storageConfig = [
				'files_table' => 'files',
				'usage_table' => 'files_usage',
			];
			$storage = new FileManagerDatabaseStorage($connection, $storageConfig);

			$managerConfig = [
				'storage' => 'uploads',
				'public' => 'files',
				'chmod_dirs' => 0777,
				'chmod_files' => 0666,
			];
			return new FileManager($storage, $managerConfig);
		});
	}

	/**
	 *
	 */
	protected function _publishConfig() {
		$this->publishes([
			__DIR__ . '/config/filemanager.php' => config_path('filemanager.php'),
		]);
	}

	/**
	 *
	 */
	protected function _loadMigrations() {
		$this->loadMigrationsFrom(__DIR__ . '/../migrations/');
	}

	/**
	 *
	 */
	protected function _defineRouteParams(Router $router) {
		$router->pattern('managed_file', '[0-9]+');
		$router->bind('managed_file', function($id) {
			$manager = $this->app->make(FileManager::class);
			return $manager->findOrFail($id);
		});

		$router->pattern('managed_file_path', '.+');
	}

}
