<?php

/**
 * entries-class
 * 
 * @date 2012-07-29
 * @version 1.0
 * 
 */

class Entries{
	
	private $entries = array();		// array with all entries
	private $filename = "data.txt";	// name of the data-file
	private $filepath = "";			// path to the folder where the data-file is (with ending /)
	private $delemitter = "#|#";	// delemitter for the fields
	public $entries_qty = 0;		// entry-quantity
	public $entries_active = 0;		// entry-quantity of the active entries
	public $error = array();		// error-message
	
	
	
	/**
	 * get all entries from data-file and returns them in an array
	 *
	 * @param string $filepath					the path to the folder where the data-file is (with ending /)
	 * @param bool $store_entries_in_array		should we save all entries in an array?
	 */
	function __construct($filepath, $store_entries_in_array=0){
	
		// save the path to the file
		$this->filepath = $filepath;
	
		// save the entries in an array
		if(""!=$filepath && $store_entries_in_array){
			
			// get the datasets
			if(!($datasets = @file($filepath.$this->filename))){
				$this->error[] = "cant_open_file";
			}
			
			// there are datasets
			elseif(0<count($datasets)){
			
				$this->entries_qty = 0;
				$this->entries_active = 0;
			
				// store the datasets in the entries-array
				foreach($datasets as $dataset){
				
					$fields = explode($this->delemitter, trim($dataset));
					$this->entries_qty++;
					if(1==$fields[7]){
						$this->entries_active++;
					}
					
					$this->entries[$this->entries_qty] = array();
					$this->entries[$this->entries_qty]['id'] = $fields[0];
					$this->entries[$this->entries_qty]['name'] = $fields[1];
					$this->entries[$this->entries_qty]['city'] = $fields[2];
					$this->entries[$this->entries_qty]['email'] = $fields[3];
					$this->entries[$this->entries_qty]['web'] = ((""!=trim($fields[4]) && false===strpos($fields[4], "http://") && false===strpos($fields[4], "https://")) ? "http://".$fields[4] : $fields[4]);
					$this->entries[$this->entries_qty]['message'] = $fields[5];
					$this->entries[$this->entries_qty]['comment'] = $fields[6];
					$this->entries[$this->entries_qty]['active'] = $fields[7];
					$this->entries[$this->entries_qty]['entry_date'] = $fields[8];
					$this->entries[$this->entries_qty]['comment_date'] = $fields[9];
				}
			}
		}
	}	
	
	
	/**
	 * get all entries from data-file and returns them in an array
	 *
	 * @param int $start					the start-number of the entry
	 * @param int $quantity					the quantity of the searched entries
	 * @param bool $only_active				should only the active entries be displayed
	 * @return array $return_entries		array with the searched entries
	 */
	function get_entries($start=1, $quantity=0, $only_active=0){
		
		$return_entries = array();
		
		// set the correct quantity
		if(0==$quantity){
			$quantity = $this->entries_qty;
		}
		
		// get correct entries if there are some
		if(0<count($this->entries)){
			
			// get the start-number
			if(0==$only_active){
				$number = $this->entries_qty;
			}
			else{
				$number = $this->entries_active;
			}
			
			// get the searched entries
			for($i=$this->entries_qty, $valid_entry=0; $i>=1 && $quantity>0 && isset($this->entries[$i]); $i--){
				
				// the entry is active or we don't care about active
				if(0==$only_active || 1==$this->entries[$i]['active']){
					$valid_entry++;
					
					if($valid_entry>=$start){
						$return_entries[$i] = $this->entries[$i];
						$return_entries[$i]['number'] = $number-$valid_entry+1;
						$quantity--;
					}
				}
			}
		}
		
		return $return_entries;
	}
	
	
	/**
	 * return the entry with the posted id
	 *
	 * @param int $id						the id of the entry
	 * @return array $return_entry			array with the searched entry
	 */
	function get_entry($id=0){
		
		$return_entry = array();
		
		// go through the entry-array to get the correct entry
		foreach($this->entries as $entry){
			
			// we have the correct entry
			if($entry['id'] ==$id){
				$return_entry = $entry;
				break;
			}
		}
		
		return $return_entry;
	}
	
	
	/**
	 * save changes into the entry-file
	 *
	 * @param array $update_entry		array with the changed entry-data
	 * @return bool 					was the update successful?
	 */
	function update_entries($update_entry=""){
	
		$content = "";
	
		// get the searched entries
		foreach($this->entries as $entry){
		
			// entry is the searched entry -> update it
			if(""!=$update_entry && $entry['id'] == $update_entry['id']){
				
				// clear entry
				foreach($update_entry as $key=>$value){
					if("message"==$key || "comment"==$key){
						$update_entry[$key] = str_replace(array("\r\n", "\n", "\r", $this->delemitter), array("", "", "", " "), nl2br(htmlentities(stripslashes($value), ENT_COMPAT, 'UTF-8')));
					}
					else{
						$update_entry[$key] = str_replace(array("\r\n", "\n", "\r", $this->delemitter), array("", "", "", " "), htmlentities(stripslashes($value), ENT_COMPAT, 'UTF-8'));
					}
				}
			
				// update entry
				$content .= 
					$update_entry['id'].$this->delemitter.
					$update_entry['name'].$this->delemitter.
					$update_entry['city'].$this->delemitter.
					$update_entry['email'].$this->delemitter.
					$update_entry['web'].$this->delemitter.
					nl2br($update_entry['message']).$this->delemitter.
					nl2br($update_entry['comment']).$this->delemitter.
					$update_entry['active'].$this->delemitter.
					$update_entry['entry_date'].$this->delemitter.
					(($update_entry['comment']!="" && $entry['comment_date']=="") ? time() : $update_entry['comment_date'])."\n";
			}
			
			// entry is not the searched entry -> save entry as it is
			else{				
				// clear entry
				foreach($entry as $key=>$value){
					if("message"==$key || "comment"==$key){
						$entry[$key] = str_replace(array("\r\n", "\n", "\r", $this->delemitter), array("", "", "", " "), nl2br(stripslashes($value)));
					}
					else{
						$entry[$key] = str_replace(array("\r\n", "\n", "\r", $this->delemitter), array("", "", "", " "), stripslashes($value));
					}
				}
			
				// update entry
				$content .= 
					$entry['id'].$this->delemitter.
					$entry['name'].$this->delemitter.
					$entry['city'].$this->delemitter.
					$entry['email'].$this->delemitter.
					$entry['web'].$this->delemitter.
					$entry['message'].$this->delemitter.
					$entry['comment'].$this->delemitter.
					$entry['active'].$this->delemitter.
					$entry['entry_date'].$this->delemitter.
					$entry['comment_date']."\n";
			}
		}
		
		if(!$this->save_entries($content)){
			return false;
		}
		else{
			return true;
		}
	}	
	

	/**
	 * checks if the entry-values are valid and save the data in the session
	 *
	 * @param array $new_entry			array with the new entry-data
	 * @return bool 					is the new entry valid?
	 */
	function check_new_entry($new_entry){
		
		$this->error = array();

		
		// store data in session data
		$_SESSION['gbook']['new_entry']['comment'] = "";
		$_SESSION['gbook']['new_entry']['active'] = (ENABLE_ENTRIES ? 0 : 1);
		$_SESSION['gbook']['new_entry']['comment_date'] = "";
		foreach($new_entry as $key=>$value){
			$_SESSION['gbook']['new_entry'][$key] = stripslashes($value);
		}

		
		// prevent double-send
		if(!isset($_SESSION['gbook']['new_entry']['sessid']) || !isset($new_entry['sessid']) || $new_entry['sessid'] != session_id()){
			$this->error[] = "###error_session_is_over###";
			return false;
		}
		
		
		// clear all values from php- and html-values
		foreach($new_entry as $key=>$value){
			$new_entry[$key] = trim(stripslashes(strip_tags($value)));
		}

		
		// check name
		if(!isset($new_entry['name']) || ""==$new_entry['name']){
			$this->error[] = "###error_no_name_given###";
		}
		elseif(MAX_NAME_CHARACTERS<strlen($new_entry['name'])){
			$this->error[] = "###error_name_is_too_long### ".MAX_NAME_CHARACTERS;
		}		
		
		
		// check city
		if(isset($new_entry['city']) && ""!=$new_entry['city'] && MAX_CITY_CHARACTERS<strlen($new_entry['city'])){
			$this->error[] = "###error_city_is_too_long### ".MAX_CITY_CHARACTERS;
		}
		
		
		//check email
		if(isset($new_entry['email']) && ""!=$new_entry['email'] && !preg_match("/^[_a-zA-Z0-9-]+(\.[_a-zA-Z0-9-]+)*@[a-zA-Z0-9-]+(\.[a-zA-Z0-9-]+)*(\.[a-zA-Z0-9]+)$/", $new_entry['email'])){
			$this->error[] = "###error_no_valid_email###";
		}
		elseif(isset($new_entry['email']) && ""!=$new_entry['email'] && MAX_EMAIL_CHARACTERS<strlen($new_entry['email'])){
			$this->error[] = "###error_email_is_too_long### ".MAX_EMAIL_CHARACTERS;
		}	
		
		
		
		
		
		// check message
		if(!isset($new_entry['message']) || ""==$new_entry['message']){
			$this->error[] = "###error_no_message_given###";
		}
		elseif(MAX_MESSAGE_CHARACTERS<strlen($new_entry['message'])){
			$this->error[] = "###error_message_is_too_long### ".MAX_MESSAGE_CHARACTERS;
		}
		
		
		// check securequestion
		if(ENABLE_SECUREQUESTION){
			$securequestion_obj = new Securequestions(PATH_TO_DATA);
			$securequestion = $securequestion_obj->get_securequestion($_SESSION['gbook']['new_entry']['securequestion_id']);
			if($securequestion['answer']!=htmlentities(stripslashes(trim($_SESSION['gbook']['new_entry']['securequestion_answer'])), ENT_COMPAT, 'UTF-8') || ""==trim($_SESSION['gbook']['new_entry']['securequestion_answer'])){
				$this->error[] = "###error_wrong_securequestion_answer###";
			}
		}
			
	
		// errors -> return false
		if(0<count($this->error)){
			return false;
		}
	
	
		// prevent spam via hidden field
		if(!isset($new_entry['first_name']) || ""!=$new_entry['first_name']){
			$this->error[] = "###error_bot_warning_hidden_field###";
		}
	
	
		// prevent spam via timecheck
		if(
			!isset($new_entry['time']) || 
			!is_numeric($new_entry['time']) || 
			""==$new_entry['time'] || 
			(60*60) <= (time()-$new_entry['time']) || 
			2 >= (time()-$new_entry['time'])
		){
			$this->error[] = "###error_bot_warning###";
			return false;
		}
			
		return true;
	}
	
	
	/**
	 * checks if the entry-values are valid and save the new entry
	 *
	 * @param array $new_entry			array with the new entry-data
	 * @return bool 					was the insert of the new entry successful?
	 */
	function new_entry($new_entry){
		
		$this->error = array();

		
		// prevent double-send
		if(!isset($_SESSION['gbook']['new_entry']['sessid']) || $_SESSION['gbook']['new_entry']['sessid'] != session_id()){
			$this->error[] = "###error_session_is_over###";
			return false;
		}
		
		
		// set rest data
		$_SESSION['gbook']['new_entry']['id'] = AUTO_INCREMENT;
		$_SESSION['gbook']['new_entry']['entry_date'] = time();
		
		
		// set data for the securequestion
		$_SESSION['gbook']['new_entry']['securequestion_id'];
		$_SESSION['gbook']['new_entry']['securequestion_answer'];
		
		
		// save entry
		$this->entries[($this->entries_qty+1)] = $_SESSION['gbook']['new_entry'];
		if($this->update_entries($_SESSION['gbook']['new_entry'])){
		
			// send admin-mail if wanted
			if(NEW_ENTRY_MAIL && ""!=ADMIN_MAIL){
			
				// create email-header
				$from_user = $_SESSION['gbook']['texts']['guestbook_email']."@".str_replace("www.", "", $_SERVER['HTTP_HOST']);
				$subject = "=?UTF-8?B?".base64_encode($_SESSION['gbook']['texts']['new_entry'])."?=";
				
				$headers   = array();
				$headers[] = "MIME-Version: 1.0";
				$headers[] = "Content-type: text/plain; charset=utf-8";
				$headers[] = "From: ".$from_user." <".$from_user.">";
				$headers[] = "Subject: ".$subject;
				$headers[] = "X-Mailer: PHP/".phpversion();		

				$message = $_SESSION['gbook']['new_entry']['name'].": \r\n\r\n".$_SESSION['gbook']['new_entry']['message'];
				
				if(!@mail(ADMIN_MAIL, $subject, $message, implode("\r\n", $headers))){
					$this->error[] = "###error_couldnt_send_adminmail###";
				}
			}
			
			// clean session from the new entry
			$_SESSION['gbook']['new_entry'] = array();
			$_SESSION['gbook']['new_entry']['sid'] = session_regenerate_id();
			
			// increment autoincrement-config
			$config = new Config($this->filepath);
			$config->set_config("AUTO_INCREMENT", (AUTO_INCREMENT+1));
			$config->save_config();
			return true;
		}
		else{
			return false;
		}
	}
	
	
	
	/**
	 * deletes the entry with the posted id
	 *
	 * @param int $id					the id of the entry
	 * @return bool 					was the delete of the new entry successful?
	 */
	function delete_entry($id){
		
		$this->error = array();
		$deleted = false;
		
		// go through the entry-array to get the correct entry
		foreach($this->entries as $key=>$entry){
			
			// we have the correct entry
			if($entry['id'] ==$id){
				unset($this->entries[$key]);
				$deleted = $this->update_entries();
				break;
			}
		}
		
		// no errors and no deleted -> didn't found entry to delete
		if(false==$deleted && 0==count($this->error)){
			$this->error[] = '###error_no_entry_to_delete###';
		}
		
		return $deleted;
	}
	
	
	
	/**
	 * save the entries in the data-file
	 *
	 * @param string $content			the content for the file
	 * @return bool 					was the saving successful?
	 */
	function save_entries($content){
		
		$this->error = array();

		// if content is empty (last dataset is deleted): clear the file
		if(""==$content){
			if(!($fh = @fopen($this->filepath.$this->filename, "w+"))){
				$this->error[] = "###error_cant_save_file###";
				return false;
			}
			else{
				fclose($fh);
				return true;
			}
		}
		
		// if content is not empty: handle saving with temp file to prevent data loss
		else{
		
			// create temp-file
			if(!($fh = @fopen($this->filepath."temp_".$this->filename, "w+"))){
				$this->error[] = "###error_cant_create_temp_file###";
				return false;
			}
			else{	
				// save contents in temp file
				if(!fwrite($fh, $content)){
					fclose($fh);
					unlink($this->filepath."temp_".$this->filename);
					$this->error[] = "###error_cant_save_temp_file###";
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
 } 
 
 ?>