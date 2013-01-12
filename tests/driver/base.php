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
    
    public function setUp()
    {
        $this->time = time(); // use a unique string.
        $this->name = 'img/image.png';
        $this->container_name = 'test_container_' . $this->time;
        $this->path_to_test_image = dirname(__FILE__).'/image.png';
    }
    
    public function tearDown()
    {
//        $this->Cloud_Storage->set_config('container', $this->container_name);
//        $this->Cloud_Storage->delete_container($this->container_name);
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
        $url = $this->Cloud_Storage->get_container_url($container_name) . $this->name;
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
        $url = $this->Cloud_Storage->get_container_url($container_name) . $this->name;
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
        
        $deleted = $this->Cloud_Storage->delete_container($container_name);
        $this->assertTrue($deleted);
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