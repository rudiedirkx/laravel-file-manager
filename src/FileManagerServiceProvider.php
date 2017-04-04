<?php

namespace rdx\filemanager;

use Illuminate\Contracts\Config\Repository as Config;
use Illuminate\Contracts\Container\Container;
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
		$this->_publishConfig();
	}

	/**
	 *
	 */
	public function boot() {
		$router = $this->app['router'];
		$config = $this->app['config'];

		$this->_loadMigrations();
		$this->_defineRouteParams($router);

		if ($config['filemanager']['publishers']['route']) {
			$this->_definePublishRoute($router, $config);
		}
	}

	/**
	 *
	 */
	protected function _registerService() {
		$this->app->singleton('filemanager', function(Container $app) {
			$connection = $app['db.connection'];
			$config = $app['config'];

			$storage = new FileManagerDatabaseStorage($connection, $config['filemanager']['storage']);
			$manager = new FileManager($storage, $config['filemanager']['manager']);

			if ($config['publishers']['original']) {
				$this->addPublisher('original', function(FileManager $manager, ManagedFile $file) {
					$target = $manager->resolvePublicPath('original', $file->filepath);
					copy($file->fullpath, $target);
				});
			}

			return $manager;
		});

		$this->app->alias('filemanager', FileManager::class);
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
			$manager = $this->app->make('filemanager');
			return $manager->findOrFail($id);
		});

		$router->pattern('filemanager_publisher', '[^/]+');
		$router->pattern('filemanager_file_path', '.+');
	}

	/**
	 *
	 */
	protected function _definePublishRoute(Router $router, Config $config) {
		$prefix = $config['filemanager']['manager']['public'];
		$uri = "$prefix/{filemanager_publisher}/{filemanager_file_path}";

		$router->get($uri, FileManagerController::class . '@getPublish')
			->name('filemanager.publish')
			->middleware('web');
	}

}
