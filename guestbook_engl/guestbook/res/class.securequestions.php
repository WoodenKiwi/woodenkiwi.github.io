<?php

/**
 * class for security-questions
 * 
 * @date 2013-01-13
 * @version 1.0
 * 
 */

class Securequestions{
	
	private $securequestions = array();				// array with the securequestions
													//		format: $securequestions[id] = array(
													//					answer = "...",
													//					[question_language1] = "...",
													//					[question_language2] = "...",
													//					...
													//				)
	private $filename = "securequestions.txt";		// name of the data-file
	private $filepath = "";							// the path to the folder where the securequestion-file is (with ending /)
	public $error = array();						// array with the error-messages
	
	
	/**
	 * constructor - store the securequestions in an array
	 *
	 * @param string $filepath		the path to the folder where the securequestion-file is (with ending /)
	 */
	function __construct($filepath){
	
		$this->filepath = $filepath;
	
		// get the securequestions
		if(0<filesize($this->filepath.$this->filename)){
			$fh = @fopen($this->filepath.$this->filename, 'r');
			if(!$fh){
				$this->error[] = "error_cant_open_securequestions_file";
			}
			else{
				$content = fread($fh, filesize($this->filepath.$this->filename));
				fclose($fh);
				if(""!=$content){
					$this->securequestions = unserialize($content);
				}		
			}
		}
	}
	

	
	/**
	 * returns the array with all securequestions
	 *
	 * @return array			the array width all securequestions
	 */
	function get_securequestions(){
		return $this->securequestions;
	}
	
	
	
	/**
	 * returns the array for a specific securequestion
	 *
	 * @param string $id		the id of the securequestion
	 * @return array			the array of the securequestion
	 */
	function get_securequestion($id){
	
		// there is a securequestion with this id
		if(isset($this->securequestions[$id])){
			return $this->securequestions[$id];
		}
		
		// there is no securequestion with this id
		else{
			return array();
		}
	}
	
	
	
	/**
	 * save all securequestions
	 *
	 * @return bool				was the saving of the securequestions successful?
	 */
	function save_securequestions(){
	
		$this->error = array();
		$this->securequestions = array();
	
	
		// write all post-values in the array
		if(isset($_POST['securequestions'])){
			foreach($_POST['securequestions'] as $id=>$securequestion){
			
				// check if the fields are filled
				$filled = 0;
				foreach($securequestion as $key=>$value){
					if(""!=trim($value)){
						$filled++;
					}
				}
				
				if(!isset($securequestion['delete']) && 0<$filled){
					$this->securequestions[$id] = array();
					foreach($securequestion as $key=>$value){
						$this->securequestions[$id][$key] = htmlentities(stripslashes($value), ENT_COMPAT, 'UTF-8');
					}
				}
			}
		}
		$content = serialize($this->securequestions);

		// create temp-file
		if(!($fh = @fopen($this->filepath."temp_".$this->filename, "w+"))){
			$this->error[] = "error_cant_create_securequestions_temp_file";
			return false;
		}
		else{
			// save contents in temp file
			if(!fwrite($fh, $content)){
				fclose($fh);
				unlink($this->filepath."temp_".$this->filename);
				$this->error[] = "error_cant_save_temp_securequestions_file";
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