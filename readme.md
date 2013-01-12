# Fuel Cloud Storage Package.

A simple class abstraction for cloud storage providers for Fuel. Default providers include Amazon S3 and Rackspace Cloud .

# Summary

* Upload, update and delete files from a given container, 
* creates and deletes containers. 
* It can also list all the files in a container by prefix, 

# Supported Drivers

* - Amazon S3 (Uses AWS-PHP-SDK 2)
* - Rackspace Cloud Files 

# Installation

* Use Composer to install the appropriate libraries. 
* A sample_composer.json file is provided with this package,
* this package assumes that composer is setup to work as 
* specified in this guide: 
* http://tomschlick.com/2012/11/01/composer-with-fuelphp/
* This package follows very closely the conventions used in 
* the FuelPHP email package. 
*
* Use the config file to set the correct authentication 
* key pair (access/secret keys in the case of Amazon and 
* username/api-key for RackSpace). For uniformity these are
* simply called access_key and access_secret.
* If no Keys are specified in the config file, these can 
* still be set at run time by using the method set_config:

    $Driver->set_config('access_key', $access_key_value);
    $Driver->set_config('access_secret', $access_secret_value);

* Once the libraries have been installed, you can use the 
* sample below or run the test cases from the command line 
*     oil test --group=storage

# Usage

    $name = 'img/'.time().'.png';
    $container_name = 'my_fuel_cloud_storage_container';
    $path_to_test_image = '/path/to/test/image.png';

    // This will use the default config values, use the param s3 or cf to get specific values.
    $Cloud_Storage = \Cloud_Storage\Cloud_Storage::forge();
    $Cloud_Storage->create_container($container_name);

    // change the container to use the new one, otherwise the one specified in the config will be used
    $Cloud_Storage->set_config('container', $container_name);

    // Upload file
    $Cloud_Storage->upload_object($path_to_test_image, $name);

    // Verify file existence
    $url = $Cloud_Storage->get_container_url($container_name) . $name;
    $code = file_code($url);

    if($code == 200)
    {
        echo "file was uploaded correctly<br>";
    }

    // Delete Object
    $Cloud_Storage->delete_object($name);
    $code = file_code($url);

    if($code > 400)
    {
        echo "file was deleted correctly<br>";
    }

    $Cloud_Storage->delete_container($container_name);

    function file_code($url) 
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_NOBODY, true);
        curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        // $retcode > 400 -> not found, $retcode = 200, found.
        curl_close($ch);
        return $code;
    }
	

# Exceptions
    + \InvalidDriverException, thrown when the given driver doesn't exist
    + \InvalidFileException, thrown when the give file doesn't exist
    + \UploadObjectException, thrown when the object was not uploaded
    + \DeleteObjectException, thrown when the object can't be deleted
    + \CreateContainerException, thrown when the container can't be created
    + \DeleteContainerException, thrown when the container can't be deleted
    + \ListObjectsException, thrown when the list of objects can't be retrieved 
    + \AuthenticationException, thrown when the credentials fail to authenticate
    + \InvalidContainerException, thrown when the given container doesn't exist or is invalid 

# TODOS:

* - Create an update method that will replace an existing file.
* - Fail upload if file already exist.
* - Enable multi-part upload for large size files.
* - Handle each exception appropriately, currently each exception re-throwns 
*   the exception as one of the above.
* - List all containers

	
	