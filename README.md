Laravel file manager
====

Keeps track of files and their usage. Works with an SQL backend by default.

Install
----

1. Publish the config:
   ```
   php artisan vendor:publish
   ```
2. Optionally alter the config:
   ```
   config/filemanager.php
   ```
3. Install the schema (SQL provider, but you can make your own)
   ```
   php artisan migrate
   ```
   ^ This makes `files` and `files_usage` tables.
4. Add the service provider:
   ```
   rdx\filemanager\FileManagerServiceProvider::class
   ```
5. Inject the service:
   ```
   public function store(FileManager $files)
   ```

Save uploads
----

`ManagedFile` records will be the primary source of files, not the file system.

	$uploaded = $request['picture']; // Illuminate\Http\UploadedFile

	$managed = $files->saveFile($uploaded); // in root dir
	$managed = $files->saveFile($uploaded, 'some/sub/dir'); // in sub dir

File usage
----

File usage is useful to automatically delete orphaned files, or make sure used
files aren't deleted.

Usage is kept by creating a `FileUsageContract`. There are 2 provided, but
your app can make others.

To truly customize file usage, change the migration to add usage columns, AND create
custom usage objects to reflect those columns.

	// Generic FileUsage
	$managed->addUsage(new FileUsage('type', 'more', 'specific', 'keys'));
	// Save this file's usage for [type, more:specific:keys]

	// Model bound ModelFileUsage
	$managed->addUsage(new ModelFileUsage($user, 'picture'));
	// Save this file's usage for [User, 14:picture]

Deleting files
----

@todo Can't delete used files
@todo Auto delete unused files

To do
----

* Deleting files
* Use Laravel's file system helpers
* Some kind of access control?
