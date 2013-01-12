<?php

return array(

	/**
	 * Default settings
	 */
	'defaults' => array(

		/**
		 * Useragent string?
		 */
		'useragent'	=> 'FuelPHP, PHP 5.3 Framework',
		/**
		 * Cloud Storage driver (s3, cf)
		 */
		'driver'		=> 's3',
        
        /**
         * Default driver access key
         */
        'access_key'    => '',
        
        /** 
         * Default driver access secret
         */
        'access_secret' => '',
        
        /**
         * container name where files will be uploaded to
         */
        'container'    => 'my_s3_bucket_name',
	),
    
	/**
	 * Default setup group
	 */
	'default_setup' => 'default',

	/**
	 * Setup groups
	 */
	'setups' => array(
       /**
        * S3 settings
        */
       's3' => array(

           /**
            * Useragent string
            */
           'useragent'	=> 'FuelPHP, PHP 5.3 Framework',
           /**
            * Cloud Storage driver (s3, cf)
            */
           'driver'		=> 's3',

           /**
            * Default driver access key
            */
           'access_key'    => '',

           /** 
            * Default driver access secret
            */
           'access_secret' => '',

           /**
            * container name where files will be uploaded to
            */
           'container'    => 'my_s3_bucket_name',

       ),
       /**
        * Cloud Files settings
        */
       'cf' => array(

           /**
            * Useragent string?
            */
           'useragent'	=> 'FuelPHP, PHP 5.3 Framework',
           /**
            * Cloud Storage driver (s3, cf)
            */
           'driver'		=> 'cf',

           /**
            * Default driver access key
            */
           'access_key'    => 'rackspace_username',

           /** 
            * Default driver access secret
            */
           'access_secret' => 'rackspace_api_key',

           /**
            * container name where files will be uploaded to
            */
           'container'    => 'my_cf_container',

       ),
	),

);
