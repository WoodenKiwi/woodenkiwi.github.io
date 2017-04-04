<?php

/**
 * template-class
 * 
 * @date 2012-07-29
 * @version 1.0
 * 
 */

class Template{
	
	private $content = "";
	
	
	
	/**
	 * constructor - store the template-content in the conten-var
	 *
	 * @param string $file		the path to the template-file
	 */
	function __construct($file=""){
	
		// get file-content if file-path is not empty
		if(""!=$file){
			$fh = fopen($file, "r");
			$this->content = fread($fh, filesize($file));
			fclose($fh);
		}
	}
	
	
	
	
	/**
	 * replace an marker in the content
	 *
	 * @param string $marker	the name of the marker
	 * @param string $value		the value which should be placed in the marker
	 */
	function replace_marker($marker, $value){
		$this->content = str_replace("###".$marker."###", $value, $this->content);
	}	
	
	
	
	/**
	 * returns the (modified) content of the template
	 *
	 * @return string $this->content		the (modified) content of the template
	 */
	function get_content(){
		return $this->content;
	}
	
	
	
	/**
	 * removes an area from the template
	 *
	 * @param string $area		the name of the area
	 */

	function remove_area($area){	
		$this->content = preg_replace("/<!--###".$area."_START###-->(.*?)<!--###".$area."_END###-->/is", "", $this->content);
	}
 } 
 
 ?>