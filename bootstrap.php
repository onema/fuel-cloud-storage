<?php

Autoloader::add_core_namespace('Cloud_Storage');

Autoloader::add_classes(array(
	/**
	 * Cloud_Storage classes.
	 */
	'Cloud_Storage\\Cloud_Storage'				=> __DIR__.'/classes/cloud_storage.php',
	'Cloud_Storage\\Cloud_Storage_Driver'		=> __DIR__.'/classes/cloud_storage/driver.php',
	'Cloud_Storage\\Cloud_Storage_Driver_Cf'	=> __DIR__.'/classes/cloud_storage/driver/cf.php',
	'Cloud_Storage\\Cloud_Storage_Driver_S3'	=> __DIR__.'/classes/cloud_storage/driver/s3.php',
	
	/**
	 * Cloud_Storage exceptions.
	 */
    'Cloud_Storage\\CloudStorageException'      => __DIR__.'/classes/cloud_storage.php',
    'Cloud_Storage\\AuthenticationException'    => __DIR__.'/classes/cloud_storage.php',
	'Cloud_Storage\\InvalidDriverException'     => __DIR__.'/classes/cloud_storage.php',
	'Cloud_Storage\\InvalidFileException'       => __DIR__.'/classes/cloud_storage.php',
	'Cloud_Storage\\UploadObjectException'      => __DIR__.'/classes/cloud_storage.php',
    'Cloud_Storage\\DeleteObjectException'      => __DIR__.'/classes/cloud_storage.php',
    'Cloud_Storage\\ListObjectsException'       => __DIR__.'/classes/cloud_storage.php',
    'Cloud_Storage\\CreateContainerException'   => __DIR__.'/classes/cloud_storage.php',
    'Cloud_Storage\\DeleteContainerException'   => __DIR__.'/classes/cloud_storage.php',
    'Cloud_Storage\\CopyObjectException'        => __DIR__.'/classes/cloud_storage.php',
    
	
));
