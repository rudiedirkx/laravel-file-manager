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
			$config = $app->make('config');

			$storage = new FileManagerDatabaseStorage($connection, $config['filemanager']['storage']);
			return new FileManager($storage, $config['filemanager']['manager']);
		});
	}

	/**
	 *
	 */
	protected function _publishConfig() {
		$path = __DIR__ . '/../config/filemanager.php';

		$this->publishes([
			$path => config_path('filemanager.php'),
		]);

		$this->mergeConfigFrom($path, 'filemanager');
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
