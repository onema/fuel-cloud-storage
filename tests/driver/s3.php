<?php
/**
 * @group storage
 * @group storage_s3
 */
class test_driver_s3 extends test_driver_base
{
    public function setUp()
    {
        $this->driver = 's3';
        parent::setUp();
    }
}