<?php
/**
 * @group storage_base
 */
class test_driver_base extends TestCase
{
    protected $time;
    protected $name;
    protected $container_name;
    protected $path_to_test_image;
    protected $driver;
    protected $Cloud_Storage;

    protected $access_key;
    protected $access_secret;
    
    public function setUp()
    {
        $this->time = time(); // use a unique string.
        $this->name = 'img/image.png';
        $this->container_name = 'test_container_' . $this->time;
        $this->path_to_test_image = dirname(__FILE__).'/image.png';
        
        // This will use the default config values
        $this->Cloud_Storage = \Cloud_Storage\Cloud_Storage::forge('s3');
        $this->Cloud_Storage->create_container($this->container_name);
        $this->Cloud_Storage->set_config('container', $this->container_name);
        
        $this->access_key = $this->Cloud_Storage->get_config('access_key');
        $this->access_secret = $this->Cloud_Storage->get_config('access_secret');
        
        
    }
    
    public function tearDown()
    {
        $this->Cloud_Storage->set_config('access_key', $this->access_key);
        $this->Cloud_Storage->set_config('access_secret', $this->access_secret);
        $this->Cloud_Storage->set_config('container', $this->container_name);
        
        // Verify file existence and delete it
        $url = $this->Cloud_Storage->get_container_url($this->container_name) . $this->name;
        $code = $this->file_code($url);
        
        if($code == 200)
        {
            $uploaded = $this->Cloud_Storage->delete_object($this->name);
        }
        
        $this->Cloud_Storage->delete_container($this->container_name);
    }
    
    public function test_create_container()
    {
        $container_name = 'test_create_container_' . $this->time;
        $url = $this->Cloud_Storage->create_container($container_name);
        $this->Cloud_Storage->set_config('container', $container_name);
        $this->assertTrue(is_string($url));
        
        return $container_name;
    }
    
    /**
     * @depends test_create_container
     */
    public function test_upload($container_name)
    {
        $created = $this->Cloud_Storage->set_config('container', $container_name);
        // Upload file
        $uploaded = $this->Cloud_Storage->upload_object($this->path_to_test_image, $this->name);
        $this->assertTrue($uploaded);
        
        // Verify file existence
        $url = $this->Cloud_Storage->get_container_url() . $this->name;
        $code = $this->file_code($url);
        $this->assertEquals($code, 200);
        
        return $container_name;
    }
    
    /**
     * @depends test_upload
     */
    public function test_delete($container_name)
    {
        $created = $this->Cloud_Storage->set_config('container', $container_name);
        
        // Delete file
        $deleted = $this->Cloud_Storage->delete_object($this->name);
        $this->assertTrue($deleted);
        
        // Verify file was deleted
        $url = $this->Cloud_Storage->get_container_url() . $this->name;
        $code = $this->file_code($url);
        $this->assertGreaterThan(400, $code);
        
        return $container_name;
    }
    
    /**
     * @depends test_delete
     */
    public function test_delete_container($container_name)
    {
        $this->Cloud_Storage->set_config('container', $container_name);
        
        $deleted = $this->Cloud_Storage->delete_container();
        $this->assertTrue($deleted);
    }
    
    /**
     * This method is identical to test_create_container it is here to test the following methods.
     * @return string
     */
    public function test_create_container_with_parameter()
    {
        $container_name = 'test_create_container_' . $this->time;
        $url = $this->Cloud_Storage->create_container($container_name);
        $this->Cloud_Storage->set_config('container', $container_name);
        $this->assertTrue(is_string($url));
        
        return $container_name;
    }
    
    /**
     * @depends test_create_container_with_parameter
     */
    public function test_upload_with_parameter($container_name)
    {
        // set a dummy default container
        $created = $this->Cloud_Storage->set_config('container', '');
        // Upload file
        $uploaded = $this->Cloud_Storage->upload_object($this->path_to_test_image, $this->name, $container_name);
        $this->assertTrue($uploaded);
        
        // Verify file existence
        $url = $this->Cloud_Storage->get_container_url($container_name) . $this->name;
        $code = $this->file_code($url);
        $this->assertEquals($code, 200);
        
        return $container_name;
    }
    
    /**
     * @depends test_upload_with_parameter
     */
    public function test_delete_with_parameter($container_name)
    {
        // set a dummy default container
        $this->Cloud_Storage->set_config('container', '');
        
        // Delete file
        $deleted = $this->Cloud_Storage->delete_object($this->name, $container_name);
        $this->assertTrue($deleted);
        
        // Verify file was deleted
        $url = $this->Cloud_Storage->get_container_url($container_name) . $this->name;
        $code = $this->file_code($url);
        $this->assertGreaterThan(400, $code);
        
        return $container_name;
    }
    
    /**
     * @depends test_delete_with_parameter
     */
    public function test_delete_container_with_parameter($container_name)
    {
        // set a dummy default container
        $this->Cloud_Storage->set_config('container', '');
        
        $deleted = $this->Cloud_Storage->delete_container($container_name);
        $this->assertTrue($deleted);
    }
    
    /**
     * @expectedException UploadObjectException
     */
    public function test_upload_with_bad_credentials()
    {
        $this->Cloud_Storage->set_config('access_key', '');
        $this->Cloud_Storage->set_config('access_secret', '');
        
        $uploaded = $this->Cloud_Storage->upload_object($this->path_to_test_image, $this->name);
    }
    
    
    /**
     * @expectedException InvalidFileException
     */
    public function test_upload_bad_object()
    {
        $uploaded = $this->Cloud_Storage->upload_object('nothing.txt', $this->name);
    }
    
    /**
     * @expectedException DeleteObjectException
     */
    public function test_delete_bad_object()
    {
        $deleted = $this->Cloud_Storage->delete_object('nothing.txt');
    }
    
    /**
     * @expectedException UploadObjectException
     */
    public function test_upload_to_bad_container()
    {
        $this->Cloud_Storage->set_config('container', '');
        
        $uploaded = $this->Cloud_Storage->upload_object($this->path_to_test_image, $this->name);
    }
    
    
    /**
     * @expectedException CreateContainerException
     */
    public function test_create_bad_container()
    {
        $created = $this->Cloud_Storage->create_container('');
    }
    
    /**
     * @expectedException DeleteContainerException
     */
    public function test_delete_nonempty_container()
    {
        $uploaded = $this->Cloud_Storage->upload_object($this->path_to_test_image, $this->name);
        $created = $this->Cloud_Storage->delete_container($this->container_name);
        
    }
    
    
    protected function file_code($url) 
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_NOBODY, true);
        curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        // $retcode > 400 -> not found, $retcode = 200, found.
        curl_close($ch);
        return $code;
    }
}