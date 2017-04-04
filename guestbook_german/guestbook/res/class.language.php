<?php

/**
 * language-class
 * 
 * @date 2012-07-29
 * @version 1.0
 * 
 */

class Language{
	
	private $language = "";					// selected language
	private $filepath = "";					// path to the folder where the language-files are (with ending /)
	public $error = array();				// array with errors
	
	
	/**
	 * constructor - store the filepath
	 *
	 * @param string $filepath		the path to the folder where the language-file is (with ending /)
	 */
	function __construct($filepath, $language){
		$this->filepath = $filepath;
		$this->language = $language;
	}

	
	/**
	 * save the texts from the language-file in the session
	 *
	 * @return bool 				was the saving of the texts successful?
	 */
	function get_texts(){
		
		$this->error = array();
		
		// open the language-file
		if(!($fh = @fopen($this->filepath.$this->language.'.xml', 'r'))){
			$this->error[] = "Can't open the language-file '".$this->filepath.$this->language.".xml'<br/>Die Datei '".$this->filepath.$this->language.".xml' konnte nicht geöffnet werden";
			return false;
		}
		else{
			// get the file-content
			$content = fread($fh, filesize($this->filepath.$this->language.'.xml'));
			fclose($fh);
					
			// store the texts in the array
			preg_match_all('#\<text name="(.*?)"\>#', $content, $keys);
			preg_match_all('#"\>(.*?)\<\/text\>#', $content, $values);	
			
			foreach($keys[1] as $number=>$key){
				$_SESSION['gbook']['texts'][$key] = $values[1][$number];
			}

			return true;
		}
	}
	
	
	/**
	 * get the available languages
	 *
	 * @return array|bool 				array with the available languages | false if there was an error
	 */
	function get_languages(){
		
		$this->error = array();
		$languages = array();
		
		// get the files of the language-directory
		if(!($files = scandir($this->filepath))){
			$this->error[] = "error_cant_access_language_dir";
			return false;
		}
		
		else{
			// store languages in array
			foreach($files as $file){
				if('.'!=$file && '..'!=$file && '.htaccess'!=$file){
					$languages[] = str_replace('.xml', '', $file);
				}
			}
			return $languages;
		}
	}
 } 
 
 ?>