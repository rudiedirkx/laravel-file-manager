<?php

return [
	'manager' => [
		'storage' => 'uploads',
		'public' => 'uploads',
		'chmod_dirs' => 0777,
		'chmod_files' => 0666,
	],

	'storage' => [
		'files_table' => 'files',
		'usage_table' => 'files_usage',
	],

	'publishers' => [
		'original' => true,
		'route' => true,
	],
];
