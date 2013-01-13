<?php
/**
 *
 * @package		Cloud_Storage
 * @version		1.0
 * @author 		
 * @license		
 * @copyright	
 * @link 		
 */

namespace Cloud_Storage;

abstract class Cloud_Storage_Driver
{
    const TTL = 3600; 
    
    
	/**
	 * Driver config
	 */
	protected $config = array();
    
    
    /**
     * Driver constructor
     *
     * @param	array	$config		driver config
     */
    public function __construct(array $config)
    {
        $this->config = $config;
    }

    /**
     * Get a driver config setting.
     *
     * @param	string		$key		the config key
     * @return	mixed					the config setting value
     */
    public function get_config($key, $default = null)
    {
        return \Arr::get($this->config, $key, $default);
    }

    /**
     * Set a driver config setting.
     *
     * @param	string		$key		the config key
     * @param	mixed		$value		the new config value
     * @return	object					$this
     */
    public function set_config($key, $value)
    {
        \Arr::set($this->config, $key, $value);

        return $this;
    }
    
    
    /**
     * Returns the content of pathinfo and updates the basename if a new file name
     * has been given.
     * @param type $path_to_object
     * @param type $new_file_name
     * @return array
     * @throws InvalidFileException if the file doesn't exist.
     */
    public function get_file_info($path_to_object, $new_file_name = null)
    {
        if(!file_exists($path_to_object))
        {
            throw new InvalidFileException('Invalid path to object');
        }
        
        $file_info = pathinfo($path_to_object);
        
        if(isset($new_file_name))
        {
            $new_file_name = ltrim($new_file_name, '/');
            $file_info['basename'] = $new_file_name;
        }
        
        return $file_info;
    }

    
    abstract public function delete_object($path_to_object);
    abstract public function upload_object($path_to_object, $new_file_name = null);
    abstract public function create_container($name);
    abstract public function delete_container($name);
    abstract public function list_objects($path = '', $bucket_name = null);
    abstract public function get_container_url($name = null);
    
    abstract public function copy_to($from_container_name, $to_container_name, $file_name, $new_file_name = null);

    /**
     * @todo implement the following methods.
     */
    
    //abstract public function rename_file($old_file_name, $new_file_name);
    //abstract public function rename_container($old_container_name, $new_container_name);
    //abstract public function list_containers();
    
    

}
