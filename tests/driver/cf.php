<?php
/**
 * @group storage
 */
class test_driver_cf extends test_driver_base
{
    public function setUp()
    {
        $this->driver = 'cf';
        parent::setUp();
    }
}