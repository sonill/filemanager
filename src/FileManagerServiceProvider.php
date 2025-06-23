<?php

namespace Sanil\FileManager;

use Illuminate\Support\ServiceProvider;
use Sanil\FileManager\Services\UploadService;

class FileManagerServiceProvider extends ServiceProvider {
	public function boot(): void {
		// Just load migrations automatically, no publishing
		$this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
	}

	public function register(): void {
		$this->app->singleton(UploadService::class, function ($app) {
			return new UploadService();
		});
	}
}
