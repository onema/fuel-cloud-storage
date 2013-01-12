<?php
/**
 * @group storage
 */
class test_driver_cf extends test_driver_base
{
    public function setUp()
    {
        parent::setUp();
        
        // This will use the default config values
        $this->Cloud_Storage = \Cloud_Storage\Cloud_Storage::forge('cf');
//        $this->Cloud_Storage->create_container($this->container_name);
//        $this->Cloud_Storage->set_config('container', $this->container_name);
        
        $this->driver = 'cf';
    }
}