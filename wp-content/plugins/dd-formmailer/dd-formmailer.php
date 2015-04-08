<?php

/*
Plugin Name: Dagon Design Form Mailer
Plugin URI: http://www.dagondesign.com/articles/secure-form-mailer-plugin-for-wordpress/
Description: The WordPress plugin version of my secure php form mailer script
Author: Dagon Design
Version: 5.8
Author URI: http://www.dagondesign.com
*/

// error_reporting(E_ALL);

$ddfm_version = '5.8';

if (!defined('PHP_EOL')) define ('PHP_EOL', strtoupper(substr(PHP_OS,0,3) == 'WIN') ? "\r\n" : "\n");

/* prevent data from being resubmitted by refresh */
if (count($_POST) > 0) {
	$lastpost = isset($_COOKIE['lastpost']) ? $_COOKIE['lastpost'] : '';
	if ($lastpost != md5(serialize($_POST))) {
		setcookie('lastpost', md5(serialize($_POST)));
		$_POST['_REPEATED'] = 0;
	} else {
		$_POST['_REPEATED'] = 1;
	}
}

/* Convert hex color code to R, G, B */
function ddfm_hex_to_rgb($h) {
	$h = trim($h, "#");
	$color = array();	
	if (strlen($h) == 6) {
		$color[] = (int)hexdec(substr($h, 0, 2));
		$color[] = (int)hexdec(substr($h, 2, 2));
		$color[] = (int)hexdec(substr($h, 4, 2));
	} else if (strlen($h) == 3) {
		$color[] = (int)hexdec(substr($h, 0, 1) . substr($h, 0, 1));
		$color[] = (int)hexdec(substr($h, 1, 1) . substr($h, 1, 1));
		$color[] = (int)hexdec(substr($h, 2, 1) . substr($h, 2, 1));
	}
	return $color;
}



function ddfm_get_bool_from_post($id) {
	if (isset($_POST[$id])) {
		return (bool) $_POST[$id];
	} else {
		return FALSE;
	}								
}


/* Handle requests for verification code */
if (isset($_GET['v'])) {
	if ($_GET['v'] == '1') {

		// find wp-blog-header.php to access WP stuff
		$testpath = (string)$_SERVER['PHP_SELF'];
		$s = strpos($testpath, '/wp-content/');
		$e = strpos($testpath, '/dd-formmailer.php');
		$testpath = substr($testpath, $s+1, $e-$s);
		$slashc = substr_count($testpath, "/");
		$wpcp = 'wp-blog-header.php';			
		while ($slashc > 0) {
			$wpcp = '../' . $wpcp;
			$slashc--;
		}		
		include $wpcp;
	
		$this_domain = preg_replace("/^www\./", "", $_SERVER['HTTP_HOST']);

		$verify_background = get_option('ddfm_verify_background');
		$verify_text = get_option('ddfm_verify_text');

		// Choose image type
		$type = '';

		if (function_exists("imagegif")) {
			$type = 'gif';
		} else if (function_exists("imagejpeg")) {
			$type = 'jpeg';
		} else if (function_exists("imagepng")) {
			$type = 'png';
		}

		$force_type = get_option('ddfm_force_type');
		if (trim($force_type) != '') {
			$type = $force_type;
		}


		// Generate verification code
		srand((double)microtime()*1000000); 
		$ddfmcode = substr(strtoupper(md5(rand(0, 999999999))), 2, 5); 
		$ddfmcode = str_replace("O", "A", $ddfmcode); // for clarity
		$ddfmcode = str_replace("0", "B", $ddfmcode);
		setcookie("ddfmcode", md5($ddfmcode), time()+3600, '/', '.' . $this_domain); 

		// Generate image
		header("Content-type: image/" . $type);
		header("Cache-Control: no-store, no-cache, must-revalidate"); 
		header("Cache-Control: post-check=0, pre-check=0", false); 
		header("Pragma: no-cache"); 
		header("Expires: Mon, 1 Jan 2000 01:00:00 GMT"); // Date in the past
		$image = imagecreate(60, 24);

		list($br, $bg, $bb) = ddfm_hex_to_rgb($verify_background);
		list($rr, $rg, $rb) = ddfm_hex_to_rgb($verify_text);

		$background_color = imagecolorallocate ($image, $br, $bg, $bb);
		$text_color = imagecolorallocate($image, $rr, $rg, $rb);

		imagestring($image, 5, 8, 4, $ddfmcode, $text_color);

		switch ($type) {
			case 'gif': imagegif($image); break;
			case 'png': imagepng($image); break;
			case 'jpeg': imagejpeg($image, NULL, 100); break;
		}		
		imagedestroy($image);

		exit();
	}
}

/* Check for GD support */
function ddfm_check_gd_support() {
	if (extension_loaded("gd") && (function_exists("imagegif") || function_exists("imagepng") || function_exists("imagejpeg"))) {
		return TRUE;
	} else {
		return FALSE;
	}
}

/* Safe str_replace */
function ddfm_str_replace($search, $replace, $subject) {
	if (isset($search)) {
		return str_replace($search, $replace, $subject);
	} else {
		return $subject;
	}
}

/* Check for valid URL */
function ddfm_is_valid_url($link) { 
	if (strpos($link, "http://") === FALSE) {
		$link = "http://" . $link;
	}
	$url_parts = @parse_url($link);
	if (empty($url_parts["host"])) 
		return( false );
	if (!empty($url_parts["path"])) {
		$documentpath = $url_parts["path"];
	} else {
		$documentpath = "/";
	}
	if (!empty($url_parts["query"])) {
		$documentpath .= "?" . $url_parts["query"];
	}
	$host = $url_parts["host"];
	$port = $url_parts["port"];
	if (empty($port)) 
		$port = "80";
	$socket = @fsockopen( $host, $port, $errno, $errstr, 30 );
	if (!$socket) {
		return(false);
	} else  {
		fwrite ($socket, "HEAD ".$documentpath." HTTP/1.0\r\nHost: $host\r\nUser-Agent: DDFMVerify\r\n\r\n");
		$http_response = fgets( $socket, 22 );
		if (ereg("200 OK", $http_response, $regs)) {
			return(true);
			fclose($socket);
		} else {
			return(false);
		}
	}
}

/* Check for valid email address */
function dd_is_valid_email($email) {

	$validator = new EmailAddressValidator;
	if ($validator->check_email_address($email)) {
		return TRUE;
	} else {
		return FALSE;
	}
} 

    /*
        EmailAddressValidator Class
        http://code.google.com/p/php-email-address-validation/

        Released under New BSD license
        http://www.opensource.org/licenses/bsd-license.php
    */

    class EmailAddressValidator {

        /**
         * Check email address validity
         * @param   strEmailAddress     Email address to be checked
         * @return  True if email is valid, false if not
         */
         function check_email_address($strEmailAddress) {
            
            // If magic quotes is "on", email addresses with quote marks will
            // fail validation because of added escape characters. Uncommenting
            // the next three lines will allow for this issue.
            //if (get_magic_quotes_gpc()) { 
            //    $strEmailAddress = stripslashes($strEmailAddress); 
            //}

            // Control characters are not allowed
            if (preg_match('/[\x00-\x1F\x7F-\xFF]/', $strEmailAddress)) {
                return false;
            }

            // Split it into sections using last instance of "@"
            $intAtSymbol = strrpos($strEmailAddress, '@');
            if ($intAtSymbol === false) {
                // No "@" symbol in email.
                return false;
            }
            $arrEmailAddress[0] = substr($strEmailAddress, 0, $intAtSymbol);
            $arrEmailAddress[1] = substr($strEmailAddress, $intAtSymbol + 1);

            // Count the "@" symbols. Only one is allowed, except where 
            // contained in quote marks in the local part. Quickest way to
            // check this is to remove anything in quotes.
            $arrTempAddress[0] = preg_replace('/"[^"]+"/'
                                             ,''
                                             ,$arrEmailAddress[0]);
            $arrTempAddress[1] = $arrEmailAddress[1];
            $strTempAddress = $arrTempAddress[0] . $arrTempAddress[1];
            // Then check - should be no "@" symbols.
            if (strrpos($strTempAddress, '@') !== false) {
                // "@" symbol found
                return false;
            }

            // Check local portion
            if (!$this->check_local_portion($arrEmailAddress[0])) {
                return false;
            }

            // Check domain portion
            if (!$this->check_domain_portion($arrEmailAddress[1])) {
                return false;
            }

            // If we're still here, all checks above passed. Email is valid.
            return true;

        }

        /**
         * Checks email section before "@" symbol for validity
         * @param   strLocalPortion     Text to be checked
         * @return  True if local portion is valid, false if not
         */
         function check_local_portion($strLocalPortion) {
            // Local portion can only be from 1 to 64 characters, inclusive.
            // Please note that servers are encouraged to accept longer local
            // parts than 64 characters.
            if (!$this->check_text_length($strLocalPortion, 1, 64)) {
                return false;
            }
            // Local portion must be:
            // 1) a dot-atom (strings separated by periods)
            // 2) a quoted string
            // 3) an obsolete format string (combination of the above)
            $arrLocalPortion = explode('.', $strLocalPortion);
            for ($i = 0, $max = sizeof($arrLocalPortion); $i < $max; $i++) {
                 if (!preg_match('.^('
                                .    '([A-Za-z0-9!#$%&\'*+/=?^_`{|}~-]' 
                                .    '[A-Za-z0-9!#$%&\'*+/=?^_`{|}~-]{0,63})'
                                .'|'
                                .    '("[^\\\"]{0,62}")'
                                .')$.'
                                ,$arrLocalPortion[$i])) {
                    return false;
                }
            }
            return true;
        }

        /**
         * Checks email section after "@" symbol for validity
         * @param   strDomainPortion     Text to be checked
         * @return  True if domain portion is valid, false if not
         */
         function check_domain_portion($strDomainPortion) {
            // Total domain can only be from 1 to 255 characters, inclusive
            if (!$this->check_text_length($strDomainPortion, 1, 255)) {
                return false;
            }
            // Check if domain is IP, possibly enclosed in square brackets.
            if (preg_match('/^(25[0-5]|2[0-4][0-9]|1[0-9][0-9]|[1-9]?[0-9])'
               .'(\.(25[0-5]|2[0-4][0-9]|1[0-9][0-9]|[1-9]?[0-9])){3}$/'
               ,$strDomainPortion) || 
                preg_match('/^\[(25[0-5]|2[0-4][0-9]|1[0-9][0-9]|[1-9]?[0-9])'
               .'(\.(25[0-5]|2[0-4][0-9]|1[0-9][0-9]|[1-9]?[0-9])){3}\]$/'
               ,$strDomainPortion)) {
                return true;
            } else {
                $arrDomainPortion = explode('.', $strDomainPortion);
                if (sizeof($arrDomainPortion) < 2) {
                    return false; // Not enough parts to domain
                }
                for ($i = 0, $max = sizeof($arrDomainPortion); $i < $max; $i++) {
                    // Each portion must be between 1 and 63 characters, inclusive
                    if (!$this->check_text_length($arrDomainPortion[$i], 1, 63)) {
                        return false;
                    }
                    if (!preg_match('/^(([A-Za-z0-9][A-Za-z0-9-]{0,61}[A-Za-z0-9])|'
                       .'([A-Za-z0-9]+))$/', $arrDomainPortion[$i])) {
                        return false;
                    }
                }
            }
            return true;
        }

        /**
         * Check given text length is between defined bounds
         * @param   strText     Text to be checked
         * @param   intMinimum  Minimum acceptable length
         * @param   intMaximum  Maximum acceptable length
         * @return  True if string is within bounds (inclusive), false if not
         */
         function check_text_length($strText, $intMinimum, $intMaximum) {
            // Minimum and maximum are both inclusive
            $intTextLength = strlen($strText);
            if (($intTextLength < $intMinimum) || ($intTextLength > $intMaximum)) {
                return false;
            } else {
                return true;
            }
        }

    }




/* Check for injection characters */
function ddfm_injection_chars($s) {
	return (eregi("\r", $s) || eregi("\n", $s) || eregi("%0a", $s) || eregi("%0d", $s)) ? TRUE : FALSE;
}

/* Make output safe for the browser */
function ddfm_bsafe($input) {
	return htmlspecialchars(stripslashes($input));
}

/* Strip slashes when magicquotes are on */
function ddfm_stripslashes($s) {
	if (defined('TEMPLATEPATH') || (get_magic_quotes_gpc())) {
		return stripslashes($s);
	} else {
		return $s;
	}
}



/* Check for common header injection methods */
function ddfm_injection_test($str) { 
	$tests = array("/bcc\:/i", "/Content\-Type\:/i", "/Mime\-Version\:/i", "/cc\:/i", "/from\:/i", "/to\:/i", "/Content\-Transfer\-Encoding\:/i"); 
	return preg_replace($tests, "", $str); 
} 

/* Sends the message */
function ddfm_send_mail($recipients, $sender_name, $sender_email, $email_subject, $email_msg, $attach_save, $attach_path, $attachments = false) {

	$extra_recips = '';

	// generate recipient data from list
	if (strpos($recipients, '|')) {

		$rdata = array();
		$ri = 0;
		$rtmp = explode('|', $recipients);
		foreach ($rtmp as $rd) {
			if (trim($rd) != "") {
				list($m, $e) = (array)explode("=", trim($rd), 2);
				$rdata[$ri]['m'] = trim(strtolower($m));
				$rdata[$ri]['e'] = trim($e);
				$ri++;
			}
		}	

		rsort($rdata);

		$r_to = array();
		$extra_recips = "";
		foreach ($rdata as $r) { 
			if ($r['m'] == 'to') $r_to[] = $r['e'];	
			if ($r['m'] == 'cc') $extra_recips .= 'cc: ' . $r['e'] . PHP_EOL;		
			if ($r['m'] == 'bcc') $extra_recips .= 'bcc: ' . $r['e'] . PHP_EOL;	
		}
		$send_to = implode(', ', $r_to);
	
	} else {
		$send_to = trim($recipients);
	}


	$sender_name = ddfm_injection_test($sender_name);
	$sender_email = ddfm_injection_test($sender_email);
	$email_subject = ddfm_injection_test($email_subject);

	if (function_exists('mb_encode_mimeheader')) {
	$email_subject = mb_encode_mimeheader($email_subject, 'UTF-8', 'Q', '');
	$sender_name = mb_encode_mimeheader($sender_name, 'UTF-8', 'Q', '');
	}

	if (trim($sender_name) == "") {
		$sender_name = 'Anonymous';
	}
	if (trim($sender_email) == "") {
		$sender_email = 'user@domain.com';
	}
	if (trim($email_subject) == "") {
		$email_subject = 'Contact Form';
	}


	$mime_boundary = md5(time()); 

	$headers = '';
	$msg = '';


	$headers .= 'From: ' . $sender_name . ' <' . $sender_email . '>' . PHP_EOL;
	$headers .= $extra_recips;
	$headers .= 'Reply-To: ' . $sender_name . ' <' . $sender_email . '>' . PHP_EOL;
	$headers .= 'Return-Path: ' . $sender_name . ' <' . $sender_email . '>' . PHP_EOL;
	$headers .= "Message-ID: <" . time() . "ddfm@" . $_SERVER['SERVER_NAME'] . ">" . PHP_EOL;
	$headers .= 'X-Sender-IP: ' . $_SERVER["REMOTE_ADDR"] . PHP_EOL;
	$headers .= "X-Mailer: PHP v" . phpversion() . PHP_EOL;

	$headers .= 'MIME-Version: 1.0' . PHP_EOL;
//	$headers .= 'Content-Type: multipart/related; boundary="' . $mime_boundary . '"';
	$headers .= 'Content-Type: multipart/mixed; boundary="' . $mime_boundary . '"';

	$msg .= '--' . $mime_boundary . PHP_EOL;
	$msg .= 'Content-Type: text/plain; charset=utf-8' . PHP_EOL;
//	$msg .= 'Content-Type: text/plain; charset="iso-8859-1"' . PHP_EOL;
	$msg .= 'Content-Transfer-Encoding: 8bit' . PHP_EOL . PHP_EOL;

	$msg .= $email_msg . PHP_EOL . PHP_EOL;

	if (count($attachments) > 0) {

		for ($i = 0; $i < count($attachments); $i++) { 

			if (is_file($attachments[$i]['tmpfile'])) {



				if ($attach_save) {

					if (!rename($attachments[$i]['tmpfile'], $attach_path . $attachments[$i]['file'])) {
						echo 'Error saving file. Check your path and permissions. Stopping script.';
						exit();
					}

				} else {

					$handle = fopen($attachments[$i]['tmpfile'], 'rb');
					$f_contents = fread($handle, filesize($attachments[$i]['tmpfile'])); 
					$f_contents = chunk_split(base64_encode($f_contents));
					fclose($handle);		

					$msg .= '--' . $mime_boundary . PHP_EOL;
					$msg .= 'Content-Type: application/octet-stream; name="' . $attachments[$i]['file'] . '"' . PHP_EOL;
					$msg .= 'Content-Transfer-Encoding: base64' . PHP_EOL;
					$msg .= 'Content-Disposition: attachment; filename="' . $attachments[$i]['file'] . '"' . PHP_EOL . PHP_EOL; 
					$msg .= $f_contents . PHP_EOL . PHP_EOL;

				}

			}
       
		}
	}

	$msg .= '--' . $mime_boundary . '--' . PHP_EOL . PHP_EOL;

	@ini_set('sendmail_from', $sender_email);
	$send_status = mail($send_to, $email_subject, $msg, $headers);
	@ini_restore('sendmail_from');

	return $send_status;
}




add_option('ddfm_instances', 1);


add_option('ddfm_verify_method', 'basic'); // off, basic, recaptcha

// for basic method
add_option('ddfm_verify_background', 'F0F0F0');
add_option('ddfm_verify_text', '005ABE');
add_option('ddfm_force_type', '');

// for recaptcha method
add_option('ddfm_re_public', '');
add_option('ddfm_re_private', '');




$form_input = array();



/* Dagon Design FormMailer Class */
class ddfmClass {

	var $version;			// Script Version
	var $inst;				// Instance number
	var $var_pre;			// Variable prefix

	/* Initialize instance */
	function ddfmClass($i, $ver) {

		$this->ver = $ver;
		$this->inst = $i;
		$this->var_pre = "ddfm{$i}_";


		// Setup default options if they do not exist
		add_option($this->var_pre . 'language', 'English');
		add_option($this->var_pre . 'desc', 'Description of this instance');
		add_option($this->var_pre . 'path_contact_page', 'http://www.yoursite.com/contact/');
		add_option($this->var_pre . 'wrap_messages', TRUE);
		add_option($this->var_pre . 'attach_save', FALSE);
		add_option($this->var_pre . 'attach_path', '');
		add_option($this->var_pre . 'show_required', TRUE);
		add_option($this->var_pre . 'show_url', FALSE);
		add_option($this->var_pre . 'show_ip_hostname', TRUE);
		add_option($this->var_pre . 'recipients', 'you@domain.com');
		add_option($this->var_pre . 'form_struct', '
type=text|class=fmtext|label=Name|fieldname=fm_name|max=100|req=true
type=text|class=fmtext|label=Email|fieldname=fm_email|max=100|req=true|ver=email
type=text|class=fmtext|label=Subject|fieldname=fm_subject|max=100|req=true
type=verify|class=fmverify|label=Verify
type=textarea|class=fmtextarea|label=Message|fieldname=fm_message|max=1000|rows=6|req=true
');
		add_option($this->var_pre . 'manual_form_code', '');
		add_option($this->var_pre . 'sender_name', 'fm_name');
		add_option($this->var_pre . 'sender_email', 'fm_email');
		add_option($this->var_pre . 'email_subject', 'Contact: fm_subject');
		add_option($this->var_pre . 'max_file_size', 1000000);
		add_option($this->var_pre . 'message_structure', '');
		add_option($this->var_pre . 'sent_message', '<br /><p>Thank you - your message has been sent.</p>');
		add_option($this->var_pre . 'auto_reply_name', '');
		add_option($this->var_pre . 'auto_reply_email', '');
		add_option($this->var_pre . 'auto_reply_subject', '');
		add_option($this->var_pre . 'auto_reply_message', '');

		add_option($this->var_pre . 'save_to_file', FALSE);
		add_option($this->var_pre . 'save_email', TRUE);
		add_option($this->var_pre . 'save_path', '');
		add_option($this->var_pre . 'save_delimiter', '|');
		add_option($this->var_pre . 'save_newlines', '<br>');
		add_option($this->var_pre . 'save_timestamp', 'm-d-Y h:i:s A');


		// Setup actions/hooks

		add_action('admin_menu', Array(&$this, 'add_options'));
		add_filter('the_content', Array(&$this, 'check_content'));

	}


	/* Add option page to WP admin panel */
	function add_options() {
		if (function_exists('add_options_page')) {

			add_options_page("Dagon Design Form Mailer v{$this->ver} (Instance {$this->inst})", 
			"DDFM{$this->inst}", 8, "DDFM{$this->inst}", Array(&$this, 'options_page'));


		}		
	}



	/* Add a text field to the options page */
	function options_page_add_text($txt, $var, $size, $desc = '') {

		$o = '<tr valign="top"><th scope="row">' . $txt . '</th><td>
			<input name="' . $this->var_pre . $var . '" type="text" size="' . $size . '" id="' . $this->var_pre . $var . '" value="' . htmlspecialchars(get_option($this->var_pre . $var)) . '"  />';
			if (trim($desc) != '') $o .= '<br />' . $desc;
		$o .= '</td></tr>';

		return $o;
	}


	/* Add a checkbox to the options page */
	function options_page_add_check($txt, $var, $desc) {
		if (get_option($this->var_pre . $var)) 
			$chk = 'checked="checked"';
		else
			$chk = ' ';	
		return '
		<tr valign="top"><th scope="row">' . $txt . '</th><td>
			<input type="checkbox" name="' . $this->var_pre . $var . '" id="' . $this->var_pre . $var . '" value="checkbox" ' . $chk . ' />
			&nbsp;' . $desc . '
		</td></tr>';
	}


	/* Generate option page */

	function options_page() {

		global $ddfm_version;


		if (isset($_POST['set_defaults'])) {
			echo '<div id="message" class="updated fade"><p><strong>';

			update_option($this->var_pre . 'language', 'English');
			update_option($this->var_pre . 'desc', 'Description of this instance');
			update_option($this->var_pre . 'path_contact_page', 'http://www.yoursite.com/contact/');
			update_option($this->var_pre . 'wrap_messages', TRUE);
			update_option($this->var_pre . 'attach_save', FALSE);
			update_option($this->var_pre . 'attach_path', '');
			update_option($this->var_pre . 'show_required', TRUE);
			update_option($this->var_pre . 'show_url', FALSE);
			update_option($this->var_pre . 'show_ip_hostname', TRUE);
			update_option($this->var_pre . 'recipients', 'you@domain.com');
			update_option($this->var_pre . 'form_struct', '
type=text|class=fmtext|label=Name|fieldname=fm_name|max=100|req=true
type=text|class=fmtext|label=Email|fieldname=fm_email|max=100|req=true|ver=email
type=text|class=fmtext|label=Subject|fieldname=fm_subject|max=100|req=true
type=verify|class=fmverify|label=Verify
type=textarea|class=fmtextarea|label=Message|fieldname=fm_message|max=1000|rows=6|req=true
');
			update_option($this->var_pre . 'manual_form_code', '');
			update_option($this->var_pre . 'sender_name', 'fm_name');
			update_option($this->var_pre . 'sender_email', 'fm_email');
			update_option($this->var_pre . 'email_subject', 'Contact: fm_subject');
			update_option($this->var_pre . 'max_file_size', 1000000);
			update_option($this->var_pre . 'message_structure', '');
			update_option($this->var_pre . 'sent_message', '<br /><p>Thank you - your message has been sent.</p>');
			update_option($this->var_pre . 'auto_reply_name', '');
			update_option($this->var_pre . 'auto_reply_email', '');
			update_option($this->var_pre . 'auto_reply_subject', '');
			update_option($this->var_pre . 'auto_reply_message', '');

			update_option($this->var_pre . 'save_to_file', FALSE);
			update_option($this->var_pre . 'save_email', TRUE);
			update_option($this->var_pre . 'save_path', '');
			update_option($this->var_pre . 'save_delimiter', '|');
			update_option($this->var_pre . 'save_newlines', '<br>');
			update_option($this->var_pre . 'save_timestamp', 'm-d-Y h:i:s A');

			echo 'Default Options Loaded!';
			echo '</strong></p></div>';


		} else	if (isset($_POST['info_update'])) {


			echo '<div id="message" class="updated fade"><p><strong>';


			update_option($this->var_pre . 'language', (string) $_POST[$this->var_pre . 'language']);
			update_option($this->var_pre . 'desc', (string) $_POST[$this->var_pre . 'desc']);
			update_option($this->var_pre . 'path_contact_page', (string) ddfm_stripslashes($_POST[$this->var_pre . 'path_contact_page']));
			update_option($this->var_pre . 'wrap_messages', ddfm_get_bool_from_post($this->var_pre . 'wrap_messages'));
			update_option($this->var_pre . 'attach_save', ddfm_get_bool_from_post($this->var_pre . 'attach_save'));
			update_option($this->var_pre . 'attach_path', (string) ddfm_stripslashes($_POST[$this->var_pre . 'attach_path']));
			update_option($this->var_pre . 'show_required', ddfm_get_bool_from_post($this->var_pre . 'show_required'));
			update_option($this->var_pre . 'show_url', ddfm_get_bool_from_post($this->var_pre . 'show_url'));
			update_option($this->var_pre . 'show_ip_hostname', ddfm_get_bool_from_post($this->var_pre . 'show_ip_hostname'));
			update_option($this->var_pre . 'recipients', (string) ddfm_stripslashes($_POST[$this->var_pre . 'recipients']));
			update_option($this->var_pre . 'form_struct', (string) ddfm_stripslashes($_POST[$this->var_pre . 'form_struct']));
			update_option($this->var_pre . 'manual_form_code', (string) $_POST[$this->var_pre . 'manual_form_code']);
			update_option($this->var_pre . 'sender_name', (string) ddfm_stripslashes($_POST[$this->var_pre . 'sender_name']));
			update_option($this->var_pre . 'sender_email', (string) ddfm_stripslashes($_POST[$this->var_pre . 'sender_email']));
			update_option($this->var_pre . 'email_subject', (string) ddfm_stripslashes($_POST[$this->var_pre . 'email_subject']));
			update_option($this->var_pre . 'max_file_size', (int) $_POST[$this->var_pre . 'max_file_size']);
			update_option($this->var_pre . 'message_structure', (string) ddfm_stripslashes($_POST[$this->var_pre . 'message_structure']));
			update_option($this->var_pre . 'sent_message', (string) ddfm_stripslashes($_POST[$this->var_pre . 'sent_message']));
			update_option($this->var_pre . 'auto_reply_name', (string) ddfm_stripslashes($_POST[$this->var_pre . 'auto_reply_name']));
			update_option($this->var_pre . 'auto_reply_email', (string) ddfm_stripslashes($_POST[$this->var_pre . 'auto_reply_email']));
			update_option($this->var_pre . 'auto_reply_subject', (string) ddfm_stripslashes($_POST[$this->var_pre . 'auto_reply_subject']));
			update_option($this->var_pre . 'auto_reply_message', (string) ddfm_stripslashes($_POST[$this->var_pre . 'auto_reply_message']));

			update_option($this->var_pre . 'save_to_file', ddfm_get_bool_from_post($this->var_pre . 'save_to_file'));
			update_option($this->var_pre . 'save_email', ddfm_get_bool_from_post($this->var_pre . 'save_email'));
			update_option($this->var_pre . 'save_path', (string) ddfm_stripslashes($_POST[$this->var_pre . 'save_path']));
			update_option($this->var_pre . 'save_delimiter', (string) ddfm_stripslashes($_POST[$this->var_pre . 'save_delimiter']));
			update_option($this->var_pre . 'save_newlines', (string) ddfm_stripslashes($_POST[$this->var_pre . 'save_newlines']));
			update_option($this->var_pre . 'save_timestamp', (string) ddfm_stripslashes($_POST[$this->var_pre . 'save_timestamp']));

			echo 'Configuration Updated!';
			echo '</strong></p></div>';
			
		}



		?>
		<div class="wrap">
		<h2>Dagon Design Form Mailer v<?php echo $ddfm_version; ?> (Instance <?php echo $this->inst; ?>)</h2>

		<p>For information and updates, please visit:<br />
		<a href="http://www.dagondesign.com/articles/secure-form-mailer-plugin-for-wordpress/">http://www.dagondesign.com/articles/secure-form-mailer-plugin-for-wordpress/</a></p>

		<?php 
		// Get list of available languages
		$language_list = array();
		$handle = opendir(dirname(__FILE__) . '/lang');
	    while ($file = readdir($handle)) {
	        if ($file != '.' && $file != '..')
	   	        $language_list[] = $file;
	    }
		if ($handle === FALSE) {
			echo '<span style="color:#FF0000; font-weight: bold;">Error!</span> Language files not found - make sure they were uploaded<br /><br />';
		}
	    closedir($handle);
		?>



	
		<form method="post" action="<?php get_option($this->var_pre . 'path_contact_page') ?>">
		<input type="hidden" name="info_update" id="info_update" value="true" />



		<h3>Settings</h3>

		<table class="form-table">

		<tr valign="top">
		<th scope="row">Language</th>
		<td>
			<select name="<?php echo $this->var_pre; ?>language">
			<?php 
			$slang = trim(get_option($this->var_pre . 'language'));
			foreach ($language_list as $lang) {
				echo '<option';
				if ($lang == ($slang . '.php')) {
					echo ' selected="selected"';
				}
				echo '>' . substr($lang, 0, strlen($lang)-4) . '</option>';
			}
			?>
			</select>
			<br />Language files are located in the <strong>dd-formmailer/lang</strong> directory.	
		</td>
		</tr>

		<?php echo $this->options_page_add_text('Instance Description', 'desc', 60, 'Description to help keep instances separate'); ?>
		<?php echo $this->options_page_add_text('Contact page', 'path_contact_page', 60, 'The full URL that this form will be displayed on'); ?>
		<?php echo $this->options_page_add_text('Recipients', 'recipients', 60, 'For single recipients, enter the email address. For multiple recipients, see the documentation'); ?>

		</table>




		<h3>Form Structure</h3>

		<table class="form-table">

		<tr valign="top">
		<td>
			<strong>One field per line!</strong> - See documentation for usage instructions<br />
			<textarea style="width:98%;" name="<?php echo $this->var_pre; ?>form_struct" cols="110" rows="12"><?php echo htmlspecialchars(get_option($this->var_pre . 'form_struct')) ?></textarea>
		</td>
		</tr>

		</table>



		<h3>Manual Form Code</h3>

		<table class="form-table">

		<tr valign="top">
		<td>
			<b>Advanced users only!</b> Read the website for details<br />
			<textarea style="width:98%;" name="<?php echo $this->var_pre; ?>manual_form_code" cols="110" rows="4"><?php echo htmlspecialchars(stripslashes(get_option($this->var_pre . 'manual_form_code'))) ?></textarea>
		</td>
		</tr>

		</table>




		<h3>Email Generation</h3>

		The options below help generate the email headers. If you enter a field name, it will be replaced by the user input from that field.<br /><br />

		For example, if you have a form field called <strong>fm_name</strong> and you use that as the <strong>Sender name</strong> option, 
		the visitor's name will be used as the <strong>From</strong> field in the email. You can also combine them. If you have a field called 
		<strong>fm_firstname</strong> and <strong>fm_lastname</strong> you could enter '<strong>fm_lastname, fm_firstname</strong>' - For the 
		<strong>Sender email</strong> option, just use your email field.<br /><br />

		The <strong>Email subject</strong> option works the same way. For example, you could enter '<strong>Contact: fm_subject</strong>' and it 
		will use the subject that the visitor entered, with the '<strong>Contact:</strong>' prefix.<br /><br />

		<table class="form-table">

		<?php echo $this->options_page_add_text('Sender name', 'sender_name', 40, 'If blank, email will show to come from \'Anonymous\''); ?>
		<?php echo $this->options_page_add_text('Sender email', 'sender_email', 40, 'If blank, email will show to come from \'user@domain.com\''); ?>
		<?php echo $this->options_page_add_text('Email subject', 'email_subject', 40, 'If blank, the subject line will be \'Contact Form\''); ?>

		</table>




		<h3>Message Structure</h3>

		<table class="form-table">

		<tr valign="top">
		<td>
			<strong>Note:</strong> This field is optional - If left blank, the plugin will generate the message itself. If you choose to use this option, it will act as the message template. Simply enter your custom text, including your field names. When the message is generated, the field names will be replaced by the user input from those fields.

			<textarea style="width:98%;" name="<?php echo $this->var_pre; ?>message_structure" cols="110" rows="10"><?php echo htmlspecialchars(get_option($this->var_pre . 'message_structure')) ?></textarea>
		</td>
		</tr>

		</table>




		<h3>Success Message</h3>

		<table class="form-table">

		<tr valign="top">
		<td>
			This is the message shown after the email has been sent. You can also use field names in the success message. They will be replaced with the user input from those fields.
			<textarea style="width:98%;" name="<?php echo $this->var_pre; ?>sent_message" cols="110" rows="4"><?php echo htmlspecialchars(get_option($this->var_pre . 'sent_message')) ?></textarea>
		</td>
		</tr>

		</table>





		<h3>Auto Reply</h3>

		A message automatically sent back to the sender. Leave the message blank to disable.

		<table class="form-table">

			<?php echo $this->options_page_add_text('Auto reply Name', 'auto_reply_name', 40, 'Name auto reply is sent from'); ?>
			<?php echo $this->options_page_add_text('Auto reply Email', 'auto_reply_email', 40, 'Email address auto reply is sent from'); ?>
			<?php echo $this->options_page_add_text('Auto reply Subject', 'auto_reply_subject', 40, 'Subject line for auto reply'); ?>

		<tr valign="top"><th scope="row">Auto reply Message</th>
		<td>
			If you choose to use this option, you can also use field names in the message. They will be replaced with the user input from those fields.
			<textarea style="width:98%;" name="<?php echo $this->var_pre; ?>auto_reply_message" cols="110" rows="4"><?php echo htmlspecialchars(get_option($this->var_pre . 'auto_reply_message')) ?></textarea>
		</td>
		</tr>
	
		</table>




		<h3>Save Attachments</h3>

		<table class="form-table">

		<?php echo $this->options_page_add_check('Save files', 'attach_save', 'Save attachments <strong>instead</strong> of emailing them'); ?>
		<?php echo $this->options_page_add_text('Save path', 'attach_path', 40, 'Directory to save attachments <strong>(full path on your server - include trailing slash - give write access)<br/>example: /home/user/public_html/upload/</strong>'); ?>

		</table>
	



		<h3>Other Options</h3>

		<table class="form-table">

		<?php echo $this->options_page_add_check('Show required', 'show_required', 'Mark required fields with an asterisk'); ?>
		<?php echo $this->options_page_add_check('Show URL', 'show_url', 'Adds the current URL to the message'); ?>
		<?php echo $this->options_page_add_check('Show IP and hostname', 'show_ip_hostname', 'Adds the visitors IP and hostname to the message'); ?>
		<?php echo $this->options_page_add_check('Wrap messages', 'wrap_messages', 'Wrap message lines to 70 characters for RFC compliance'); ?>
		<?php echo $this->options_page_add_text('Max upload size', 'max_file_size', 9, 'Maximum size of file uploads (in bytes) - <strong>Note:</strong> Only applies if the value is lower than the limit set in php.ini'); ?>

		</table>



		<h3>Save Data to File</h3>

		<table class="form-table">

		<?php echo $this->options_page_add_check('Save data to file', 'save_to_file', 'If enabled, form input will be saved in a delimited file'); ?>
		<?php echo $this->options_page_add_check('Still send email', 'save_email', 'If saving data, still send email?'); ?>
		<?php echo $this->options_page_add_text('Data path', 'save_path', 40, ' Path to data file <strong>(relative to the root directory of your WP install - give write access!)</strong>'); ?>
		<?php echo $this->options_page_add_text('Delimiter', 'save_delimiter', 6, 'Fields will be separated by this'); ?>
		<?php echo $this->options_page_add_text('Newlines', 'save_newlines', 6, 'Newlines in input will be replaced by this'); ?>
		<?php echo $this->options_page_add_text('Timestamp', 'save_timestamp', 25, 'Add date/time to the beginning of each line (uses the <a href="http://us.php.net/date">PHP date format</a>) - Leave blank to disable'); ?>

		</table>



		<div class="submit">
			<input type="submit" name="set_defaults" value="<?php _e('Load Default Options'); ?> &raquo;" />
			<input type="submit" name="info_update" value="<?php _e('Update options'); ?> &raquo;" />
		</div>
		</form>

		</div>
		<?php
	}


	/* Look for trigger text */
	function check_content($content) {

		if (strpos($content, '<!-- ddfm' . $this->inst . ' -->') !== FALSE) {

			$content = preg_replace('/<p>\s*<!--(.*)-->\s*<\/p>/i', "<!--$1-->", $content);

			$content = str_replace('<!-- ddfm' . $this->inst . ' -->', $this->generate_data(), $content);

		}


		return $content;

	}




// START of functions to show form output


function ddfm_gen_html($item) {

	// type=html|text=

	$gen = $item['text'] . "\n";

	return $gen;
}








function ddfm_gen_date($item) {

	// type=date|class=|label=|fieldname=|req=(TRUEFALSE)

	global $form_submitted, $form_input, $show_required;


	$req_text = (($show_required) && ($item['req'] == 'true')) ? '<span class="required">' . DDFM_REQUIREDTAG . '</span> ' : '';

	$gen = "";
	$gen .= '<p class="fieldwrap"><label for="' . $item['fieldname'] . '">' . $req_text . $item['label'] . '</label>';
	$gen .= '<input class="' . $item['class'] . '" type="text" name="' . $item['fieldname'] . '" id="' . $item['fieldname'] . '" value="';

	if ($form_submitted) {
		$gen .= ddfm_bsafe($form_input[$item['fieldname']]);
	} else if (isset($item['default'])) {
		$gen .= ddfm_bsafe($item['default']);
	}

	$gen .= '" />';


	// $gen .= '<input type="button" value="select" onclick="displayDatePicker(\'' . $item['fieldname'] . '\', this);">';
	$gen .= '<img src="' . rtrim(get_settings('siteurl'), '/') . '/wp-content/plugins/dd-formmailer/calendar.gif" alt=""  onclick="displayDatePicker(\'' . $item['fieldname'] . '\', this);" />';


	$gen .='</p>' . "\n\n";

	return $gen;
}


function ddfm_gen_text($item) {

	// type=text|class=|label=|fieldname=|max=|req=(TRUEFALSE)|[ver=]|[default=]

	global $form_submitted, $form_input, $show_required;

	$req_text = (($show_required) && ($item['req'] == 'true')) ? '<span class="required">' . DDFM_REQUIREDTAG . '</span> ' : '';

	$gen = "";
	$gen .= '<p class="fieldwrap"><label for="' . $item['fieldname'] . '">' . $req_text . $item['label'] . '</label>';
	$gen .= '<input class="' . $item['class'] . '" type="text" name="' . $item['fieldname'] . '" id="' . $item['fieldname'] . '" value="';

	if ($form_submitted) {
		$gen .= ddfm_bsafe($form_input[$item['fieldname']]);
	} else if (isset($item['default'])) {
		$gen .= ddfm_bsafe($item['default']);
	}

	$gen .= '" /></p>' . "\n\n";

	return $gen;
}



function ddfm_gen_password($item) {

	// type=password|class=|label=|fieldname=|max=|req=(TRUEFALSE)|confirm=(TRUEFALSE)

	global $form_submitted, $form_input, $show_required;

	$req_text = (($show_required) && $item['req'] == 'true') ? '<span class="required">' . DDFM_REQUIREDTAG . '</span> ' : '';

	$gen = "";
	$gen .= '<p class="fieldwrap"><label for="' . $item['fieldname'] . '">' . $req_text . $item['label'] . '</label>' . "\n";
	$gen .= '<input class="' . $item['class'] . '" type="password" name="' . $item['fieldname'] . '" id="' . $item['fieldname'] . '" value="';
	$gen .= '" /></p>' . "\n\n";

	if ($item['confirm'] == 'true') {

		// Duplicate field (add 'c' to end)
		$gen .= '<p class="fieldwrap"><label for="' . $item['fieldname'] . 'c">' . $req_text . DDFM_CONFIRMPASS . ' ' . $item['label'] . '</label>' . "\n";
		$gen .= '<input class="' . $item['class'] . '" type="password" name="' . $item['fieldname'] . 'c" id="' . $item['fieldname'] . 'c" value="';
		$gen .= '" /></p>' . "\n\n";

	}

	return $gen;
}



function ddfm_gen_textarea($item) {

	// type=textarea|class=|label=|fieldname=|max=|rows=|req=(TRUEFALSE)|[default=]

	global $form_submitted, $form_input, $show_required;

	$req_text = (($show_required) && $item['req'] == 'true') ? '<span class="required">' . DDFM_REQUIREDTAG . '</span> ' : '';

	$gen = "";
	$gen .= '<p class="fieldwrap"><label for="' . $item['fieldname'] . '">' . $req_text . $item['label'] . '</label>' . "\n";
	$gen .= '<textarea class="' . $item['class'] . '" name="' . $item['fieldname'] . '" cols="20" rows="' . $item['rows'] . '" id="' . $item['fieldname'] . '">';

	if ($form_submitted) {
		$gen .= ddfm_bsafe($form_input[$item['fieldname']]);
	} else if (isset($item['default'])) {
		$gen .= ddfm_bsafe($item['default']);
	}

	$gen .= '</textarea></p>' . "\n\n";

	return $gen;
}



function ddfm_gen_widetextarea($item) {

	// type=widetextarea|class=|label=|fieldname=|max=|rows=|req=(TRUEFALSE)|[default=]

	global $form_submitted, $form_input, $show_required;

	$req_text = (($show_required) && $item['req'] == 'true') ? '<span class="required">' . DDFM_REQUIREDTAG . '</span> ' : '';

	$gen = "";
	$gen .= '<p class="fieldwrap"><label for="' . $item['fieldname'] . '" class="fmtextlblwide">' . $req_text . $item['label'] . '</label>' . "\n";
	$gen .= '<textarea class="' . $item['class'] . '" name="' . $item['fieldname'] . '" cols="20" rows="' . $item['rows'] . '" id="' . $item['fieldname'] . '">';

	if ($form_submitted) {
		$gen .= ddfm_bsafe($form_input[$item['fieldname']]);
	} else if (isset($item['default'])) {
		$gen .= ddfm_bsafe($item['default']);
	}

	$gen .= '</textarea></p>' . "\n\n";

	return $gen;
}


function ddfm_gen_verify($item) {

	// type=verify|class=|label=

	if (get_option('ddfm_verify_method') != 'basic') return '';

	global $show_required;

	$gen = "";

	$req_text = ($show_required) ? '<span class="required">' . DDFM_REQUIREDTAG . '</span> ' : '';
	if (ddfm_check_gd_support()) {
		$gen .= '<p class="fieldwrap"><label for="fm_verify">' . $req_text . $item['label'] . '</label>' . "\n";
		$gen .= '<input class="'. $item['class'] . '" type="text" name="fm_verify" id="fm_verify" />' . "\n";
		$gen .= '<img width="60" height="24" src="' . rtrim(get_settings('siteurl'), '/') . '/wp-content/plugins/dd-formmailer/dd-formmailer.php?v=1" alt="' . $item['label'] . '" title="' . $item['label'] . '" /></p>' . "\n\n";
	}

	return $gen;
}


function ddfm_gen_fullblock($item) {

	// type=fullblock|class=|text=

	$gen = "";

	$gen .= '<div class="' . $item['class'] . '"><p class="fieldwrap">' . "\n";
	$gen .= $item['text'] . "\n";
	$gen .= '</p></div>' . "\n\n";

	return $gen;
}


function ddfm_gen_halfblock($item) {

	// type=halfblock|class=|text=

	$gen = "";

	$gen .= '<div class="' . $item['class'] . '"><p class="fieldwrap">' . "\n";
	$gen .= $item['text'] . "\n";
	$gen .= '</p></div>' . "\n\n";

	return $gen;
}

  
function ddfm_gen_openfieldset($item) {

	// type=openfieldset|legend=

	$gen = "";

	$gen .= '<fieldset><legend>' . ddfm_bsafe($item['legend']) . '</legend>' . "\n\n";

	return $gen;
}


function ddfm_gen_closefieldset($item) {

	// type=closefieldset

	$gen = "";

	$gen .= '</fieldset>' . "\n\n";

	return $gen;
}


function ddfm_gen_checkbox($item) {

	// type=checkbox|class=|label=|data=
	//	 (fieldname),(text),(CHECKED),(REQUIRED),
	//	 (fieldname),(text),(CHECKED),(REQUIRED),
	//	 (fieldname),(text),(CHECKED),(REQUIRED)

	global $form_submitted, $form_input, $show_required;
	
	// ### added following line, add by MG ###
	$req_text = (($show_required) && $item['req'] == 'true') ? '<span class="required">' . DDFM_REQUIREDTAG . '</span> ' : '';

	$gen = "";

	// ### added $req_text to the following line, add by MG ###
	$gen .= '<p class="fieldwrap"><label>' . $req_text . $item['label'] . '</label><span class="' . $item['class'] . '">' . "\n";

	$item['data'] = str_replace(",,", "C0mM@", $item['data']);
	$data = explode(",", trim($item['data']));
	$data = str_replace("C0mM@", ",", $data);

	for ($i = 0; $i < sizeof($data); $i+=4) {

		$req_text = (($show_required) && ($data[$i+3] == 'true')) ? ' <span class="required">' . DDFM_REQUIREDTAG . '</span>' : '';

		$gen .= '<input type="checkbox" name="' . $data[$i] . '" id="' . $data[$i] . '" value="' . $data[$i + 1] . '"';

		if ($form_submitted) {
			if ((isset($form_input[$data[$i]])) && (trim($form_input[$data[$i]]) != '')) {
				$gen .= ' checked="checked"';
			}
		} else {
			if ($data[$i + 2] == 'true') {
				$gen .= ' checked="checked"';
			}
		}

		$gen .= ' /><label for="' . $data[$i] . '" class="fmchecklabel">' . $data[$i + 1] . $req_text . '</label>' . "\n";
		$gen .= '<br />';
	}

	$gen .= '</span></p>' . "\n\n";

	return $gen;
}


function ddfm_gen_radio($item) {

	//  type=radio|class=|label=|fieldname=|req=|[default=]|data=
	//	  (text),(text),(text)

	global $form_submitted, $form_input, $show_required;

	$req_text = (($show_required) && ($item['req'] == 'true')) ? '<span class="required">' . DDFM_REQUIREDTAG . '</span> ' : '';

	$gen = "";

	$gen .= '<p class="fieldwrap"><label>' . $req_text . $item['label'] . '</label><span class="' . $item['class'] . '">' . "\n";
	
	$c = 1;

	$item['data'] = str_replace(",,", "C0mM@", $item['data']);
	$data = explode(",", trim($item['data']));
	$data = str_replace("C0mM@", ",", $data);
	
	for ($i = 0; $i < sizeof($data); $i++) {

		$gen .= '<input type="radio" name="' . $item['fieldname'] . '" id="' . $data[$i] . '" value="' . $data[$i] . '"';

		if ($form_submitted) {
			if (trim($form_input[$item['fieldname']]) == $data[$i]) {
				$gen .= ' checked="checked"';
			}
		} else {
			if ($c == $item['default']) {
				$gen .= ' checked="checked"';
			}
		}

		$gen .= ' /><label for="' . $data[$i] . '" class="fmradiolabel">' . $data[$i] . '</label>' . "\n";
		$gen .= '<br />';

		$c++;
	}

	$gen .= '</span></p>' . "\n\n";

	return $gen;
}



function ddfm_gen_select($item) {

	//	type=select|class=|label=|fieldname=|multi=(TRUEFALSE)|data=
	//    (#group),(text),(text),(#group),(text),(text)

	global $form_submitted, $form_input, $show_required;

	$req_text = (($show_required) && ($item['req'] == 'true')) ? '<span class="required">' . DDFM_REQUIREDTAG . '</span> ' : '';

	$gen = "";

	$gen .= '<p class="fieldwrap"><label>' . $req_text . $item['label'] . '</label><select class="' . $item['class'] . '" code id="' . $item['fieldname'] . '" name="' . $item['fieldname'];

	if ($item['multi'] == 'true') {
		$gen .= '[]';
	}

	$gen .= '"';

	if ($item['multi'] == 'true') {
		$gen .= ' multiple="multiple"';
	}

	$gen .= '>' . "\n";
	
	$c = 1;

	$og = FALSE;

	$item['data'] = str_replace(",,", "C0mM@", $item['data']);
	$data = explode(",", trim($item['data']));
	$data = str_replace("C0mM@", ",", $data);

	for ($i = 0; $i < sizeof($data); $i++) {

		if (substr($data[$i], 0, 1) == '#' ) {

			if ($og) {
				$gen .= '</optgroup>' . "\n";	
			}
			$gen .= '<optgroup label="' . ltrim($data[$i], '#') . '">' . "\n";
			$og = TRUE;

		} else {

			$gen .= '<option';

			if ($form_submitted) {
		
				if ($item['multi'] == 'true') {

					foreach ((array)$form_input[$item['fieldname']] as $ii) {

						if ($data[$i] == $ii) {
							$gen .= ' selected="selected"';
						}
					}
			
				} else {

					if (trim($form_input[$item['fieldname']]) == $data[$i]) {
						$gen .= ' selected="selected"';
					}

				}
			} 

			$gen .= ' >' . $data[$i] . '</option>' . "\n";
		}

		$c++;
	}

	if ($og) {
		$gen .= '</optgroup>' . "\n";	
		$og = FALSE;
	} 

	$gen .= '</select></p>' . "\n\n";

	return $gen;
}


function ddfm_gen_file($item) {

	// type=file|class=|label=|fieldname=|req=(TRUEFALSE)|[allowed=1,2,3]

	global $form_submitted, $form_input, $show_required, $max_file_size;

	$req_text = (($show_required) && ($item['req'] == 'true')) ? '<span class="required">' . DDFM_REQUIREDTAG . '</span> ' : '';

	$gen = "";

	$gen .= '<p class="fieldwrap"><label for="' . $item['fieldname'] . '">' . $req_text . $item['label'] . '</label>' . "\n";
	$gen .= '<input class="' . $item['class'] . '" type="file" name="' . $item['fieldname'] . '" id="' . $item['fieldname'] . '" ';
	$gen .= ' /></p>' . "\n\n";

	return $gen;
}


function ddfm_gen_selrecip($item) {

	// type=selrecip|class=|label=|data=User1,user1@domain.com,User2 etc..

	global $form_submitted, $form_input, $show_required;

	$req_text = ($show_required) ? '<span class="required">' . DDFM_REQUIREDTAG . '</span> ' : '';

	$gen = "";

	$gen .= '<p class="fieldwrap"><label>' . $req_text . $item['label'] . '</label><select class="' . $item['class'] . '" name="fm_selrecip">' . "\n";
	

	$data = explode(",", trim($item['data']));


	$gen .= '<option';
	if ($form_submitted) {
		if (trim($form_input['fm_selrecip']) == $data[0]) {
			$gen .= ' selected="selected"';
		}
	}
	$gen .= ' >' . $data[0] . '</option>' . "\n";



	for ($i = 1; $i < sizeof($data); $i+=2) {

		$gen .= '<option';

		if ($form_submitted) {
			if (trim($form_input['fm_selrecip']) == $data[$i]) {
				$gen .= ' selected="selected"';
			}
		}

		$gen .= ' >' . $data[$i] . '</option>' . "\n";
	}

	$gen .= '</select></p>' . "\n\n";

	return $gen;

}

// END of functions to show form output




	
	/* Generate the script output */
	function generate_data() {


		global $form_submitted, $form_input, $show_required;


		// Get local copy of options
		$path_contact_page = get_option($this->var_pre . 'path_contact_page');
		$wrap_messages = get_option($this->var_pre . 'wrap_messages');
		$show_required = get_option($this->var_pre . 'show_required');
		$show_ip_hostname = get_option($this->var_pre . 'show_ip_hostname');
		$recipients = get_option($this->var_pre . 'recipients');
		$form_struct = get_option($this->var_pre . 'form_struct');
		$manual_form_code = get_option($this->var_pre . 'manual_form_code');
		$sender_name = get_option($this->var_pre . 'sender_name');
		$sender_email = get_option($this->var_pre . 'sender_email');
		$email_subject = get_option($this->var_pre . 'email_subject');
		$max_file_size = get_option($this->var_pre . 'max_file_size');

		$save_to_file = get_option($this->var_pre . 'save_to_file');
		$save_email = get_option($this->var_pre . 'save_email');
		$save_path = get_option($this->var_pre . 'save_path');
		$save_delimiter = get_option($this->var_pre . 'save_delimiter');
		$save_newlines = get_option($this->var_pre . 'save_newlines');
		$save_timestamp = get_option($this->var_pre . 'save_timestamp');


		$verify_method = get_option('ddfm_verify_method');


		// convert $form_struct into array of strings
		$form_struct = (array)explode('<br />', nl2br(trim($form_struct)));



		// Prepare globals
		$form_submitted = FALSE;



		// Load language settings
		@include_once('lang/' . get_option($this->var_pre . 'language') . '.php');


		$message_sent = FALSE;


		// Prepare output

		$o = "\n\n\n" . '<!-- START of Dagon Design Formmailer output -->' . "\n\n";



		// Convert form structure to multi-dimensional array

		$fs_tmp1 = array();
		$fs_tmp2 = array();
		$fitem = 0;

		foreach ($form_struct as $fs) {
			if (trim($fs) != "") {
				$fs_tmp1 = (array)explode("|", trim($fs));
				foreach ($fs_tmp1 as $fs1) {
					list($k, $v) = (array)explode("=", trim($fs1), 2);	

					$fs_tmp2[$fitem][$k] = $v;
				}			
			}

			$fitem++;
		}
		$form_struct = $fs_tmp2;


	// Make sure form structure is not missing empty keys

	$valid_keys = array('fieldname', 'type', 'req', 'label', 'max', 'ver', 'confirm', 'data', 'multi', 'allowed', 'default');

	for ($i = 0; $i < count($form_struct); $i++) {
		foreach ($valid_keys as $k) {
			if (!isset($form_struct[$i][$k])) $form_struct[$i][$k] = NULL;
		}
	}
	
	
	// Do a quick check to make sure there are no duplicate field names
	$dd_unique_fields = array();
	$dd_unique_test = TRUE;
	foreach ($form_struct as $fs) {
		if ($dd_unique_test && ($fs['fieldname'] != NULL) && (in_array($fs['fieldname'], $dd_unique_fields))) {
			$dd_unique_test = FALSE;
		} else {
			$dd_unique_fields[] = $fs['fieldname'];
		}
	}
	if (!$dd_unique_test) {
		echo '<p>*** ERROR - You have duplicate fieldnames in your form structure ***</p>';
	}


	// Was form submitted?

	if (isset($_POST["form_submitted_".$this->inst])) {

		$form_submitted = TRUE;

		$csv = "";

		$mail_message = "";
		$auto_reply_message = "";
		
		$orig_auto_reply_message = trim(get_option($this->var_pre . 'auto_reply_message'));

		// make correct encoding in auto - sokai - BEGIN
		$mime_boundary = md5(time());
		$auto_reply_message .= '--' . $mime_boundary . PHP_EOL;
		$auto_reply_message .= 'Content-Type: text/plain; charset="utf-8"' . PHP_EOL;
		$auto_reply_message .= 'Content-Transfer-Encoding: 8bit' . PHP_EOL . PHP_EOL;
		// make correct encoding in auto - sokai - END

		$sent_message = "";

		$attached_files = array();
		$attached_index = 0;

		$sel_recip = NULL;

		$message_structure = trim(get_option($this->var_pre . 'message_structure'));

		$auto_reply_name = trim(get_option($this->var_pre . 'auto_reply_name'));
		$auto_reply_email = trim(get_option($this->var_pre . 'auto_reply_email'));
		$auto_reply_subject = trim(get_option($this->var_pre . 'auto_reply_subject'));
		$auto_reply_message .= trim(get_option($this->var_pre . 'auto_reply_message'));
		

		$sent_message = trim(get_option($this->var_pre . 'sent_message'));


		unset($errors);
		$errors = array();


		if ($verify_method == 'recaptcha') {
			@include_once('recaptchalib.php');
			$privatekey = get_option('ddfm_re_private');
			$resp = recaptcha_check_answer ($privatekey,
                                $_SERVER["REMOTE_ADDR"],
                                $_POST["recaptcha_challenge_field"],
                                $_POST["recaptcha_response_field"]);
			if (!$resp->is_valid) {
				$errors[] = DDFM_INVALIDVER;
			}
		}


		// Get form input and put in array

		foreach ($_POST as $key => $i) {

			if ($key != "form_submitted") {
				if (!is_array($i)) {
					$form_input[strtolower($key)] = trim($i);
				} else {
					$form_input[strtolower($key)] = $i;
				}
			}

		}

		
		$msg_field_sep = ': ';
		$msg_field_line_end = "\n\n";

		$fsindex = -1;

		// Validate input
		foreach ($form_struct as $fs) {
		
			if (!isset($form_input[$fs['fieldname']])) {
				$form_input[$fs['fieldname']] = '';
			}

			$fsindex++;
			
			// check for fields used in vars
			if (isset($form_input[$fs['fieldname']])) {
				$sender_name = ddfm_str_replace($fs['fieldname'], ddfm_stripslashes($form_input[$fs['fieldname']]), $sender_name);
				$sender_email = ddfm_str_replace($fs['fieldname'], ddfm_stripslashes($form_input[$fs['fieldname']]), $sender_email);
				$email_subject = ddfm_str_replace($fs['fieldname'], ddfm_stripslashes($form_input[$fs['fieldname']]), $email_subject);
			}

			switch ($fs['type']) {

				case 'date':

					// type=date|class=|label=|fieldname=|req=(TRUEFALSE)

					$t = ddfm_stripslashes($form_input[$fs['fieldname']]);

					if ((strtolower($fs['req']) == 'true') && ($t == "")) { 

						$errors[] = DDFM_MISSINGFIELD . " '" . $fs['label'] . "'";

					} else if (ddfm_injection_chars($t)) {

						$errors[] = DDFM_INVALIDINPUT . " '" . $fs['label'] . "'";

					} 

					$csv .= str_replace($save_delimiter, ' ', $t) . $save_delimiter;
					$mail_message .= $fs['label'] . $msg_field_sep . $t . $msg_field_line_end;
					$message_structure = ddfm_str_replace($fs['fieldname'], $t, $message_structure);
					$auto_reply_message = ddfm_str_replace($fs['fieldname'], $t, $auto_reply_message);
					$sent_message = ddfm_str_replace($fs['fieldname'], ddfm_bsafe($t), $sent_message);

					break;
					
				case 'text':

					// type=text|class=|label=|fieldname=|max=|req=(TRUEFALSE)|[ver=]|[default=]

					$t = ddfm_stripslashes($form_input[$fs['fieldname']]);

					if ((strtolower($fs['req']) == 'true') && ($t == "")) { 

						$errors[] = DDFM_MISSINGFIELD . " '" . $fs['label'] . "'";

					} else if (strlen($t) > (int)$fs['max']) {

						$errors[] = $fs['max'] . ' ' . DDFM_MAXCHARLIMIT . " '" . $fs['label'] . "'";

					} else if (ddfm_injection_chars($t)) {

						$errors[] = DDFM_INVALIDINPUT . " '" . $fs['label'] . "'";

					} else	if ((strtolower($fs['ver']) == 'email') && ((strtolower($fs['req']) == "true") || ($t != ""))) {

						if (!dd_is_valid_email($t)) $errors[] = DDFM_INVALIDEMAIL . " '" . $fs['label'] . "'";

					} else if ((strtolower($fs['ver']) == 'url') && ((strtolower($fs['req']) == "true") || ($t != ""))) {

						if (!ddfm_is_valid_url($t)) $errors[] = DDFM_INVALIDURL . " '" . $fs['label'] . "'";

					} 

					$csv .= str_replace($save_delimiter, ' ', $t) . $save_delimiter;
					$mail_message .= $fs['label'] . $msg_field_sep . $t . $msg_field_line_end;
					$message_structure = ddfm_str_replace($fs['fieldname'], $t, $message_structure);
					$auto_reply_message = ddfm_str_replace($fs['fieldname'], $t, $auto_reply_message);
					$sent_message = ddfm_str_replace($fs['fieldname'], ddfm_bsafe($t), $sent_message);

					break;
					
				case 'password':

					// type=password|class=|label=|fieldname=|max=|req=(TRUEFALSE)|confirm=(TRUEFALSE)

					$t = ddfm_stripslashes($form_input[$fs['fieldname']]);

					if ((strtolower($fs['req']) == 'true') && ($t == "")) {

						$errors[] = DDFM_MISSINGFIELD . " '" . $fs['label'] . "'";
		
					} else if (strlen($t) > (int)$fs['max']) {

						$errors[] = $fs['max'] . ' ' . DDFM_MAXCHARLIMIT . " '" . $fs['label'] . "'";

					} else if (ddfm_injection_chars($t)) {

						$errors[] = DDFM_INVALIDINPUT . " '" . $fs['label'] . "'";

					} else if (strtolower($fs['confirm']) == 'true') {

						$tc = ddfm_stripslashes($form_input[$fs['fieldname']  . 'c']);
			
						if ($t != $tc) $errors[] = DDFM_NOMATCH . " '" . $fs['label'] . "'";

					}

					$csv .= str_replace($save_delimiter, ' ', $t) . $save_delimiter;
					$mail_message .= $fs['label'] . $msg_field_sep . $t . $msg_field_line_end;
					$message_structure = ddfm_str_replace($fs['fieldname'], $t, $message_structure);
					$auto_reply_message = ddfm_str_replace($fs['fieldname'], $t, $auto_reply_message);
					$sent_message = ddfm_str_replace($fs['fieldname'], ddfm_bsafe($t), $sent_message);

					break;

				case 'textarea':
				case 'widetextarea':
			
					// type=textarea|class=|label=|fieldname=|max=|rows=|req=(TRUEFALSE)|[default=]

					$t = ddfm_stripslashes($form_input[$fs['fieldname']]);

					if ((strtolower($fs['req']) == 'true') && ($t == "")) {

						$errors[] = DDFM_MISSINGFIELD . " '" . $fs['label'] . "'";

					} else if (strlen($t) > (int)$fs['max']) {
			
						$errors[] = $fs['max'] . ' ' . DDFM_MAXCHARLIMIT . " '" . $fs['label'] . "'";

					}

					$csv .= str_replace($save_delimiter, ' ', $t) . $save_delimiter;
					$mail_message .= $fs['label'] . $msg_field_sep . $t . $msg_field_line_end;
					$message_structure = ddfm_str_replace($fs['fieldname'], $t, $message_structure);
					$auto_reply_message = ddfm_str_replace($fs['fieldname'], $t, $auto_reply_message);
					$sent_message = ddfm_str_replace($fs['fieldname'], ddfm_bsafe($t), $sent_message);

					break;					

				case 'verify':

					// type=verify|class=|label=

					if ($verify_method == 'basic') {

						$t = ddfm_stripslashes($form_input['fm_verify']);

						if ($t == "") {

							$errors[] = DDFM_MISSINGVER;

						} else if (trim($_COOKIE["ddfmcode"]) == "") {

							$errors[] = DDFM_NOVERGEN;

						} else if ($_COOKIE["ddfmcode"] != md5(strtoupper($t))) { 

							$errors[] = DDFM_INVALIDVER;

						} 

					}

					break;

				case 'checkbox':

					//  type=checkbox|class=|label=|data=
					//	  (fieldname),(text),(CHECKED),(REQUIRED),
					//	  (fieldname),(text),(CHECKED),(REQUIRED),
					//	  (fieldname),(text),(CHECKED),(REQUIRED)

					// ### following three lines edited in order to have commas in the values, add by MG ###
					$fs['data'] = str_replace(",,", "C0mM@", $fs['data']);
					$data = explode(",", trim($fs['data']));
					$data = str_replace("C0mM@", ",", $data);

					$tmp_msg = array();
					
					$checkBoxChecked = false; //### added by MG ###

					for ($i = 0; $i < count($data); $i+=4) {

						$t = '';
						if (isset($form_input[$data[$i]])) {
							$t = ddfm_stripslashes(trim($form_input[$data[$i]]));
						}

						if ((strtolower($data[$i+3]) == 'true') && ($t == "")) {
							$errors[] = DDFM_MISSINGFIELD . " '" . $fs['label'] . "'";
						}

						if ($t != "") {
							$tmp_msg[] = $t;
							$checkBoxChecked = true; //### added by MG ###
						}

						$message_structure = ddfm_str_replace($data[$i], $t, $message_structure);
						$auto_reply_message = ddfm_str_replace($data[$i], $t, $auto_reply_message);
						$sent_message = ddfm_str_replace($data[$i], ddfm_bsafe($t), $sent_message);					
						
					}


					// ### start of changes by MG ###
					if ((strtolower($fs['req']) == 'true') && !$checkBoxChecked) { 
						$errors[] = DDFM_MISSINGFIELD . " '" . $fs['label'] . "'";
					}
					// ### end of changes by MG ###
					
					$csv .= str_replace($save_delimiter, ' ', implode(', ', $tmp_msg)) . $save_delimiter;
					$mail_message .= $fs['label'] . $msg_field_sep . implode(', ', $tmp_msg) . $msg_field_line_end;

					break;
				
				case 'radio':

					//  type=radio|class=|label=|fieldname=|req=|[default=]|data=
					//	  (text),(text),(text),(text)

					$t = ddfm_stripslashes(trim($form_input[$fs['fieldname']]));

					if ((strtolower($fs['req']) == 'true') && ($t == "")) {

						$errors[] = DDFM_MISSINGFIELD . " '" . $fs['label'] . "'";

					}
	
					$csv .= str_replace($save_delimiter, ' ', $t) . $save_delimiter;
					$mail_message .= $fs['label'] . $msg_field_sep . $t . $msg_field_line_end;
					$message_structure = ddfm_str_replace($fs['fieldname'], $t, $message_structure);
					$auto_reply_message = ddfm_str_replace($fs['fieldname'], $t, $auto_reply_message);
					$sent_message = ddfm_str_replace($fs['fieldname'], ddfm_bsafe($t), $sent_message);

					break;

				case 'select':

					//  type=select|class=|label=|fieldname=|multi=(TRUEFALSE)|data=
					//    (#group),(text),(text),(#group),(text),(text)

					$data = explode(",", trim($fs['data']));

					if (strtolower($fs['multi']) != 'true') {				

						$t = ddfm_stripslashes($form_input[$fs['fieldname']]);

						$first_item = $data[0];

						if ((strtolower($fs['req']) == 'true') && (($t == "") || ($t == $first_item))) {

							$errors[] = DDFM_MISSINGFIELD . " '" . $fs['label'] . "'";

						}
					
						$csv .= str_replace($save_delimiter, ' ', $t) . $save_delimiter;
						$mail_message .= $fs['label'] . $msg_field_sep . $t . $msg_field_line_end;
						$message_structure = ddfm_str_replace($fs['fieldname'], $t, $message_structure);
						$auto_reply_message = ddfm_str_replace($fs['fieldname'], $t, $auto_reply_message);
						$sent_message = ddfm_str_replace($fs['fieldname'], ddfm_bsafe($t), $sent_message);


					} else { // multi = true
	
						$t = (array)$form_input[$fs['fieldname']];

						if ((count($t) == 1) && ($t[0] == '')) {
							unset($t[0]);
						}

						if ((strtolower($fs['req']) == 'true') && (count($t) == 0)) {
							$errors[] = DDFM_MISSINGFIELD . " '" . $fs['label'] . "'";
						}
						
						$tmp_msg = array();

						foreach ($t as $tt) {
							if ($tt != "") $tmp_msg[] = $tt;
						}

						$csv .= str_replace($save_delimiter, ' ', implode(', ', $tmp_msg)) . $save_delimiter;
						$mail_message .= $fs['label'] . $msg_field_sep . implode(', ', $tmp_msg) . $msg_field_line_end;
						$message_structure = ddfm_str_replace($fs['fieldname'], implode(', ', $tmp_msg), $message_structure);
						$auto_reply_message = ddfm_str_replace($fs['fieldname'], implode(', ', $tmp_msg), $auto_reply_message);
						$sent_message = ddfm_str_replace($fs['fieldname'], ddfm_bsafe(implode(', ', $tmp_msg)), $sent_message);
				
					}

					break;

				case 'file':

					// type=file|class=|label=|fieldname=|[req=]|[allowed=1,2,3]

					if ((strtolower($fs['req']) == 'true') && (($_FILES[$fs['fieldname']]['name'] == ""))) { 
						$errors[] = DDFM_MISSINGFILE . " '" . $fs['label'] . "'";
					}
					

					$allowed = array();

					if (trim($fs['allowed']) != "") {
						$allowed = (array)explode(",", trim(strtolower($fs['allowed'])));
					}

					if (($_FILES[$fs['fieldname']]['name'] != "") && ((int)$_FILES[$fs['fieldname']]['size'] == 0)) {

							$errors[] = DDFM_FILETOOBIG . ' ' . $_FILES[$fs['fieldname']]['name'];

					} else if ($_FILES[$fs['fieldname']]['tmp_name'] != "") {

						if (($_FILES[$fs['fieldname']]['error'] == UPLOAD_ERR_OK) && ($_FILES[$fs['fieldname']]['size'] > 0)) {

							$origfilename = $_FILES[$fs['fieldname']]['name'];
							$filename = explode(".", $_FILES[$fs['fieldname']]['name']);
							$filenameext = $filename[count($filename) - 1];
							unset($filename[count($filename) - 1]);
							$filename = implode(".", $filename);
							$filename = substr($filename, 0, 15) . "." . $filenameext;
							$file_ext_allow = TRUE;

							if (count($allowed) > 0) {

								$file_ext_allow = FALSE;
								
								for ($x = 0; $x < count($allowed); $x++) { 
									if (strtolower($filenameext) == strtolower($allowed[$x])) {
										$file_ext_allow = TRUE;
									}
								} 
							}
							if ($file_ext_allow) {

								if((int)$_FILES[$fs['fieldname']]['size'] < $max_file_size) {

									$attached_files[$attached_index]['file'] = $_FILES[$fs['fieldname']]['name']; 
									$attached_files[$attached_index]['tmpfile'] = $_FILES[$fs['fieldname']]['tmp_name']; 
									$attached_files[$attached_index]['content_type'] = $_FILES[$fs['fieldname']]['type']; 
									$attached_index++;

									$csv .= str_replace($save_delimiter, ' ', $_FILES[$fs['fieldname']]['name']) . $save_delimiter;

									$attach_save = (bool)get_option($this->var_pre . 'attach_save');
									if (!$attach_save) {
										$mail_message .= DDFM_ATTACHED . $msg_field_sep . $_FILES[$fs['fieldname']]['name'] . $msg_field_line_end; 
									} else {
										$mail_message .= $fs['label'] . $msg_field_sep . $_FILES[$fs['fieldname']]['name'] . $msg_field_line_end;
									}

									$message_structure = ddfm_str_replace($fs['fieldname'], $_FILES[$fs['fieldname']]['name'], $message_structure);
									$auto_reply_message = ddfm_str_replace($fs['fieldname'], $_FILES[$fs['fieldname']]['name'], $auto_reply_message);
									$sent_message = ddfm_str_replace($fs['fieldname'], $_FILES[$fs['fieldname']]['name'], $sent_message);
									
								} else { 
									$errors[] = DDFM_FILETOOBIG . ' ' . $_FILES[$fs['fieldname']]['name'];
								}
							} else { 
								$errors[] = DDFM_INVALIDEXT . ' ' . $_FILES[$fs['fieldname']]['name'];
							}
						} else { 
							$errors[] = DDFM_UPLOADERR . ' ' . $_FILES[$fs['fieldname']]['name'];
						}
					} 

					/* handled above
					$csv .= str_replace($save_delimiter, ' ', $_FILES[$fs['fieldname']]['name']) . $save_delimiter;
					$mail_message .= $fs['label'] . $msg_field_sep . $_FILES[$fs['fieldname']]['name'] . $msg_field_line_end;
					$message_structure = ddfm_str_replace($fs['fieldname'], $_FILES[$fs['fieldname']]['name'], $message_structure);
					$auto_reply_message = ddfm_str_replace($fs['fieldname'], $_FILES[$fs['fieldname']]['name'], $auto_reply_message);
					$sent_message = ddfm_str_replace($fs['fieldname'], $_FILES[$fs['fieldname']]['name'], $sent_message);
					*/

					break;


				case 'selrecip':

					//  type=selrecip|class=|label=|data=(select),User1,user1@domain.com,User2 etc..

					$data = explode(",", trim($fs['data']));
					
					$t = ddfm_stripslashes($form_input['fm_selrecip']);

					if (($t == "") || ($t == $data[0])) {

						$errors[] = DDFM_MISSINGFIELD . " '" . $fs['label'] . "'";

					} else {

						for ($i = 1; $i < count($data); $i+=2) {

							if ($data[$i] == $t) {
								$sel_recip = trim($data[$i+1]);
							}
						}

					}	

					break;

			}


		}



		// make sure no un-used fieldnames are left in template
		foreach ($form_struct as $fs) {
			$message_structure = ddfm_str_replace($fs['fieldname'], '', $message_structure);
			$auto_reply_message = ddfm_str_replace($fs['fieldname'], '', $auto_reply_message);
			$sent_message = ddfm_str_replace($fs['fieldname'], '', $sent_message);
		}



		if (ddfm_injection_chars($sender_name)) $errors[] = DDFM_INVALIDINPUT;
		if (ddfm_injection_chars($sender_email)) $errors[] = DDFM_INVALIDINPUT;
		if (ddfm_injection_chars($email_subject)) $errors[] = DDFM_INVALIDINPUT;




		
		if ($errors) {

			$o .= '<div class="ddfmwrap"><div class="ddfmerrors">' . DDFM_ERRORMSG . '</div>';
			$o .= '<div class="errorlist">';
			foreach ($errors as $err) {
				$o .= $err . '<br />';
			}
			$o .= '</div><div style="clear:both;"><!-- --></div></div>';
	
		} else {

			if ($wrap_messages) {
				$mail_message = wordwrap($mail_message, 70);
			}

			if ($recipients == 'selrecip') {
				$recipients = $sel_recip;
			}
				
			// if template exists, use it instead
			if (strlen(trim($message_structure)) > 0) {
				$mail_message = $message_structure . "\n\n";
			}

			if ($show_ip_hostname) {
				$mail_message .= 'IP: ' . $_SERVER['REMOTE_ADDR'] . "\n" . 'HOST: ' . gethostbyaddr($_SERVER['REMOTE_ADDR']) . "\n\n";
			}			


			$sndmsg = TRUE;
			if (($save_to_file == TRUE) && ($save_email == FALSE)) {
				$sndmsg = FALSE;
			}
			
			$csv = str_replace("\n", $save_newlines, $csv);
			$csv = str_replace("\r", '', $csv);
			$csv = substr($csv, 0, strlen($csv) - strlen($save_delimiter));

			if (trim($save_timestamp) != '') {
				$csv = date($save_timestamp) . $save_delimiter . $csv;
			}

			if (is_writable($save_path)) {
				$handle = fopen($save_path, 'a+');
				fwrite($handle, $csv . "\n");
				fclose($handle);
			}

			$show_url = (bool)get_option($this->var_pre . 'show_url');
			if ($show_url == TRUE) {
				$mail_message .= "http://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
			}

			if ($sndmsg == TRUE) {	

				$attach_path = trim(get_option($this->var_pre . 'attach_path'));
				$attach_save = trim(get_option($this->var_pre . 'attach_save'));

				if (ddfm_send_mail($recipients, $sender_name, $sender_email, $email_subject, $mail_message, $attach_save, $attach_path, $attached_files)) {
		
					$o .= $sent_message;
	
					if (($orig_auto_reply_message != "") && (trim($sender_email != ""))) {
	
						$auto_reply_headers = '';

						$auto_reply_headers .= 'From: ' . $auto_reply_name . ' <' . $auto_reply_email . '>' . PHP_EOL;
						$auto_reply_headers .= 'Reply-To: ' . $auto_reply_name . ' <' . $auto_reply_email . '>' . PHP_EOL;
						$auto_reply_headers .= 'Return-Path: ' . $auto_reply_name . ' <' . $auto_reply_email . '>' . PHP_EOL;
						$auto_reply_headers .= "Message-ID: <" . time() . "ddfm@" . $_SERVER['SERVER_NAME'] . ">" . PHP_EOL;
						$auto_reply_headers .= 'X-Sender-IP: ' . $_SERVER["REMOTE_ADDR"] . PHP_EOL;
						$auto_reply_headers .= "X-Mailer: PHP v" . phpversion() . PHP_EOL;

						$auto_reply_headers .= 'MIME-Version: 1.0' . PHP_EOL;
						$auto_reply_headers .= 'Content-Type: multipart/related; boundary="' . $mime_boundary . '"';
						// $auto_reply_headers .= 'Content-Type: text/plain; charset=utf-8';
						// make correct encoding in auto - sokai - BEGIN
						//$auto_reply_message .= PHP_EOL . PHP_EOL;
						$auto_reply_message .= PHP_EOL . PHP_EOL . '--' . $mime_boundary . '--' . PHP_EOL . PHP_EOL;
						// make correct encoding in auto - sokai - END
			
						mail($sender_email, $auto_reply_subject, $auto_reply_message, $auto_reply_headers);
					}
	
	
					$message_sent = TRUE;
	
				} else {
					$o .= DDFM_SERVERERR;
					$message_sent = FALSE;
				}

			} else {

				$o .= $sent_message;

			}

		}


	} // end of form submission processing






	// Generate form if message has not been sent

	if (!$message_sent) {	


		if ($verify_method == 'basic' && !ddfm_check_gd_support()) {
			$o .= DDFM_GDERROR;
		}


		if (trim($manual_form_code) == '') {	// ** Use normal form generation

			$o .= '<div class="ddfmwrap">';
			$o .= '<form class="ddfm" method="post" action="' . $path_contact_page . '" enctype="multipart/form-data">' . "\n\n";
	
			// Loop through form items

			foreach ($form_struct as $f_i) {

				switch ($f_i['type']) {

					case 'html':			$o .= $this->ddfm_gen_html($f_i);			break;
					case 'date':			$o .= $this->ddfm_gen_date($f_i);			break;
					case 'text':			$o .= $this->ddfm_gen_text($f_i);			break;
					case 'password':		$o .= $this->ddfm_gen_password($f_i);		break;					
					case 'textarea':		$o .= $this->ddfm_gen_textarea($f_i);		break;
					case 'widetextarea':	$o .= $this->ddfm_gen_widetextarea($f_i);	break;
					case 'verify':			$o .= $this->ddfm_gen_verify($f_i);			break;
					case 'fullblock':		$o .= $this->ddfm_gen_fullblock($f_i);		break;
					case 'halfblock':		$o .= $this->ddfm_gen_halfblock($f_i);		break;
					case 'openfieldset':	$o .= $this->ddfm_gen_openfieldset($f_i);	break;
					case 'closefieldset':	$o .= $this->ddfm_gen_closefieldset($f_i);	break;
					case 'checkbox':		$o .= $this->ddfm_gen_checkbox($f_i);		break;
					case 'radio':			$o .= $this->ddfm_gen_radio($f_i);			break;
					case 'select':			$o .= $this->ddfm_gen_select($f_i);			break;
					case 'file':			$o .= $this->ddfm_gen_file($f_i);			break;				
					case 'selrecip':		$o .= $this->ddfm_gen_selrecip($f_i);		break;
				}
			}

			if ($verify_method == 'recaptcha') {

				$o .= "<script>
				var RecaptchaOptions = {
			    theme : 'white'
				};
				</script>";

				@include_once('recaptchalib.php');
				$publickey = get_option('ddfm_re_public');

				$o .= '<div class="recaptcha"><div class="recaptcha-inner">';
				$o .= recaptcha_get_html($publickey);
				$o .= '</div></div>';
			}

			$o .= "\n\n" . '<p><input type="hidden" name="MAX_FILE_SIZE" value="' . $max_file_size . '" /></p>' . "\n";

			$o .= '<div class="submit"><input type="submit" name="form_submitted_' . $this->inst . '" value="' . DDFM_SUBMITBUTTON . '" /></div>' . "\n\n";
	
			$o .= '<div class="credits">' . DDFM_CREDITS . ' <a href="http://www.dagondesign.com" title="Dagon Design">Dagon Design</a></div>' . "\n\n";
	
			$o .= '</form>';
			$o .= '</div>' . "\n\n";

		} else { 
			// Use manual form code

			$o .= stripslashes($manual_form_code);

		}

		// Form generation complete

	} // end of display form code




	$o .= '<!-- END of Dagon Design Formmailer output -->' . "\n\n\n";

	return $o;


}

} /* end of class */







/* Load CSS into WP header */
add_action('wp_head', 'ddfm_add_css');

function ddfm_add_css() {
	echo "\n" . '<link rel="stylesheet" href="' . rtrim(get_settings('siteurl'), '/') . '/wp-content/plugins/dd-formmailer/dd-formmailer.css" type="text/css" media="screen" />' . "\n";
	echo "\n" . '<script type="text/javascript" src="' . rtrim(get_settings('siteurl'), '/') . '/wp-content/plugins/dd-formmailer/date_chooser.js"></script>' . "\n";
}






/* Show main options page */
add_action('admin_menu', 'ddfm_add_main_options');


function ddfm_add_main_options() {
	global $ddfm_version;
	if (function_exists('add_options_page')) {
			add_options_page("Dagon Design Form Mailer v{$ddfm_version} - Main", 
			"DDFM-Main", 8, "DDFM-Main", 'ddfm_main_options');
	}		
}



/* Global options page */
function ddfm_main_options() {

	global $ddfm_version;


	if (isset($_POST['set_defaults'])) {

		echo '<div id="message" class="updated fade"><p><strong>';

		update_option('ddfm_instances', 1);
		update_option('ddfm_verify_method', 'basic');

		update_option('ddfm_verify_background', 'F0F0F0');
		update_option('ddfm_verify_text', '005ABE');
		update_option('ddfm_force_type', '');

		update_option('ddfm_re_public', '');
		update_option('ddfm_re_private', '');

		echo 'Default Options Loaded!';

		echo '</strong></p></div>';

	} else if (isset($_POST['info_update'])) {

		echo '<div id="message" class="updated fade"><p><strong>';

		update_option('ddfm_instances', (int)$_POST['ddfm_instances']);
		update_option('ddfm_verify_method', (string)$_POST['verify_method']);

		update_option('ddfm_verify_background', (string)$_POST['verify_background']);
		update_option('ddfm_verify_text', (string)$_POST['verify_text']);
		update_option('ddfm_force_type', (string)$_POST['force_type']);

		update_option('ddfm_re_public', (string)$_POST['re_public']);
		update_option('ddfm_re_private', (string)$_POST['re_private']);

		echo 'Configuration Updated!';

		echo '</strong></p></div>';

	}

	?>
	<div class="wrap">
	<h2>Dagon Design Form Mailer v<?php echo $ddfm_version; ?></h2>

	<p>For information and updates, please visit:<br />
	<a href="http://www.dagondesign.com/articles/secure-form-mailer-plugin-for-wordpress/">http://www.dagondesign.com/articles/secure-form-mailer-plugin-for-wordpress/</a></p>

	<form method="post" action="<?php echo $_SERVER["REQUEST_URI"]; ?>">
	<input type="hidden" name="info_update" id="info_update" value="true" />


 
	<h3>Global Settings</h3>

	<p>This is where you specify how many instances of the formmailer you want to use, as well as a few options that 
		apply to all instances. For every instance created, you will see a new sub-panel under the options page. Example: DDFM1, DDFM2, etc.
		To use a particular instance on your page, use the trigger text followed by the instance number. Example: <b>&lt;!-- ddfm1 --&gt;</b></p>

	<table class="form-table">

	<tr valign="top">
	<th scope="row">Instances</th>
	<td>
		<input name="ddfm_instances" type="text" id="ddfm_instances" value="<?php echo trim(get_option('ddfm_instances')); ?>" size="3" /> The number of contact forms this plugin will generate
	</td>
	</tr>

	<tr valign="top">
	<th scope="row">Current Instances</th>
	<td>
	This is a listing of your current form instances, along with their description. Click to edit the options page for each instance.<br />
	<?php 
		$url = "http://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
		$url = str_replace("=DDFM-Main", "=", $url);
		$count = trim(get_option('ddfm_instances'));
		for ($i = 1; $i <= $count; $i++) {
			echo '<a href="' . $url . 'DDFM' . $i . '">DDFM' . $i . '</a> - ' . get_option('ddfm' . $i . '_desc') . '<br />';
		}
	?>
	</td>
	</tr>


	<tr valign="top">
	<th scope="row">Image Verification Method</th>
	<td>
		<input name="verify_method" type="radio" value="off" <?php if (get_option('ddfm_verify_method') == "off") echo "checked='checked'"; ?> /> Disabled (no image verification)<br />
		<input name="verify_method" type="radio" value="basic" <?php if (get_option('ddfm_verify_method') == "basic") echo "checked='checked'"; ?> /> Basic (simple built-in method - requires GD support)<br />
		<input name="verify_method" type="radio" value="recaptcha" <?php if (get_option('ddfm_verify_method') == "recaptcha") echo "checked='checked'"; ?> /> ReCaptcha (requires free <a href="http://recaptcha.net" target="_blank">ReCaptcha</a> account - <b>Enter codes below!</b>)<br />
	</td>	
	</tr>


	<tr valign="top">
	<th scope="row">Basic Method Options</th>
	<td>
		<input name="verify_background" type="text" size="8" value="<?php echo trim(get_option('ddfm_verify_background')); ?>" /> 
		 Hex color code for verification image background
		<br />
		<input name="verify_text" type="text" size="8" value="<?php echo trim(get_option('ddfm_verify_text')); ?>" /> 
		 Hex color code for verification image text
		<br /><br />
		<strong>Force Image Type:</strong>  &nbsp;
		<input name="force_type" type="radio" value="" <?php if (get_option('ddfm_force_type') == "") echo "checked='checked'"; ?> /> Automatic &nbsp;&nbsp;
		<input name="force_type" type="radio" value="jpeg" <?php if (get_option('ddfm_force_type') == "jpeg") echo "checked='checked'"; ?> /> jpeg &nbsp;&nbsp;
		<input name="force_type" type="radio" value="gif" <?php if (get_option('ddfm_force_type') == "gif") echo "checked='checked'"; ?> /> gif &nbsp;&nbsp;
		<input name="force_type" type="radio" value="png" <?php if (get_option('ddfm_force_type') == "png") echo "checked='checked'"; ?> /> png &nbsp;&nbsp;
	</td>	
	</tr>


	<tr valign="top">
	<th scope="row">ReCaptcha Method Options</th>
	<td>
		<input name="re_public" type="text" size="55" value="<?php echo trim(get_option('ddfm_re_public')); ?>" /> 
		Public Key
		<br />
		<input name="re_private" type="text" size="55" value="<?php echo trim(get_option('ddfm_re_private')); ?>" /> 
		Private Key	</td>	
	</tr>



	</table>


	<div class="submit">
		<input type="submit" name="set_defaults" value="<?php _e('Load Default Options'); ?> &raquo;" />
		<input type="submit" name="info_update" value="<?php _e('Update options'); ?> &raquo;" />
	</div>
	</form>

	</div>
	<?php
}




/* Initialize instances */
for ($i = 1; $i <= (int)get_option('ddfm_instances'); $i++) {
	$ddfm{$i} = &New ddfmClass($i, $ddfm_version); 
}


?>