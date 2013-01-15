<?php
/**
 * Amazon Web Services S3 Driver. 
 * Use Composer "require": {"aws/aws-sdk-php": "2.*"}
 *
 * @package Cloud_Storage
 * @version 0.1 
 * @author  Juan Manuel Torres <juan.torres@alleluu.com>
 * @license MIT License
 * @copyright  2013-2014 Alleluu Development team
 * @link  https://github.com/alleluu/fuel-cloud-storage
 * @link  http://opensource.org/licenses/MIT
 */

namespace Cloud_Storage;

use Aws\Common\Aws;
use Aws\S3\Enum\CannedAcl;
use Aws\S3\Exception\S3Exception;


class Cloud_Storage_Driver_S3 extends Cloud_Storage_Driver
{
    // Will contain an insatance of an S3 Client
    private $s3;
    
    
    /**
     * Delete an object from an Amazon S3 Bucket. The path to object is the full
     * name of the file that will be deleted.
     * 
     * @param string $path_to_object full path to object including base name and extension
     * @return boolean
     * @throws CantDeleteException
     */
    public function delete_object($path_to_object)
    {
        try 
        {
            // AWS doesn't tell us if the file exists or not.
            if(!$this->object_exists($path_to_object))
            {
                throw new DeleteObjectException('File does not exists');
            }
            
            $this->s3->deleteObject(array(
                'Bucket' => $this->get_config('container'),
                'Key'    => $path_to_object,
                'args'   => array('required' => true) 
            ));
        } 
        catch (S3Exception $e) 
        {
            throw new DeleteObjectException($e->getMessage());
        }
        
        return true;
    }
     
     
    /**
     * Upload an object to the selected container.
     * 
     * @param string $path_to_object
     * @param string $new_file_name use an alternative name for the file, otherwise use the same name as the source
     * @return boolean
     * @throws CantUploadException
     * @throws InvalidFileException
     */
    public function upload_object($path_to_object, $new_file_name = null)
    {
        $file_info = $this->get_file_info($path_to_object, $new_file_name);
        
        /* 
         * Upload a publicly accessible file. File size, file type, and md5 
         * hash are automatically calculated by the SDK
         */
        try 
        {
            /*
             * Create a new s3 client instance, this happens here to allow for 
             * the keys to be changed/updated at run time.
             */
            $this->create_instance();
            $this->s3->putObject(array(
                'Bucket' => $this->get_config('container'),
                'Key'    => $file_info['basename'],
                'Body'   => fopen($path_to_object, 'r'),
                'ACL'    => CannedAcl::PUBLIC_READ
            ));
        } 
        catch (S3Exception $e) 
        {
            throw new UploadObjectException($e->getMessage());
        }
        catch (\Aws\Common\Exception\InstanceProfileCredentialsException $e) 
        {
            throw new UploadObjectException($e->getMessage());
        }
        
        return true;
    }
    
    
    /**
     * Creates a new S3 Bucket.
     * 
     * @param string $name name of the bucket to be created
     * @param string $location Specifies the region where the bucket will be created.
     * @return boolean
     * @throws CreateContainerException
     */
    public function create_container($name, $location = null)
    {
        $setup = array(
                    'Bucket' => $name,
                    'ACL'    => CannedAcl::PUBLIC_READ
                );
        
        isset($location) and $setup = array_push($setup, $location);
        
        try 
        {
            /*
             * Create a new s3 client instance, this happens here to allow for 
             * the keys to be changed/updated at run time.
             */
            $this->create_instance();
            $this->s3->createBucket($setup);
        } 
        catch (S3Exception $e) 
        {
            throw new CreateContainerException($e->getMessage());
        }
        
        return $this->get_container_url($name);
    }
   
    
    /**
     * Executes a DeleteBucket command: Deletes the bucket. All objects 
     * (including all object versions and Delete Markers) in the bucket 
     * must be deleted before the bucket itself can be deleted.
     * 
     * @param string $name bucket name to be deleted
     * @return boolean
     * @throws DeleteContainerException
     */
    public function delete_container($name)
    {
        try 
        {
            /*
             * Create a new s3 client instance, this happens here to allow for 
             * the keys to be changed/updated at run time.
             */
            $this->create_instance();
            $this->s3->deleteBucket(array(
                        'Bucket' => $name,
                    ));
        } 
        catch (S3Exception $e) 
        {
            throw new DeleteContainerException($e->getMessage());
        }
        
        return true;
    }
    
    
    /**
     * Returns a list of all the objects within a specified path/prefix. 
     * 
     * @param type $path the base path to the objects we want to list
     * @param type $bucket_name (Optional) if not provided the default bucket will be used
     * @return array A list with the most basic information about each object in the list
     * @throws ListObjectsException
     */
    public function list_objects($path = '', $bucket_name = null)
    {
        // Use the default bucket if none is specified
        !isset($bucket_name) and $bucket_name = $this->get_config('container');
        
        try 
        {
            /*
             * Create a new s3 client instance, this happens here to allow for 
             * the keys to be changed/updated at run time.
             */
            $this->create_instance();
            $list_model = $this->s3->listObjects(array(
                        'Bucket' => $bucket_name,
                        'Prefix' => $path
                    ));
        } 
        catch (S3Exception $e) 
        {
            throw new ListObjectsException($e->getMessage());
        }
        
        $list = $list_model->getAll();
        
        return $this->get_list_objects($list);;
    }
    
    
    /**
     * Returns the Amazon S3 bucket url, by default it uses https protocol
     * 
     * @param string $name container name
     * @return string
     * @throws InvalidContainerException
     */
    public function get_container_url($name = null)
    {
        // use the default container name if one is not provided.
        !isset($name) and $name = $this->get_config('container');
        
        try 
        {
            /*
             * Create a new s3 client instance, this happens here to allow for 
             * the keys to be changed/updated at run time.
             */
            $this->create_instance();
            
        } 
        catch (S3Exception $e) 
        {
            throw new InvalidContainerException($e->getMessage());
        }
        
        if(!$this->s3-> doesBucketExist($name))
        {
            throw new InvalidContainerException("The conainer $name doesn't exist");
        }
        
        return 'https://s3.amazonaws.com/' . $name . '/' ;
    }
    
    
    /**
     * Copy a file from one container to another.
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
            /*
             * Create a new s3 client instance, this happens here to allow for 
             * the keys to be changed/updated at run time.
             */
            $this->create_instance();
            $url = $this->get_container_url($from_container_name);
            $this->s3->copyObject(array(
                'Bucket'        => $to_container_name,
                'Key'           => $new_file_name,
                'CopySource'    => urlencode($from_container_name . '/' . $file_name),
                'ACL'           => CannedAcl::PUBLIC_READ
            ));
        } 
        catch(S3Exception $e) 
        {
            throw new CopyObjectException($e->getMessage());
        }
        catch(\Guzzle\Service\Exception\ValidationException $e)
        {
            throw new CopyObjectException($e->getMessage());
        }
        
        return true;
    }
    
    
    /**
     * Check if the object exist. This particular method uses the sdk method 
     * DoesObjectExist and returns it's value.
     * 
     * @param string $path_to_object
     * @param string $container_name
     * @return boolean
     */
    public function object_exists($path_to_object, $container_name = null)
    {
        !isset($container_name) and $container_name = $this->get_config('container');
        
        try 
        {
            /*
             * Create a new s3 client instance, this happens here to allow for 
             * the keys to be changed/updated at run time.
             */
            $this->create_instance();
            return $this->s3-> doesObjectExist($container_name, $path_to_object);
        } 
        catch (S3Exception $e) 
        {
            throw new CloudStorageException($e->getMessage());
        }
    }
    
    
    /**
     * Create an instance of an S3 Client, This gets the current object config
     * for key and secret.
     */
    private function create_instance()
    {
        // Create S3 client instance
        $this->s3 = Aws::factory(array(
                    'key'    => $this->get_config('access_key'),
                    'secret' => $this->get_config('access_secret'),
                ))->get('s3');
    }
    
    
    /**
     * Helper method to get a list with the basic info of each object in the list.
     * @param array $list array of contents returned by the amazon Guzzle\Service\Resource\Model
     * @return array $objects with basic information about the objects
     */
    private function get_list_objects($list)
    {
        $objects = array();
        
        foreach($list['Contents'] as $content)
        {
            $path_info = pathinfo($content['Key']);
            !isset($path_info['extension']) and $path_info['extension'] = '';
            
            $objects[] = $content['Key'];
// support other information. commeted out until I figure out how to do the same with RS CF.
//            $objects[] = array(
//                    'full_name' => $content['Key'],
//                    'base_name' => $path_info['basename'],
//                    'extension' => $path_info['extension'],
//                    'size' => $content['Size'],
//                ); 
        }
        
        return $objects;
    }
}
