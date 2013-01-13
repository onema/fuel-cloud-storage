<?php
/**
 * 
 *
 * @package    
 * @version    
 * @author     
 * @license    
 * @copyright  
 * @link       
 */

namespace Cloud_Storage;

class CloudStorageException extends \FuelException {};

class InvalidDriverException extends CloudStorageException {};

class InvalidFileException extends CloudStorageException {};

class UploadObjectException extends CloudStorageException {};

class DeleteObjectException extends CloudStorageException {};

class CreateContainerException extends CloudStorageException {};

class DeleteContainerException extends CloudStorageException {};

class ListObjectsException extends CloudStorageException {};

class AuthenticationException extends CloudStorageException {};

class InvalidContainerException extends CloudStorageException {};

class CopyObjectException extends CloudStorageException {};

class Cloud_Storage
{

	/**
	 * Instance for singleton usage.
	 */
	public static $_instance = false;

	/**
	 * Driver config defaults.
	 */
	protected static $_defaults;

	/**
	 * Cloud_Storage driver forge.
	 *
	 * @param	string|array	$setup		setup key for array defined in cloud_storage.setups config or config array
	 * @param	array			$config		extra config array
	 * @return  Cloud_Storage_Driver        one of the cloud_storage drivers    
	 */
	public static function forge($setup = null, array $config = array())
	{
		empty($setup) and $setup = \Config::get('cloud_storage.default_setup', 'default');
		is_string($setup) and $setup = \Config::get('cloud_storage.setups.'.$setup, array());

		$setup = \Arr::merge(static::$_defaults, $setup);
		$config = \Arr::merge($setup, $config);

		$driver = '\\Cloud_Storage_Driver_'.ucfirst(strtolower($config['driver']));

		if( ! class_exists($driver, true))
		{
			throw new InvalidDriverException('Could not find Cloud Storage driver: '.$config['driver']. ' ('.$driver.')');
		}

		$driver = new $driver($config);

		return $driver;
	}

	/**
	 * Init, config loading.
	 */
	public static function _init()
	{
		\Config::load('cloud_storage', true);
		static::$_defaults = \Config::get('cloud_storage.defaults');
	}

	/**
	 * Call rerouting for static usage.
	 *
	 * @param	string	$method		method name called
	 * @param	array	$args		supplied arguments
	 */
	public static function __callStatic($method, $args = array())
	{
		if(static::$_instance === false)
		{
			$instance = static::forge();
			static::$_instance = &$instance;
		}

		if(is_callable(array(static::$_instance, $method)))
		{
			return call_user_func_array(array(static::$_instance, $method), $args);
		}

		throw new \BadMethodCallException('Invalid method: '.get_called_class().'::'.$method);
	}

}
