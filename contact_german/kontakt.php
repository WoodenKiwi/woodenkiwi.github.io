<?php
session_start();
error_reporting(E_ERROR | E_PARSE);
date_default_timezone_set('Europe/Berlin');

header('Content-type: text/html; charset=utf-8');


#########################################################################
#	Kontaktformular.com         					                                #
#	http://www.kontaktformular.com        						                    #
#	All rights by KnotheMedia.de                                    			#
#-----------------------------------------------------------------------#
#	I-Net: http://www.knothemedia.de                            					#
#########################################################################

// Der Copyrighthinweis darf NICHT entfernt werden!


$script_root = substr(__FILE__, 0,
                        strrpos(__FILE__,
                                DIRECTORY_SEPARATOR)
                       ).DIRECTORY_SEPARATOR;


require_once $script_root.'upload.php';


$remote = getenv("REMOTE_ADDR");


function encrypt($string, $key) {
$result = '';
for($i=0; $i<strlen($string); $i++) {
   $char = substr($string, $i, 1);
   $keychar = substr($key, ($i % strlen($key))-1, 1);
   $char = chr(ord($char)+ord($keychar));
   $result.=$char;
}

return base64_encode($result);
}

$sicherheits_eingabe = encrypt($_POST["sicherheitscode"], "8h384ls94");
$sicherheits_eingabe = str_replace("=", "", $sicherheits_eingabe);


@require('config.php');


if ($_POST['delete'])
{
unset($_POST);
}


if ($_POST["kf-km"]) {


   $name      = $_POST["name"];
   $email      = $_POST["email"];
   $telefon = $_POST["telefon"];
   $ort   = $_POST["ort"];
   $betreff   = $_POST["betreff"];
   $nachricht   = $_POST["nachricht"];
   $sicherheitscode   = $_POST["sicherheitscode"];
   $date = date("d.m.Y | H:i");
   $ip = $_SERVER['REMOTE_ADDR']; 
   $UserAgent = $_SERVER["HTTP_USER_AGENT"];
   $host = getHostByAddr($remote);


$name = stripslashes($name);
$email = stripslashes($email);
$betreff = stripslashes($betreff);
$nachricht = stripslashes($nachricht);


if (!$name) {
 $fehler['name'] = "<span class='errormsg'>Geben Sie bitte Ihren <strong>Namen</strong> ein.</span>";
 
}




if (!preg_match("/^[0-9a-zA-ZÄÜÖ_.-]+@[0-9a-z.-]+\.[a-z]{2,6}$/", $email)) {
   $fehler['email'] = "<span class='errormsg'>Geben Sie bitte Ihre <strong>E-Mail-Adresse</strong> ein.</span>";
}



 

if(!$betreff) {
 
 $fehler['betreff'] = "<span class='errormsg'>Geben Sie bitte einen <strong>Betreff</strong> ein.</span>";
 
 
}

 

if (!$nachricht) {
 $fehler['nachricht'] = "<span class='errormsg'>Geben Sie bitte eine <strong>Nachricht</strong> ein.</span>";

}



if ($sicherheits_eingabe != $_SESSION['captcha_spam']){

unset($_SESSION['captcha_spam']);

   $fehler['captcha'] = "<span class='errormsg'>Der <strong>Code</strong> wurde falsch eingegeben.</span>";
   }


    if (!isset($fehler) || count($fehler) == 0) {
      $error             = false;
      $errorMessage      = '';
      $uploadErrors      = array();
      $uploadedFiles     = array();
      $totalUploadSize   = 0;

      if ($cfg['UPLOAD_ACTIVE'] && in_array($_SERVER['REMOTE_ADDR'], $cfg['BLACKLIST_IP']) === true) {
          $error = true;
          $fehler['upload'] = '<font color=#990000>Sie haben keine Erlaubnis Dateien hochzuladen.<br /></font>';
      }


      if (!$error) {
          for ($i=0; $i < $cfg['NUM_ATTACHMENT_FIELDS']; $i++) {
              if ($_FILES['f']['error'][$i] == UPLOAD_ERR_NO_FILE) {
                  continue;
              }


              $extension = explode('.', $_FILES['f']['name'][$i]);
              $extension = strtolower($extension[count($extension)-1]);
              $totalUploadSize += $_FILES['f']['size'][$i];


              if ($_FILES['f']['error'][$i] != UPLOAD_ERR_OK) {
                  $uploadErrors[$j]['name'] = $_FILES['f']['name'][$i];
                  switch ($_FILES['f']['error'][$i]) {
                      case UPLOAD_ERR_INI_SIZE :
                          $uploadErrors[$j]['error'] = 'Die Datei ist zu groß (PHP-Ini Direktive).';
                      break;
                      case UPLOAD_ERR_FORM_SIZE :
                          $uploadErrors[$j]['error'] = 'Die Datei ist zu groß (MAX_FILE_SIZE in HTML-Formular).';
                      break;
                      case UPLOAD_ERR_PARTIAL :
						  if ($cfg['UPLOAD_ACTIVE']) {
                          	  $uploadErrors[$j]['error'] = 'Die Datei wurde nur teilweise hochgeladen.';
						  } else {
							  $uploadErrors[$j]['error'] = 'Die Datei wurde nur teilweise versendet.';
					  	  }
                      break;
                      case UPLOAD_ERR_NO_TMP_DIR :
                          $uploadErrors[$j]['error'] = 'Es wurde kein temporärer Ordner gefunden.';
                      break;
                      case UPLOAD_ERR_CANT_WRITE :
                          $uploadErrors[$j]['error'] = 'Fehler beim Speichern der Datei.';
                      break;
                      case UPLOAD_ERR_EXTENSION  :
                          $uploadErrors[$j]['error'] = 'Unbekannter Fehler durch eine Erweiterung.';
                      break;
                      default :

						  if ($cfg['UPLOAD_ACTIVE']) {
                          	  $uploadErrors[$j]['error'] = 'Unbekannter Fehler beim Hochladen.';
						  } else {
							  $uploadErrors[$j]['error'] = 'Unbekannter Fehler beim Versenden des Email-Attachments.';
						  }
                  }


                  $j++;
                  $error = true;
              }

              else if ($totalUploadSize > $cfg['MAX_ATTACHMENT_SIZE']*1024) {
                  $uploadErrors[$j]['name'] = $_FILES['f']['name'][$i];
                  $uploadErrors[$j]['error'] = 'Maximaler Upload erreicht ('.$cfg['MAX_ATTACHMENT_SIZE'].' KB).';
                  $j++;
                  $error = true;
              }

              else if ($_FILES['f']['size'][$i] > $cfg['MAX_FILE_SIZE']*1024) {
                  $uploadErrors[$j]['name'] = $_FILES['f']['name'][$i];
                  $uploadErrors[$j]['error'] = 'Die Datei ist zu groß (max. '.$cfg['MAX_FILE_SIZE'].' KB).';
                  $j++;
                  $error = true;
              }
              else if (!empty($cfg['BLACKLIST_EXT']) && strpos($cfg['BLACKLIST_EXT'], $extension) !== false) {
                  $uploadErrors[$j]['name'] = $_FILES['f']['name'][$i];
                  $uploadErrors[$j]['error'] = 'Die Dateiendung ist nicht erlaubt.';
                  $j++;
                  $error = true;
              }
              else if (preg_match("=^[\\:*?<>|/]+$=", $_FILES['f']['name'][$i])) {
                  $uploadErrors[$j]['name'] = $_FILES['f']['name'][$i];
                  $uploadErrors[$j]['error'] = 'Ungültige Zeichen im Dateinamen (\/:*?<>|).';
                  $j++;
                  $error = true;
              }

              else if ($cfg['UPLOAD_ACTIVE'] && file_exists($cfg['UPLOAD_FOLDER'].'/'.$_FILES['f']['name'][$i])) {
                  $uploadErrors[$j]['name'] = $_FILES['f']['name'][$i];
                  $uploadErrors[$j]['error'] = 'Die Datei existiert bereits.';
                  $j++;
                  $error = true;
              }

              else {
				  if ($cfg['UPLOAD_ACTIVE']) {
                     move_uploaded_file($_FILES['f']['tmp_name'][$i], $cfg['UPLOAD_FOLDER'].'/'.$_FILES['f']['name'][$i]);	
				  }
                  $uploadedFiles[$_FILES['f']['tmp_name'][$i]] = $_FILES['f']['name'][$i];
              }
          }
      }



      if ($error) {
          $errorMessage = 'Es sind folgende Fehler beim Versenden des Kontaktformulars aufgetreten:'."\n";
          if (count($uploadErrors) > 0) {
              foreach ($uploadErrors as $err) {
                  $tmp .= '<strong>'.$err['name']."</strong><br/>\n- ".$err['error']."<br/><br/>\n";
              }
              $tmp = "<br/><br/>\n".$tmp;
          }
          $errorMessage .= $tmp.'';
          $fehler['upload'] = $errorMessage;
      }
  }




   if (!isset($fehler))
   {
		// ------------------------------------------------------------
		// -------------------- send mail to admin --------------------
		// ------------------------------------------------------------
	   
		// ---- create mail-message for admin
	    $mailcontent  = "Folgendes wurde am ". $date ." Uhr per Formular geschickt:\n" . "-------------------------------------------------------------------------\n\n";
   $mailcontent .= "Name: " . $name . "\n";
   $mailcontent .= "E-Mail: " . $email . "\n\n";
   $mailcontent .= "Telefon: " . $telefon . "\n";
   $mailcontent .= "Ort: " . $ort . "\n";
   $mailcontent .= "\nBetreff: " . $betreff . "\n";
   $mailcontent .= "Nachricht:\n" . $_POST['nachricht'] = preg_replace("/\r\r|\r\n|\n\r|\n\n/","\n",$_POST['nachricht']) . "\n\n";
		if(count($uploadedFiles) > 0){
			if($cfg['UPLOAD_ACTIVE']){
				$mailcontent .= 'Es wurden folgende Dateien hochgeladen:'."\n";
				foreach ($uploadedFiles as $filename) {
					$mailcontent .= ' - '.$cfg['DOWNLOAD_URL'].'/'.$cfg['UPLOAD_FOLDER'].'/'.$filename."\n";
				}
			} else {
				$mailcontent .= 'Es wurden folgende Dateien als Attachment angehängt:'."\n";
				foreach ($uploadedFiles as $filename) {
					$mailcontent .= ' - '.$filename."\n";
				}
			}
		}
		$mailcontent .= "\n\nIP Adresse: " . $ip . "\n";
		$mailcontent = strip_tags ($mailcontent);
	   
		// ---- get attachments for admin
		$attachments = array();
		if(!$cfg['UPLOAD_ACTIVE'] && count($uploadedFiles) > 0){
			foreach($uploadedFiles as $tempFilename => $filename) {
				$attachments[$filename] = file_get_contents($tempFilename);	
			}
		}
		
		// ---- send mail to admin
$success = sendMyMail($email, $name, $empfaenger, $betreff, $mailcontent, $attachments);
		
		// ------------------------------------------------------------
		// ------------------- send mail to customer ------------------
		// ------------------------------------------------------------
		if($success){
			
			// ---- create mail-message for customer
			$mailcontent  = "Vielen Dank für deine E-Mail. Wir werden schnellstmöglich darauf antworten.\n\n";
			$mailcontent .= "Zusammenfassung: \n" .

  "-------------------------------------------------------------------------\n\n";

   $mailcontent .= "Name: " . $name . "\n";
   $mailcontent .= "E-Mail: " . $email . "\n\n";
   $mailcontent .= "Telefon: " . $telefon . "\n";
   $mailcontent .= "Ort: " . $ort . "\n";
   $mailcontent .= "\nBetreff: " . $betreff . "\n";
   $mailcontent .= "Nachricht:\n" . str_replace("\r", "", $nachricht) . "\n\n";
			if(count($uploadedFiles) > 0){
				$mailcontent .= 'Sie haben folgende Dateien übertragen:'."\n";
				foreach($uploadedFiles as $file){
					$mailcontent .= ' - '.$file."\n";
				}
			}
			$mailcontent = strip_tags ($mailcontent);
			
			// ---- send mail to customer
$success = sendMyMail($empfaenger, $ihrname, $email, "Ihre Anfrage", $mailcontent);
			echo "<META HTTP-EQUIV=\"refresh\" content=\"0;URL=".$danke."\">";
			exit;
		}
	}
}
// clean post
foreach($_POST as $key => $value){
    $_POST[$key] = htmlentities($value, ENT_QUOTES, "UTF-8");
}
?>
<?php




function sendMyMail($fromMail, $fromName, $toMail, $subject, $content, $attachments=array()){
	
	$boundary = md5(uniqid(time()));
	$eol = PHP_EOL;
	
	// header
	$header = "From: =?UTF-8?B?".base64_encode(stripslashes($fromName))."?= <".$fromMail.">".$eol;
	$header .= "Reply-To: <".$fromMail.">".$eol;
	$header .= "MIME-Version: 1.0".$eol;
	if(is_array($attachments) && 0<count($attachments)){
		$header .= "Content-Type: multipart/mixed; boundary=\"".$boundary."\"";
	}
	else{
		$header .= "Content-type: text/plain; charset=utf-8";
	}
	
	
	// content with attachments
	if(is_array($attachments) && 0<count($attachments)){
		
		// content
		$message = "--".$boundary.$eol;
		$message .= "Content-type: text/plain; charset=utf-8".$eol;
		$message .= "Content-Transfer-Encoding: 8bit".$eol.$eol;
		$message .= $content.$eol;

		// attachments
		foreach($attachments as $filename=>$filecontent){
			$filecontent = chunk_split(base64_encode($filecontent));
			$message .= "--".$boundary.$eol;
			$message .= "Content-Type: application/octet-stream; name=\"".$filename."\"".$eol;
			$message .= "Content-Transfer-Encoding: base64".$eol;
			$message .= "Content-Disposition: attachment; filename=\"".$filename."\"".$eol.$eol;			
			$message .= $filecontent.$eol;
		}
		$message .= "--".$boundary."--";
	}
	// content without attachments
	else{
		$message = $content;
	}
	
	// subject
	$subject = "=?UTF-8?B?".base64_encode($subject)."?=";
	
	// send mail
	return mail($toMail, $subject, $message, $header);
}

?>
<!DOCTYPE html>
<html lang="de-DE">
	<head>
		<meta charset="utf-8">
		<meta name="language" content="de"/>
		<meta name="description" content="kontaktformular.com"/>
		<meta name="revisit" content="After 7 days"/>
		<meta name="robots" content="INDEX,FOLLOW"/>
		<title>kontaktformular.com</title>
		<link href="style-kontaktformular.css" rel="stylesheet" type="text/css" />
		<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0" />
	</head>
	<body id="Kontaktformularseite">

		<div class="container banner">
		    <img class="banner-image" src="../images/Online/Banner_5-german-1.jpg" alt="Banner" width="2557" height="465">
		</div>

		<style>
		    /* This element will == the height of the image */
		    .banner {
		        position: relative;
		    }

		    /* This element is the background image */
		    .banner-image {
		        width: 100%;
		        height: auto;
		        position: static;
		    }
		</style>

		<form class="kontaktformular" action="<?php echo $_SERVER['PHP_SELF'];?>" method="post" enctype="multipart/form-data">
			<input type="hidden" name="action" value="smail" />
			<input type="hidden" name="content" value="formular"/>		
			<div class="row">	
				<label>Name:</label><span class="pflichtfeld">*</span>
				<div class="field">
						<?php if ($fehler["name"] != "") { echo $fehler["name"]; } ?><input type="text" name="name" maxlength="<?php echo $zeichenlaenge_name; ?>" id="textfield" value="<?php echo $_POST[name]; ?>"  <?php if ($fehler["name"] != "") { echo 'class="errordesignfields"'; } ?>/>				
				</div>
			</div>
			<div class="row">	
				<label>E-Mail:</label><span class="pflichtfeld">*</span>
				<div class="field">
						<?php if ($fehler["email"] != "") { echo $fehler["email"]; } ?><input type="text" name="email" maxlength="<?php echo $zeichenlaenge_email; ?>" value="<?php echo $_POST[email]; ?>"  <?php if ($fehler["email"] != "") { echo 'class="errordesignfields"'; } ?>/>
				
				</div>
			</div>
			<div class="row">	
				<label>Ort:</label><span class="pflichtfeld"></span>
				<div class="field">
					<input type="text" name="ort" maxlength="<?php echo $zeichenlaenge_ort; ?>" value="<?php echo $_POST[ort]; ?>"  />
				</div>
			</div>
			<div class="row">	
				<label>Telefon:</label><span class="pflichtfeld"></span>
				<div class="field">
					<input type="text" name="telefon" maxlength="<?php echo $zeichenlaenge_telefon; ?>" value="<?php echo $_POST[telefon]; ?>"  />
				</div>
			</div>
			<div class="row">
				<label>Betreff:</label><span class="pflichtfeld">*</span>
				<div class="field">
				<?php if ($fehler["betreff"] != "") { echo $fehler["betreff"]; } ?>	<input type="text" name="betreff" maxlength="<?php echo $zeichenlaenge_betreff; ?>" value="<?php echo $_POST[betreff]; ?>"  <?php if ($fehler["betreff"] != "") { echo 'class="errordesignfields"'; } ?>/>
					
				</div>
			</div>
			<div class="row nachrichtrow">	
				<label>Nachricht:</label><span class="pflichtfeld">*</span>
				<div class="field">
					<?php if ($fehler["nachricht"] != "") { echo $fehler["nachricht"]; } ?><textarea name="nachricht"  cols="30" rows="8" <?php if ($fehler["nachricht"] != "") { echo 'class="errordesignfields"'; } ?>><?php echo $_POST[nachricht]; ?></textarea>
					
				</div>
			</div>

			<?php
				for ($i=0; $i < $cfg['NUM_ATTACHMENT_FIELDS']; $i++) {
					echo '<div class="row"><label>Dateianhang</label><span class="pflichtfeld"></span><div class="field"><input type="file" size="12" name="f[]" /></div></div>';
				}
			?>
			<br/>
			<div class="row">
				<label class="nobg"></label><span class="pflichtfeld"></span>
				<div class="field Sicherheitscode">
					<img src="captcha/captcha.php" alt="Sicherheitscode" title="kontaktformular.com-sicherheitscode" id="captcha" />
					<a href="javascript:void(0);" onclick="javascript:document.getElementById('captcha').src='captcha/captcha.php?'+Math.random();cursor:pointer;">
						<span><img src="icon-kf.gif" alt="Sicherheitscode neu laden" title="Bild neu laden" /></span>
					</a>
				</div>
			</div>
			<div class="row">
				<label>Sicherheitscode:</label><span class="pflichtfeld">*</span>
				<div class="field">
						<?php if ($fehler["captcha"] != "") { echo $fehler["captcha"]; } ?><input type="text" name="sicherheitscode" maxlength="150" value=""  <?php if ($fehler["captcha"] != "") { echo 'class="errordesignfields"'; } ?>/>
				
				</div>
			</div>
			<br/>
			<div class="buttons">
				<div class="pflichtfeldhinweis">Hinweis: Felder mit <span class="pflichtfeld">*</span> m&uuml;ssen ausgef&uuml;llt werden.</div>
				<input type="submit" name="kf-km" value="Senden" onclick="tescht();"/>
				<input type="submit" name="delete" value="L&ouml;schen" />
			</div>

			<div class="container return" style="text-align: center; padding-bottom: 20px;">
					<p></p>
					<a style="font-size: 20px" href="../index_german.html">&raquo; Zurück zur Hauptseite</a>
					<p></p>
				</div>

			<div class="copyright"><!-- Hinweis darf nicht entfernt werden! -->&copy; by <a href="http://www.kontaktformular.com" title="kontaktformular.com">kontaktformular.com</a> - Alle Rechte vorbehalten.</div>
		</form>
	</body>
</html>