<?php

/**
 * config-class
 * 
 * @date 2012-07-29
 * @version 1.0
 * 
 */

class Config{
	
	private $configurations = array();		// array with the configuration-data
	private $filename = "config.php";		// name of the data-file
	private $filepath = "";					// he path to the folder where the config-file is (with ending /)
	public $error = array();				// array with the error-messages
	
	
	/**
	 * constructor - store the configs in an array
	 *
	 * @param string $filepath		the path to the folder where the config-file is (with ending /)
	 */
	function __construct($filepath){
	
		$this->filepath = $filepath;
	
		// get the configs
		if(!($configs = @file($filepath.$this->filename))){
			$this->error[] = "error_cant_open_config_file";
		}
		else{
			// store the configs in the array
			foreach($configs as $config){
			
				preg_match('#define\("(.*?)",#', trim($config), $key);
				preg_match('#, "(.*?)"\)#', trim($config), $value);
				
				if(0<count($value)){
					$this->configurations[$key[1]] = $value[1];
				}
			}
		}
	}
	
	
	/**
	 * set the new value for a config in the array
	 *
	 * @param string $key		the key of the config
	 * @param string $value		the value of the config
	 */
	function set_config($key, $value){
		$this->configurations[$key] = $value;
	}		

	
	/**
	 * returns the value for the configuration
	 *
	 * @param string $key		the key of the config
	 * @return string			the value of the configuration
	 */
	function get_config($key){
		return $this->configurations[$key];
	}
	
	
	
	/**
	 * saves the configuration-settings
	 *
	 * @return bool				was the saving of the configurations successful?
	 */
	function save_config(){
	
		$this->error = array();
	
		// get content of the config-file
		if(!($fh = @fopen($this->filepath.$this->filename, 'r'))){
			$this->error[] = "error_cant_open_config_file";
			return false;
		}
		$content = fread($fh, filesize($this->filepath.$this->filename));
		fclose($fh);
		
		
		// set the new configs in the content
		foreach($this->configurations as $key=>$value){
			$content = preg_replace('#define\("'.$key.'", "(.*?)"\)#', 'define("'.$key.'", "'.$value.'")', $content);
		}

		
		// create temp-file
		if(!($fh = @fopen($this->filepath."temp_".$this->filename, "w+"))){
			$this->error[] = "error_cant_create_config_temp_file";
			return false;
		}
		else{
			// save contents in temp file
			if(!fwrite($fh, $content)){
				fclose($fh);
				unlink($this->filepath."temp_".$this->filename);
				$this->error[] = "error_cant_save_temp_config_file";
				return false;
			}
			else{
				// delete original file and rename temp-file
				fclose($fh);
				unlink($this->filepath.$this->filename);
				rename($this->filepath."temp_".$this->filename, $this->filepath.$this->filename);
				return true;
			}
		}
	}
 } 
 
 ?>