<?php

	error_reporting(E_ALL);
	
	date_default_timezone_set('UTC');
	
	function pvs_encode ($orig,$key = 'a') {
		$codetable = Array('a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','q','r','s','t','u','v','w','x','y','z','A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z');
		
		for ($i = 0; $i < 10; $i++) {
			$codetable[] = ''.$i;
		}
		$codetable[] = '_';
		$codetable[] = ':';
		$codetable[] = '.';
		$codetable[] = ',';
		$codetable[] = '+';
		$codetable[] = '-';
		$codetable[] = '=';
		$codetable[] = '|';
		$codetable[] = '~';
		$codetable[] = '*';
		$codetable[] = ';';
		
		$codetable2 = array_flip($codetable);
		
		$keyAtemp = str_split($key);
		
		$origA = str_split($orig); $keyA = $origA;
		
		for ($i = 0; $i < strlen($orig); $i++) {
			if (!isset($codetable2[$origA[$i]])) {
				$origA[$i] = 62;
			} else {
				$origA[$i] = $codetable2[$origA[$i]];
			}
		
			$j = bcmod($i,strlen($key));
			
			if (!isset($codetable2[$keyAtemp[$j]])) {
				$keyA[$i] = 0;
			} else {
				$keyA[$i] = $codetable2[$keyAtemp[$j]];
			}
		}
		
		$ret = '';
		
		for ($i = 0; $i < count($origA); $i++) {
			$charcode = bcmod(($origA[$i] + $keyA[$i]),count($codetable)); 
			$ret .= $codetable[$charcode];
		}
		
		return $ret;
	
	}
	
	$reqDate = getdate($_SERVER['REQUEST_TIME']);
	
	$reqHour = gmmktime($reqDate['hours'],0,0,$reqDate['mon'],$reqDate['mday'],$reqDate['year']).'000';
	
	$protocol = explode('/',$_SERVER['SERVER_PROTOCOL']);
	$protocol = $protocol[0];
	$protocol = strtolower($protocol);
		
	$auth_compare = pvs_encode(pvs_encode($reqHour,'pvsXHRClient'),$protocol.'://'.$_SERVER['HTTP_HOST']);
	$auth_compare2 = pvs_encode(pvs_encode($reqHour,'ResponsepvsXHRClient'),$protocol.'://'.$_SERVER['HTTP_HOST']);	
		
	//Der direkte Aufruf ist nicht erwünscht, Aufruf nur über XHR
	if (!isset($_GET['auth'])) {	
		header("HTTP/1.0 403 Forbidden");
		die('Connection refused!');
	} elseif ($_GET['auth'] != $auth_compare AND $_GET['auth'] != $auth_compare2) {
		header("HTTP/1.0 403 Forbidden");
		die('Connection refused!');
	}
	
	//Stelle sicher, dass wichtige Dateien existieren
	if (!is_file('VERSION')) { file_put_contents('VERSION','1.0'); }
	
	if ($_GET['auth'] == $auth_compare) {
	
		$globSet = parse_ini_file('settings.ini');
		$school_name = (isset($globSet['school_name']))?$globSet['school_name']:'Musterschule Musterstadt';
		$school_address = Array();
		if (isset($globSet['school_address'])) {
			if (is_array($globSet['school_address'])) {
				$school_address = $globSet['school_address'];
			} else {
				$school_address[] = $globSet['school_address'];
			}
		} else {
			$school_address[] = 'Musterschule Musterstadt, Schule für Unbegabte, Mörikeweg 110, 90432 Musterstadt';
			$school_address[] = 'Tel. 09843/55515 - Fax 09843/55516 - EMail Musterschule@Musterstadt.de - Internet ms.musterstadt.de';
		}
		
		unset($globSet);
		
		header('Content-Type: application/xml');
	
		echo '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>';
		echo '<info>';
		echo '<version>'.file_get_contents('VERSION').'</version>';
		echo '<sname>'.$school_name.'</sname>';
		echo '<saddr>'.implode(' | ',$school_address).'</saddr>';
		echo '</info>';
	
		die();
	
	} elseif ($_GET['auth'] == $auth_compare2) {
	
		file_put_contents('./update.zip',convert_uudecode($HTTP_RAW_POST_DATA));

		$zip = new ZipArchive();
		
		$zip = new ZipArchive;
		if ($zip->open('./update.zip') === TRUE) {
			$zip->extractTo('./');
			$zip->close();
			die('Update erfolgreich ausgeführt!');
		} else {
			header("HTTP/1.0 500 Internal Server Error");
			die('Update fehlgeschlagen!');
		}

		
	} else {
		header("HTTP/1.0 403 Forbidden");
		die('Connection refused!');
	}

?>