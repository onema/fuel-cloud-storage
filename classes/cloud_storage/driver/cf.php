<?php
/**
 * Rackspace Cloud Files Drivers. 
 * Use Composer  "require": {"omissis/php-cloudfiles": "dev-master"}
 *
 * @package Cloud_Storage
 * @author  Juan Manuel Torres <juan.torres@alleluu.com>
 * @license MIT License
 * @copyright  2013-2014 Alleluu Development team
 * @link  https://github.com/alleluu/fuel-cloud-storage
 * @link  http://opensource.org/licenses/MIT
 */

namespace Cloud_Storage;

class Cloud_Storage_Driver_Cf extends Cloud_Storage_Driver
{
    /**
     * Delete an object from an Rackspace Cloud Files Container. The path to 
     * object is the full name of the file that will be deleted.
     * @param string $path_to_object full path to object including base name and extension
     * @return boolean
     * @throws DeleteObjectException
     */
    public function delete_object($path_to_object, $container_name = null)
    {
        $container_name = $this->validate_container_name($container_name);
        
        try
        {
            $Container = $this->get_container($container_name);
            
            // upload file to Rackspace
            $object = $Container->delete_object($path_to_object);
            
        }
        catch(AuthenticationException $e)
        {
            throw new DeleteObjectException($e->getMessage());
        }
        catch(\SyntaxException $e)
        {
            throw new DeleteObjectException($e->getMessage());
        }
        catch(\NoSuchObjectException $e)
        {
            throw new DeleteObjectException($e->getMessage());
        }
        catch(\InvalidResponseException $e)
        {
            throw new DeleteObjectException($e->getMessage());
        }
        
        return true;
    }
     
     
    /**
     * Upload an object to the selected container.
     * @param string $path_to_object
     * @param string $new_file_name use an alternative name for the file, otherwise use the same name as the source
     * @return boolean
     * @throws CantUploadException
     * @throws InvalidFileException
     */
    public function upload_object($path_to_object, $new_file_name = null, $container_name = null)
    {
        $container_name = $this->validate_container_name($container_name);
        $file_info = $this->get_file_info($path_to_object, $new_file_name);

        try
        {
            $Container = $this->get_container($container_name);
            
            // upload file to Rackspace
            $object = $Container->create_object($file_info['basename']);
            $object->load_from_filename($path_to_object);
            
        }
        catch(AuthenticationException $e)
        {
            throw new UploadObjectException($e->getMessage());
        }
        
        return true;
    }
    
    
    /**
     * 
     * @param string $container_name name of the bucket to be created
     * @param string $location Rackspace doesn't support location for CF. Specifies the region where the bucket will be created.
     * @return string the url to the public container.
     * @throws CreateContainerException
     */
    
    public function create_container($container_name, $location = null)
    {
        try
        {
            $Connection = $this->connect();
            $Container = $Connection->create_container($container_name);

            // upload file to Rackspace
            $url = $Container->make_public(static::TTL);
        }
        catch(AuthenticationException $e)
        {
            throw new CreateContainerException($e->getMessage());
        }
        catch(\CDNNotEnabledException $e)
        {
            throw new CreateContainerException($e->getMessage());
        }
        catch(\InvalidResponseException $e)
        {
            throw new CreateContainerException($e->getMessage());
        }
        catch(\SyntaxException $e)
        {
            // there was a problem with the container provided  
            throw new CreateContainerException($e->getMessage());
        }
        
        return $url . '/';
    }
   
    
    /**
     * Executes a Delete Container command: Deletes the container. All objects 
     * in the container must be deleted before the container itself can be deleted.
     * @param string $container_name bucket name to be deleted
     * @return boolean
     * @throws DeleteContainerException
     */
    public function delete_container($container_name = null)
    {
        $container_name = $this->validate_container_name($container_name);
        
        try
        {
            $Connection = $this->connect();
            $Container = $Connection->delete_container($container_name);
        }
        catch(AuthenticationException $e)
        {
            throw new DeleteContainerException($e->getMessage());
        }
        catch(\InvalidResponseException $e)
        {
            throw new DeleteContainerException($e->getMessage());
        }
        catch(\SyntaxException $e)
        {
            throw new DeleteContainerException($e->getMessage());
        }
        catch(\NonEmptyContainerException $e)
        {
            throw new DeleteContainerException($e->getMessage());
        }
        catch(\NoSuchContainerException $e)
        {
            throw new DeleteContainerException($e->getMessage());
        }
        
        return true;
    }
    
    
    /**
     * Returns a list of all the objects within a specified path/prefix. 
     * 
     * @param type $path the base path to the objects we want to list
     * @param type $container_name (Optional) if not provided the default bucket will be used
     * @return array A list with the most basic information about each object in the list
     * @throws ListObjectsException
     */
    public function list_objects($path = '', $container_name = null)
    {
        $container_name = $this->validate_container_name($container_name);
        
        try
        {
            $Container = $this->get_container($container_name);
            
            // upload file to Rackspace
            $list = $Container->list_objects(1000, null, $path);
            
        }
        catch(AuthenticationException $e)
        {
            throw new ListObjectsException($e->getMessage());
        }
        catch(\InvalidResponseException $e)
        {
            throw new ListObjectsException($e->getMessage());
        }
        
        return $list;
    }
    
    
    /**
     * Get the url of the given container, this method will assume the container 
     * is public and It uses the method make_publc to get the URL as the API doesn't
     * provide an alternative way to get the public URL to the container.
     * @param string $container_name
     * @return type
     * @throws InvalidContainerException
     */
    public function get_container_url($container_name = null)
    {
        $container_name = $this->validate_container_name($container_name);
        
        try
        {
            $Container = $this->get_container($container_name);
            
            // upload file to Rackspace
            $url = $Container->make_public(static::TTL);
        }
        catch(AuthenticationException $e)
        {
            throw new InvalidContainerException($e->getMessage());
        }
        catch(\CDNNotEnabledException $e)
        {
            throw new InvalidContainerException($e->getMessage());
        }
        catch(\InvalidResponseException $e)
        {
            throw new InvalidContainerException($e->getMessage());
        }
        
        return $url . '/';
    }
    
    
    /**
     * Copy a file from one container to another. This method requires the following
     * patch https://github.com/rackspace/php-cloudfiles/pull/87
     * Also see https://github.com/rackspace/php-cloudfiles/issues/82
     * and https://github.com/omissis/php-cloudfiles/pull/1. 
     * 
     * @param type $from_container_name
     * @param type $to_container_name
     * @param type $file_name Full name of the origin file, this should include path.
     * @param type $new_file_name Optional, Full namee to the destination container. If not set it will use the same path and name as the source
     * @return boolean
     * @throws CopyObjectException
     */
    public function copy_to($from_container_name, $to_container_name, $file_name, $new_file_name = null)
    {
        $file_info = pathinfo($file_name);
        !isset($new_file_name) and $new_file_name = $file_info['dirname'] . '/' . $file_info['basename'];
        
        $new_file_name = ltrim($new_file_name, '/');
        
        try
        {
            $Container = $this->get_container($from_container_name);
            $Object = $Container->get_object($file_name);
            
            // Copy to Rackspace target container
            $Container->copy_object_to($Object, $to_container_name, $new_file_name);
            
        }
        catch(AuthenticationException $e)
        {
            throw new CopyObjectException($e->getMessage());
        }
        catch(\SyntaxException $e)
        {
            throw new CopyObjectException($e->getMessage());
        }
        catch(\NoSuchObjectException $e)
        {
            throw new CopyObjectException($e->getMessage());
        }
        catch(\InvalidResponseException $e)
        {
            throw new CopyObjectException($e->getMessage());
        }
        
        return true;
    }

    
    /**
     * Check if the object exist. This particular method uses cURL to get the 
     * object code. If the code is 200 we assume it exists, false otherwise.
     * 
     * @param string $path_to_object
     * @param string $container_name
     * @return boolean
     */
    public function object_exists($path_to_object, $container_name = null)
    {
        $container_name = $this->validate_container_name($container_name);
        $url = $this->get_container_url($container_name)  . $path_to_object;
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_NOBODY, true);
        curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        // $retcode > 400 -> not found, $retcode = 200, found.
        return ($code == 200) ? true : false;
    }
    
    
    /**
     * Get the container by name
     * @param string $container_name
     * @return CF_Container
     */
    private function get_container($container_name)
    {
        $Connection = $this->connect();

        // Get the container we want to use
        $Container = $Connection->get_container($container_name);
        
        return $Container;
    }
    
    
    /**
     * Creates a connection object for Rackspace Cloud Files.
     * @return \CF_Connection 
     * @throws AuthenticationException
     */
    private function connect()
    {
        $username = $this->get_config('access_key');
        $api_key = $this->get_config('access_secret');
        
        try
        {
            $auth = new \CF_Authentication($username, $api_key);
            $auth->ssl_use_cabundle();
            $auth->authenticate();
        }   
        catch(\AuthenticationException $e)
        {
            throw new AuthenticationException($e->getMessage()) ;
        }
        
        if ( $auth->authenticated() )
        {
            $Connection = new \CF_Connection($auth);
        }
        else
        {
            throw new AuthenticationException("Authentication failed") ;
        }
        
        return $Connection;
    }
}
