<?php

// Copyright © 2003 Frédéric Jaqcuot
// Copyright © 2004 Georges Khaznadar (password encryption, images)
// Copyright © 2007-2008 Johan Cwiklinski
//
// This file is part of Galette (http://galette.tuxfamily.org).
//
// Galette is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Galette is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
//  GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Galette. If not, see <http://www.gnu.org/licenses/>.

/**
 * Utilities functions
 *
 * @package Galette
 * 
 * @author     Frédéric Jaqcuot
 * @copyright  2003 Frédéric Jaqcuot
 * @copyright  2004 Georges Khaznadar (password encryption, images)
 * @copyright  2007-2008 Johan Cwiklinski
 * @license    http://www.gnu.org/licenses/gpl-3.0.html GPL License 3.0 or (at your option) any later version
 * @version    $Id$
 */

function makeRandomPassword($size){
  $pass = "";
  $salt = "abcdefghjkmnpqrstuvwxyz0123456789";
  srand((double)microtime()*1000000);
  $i = 0;
  while ($i <= $size-1){
    $num = rand() % 33;
    $tmp = substr($salt, $num, 1);
    $pass = $pass . $tmp;
    $i++;
  }
  return $pass;
}

function PasswordImageName($c){
  return "pw_".md5($c).".png";
}

function PasswordImageClean(){
  // cleans any password image file older than 1 minute
  $dh=@opendir("photos");
  while($file=readdir($dh)){
    if (substr($file,0,3)=="pw_" &&
        time() - filemtime("photos/".$file) > 60) {
      unlink("photos/".$file);
    }
  }
}

function PasswordImage(){
  // outputs a png image for a random password
  // and a crypted string for it. The filename
  // for this image can be computed from the crypted
  // string by PasswordImageName.
  // the retrun value is just the crypted password.

  PasswordImageClean(); // purges former passwords
  $mdp=makeRandomPassword(7);
  $c=crypt($mdp);
  $png= imagecreate(10+7.5*strlen($mdp),18);
  $bg= imagecolorallocate($png,160,160,160);
  imagestring($png, 3, 5, 2, $mdp, imagecolorallocate($png,0,0,0));
	$file = STOCK_FILES."/".PasswordImageName($c);

	//TODO:2 lines below is useless but necessary by a bug in php-gd(http://bugs.php.net/bug.php?id=35246)
	$fh=fopen($file,'w');
	fclose($fh);

  imagepng($png,$file);
  // The perms of the file can be wrong, correct it
  // WARN : chmod() can be desacivated (i.e. : Free/Online)
  @chmod($file, 0644);
  return $c;
}

function PasswordCheck($pass,$crypt){
  return crypt($pass,$crypt)==$crypt;
}

function print_img($img) {
	$file = STOCK_FILES."/".$img;
	$image_type = false;
	if(function_exists('exif_imagetype')) {
		$image_type = exif_imagetype($file);
	} else {
		$image_size = getimagesize($file);
		if(is_array($image_size) && isset($image_size[2])) $image_type = $image_size[2];
	}
	if( $image_type ) {
		return $file;
	}
}


function isSelected($champ1, $champ2) {
  if ($champ1 == $champ2) {
    echo " selected";
  }
}

function isChecked($champ1, $champ2) {
  if ($champ1 == $champ2) {
    echo " checked";
  }
}

function txt_sqls($champ) {
  return "'".str_replace("'", "\'", str_replace('\\', '', $champ))."'";
}

function is_valid_web_url($url) {
  return (preg_match('#^http[s]?\\:\\/\\/[a-z0-9\-]+\.([a-z0-9\-]+\.)?[a-z]+#i', $url));
}

/*
 *
 * is_valid_email(): an e-mail validation utility routine
 * Version 1.1.1 -- September 10, 2000
 *
 * Written by Michael A. Alderete
 * Please send bug reports and improvements to: <michael@aldosoft.com>
 *
 * This function matches a proposed e-mail address against a validating
 * regular expression. It's intended for use in web registration systems
 * and other places where the user is inputting their e-mail address and
 * you want to check that it's OK.
 *
 */

function is_valid_email ($address) {
  return (preg_match(
                     '/^[-!#$%&\'*+\\.\/0-9=?A-Z^_`{|}~]+'.   // the user name
                     '@'.                                     // the ubiquitous at-sign
                     '([-0-9A-Z]+\.)+' .                      // host, sub-, and domain names
                     '([0-9A-Z]){2,4}$/i',                    // top-level domain (TLD)
                     trim($address)));
}

function resizeimage($img,$img2,$w,$h)
{
	/** FIXME: Can GD not be present ? */
	if(function_exists("gd_info"))
	{
		$ext = substr($img,-4);
		$gdinfo = gd_info();
		switch(strtolower($ext))
		{
			case '.jpg':
				if (!$gdinfo['JPEG Support'])
					return false;
				break;
			case '.png':
				if (!$gdinfo['PNG Support'])
					return false;
				break;
			case '.gif':
				if (!$gdinfo['GIF Create Support'])
					return false;
				break;
			default:
				return false;
		}

		list($cur_width, $cur_height, $cur_type, $curattr) = getimagesize($img);

		$ratio = $cur_width / $cur_height;

		// calculate image size according to ratio
		if ($cur_witdh>$cur_height)
			$h = $w/$ratio;
		else
			$w = $h*$ratio;

		$thumb = imagecreatetruecolor ($w, $h);
		switch($ext)
		{
			case ".jpg":
				$image = ImageCreateFromJpeg($img);
				imagecopyresized ($thumb, $image, 0, 0, 0, 0, $w, $h, $cur_width, $cur_height);
				imagejpeg($thumb, $img2);
				break;
			case ".png":
				$image = ImageCreateFromPng($img);
				imagecopyresized ($thumb, $image, 0, 0, 0, 0, $w, $h, $cur_width, $cur_height);
				imagepng($thumb, $img2);
				break;
			case ".gif":
				$image = ImageCreateFromGif($img);
				imagecopyresized ($thumb, $image, 0, 0, 0, 0, $w, $h, $cur_width, $cur_height);
				imagegif($thumb, $img2);
				break;
		}
	}
}

function custom_html_entity_decode( $given_html, $quote_style = ENT_QUOTES )
{
  $trans_table = array_flip(get_html_translation_table( HTML_ENTITIES, $quote_style ));
  $trans_table['&#39;'] = "'";
  return ( strtr( $given_html, $trans_table ) );
}

//sanityze fields
//TODO better handling (replace bad string not just detect it)
function sanityze_mail_headers($field) {
	$result = 0;
	if ( stripos("\r",$field)!==false || stripos("\n",$field)!==false ) {
		 $result = 0;
	} else {
		$result = 1;
	}
	return $result;
}

//TODO better handling (replace bad string not just detect it)
function sanityze_superglobals_arrays() {
	$errors = 0;
	foreach($_GET as $k => $v) {
		if (stripos("'",$v)!==false || stripos(";",$v)!==false || stripos("\"",$v)!==false ) {
			 $errors++;
		}
	}
	foreach($_POST as $k => $v) {
		if (stripos("'",$v)!==false || stripos(";",$v)!==false || stripos("\"",$v)!==false ) {
			 $errors++;
		}
	}
	return $errors;
}

function custom_mail($email_to,$mail_subject,$mail_text, $content_type="text/plain"){
	// codes retour :
	//  0 - error mail()
	//  1 - mail sent
	//  2 - mail desactived in preferences
	//  3 - bad configuration ?
	//  4 - SMTP unreacheable
	//  5 - breaking attempt
	$result = 0;

	//Strip slashes if magic_quotes_gpc is enabled
	//Fix bug #9705
	if(get_magic_quotes_gpc()){
		$mail_subject = stripslashes($mail_subject);
		$mail_text = stripslashes($mail_text);
	}

	//sanityze headers
	$params = array(
			$email_to,
			$mail_subject,
			//mail_text
			$content_type
	);
	
	foreach ($params as $param) {
		if( ! sanityze_mail_headers($param) ) {
			return 5;
			break;
		}
	}

	// Headers :

	// Add a Reply-To field in the mail headers.
	// Fix bug #6654.
	if ( PREF_EMAIL_REPLY_TO )
		$reply_to = PREF_EMAIL_REPLY_TO;
	else
		$reply_to = PREF_EMAIL;

	$headers = array(
			"From: ".PREF_EMAIL_NOM." <".PREF_EMAIL.">",
			"Message-ID: <".makeRandomPassword(16)."-galette@".$_SERVER['SERVER_NAME'].">",
			"Reply-To: <".$reply_to.">",
			"X-Sender: <".PREF_EMAIL.">",
			"Return-Path: <".PREF_EMAIL.">",
			"Errors-To: <".PREF_EMAIL.">",
			"X-Mailer: Galette-".GALETTE_VERSION,
			"X-Priority: 3",
			"Content-Type: $content_type; charset=utf-8"
	);

	switch (PREF_MAIL_METHOD){
		case 0:
			$result = 2;
			break;
		case 1:
			$mail_headers = "";
			foreach($headers as $oneheader)
				$mail_headers .= $oneheader . "\r\n";
			//-f .PREF_EMAIL is to set Return-Path
			//if (!mail($email_to,$mail_subject,$mail_text, $mail_headers,"-f ".PREF_EMAIL))
			//set Return-Path
			//seems to does not work
			ini_set('sendmail_from', PREF_EMAIL);
			if (!mail($email_to,$mail_subject,$mail_text, $mail_headers)) {
				$result = 0;
			} else {
				$result = 1;
			}
			break;
		case 2:
			// $toArray format --> array("Name1" => "address1", "Name2" => "address2", ...)

			//set Return-Path
			ini_set('sendmail_from', PREF_EMAIL);
			$errno = "";
			$errstr = "";
			if (!$connect = fsockopen (PREF_MAIL_SMTP, 25, $errno, $errstr, 30))
				$result = 4;
			else{
				$rcv = fgets($connect, 1024);
				fputs($connect, "HELO {$_SERVER['SERVER_NAME']}\r\n");
				$rcv = fgets($connect, 1024);
				fputs($connect, "MAIL FROM:".PREF_EMAIL."\r\n");
				$rcv = fgets($connect, 1024);
				fputs($connect, "RCPT TO:".$email_to."\r\n");
				$rcv = fgets($connect, 1024);
				fputs($connect, "DATA\r\n");
				$rcv = fgets($connect, 1024);
				foreach($headers as $oneheader)
					fputs($connect, $oneheader."\r\n");
				fputs($connect, stripslashes("Subject: ".$mail_subject)."\r\n");
				fputs($connect, "\r\n");
				fputs($connect, stripslashes($mail_text)." \r\n");
				fputs($connect, ".\r\n");
				$rcv = fgets($connect, 1024);
				fputs($connect, "RSET\r\n");
				$rcv = fgets($connect, 1024);
				fputs ($connect, "QUIT\r\n");
				$rcv = fgets ($connect, 1024);
				fclose($connect);
				$result = 1;
			}
			break;
		default:
			$result = 3;
		}
	return $result;
}

function UniqueLogin($DB,$l) {
  $result = $DB->Execute("SELECT * FROM ".PREFIX_DB."adherents
                          WHERE login_adh='".addslashes($l)."'");
  return ($result->RecordCount() == 0);
}

function date_db2text($date) {
	if ($date != '')
	{
		list($a,$m,$j)=explode("-",$date);
		$date="$j/$m/$a";
	}
	return $date;
}

function date_text2db($DB, $date) {
	list($j, $m, $a)=explode("/",$date);
	if (!checkdate($m, $j, $a))
		return "";
	return $DB->DBDate($a.'-'.$m.'-'.$j);
}

function distance_months($beg, $end) {
	list($bj, $bm, $ba) = explode("/", $beg);
	list($ej, $em, $ea) = explode("/", $end);
	if ($bm > $em) {
		$em += 12;
		$ea--;
	}
	return ($ea -$ba)*12 + $em - $bm;
}

function beg_membership_after($date) {
	$beg = "";
	if (PREF_BEG_MEMBERSHIP != "") {
		list($j, $m) = explode("/", PREF_BEG_MEMBERSHIP);
		$y = strftime("%Y");
		while (mktime(0, 0, 0, $m, $j, $y) <= $date)
			$y++;
		$beg = $j."/".$m."/".$y;
	}
	return $beg;
}

function get_form_value($name, $defval)
{
	$val = $defval;
	if (isset($_GET[$name]))
		$val = $_GET[$name];
	elseif (isset($_POST[$name]))
		$val = $_POST[$name];
	return $val;
}

function get_numeric_form_value($name, $defval)
{
	$val = get_form_value($name, $defval);
	if (!is_numeric($val))
		$val = '';
	return $val;
}

function get_numeric_posted_value($name, $defval) {
	if (isset($_POST[$name])) {
		$val = $_POST[$name];
		if (is_numeric($val))
			return $val;
	}
	return $defval;
}

/**
* Is a string seems to be UTF-8 one ?
*
* @param $Str string: string to analyze
* @return  boolean
* @author GLPI
**/
function seems_utf8($Str) {
	for ($i=0; $i<strlen($Str); $i++) {
		if (ord($Str[$i]) < 0x80) continue; # 0bbbbbbb
		elseif ((ord($Str[$i]) & 0xE0) == 0xC0) $n=1; # 110bbbbb
		elseif ((ord($Str[$i]) & 0xF0) == 0xE0) $n=2; # 1110bbbb
		elseif ((ord($Str[$i]) & 0xF8) == 0xF0) $n=3; # 11110bbb
		elseif ((ord($Str[$i]) & 0xFC) == 0xF8) $n=4; # 111110bb
		elseif ((ord($Str[$i]) & 0xFE) == 0xFC) $n=5; # 1111110b
		else return false; # Does not match any model
		for ($j=0; $j<$n; $j++) { # n bytes matching 10bbbbbb follow ?
			if ((++$i == strlen($Str)) || ((ord($Str[$i]) & 0xC0) != 0x80))
				return false;
		}
	}
	return true;
}
?>
