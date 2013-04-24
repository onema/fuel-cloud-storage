# Fuel Cloud Storage Package.

A simple class abstraction for cloud storage providers. Default providers include Amazon S3 and Rackspace Cloud .

# Summary

* Upload, update and delete files from a given container.
* Creates and deletes containers.
* Check file existence.
* Copy files from one container to another.
* Also lists all the files in a container by prefix.

# Supported Drivers

* Amazon S3 (Uses AWS-PHP-SDK 2)
* Rackspace Cloud Files 

# Version
0.1.1

# Installation
* Install Composer. Follow this guide to enable fuelphp to work with composer
http://www.fuelphp.com/blogs/2013/01/fuelphp-and-composer. Each driver assumes
that the libraries live in the vendor directory in **APPPATH**.

* Use Composer to install the appropriate libraries. 
A sample_composer.json file is provided with this package, run 

```
    $ php composer.phar install  
```

* Use the config file to set the correct authentication.
Set the key pair (access/secret keys in the case of Amazon and 
username/api-key for RackSpace). For uniformity these are
simply called access_key and access_secret.
If no keys are specified in the config file, they can 
still be set at run time by using the method set_config:

```php
    $Driver->set_config('access_key', $access_key_value);
    $Driver->set_config('access_secret', $access_secret_value);
```

* Run the unit test or see the sample below. 
This package comes with very simple unit tests that check the most basic 
functionality of the package. The unit test can be run by using the group=storage 

```
     $ oil test --group=storage
```

# Usage

```php

    $time = time();
        
    // This is the file name in the container. it should include the "path"
    $file_name = 'img/'.$time.'.png';
    $container_name = 'fuel_cloud_storage_container';
    $backup_container_name = 'fuel_cloud_storage_backup';

    // path to local file
    $path_to_test_image = '/path/to/test/image.png';

    // This will use the default config values, pass the driver name and it 
    // will use the config for that driver
    $Cloud_Storage = \Cloud_Storage\Cloud_Storage::forge();

    // Create a new container an set it as the default
    $Cloud_Storage->create_container($container_name);
    $Cloud_Storage->set_config('container', $container_name);

    // Upload file to default container
    $Cloud_Storage->upload_object($path_to_test_image, $file_name);

    // Verify file existence in default container
    if($Cloud_Storage->object_exists($file_name))
    {
        echo "file was uploaded correctly";
    }

    // Create backup container
    $Cloud_Storage->create_container($backup_container_name);

    // Copy file from the default container to the backup one. 
    // This method do not support copy between services yet (eg: s3 -> cf).
    // This method requires the following patch for Rackspace Cloud Files:
    // https://github.com/rackspace/php-cloudfiles/pull/87
    // Also see:
    //  https://github.com/rackspace/php-cloudfiles/issues/82
    $Cloud_Storage->copy_to($container_name, $backup_container_name, $file_name, 'backup_img/' . $time . '.png');

    // Verify file existence in backup container
    if($Cloud_Storage->object_exists('backup_img/' . $time . '.png', $backup_container_name))
    {
        echo "file was copied correctly";
    }

    // Delete Object object in the default container
    $Cloud_Storage->delete_object($file_name);

    // Verify the file was deleted from default container
    if(!$Cloud_Storage->object_exists($file_name))
    {
        echo "file was deleted correctly";
    }

    // delete default container
    $Cloud_Storage->delete_container();

    // Delete Object from backup container
    $Cloud_Storage->delete_object('backup_img/' . $time . '.png', $backup_container_name);

    // Verify the file was deleted from the backup container
    if(!$Cloud_Storage->object_exists('backup_img/' . $time . '.png', $backup_container_name))
    {
        echo "file was deleted correctly";
    }

    // Delete Backup Container
    $Cloud_Storage->delete_container($backup_container_name);
```    

# Exceptions
    \InvalidDriverException, thrown when the given driver doesn't exist
    \InvalidFileException, thrown when the give file doesn't exist
    \UploadObjectException, thrown when the object was not uploaded
    \DeleteObjectException, thrown when the object can't be deleted
    \CreateContainerException, thrown when the container can't be created
    \DeleteContainerException, thrown when the container can't be deleted
    \ListObjectsException, thrown when the list of objects can't be retrieved 
    \AuthenticationException, thrown when the credentials fail to authenticate
    \InvalidContainerException, thrown when the given container doesn't exist or is invalid 

# MIT LICENSE

Copyright (c) 2013 Alleluu.com

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.

# Creating New Drivers
There are many storage service providers out there, create new drivers for them if you wish and contribute back :) . Create a new class under the driver directory and extend from the class Cloud_Storage_Driver, Implement all the abstract methods specified in this class.

# Unit Testing New Drivers
You can quickly test the functionality of new drivers by creating a new test class and placing it in the tests/driver directory. For example a test class for a new Windows Azure Blob Storage (wabs) would look like this

    <?php
    /**
     * @group storage
     */
    class test_driver_wabs extends test_driver_base
    {
        public function setUp()
        {
            $this->driver = 'wabs';
            parent::setUp();
        }
    }

And that is it! next time you run the unit test, it will run all the test for your driver.

# Credits
This package follows closely the email package included in the fuel php distribution. Thank you guys for making such an awesome framework!!

# TODOS:

* Create an update method that will replace an existing file.
* Fail upload if file already exist.
* Enable multi-part upload for large size files.
* Handle each exception appropriately.
* List all containers
* Re-name files
* Re-name containers
* Extend unit tests to verify correct exception handling

    
    