<?php


	//error_reporting(0);
	error_reporting(E_ALL);
	
	session_start();
	
	//FPDF-Library
	require('./fpdf17/fpdf.php');
	
	$output_pdf = null;
	$html_override = false;
	
	$output_before = '';
	$output_after = '';

	$output_before .= '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"';
       "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
	$output_before .= '<html xmlns="http://www.w3.org/1999/xhtml">';
	$output_before .= '<head>';
	$output_before .= '<title>Projektverteilungssystem für Schulen, (C) 2013, Janosch Zoller</title>';
	$output_before .= '<meta name="author" content="Janosch Zoller"/>';
	$output_before .= '<meta http-equiv="content-style-type" content="text/css"/>';
	$output_before .= '<meta http-equiv="content-type" content="text/html; charset=UTF-8"/>';
	$output_before .= '<link href="/pvs/style.css" rel="stylesheet" type="text/css" />';
	$output_before .= '<link href="/pvs/vorlagen/favicon.ico" rel="shortcut icon" type="image/x-icon">';
	$output_before .= '<script type="text/javascript" src="/pvs/tools.js"></script>';
	$output_before .= '</head>';
	$output_before .= '<body>';
	$output_before .= '<div id="container">';
	$output_before .= '<div id="inhalt">';
	$output_before .= '<div id="logobar" class="noprint">';
	$output_before .= '<p style="float:right;"><img src="/pvs/vorlagen/pvs.png" title="Projektverteilungssystem für Schulen, (C) Janosch Zoller" alt="Logo PVS" /></p>';
	$output_before .= '<h1 id="top">Projektverteilungssystem für Schulen</h1>';
	$output_before .= '<p>Automatische Schülerverteilung auf Projekte. &emsp; &emsp; &emsp; &copy; 2010-2013, Janosch W. Zoller</p>';
	$output_before .= '</div>';

	$output_before .= '<div id="jsWarning" class="fieldset" style="background-color:#f00;">';
	$output_before .= '<h2>JavaScript erlauben!</h2>';
	$output_before .= '<p style="color:white">Das System hat festgestellt, dass die Verwendung von JavaScript von Ihrem Browser unterdrückt wird. Um das Projektverteilungssystem für Schulen nutzen zu können, müssen Sie JavaScript aktivieren. Laden Sie danach die Seite neu. Kontaktieren Sie bei Problemen den Systemadministrator. <br/> Diese Fehlermeldung kann auch auftreten, wenn die Seite noch nicht komplett geladen ist. Warten Sie in diesem Fall ab, bis das Laden der Seite abgeschlossen ist.</p>';
	$output_before .= '</div>';
	
	$output_before .= '<div id="phpContainer" style="display:none">';

	$output_after .= '</div>';
	$output_after .= '<div id="bottom">';
	$output_after .= '<p style="float:right"><a href="./?special=changelog">Änderungsprotokoll</a> | <a href="./?special=impressum">Impressum und Support</a> | <a href="./?special=manuals">Handbücher</a> | <a href="./?special=license">Lizenz</a></p>';
	$output_after .= '<p>PVS für Schulen. Version {--input-vn--} &nbsp; - &nbsp; Lizenziert für {--input-sn--}</p>';
	$output_after .= '</div>';
	$output_after .= '</div>';
	$output_after .= '</div>';
	$output_after .= '</body>';
	$output_after .= '</html>';

	// to do:
	
	//	-	Zeitgesteuerter Logout mit entsprechender Anzeige
	//  -	Schnittstellenauswahl bei Eventerstellung
	//allgemeine Funktionen
	include('./functions.php');
	
	//Definition von Konstanten und Variablen, Einladen von Dateien:
	
	if (isset($_GET['logout']) AND $_GET['logout']) {
		session_destroy();
		unset($_SESSION['pvs_pw']);
		unset($_SESSION['pvs_auth']);
		unset($_SESSION['pvs_db']);
		unset($_SESSION['pvs_time']);
	}
	
	if (isset($_POST['password']) AND isset($_POST['auth']) AND isset($_POST['db'])) {
		$_SESSION['pvs_db'] = $_POST['db'];
		$_SESSION['pvs_auth'] = $_POST['auth'];
		$_SESSION['pvs_pw'] = $_POST['password'];
		$_SESSION['pvs_time'] = time();
	}
	
	//globale Einstellungen einlesen
	$globSet = parse_ini_file('settings.ini');
	
	$school_logo = (isset($globSet['school_logo']) AND is_file($globSet['school_logo']))?$globSet['school_logo']:'./vorlagen/schullogo.png';
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
	
	$pdfOptions = Array('pagecount'=>false);
	
	//Ansichtseinstellungen
	{
		$GLOBALS['views'] = Array();
		
		$GLOBALS['views']['cat'] = Array();
		$GLOBALS['views']['cat']['ov'] = Array(
			'ov_students' => Array('vcl'=>'pClass','filters'=>Array('class'),'pref'=>'Übersicht Klasse ','postf'=>''),
			'ov_teachers' => Array('vcl'=>'pTeach','filters'=>Array(),'pref'=>'Übersicht Lehrkräfte','postf'=>''),
			'ov_offers' => Array('vcl'=>'pOv','filters'=>Array(),'pref'=>'Übersicht Angebote','postf'=>''),
			'ov_autofill' => Array('vcl'=>'pAllOff','filters'=>Array(),'pref'=>'Übersicht Schülerverteilung','postf'=>''),
			'ov_payments' => Array('vcl'=>'pAllClass','filters'=>Array(),'pref'=>'Übersicht Zahlungen (Vorauskasse)','postf'=>'')
		);
		$GLOBALS['views']['cat']['ed'] = Array(
			'edit_students' => Array('vcl'=>'pClass','filters'=>Array('class'),'pref'=>'Bearbeitung Klasse ','postf'=>' (und Schüler erstellen bzw. löschen)'),
			'edit_payments' => Array('vcl'=>'pClass','filters'=>Array('class'),'pref'=>'Bezahlte Teilnehmerbeiträge für Klasse ','postf'=>' eintragen'),
			'add_class' => Array('vcl'=>'pAllClass','filters'=>Array(),'pref'=>'Klasse hinzufügen','postf'=>''),
			'manage_classes' => Array('vcl'=>'pAllClass','filters'=>Array(),'pref'=>'Klassen aktivieren und deaktivieren (löschen)','postf'=>''),
			'edit_teachers' => Array('vcl'=>'pTeach','filters'=>Array(),'pref'=>'Bearbeitung Lehrer, Lehrer hinzufügen und löschen','postf'=>''),
			'edit_offer' => Array('vcl'=>'pOffer','filters'=>Array('offer'),'pref'=>'Bearbeitung Angebotsdaten ','postf'=>' (und Angebot löschen sowie Teilnahmestatus eintragen)'),
			'add_offer' => Array('vcl'=>'pAllOff','filters'=>Array(),'pref'=>'Angebot hinzufügen','postf'=>''),
			'edit_buses' => Array('vcl'=>'pAllOff','filters'=>Array(),'pref'=>'Bearbeitung Busliste und Fahrgemeinschaften','postf'=>'')
		);
		$GLOBALS['views']['cat']['pr'] = Array(
			'pdf_classteacher_wishlist' => Array('vcl'=>'pClass','filters'=>Array('class'),'pref'=>'Klassenliste zum Wünsche ausfüllen für Klasse ','postf'=>''),
			'pdf_offerprizelist_class' => Array('vcl'=>'pClass','filters'=>Array('class'),'pref'=>'Angebotszuordnung und Preisliste für Klasse ','postf'=>''),
			'pdf_busofferlist_class'  => Array('vcl'=>'pClass','filters'=>Array('class'),'pref'=>'Angebots- und Buszuordnung für Klasse ','postf'=>''),
			'pdf_all_wishlists' => Array('vcl'=>'pAllClass','filters'=>Array(),'pref'=>'Wunschlisten für alle Klassen','postf'=>''),
			'pdf_offerprizelist_all' => Array('vcl'=>'pAllClass','filters'=>Array(),'pref'=>'Angebotszuordnung und Preisliste für alle Klassen','postf'=>''),
			'pdf_busofferlist_all' => Array('vcl'=>'pAllClass','filters'=>Array(),'pref'=>'Angebots- und Buszuordnung für alle Klassen','postf'=>''),
			'pdf_teachers_wishlist' => Array('vcl'=>'pTeach','filters'=>Array(),'pref'=>'Wunschliste für die Lehrkräfte','postf'=>''),
			'pdf_busofferlist_teachers' => Array('vcl'=>'pAllClass','filters'=>Array(),'pref'=>'Angebots- und Buszuordnung Lehrkräfte','postf'=>''),
			'pdf_ov_offers' => Array('vcl'=>'pOv','filters'=>Array(),'pref'=>'Angebotsübersicht','postf'=>''),
			'pdf_offerstudentslist' => Array('vcl'=>'pOffer','filters'=>Array('offer'),'pref'=>'Teilnehmerliste Angebot ','postf'=>''),
			'pdf_offerbuslist' => Array('vcl'=>'pOffer','filters'=>Array('offer'),'pref'=>'Buslisten für Angebot ','postf'=>''),
			'pdf_phonelist_offer' => Array('vcl'=>'pOffer','filters'=>Array('offer'),'pref'=>'Hinweise und Nummernliste für Angebot ','postf'=>''),
			'pdf_offerstudentslist_all' => Array('vcl'=>'pAllOff','filters'=>Array(),'pref'=>'Teilnehmerlisten für alle Angebote','postf'=>''),
			'pdf_offerbuslist_all' => Array('vcl'=>'pAllOff','filters'=>Array(),'pref'=>'Buslisten für alle Angebote','postf'=>''),
			'pdf_phonelist_all' => Array('vcl'=>'pAllOff','filters'=>Array(),'pref'=>'Hinweislisten für alle Angebote','postf'=>''),
			'pdf_cashback' => Array('vcl'=>'pOv','filters'=>Array(),'pref'=>'Rückzahlungslisten für alle Angebote','postf'=>''),
			'pdf_statistics' => Array('vcl'=>'pOv','filters'=>Array(),'pref'=>'Gesamtstatistik','postf'=>'')
		);
		$GLOBALS['views']['cat']['mg'] = Array(
			'management_autofill_offers' => Array('vcl'=>'pOffer','filters'=>Array(),'pref'=>'Automatische Schülerzuteilung auf Angebote.','postf'=>''),
			'management_autofill_busses' => Array('vcl'=>'pAllOff','filters'=>Array(),'pref'=>'Automatische Schülerzuteilung auf Busse.','postf'=>''),
			'management_change_title' => Array('vcl'=>'pOv','filters'=>Array(),'pref'=>'Eventtitel bearbeiten','postf'=>''),
			'management_configure_access' => Array('vcl'=>'pOv','filters'=>Array(),'pref'=>'Zugriffsrechte konfigurieren','postf'=>''),
			'management_affirm_archiving' => Array('vcl'=>'pOv','filters'=>Array(),'pref'=>'Dieses Event archivieren.','postf'=>''),
			'management_archiving_lastwarning' => Array('vcl'=>'pHidden','filters'=>Array(),'pref'=>'','postf'=>'', 'show' => false),
		);
		
		$GLOBALS['views']['all'] = Array();
		
		foreach ($GLOBALS['views']['cat'] as $tcat => $tcdata) {
			foreach ($tcdata as $tview => $tvdata) {
				$GLOBALS['views']['cat'][$tcat][$tview]['cat'] = $tcat;
				if (!isset($tvdata['show'])) $GLOBALS['views']['cat'][$tcat][$tview]['show'] = true;
				$GLOBALS['views']['cat'][$tcat][$tview]['basallowed'] = false;
				$GLOBALS['views']['cat'][$tcat][$tview]['advallowed'] = false;
				$GLOBALS['views']['all'][$tview] = &$GLOBALS['views']['cat'][$tcat][$tview];
			}
		}
		
		$GLOBALS['views']['basdef'] = Array('ov_students','ov_teachers','ov_offers','ov_autofill','ov_payments','edit_students','pdf_classteacher_wishlist','pdf_offerprizelist_class','pdf_busofferlist_class','pdf_ov_offers');
		$GLOBALS['views']['advdef'] = Array('ov_students','ov_teachers','ov_offers','ov_autofill','ov_payments','edit_offer','pdf_ov_offers','pdf_offerstudentslist','pdf_offerbuslist','pdf_phonelist_offer');
	}
		
	define("MMPPT", 0.352777777777777777);
	define("CPW",	(isset($globSet['creatorpw'])?$globSet['creatorpw']:null)	);
	define("DB",	(isset($_SESSION['pvs_db'])?$_SESSION['pvs_db']:'N/A')	);
	define("AUTH",	(isset($_SESSION['pvs_auth'])?$_SESSION['pvs_auth']:'N/A')	);
	define("PW",	(isset($_SESSION['pvs_pw'])?$_SESSION['pvs_pw']:'N/A')	);
	define("VIEW",	(isset($_REQUEST['view'])?$_REQUEST['view']:'default')	);
	define("FORMAT",(isset($_REQUEST['format'])?strtoupper($_REQUEST['format']):(substr(VIEW,0,4)=='pdf_'?'PDF':'HTML')));
	define("CLASSFILTER",	(isset($_REQUEST['class'])?$_REQUEST['class']:'N/A')	);
	define("OFFER",	(isset($_REQUEST['offer'])?$_REQUEST['offer']:'0')	);
	define("ACTION",	(isset($_REQUEST['action'])?$_REQUEST['action']:'N/A')	);
	define("SPECIAL",	(isset($_REQUEST['special'])?$_REQUEST['special']:'N/A')	);
	
	if (FORMAT == 'pdf') {
		error_reporting(E_ALL ^ E_NOTICE);
	}
	
	unset($globSet);
	
	$GLOBALS['hints'] = Array();
	$GLOBALS['output'] = "";
	$GLOBALS['nav'] = "";
	
	if (isset($_REQUEST['hint']) AND is_string($_REQUEST['hint']) AND $_REQUEST['hint'] != '') {
		$GLOBALS['hints'][] = '<span class="b cb">'.htmlentities($_REQUEST['hint']).'</span>';
	}	
	
	//Falls nötig: Neues Event anlegen
	if (ACTION == 'createdb_finished') {
		if(isset($_REQUEST['creatorpw_input']) AND (( $_REQUEST['creatorpw_input'] !== null AND $_REQUEST['creatorpw_input'] == CPW ) OR CPW === "") ) {
		
			if (
				isset($_REQUEST['crdata_name']) AND
				$_REQUEST['crdata_name'] AND
				isset($_REQUEST['crdata_eventid']) AND
				isset($_REQUEST['crdata_wishes']) AND
				isset($_REQUEST['crdata_pwbas']) AND
				$_REQUEST['crdata_pwbas'] AND
				isset($_REQUEST['crdata_pwadv']) AND
				$_REQUEST['crdata_pwadv'] AND
				isset($_REQUEST['crdata_pwsup']) AND
				$_REQUEST['crdata_pwsup']
			) {
			
				$eventid = $_REQUEST['crdata_eventid'];
			
				if ($eventid AND !preg_match("/\W/",$eventid)) {
					$idtest = true;
				} else { $idtest = false; }
				
				if (is_dir('./db/'.$eventid)) { $idtest = false; }
				
				while (!$idtest) {
				
					$idtest = true;
					
					$pool = Array(0,1,2,3,4,5,6,7,8,9,"_","a","b","c","d","e","f","g","h","i","j","k","l","m","n","o","p","q","r","s","t","u","v","w","x","y","z");
				
					$rand = 0;
					$eventid = "";
					
					for ($tc = 0; $tc < 8; $tc++) {
						$rand = rand(0,36);
					
						$eventid = $eventid."".$pool[$rand]."";
					}
								
					if (is_dir('./db/'.$eventid) OR !$eventid) { $idtest = false; }
				
				}
				
				if ($eventid != $_REQUEST['crdata_eventid']) {
				
					$GLOBALS['hints'][] = "<span style='font-weight:bold;'>Die Eventid war nicht vergeben, enthielt illegale Zeichen oder war schon vorhanden und wurde dementsprechend von </span>".$_REQUEST['crdata_eventid']."<span style='font-weight:bold;'> zu </span>".$eventid."<span style='font-weight:bold;'> geändert!</span>";
				
				}
				
				mkdir('./db/'.$eventid);
				
				if (is_dir('./db/'.$eventid)) {
				
					file_put_contents('./db/'.$eventid.'/index.php','');
				
					$info = Array();
					$info['info'] = Array('name' => $_REQUEST['crdata_name'], 'desc' => '', 'print_header' => '');
					$info['auth'] = Array('basic' => $_REQUEST['crdata_pwbas'], 'advanced' => $_REQUEST['crdata_pwadv'], 'supervisor' => $_REQUEST['crdata_pwsup']);
					$info['props'] = Array('max_wishes' => $_REQUEST['crdata_wishes']);
				
					write_ini_file('./db/'.$eventid.'/info.ini',$info);
					file_put_contents('./db/'.$eventid.'/counter','1');
					
					if (isset($_REQUEST['crdata_autodb']) AND $_REQUEST['crdata_autodb'] == 'true' AND is_file('./dbinterface.php')) {
						
					
						//Schuldatenbank über Schnittstelle einladen
						$teachers = Array();
						$classes = Array();
						$offers = Array();
						$buses = Array();
						$carpools = Array();
						
						include './dbinterface.php';

						write_ini_file('./db/'.$eventid.'/teachers.ini',$teachers);
						write_ini_file('./db/'.$eventid.'/offers.ini',$offers);
						write_ini_file('./db/'.$eventid.'/buses.ini',$buses);
						write_ini_file('./db/'.$eventid.'/carpools.ini',$carpools);
						
						foreach ($classes as $cn => $cd) {
							write_ini_file('./db/'.$eventid.'/class_'.$cn.'.ini',$cd);
						}
					
					} else {
					
						
						if (isset($_REQUEST['crdata_autodb']) AND $_REQUEST['crdata_autodb'] == 'true' AND !is_file('./dbinterface.php')) {
							
							$GLOBALS['hints'][] = "<span style='color:#900;font-weight:bold;'>Die Schülerdaten konnten nicht eingelesen werden, da keine Schnittstelle zur Schuldatenbank vorhanden ist. Sprechen Sie mit dem Systemadministrator. Die Datenbank wurde leer angelegt.</span>";
						
						}
					
						file_put_contents('./db/'.$eventid.'/teachers.ini','');
						file_put_contents('./db/'.$eventid.'/offers.ini','');
						file_put_contents('./db/'.$eventid.'/buses.ini','');
					
					}
					
					$GLOBALS['hints'][] = "<span style='color:#090;font-weight:bold;'>Das neue Event wurde ordnungsgemäß erstellt und ist nun zum Login verfügbar.</span>";
				
				} else {
					
					$GLOBALS['hints'][] = "<span style='color:#900;font-weight:bold;'>Die Eventerzeugung ist fehlgeschlagen, da das System die Ordnererstellung verweigert hat! Versuchen Sie es nocheinmal mit anderer ID und kontaktieren Sie bei wiederholtem Auftreten den Systemadministrator.</span>";
				
				}
				
			} else {
			
			$GLOBALS['hints'][] = "<span style='color:#900;font-weight:bold;'>Die Eventerzeugung ist fehlgeschlagen, da nicht alle Datenfelder ausgefüllt waren!</span>";
			
			}
			
			
		} else {
			$GLOBALS['hints'][] = "<span style='color:#900;font-weight:bold;'>Die Eventerzeugung ist fehlgeschlagen! Fehlerhafte Autorisierung!</span>";
		}
	}
	
	//Info-Datenbanken vorladen
	$dbs = Array();
	$info = Array();
	foreach (scandir('db') as $fn) {
		if (is_dir('db/'.$fn) AND $fn != '.' AND $fn != '..') {
			$temp = parse_ini_file('db/'.$fn.'/info.ini',true);
			$dbs[$fn] = Array('id'=>$fn,'name'=>$temp['info']['name'],'desc'=>$temp['info']['desc']);
			
			if ($fn == DB) {
				$info = $temp;
			}
		}
	} unset($temp);
	
	//Info-Datenbanken sortieren
	uasort($dbs,'sort_db');
	if (SPECIAL == 'N/A') {
	
		if (ACTION == 'createdb' AND isset($_REQUEST['creatorpw_input']) AND (( $_REQUEST['creatorpw_input'] !== null AND $_REQUEST['creatorpw_input'] == CPW ) OR CPW === "") ) {
			{ 
				$GLOBALS['output'] = $GLOBALS['output'] . '<div class="fieldset">';
				$GLOBALS['output'] = $GLOBALS['output'] . '<h2>Eventerstellung</h2>';
				$GLOBALS['output'] = $GLOBALS['output'] . '<p>Sie haben sich ordnungsgemäß zur Eventerstellung eingeloggt. Bitte geben Sie nun die erforderlichen Daten an.</p>';
				$GLOBALS['output'] = $GLOBALS['output'] . '<form action="./" method="POST">';
				$GLOBALS['output'] = $GLOBALS['output'] . '<input type="hidden" name="action" value="createdb_finished"/>';
				$GLOBALS['output'] = $GLOBALS['output'] . '<input type="hidden" name="creatorpw_input" value="'.$_REQUEST['creatorpw_input'].'"/>';
				$GLOBALS['output'] = $GLOBALS['output'] . '<fieldset>';
				$GLOBALS['output'] = $GLOBALS['output'] . '<legend>Informationen zum Erstellen der Datenbank</legend>';
				$GLOBALS['output'] = $GLOBALS['output'] . '<table class="st3c">';
				$GLOBALS['output'] = $GLOBALS['output'] . '<tr><td><label for="crdata_name">Titel des Events:</label></td><td><input type="text"  id="crdata_name" name="crdata_name" value=""/></td><td>Titel des Events, z.B. "Wintersporttag 2013"</td></tr>';
				$GLOBALS['output'] = $GLOBALS['output'] . '<tr><td><label for="crdata_autoid">Event-ID automatisch:</label></td><td><input type="checkbox" id="crdata_autoid" name="crdata_autoid" checked="checked" value="true"/></td><td>Die Event-ID automatisch generieren lassen? (Leichtere Lesbarkeit bei selbst vergebener ID)</td></tr>';
				$GLOBALS['output'] = $GLOBALS['output'] . '<tr><td><label for="crdata_eventid">Event-ID:</label></td><td><input type="text" id="crdata_eventid" name="crdata_eventid" readonly="readonly" value=""/></td><td>Erlaubt sind nur Ziffern, Kleinbuchstaben und der Unterstrich! Sollte die ID schon existieren wird sie automatisch angepasst.</td></tr>';
				$GLOBALS['output'] = $GLOBALS['output'] . '<tr><td><label for="crdata_autodb">Datenbank vorladen:</label></td><td><input type="checkbox" id="crdata_autodb" name="crdata_autodb" checked="checked" value="true"/></td><td>Die Datenbank für Schüler und Lehrer automatisch aus der Schuldatenbank auslesen (funktioniert nur, wenn entsprechende Schnittstelle eingerichtet ist). <span style="color:#900; font-weight:bold">Das Einladen von Daten aus der Schuldatenbank ist zu einem späteren Zeitpunkt nicht mehr möglich!</span></td></tr>';
				$GLOBALS['output'] = $GLOBALS['output'] . '<tr><td><label for="crdata_wishes">Anzahl Wünsche:</label></td><td>';
				$GLOBALS['output'] = $GLOBALS['output'] . '<select size="1" name="crdata_wishes" id="crdata_wishes">';
				$GLOBALS['output'] = $GLOBALS['output'] . '<option value="0">0 - Nur manuelle Angebotszuteilung</option>';
				$GLOBALS['output'] = $GLOBALS['output'] . '<option>1</option>';
				$GLOBALS['output'] = $GLOBALS['output'] . '<option selected="selected">2</option>';
				$GLOBALS['output'] = $GLOBALS['output'] . '<option>3</option>';
				$GLOBALS['output'] = $GLOBALS['output'] . '<option>4</option>';
				$GLOBALS['output'] = $GLOBALS['output'] . '<option>5</option>';
				$GLOBALS['output'] = $GLOBALS['output'] . '</select>';
				$GLOBALS['output'] = $GLOBALS['output'] . '</td><td>Anzahl der Wünsche für Angebotszuteilung, die Lehrkräfte und Schüler äußern können. Mehr freigegebene Wünsche erhöhen die Chance auf erfolgreiche Zuteilung, schaden aber der Übersichtlichkeit.<span style="color:#900; font-weight:bold">Dies kann im Nachhinein nicht mehr angepasst werden!</span></td></tr>';
				$GLOBALS['output'] = $GLOBALS['output'] . '</table>';
				$GLOBALS['output'] = $GLOBALS['output'] . '</fieldset>';
				$GLOBALS['output'] = $GLOBALS['output'] . '<fieldset>';
				$GLOBALS['output'] = $GLOBALS['output'] . '<legend>Zugriffssteuerung</legend>';
				$GLOBALS['output'] = $GLOBALS['output'] . '<table class="st3c">';
				$GLOBALS['output'] = $GLOBALS['output'] . '<tr><td><label for="crdata_pwclear">Passwörter anzeigen:</label></td><td><input type="checkbox" id="crdata_pwclear" name="crdata_pwclear" value="true" checked="checked"/></td><td>Passworteingabe im Klartext anzeigen</td></tr>';
				$GLOBALS['output'] = $GLOBALS['output'] . '<tr><td><label for="crdata_pwbas">Passwort Schülermanagement:</label></td><td><input type="text" id="crdata_pwbas" name="crdata_pwbas" value=""/></td><td>Passwort für Zugriff auf das Schülermanagement: Wünsche von Schülern eintragen, Schülerlisten einsehen und drucken, persönliche Daten bearbeiten</td></tr>';
				$GLOBALS['output'] = $GLOBALS['output'] . '<tr><td><label for="crdata_pwadv">Passwort Angebotsmanagement:</label></td><td><input type="text" id="crdata_pwadv" name="crdata_pwadv" value=""/></td><td>Passwort für Zugriff auf das Angebotsmanagement: Angebotsdaten bearbeiten, Teilnehmerlisten einsehen und drucken, Anwesenheitsliste ausfüllen</td></tr>';
				$GLOBALS['output'] = $GLOBALS['output'] . '<tr><td><label for="crdata_pwsup">Passwort Eventmanagement:</label></td><td><input type="text" id="crdata_pwsup" name="crdata_pwsup" value=""/></td><td>Passwort für Zugriff auf das Eventmanagement: Eventdaten bearbeiten, Lehrerliste bearbeiten, Schüler und Lehrer manuell oder automatisch zuteilen, Bearbeitungen aller Art, Gesamtüberblick</td></tr>';
				$GLOBALS['output'] = $GLOBALS['output'] . '</table>';
				$GLOBALS['output'] = $GLOBALS['output'] . '</fieldset>';
				$GLOBALS['output'] = $GLOBALS['output'] . '<fieldset>';
				$GLOBALS['output'] = $GLOBALS['output'] . '<p><input type="submit" value="Event erstellen!"> oder <a href="./">zurück zur Loginseite</a>.</p>';
				$GLOBALS['output'] = $GLOBALS['output'] . '</fieldset>';
				$GLOBALS['output'] = $GLOBALS['output'] . '</form>';
				//$GLOBALS['output'] = $GLOBALS['output'] . '<tr><td><label for=""></label></td><td><input type="text" id="" name="" value=""/></td><td></td></tr>';
				$GLOBALS['output'] = $GLOBALS['output'] . '</div>';
			}
		} elseif (DB != "N/A" AND AUTH != "N/A" AND PW != "N/A" AND isset($info) AND isset($info['auth'][AUTH]) AND $info['auth'][AUTH] == PW) {  
		
			{ //ANMELDUNG ERFOLGREICH!
			
			//Rechte festlegen
			$temp = (isset($info['access']) AND isset($info['access']['basic']))?$info['access']['basic']:$GLOBALS['views']['basdef'];
			foreach ($temp as $tview) {				
				$GLOBALS['views']['all'][$tview]['basallowed'] = true;				
			}
			$temp = (isset($info['access']) AND isset($info['access']['advanced']))?$info['access']['advanced']:$GLOBALS['views']['advdef'];
			foreach ($temp as $tview) {				
				$GLOBALS['views']['all'][$tview]['advallowed'] = true;				
			}
		
		
			//Aktionen ausführen
			include 'actionswitch.php';
			
			$GLOBALS['nav'] .= "<h2>".$info['info']['name']."</h2>";
			
			$GLOBALS['nav'] .= '<form action="./" method="POST" style="float:right;"><input type="submit" value="Zur Portalseite" /></form>';
			
			$GLOBALS['nav'] .= "<form action='./' method='GET'><input type='hidden' name='logout' value='true'/><input id='bt_logout' type='submit' value='Abmelden' /></form>";
			
			switch (AUTH) {		
				case 'basic':				
					$GLOBALS['nav'] .= "<p id='auth'>- Schülermanagement -</p><br/>";
					break;				
				case 'advanced':			
					$GLOBALS['nav'] .= "<p id='auth'>- Angebotsmanagement -</p><br/>";	
					break;				
				case 'supervisor':			
					$GLOBALS['nav'] .= "<p id='auth'>- Eventmanagement -</p><br/>";	
					break;
			
			}
			
			}
			
			//Datenbanken laden
			$eventid = DB;
			$teachers = is_file('./db/'.$eventid.'/teachers.ini')?parse_ini_file('./db/'.$eventid.'/teachers.ini',true):Array();
			uasort($teachers,'sort_persons');
			$offers = is_file('./db/'.$eventid.'/offers.ini')?parse_ini_file('./db/'.$eventid.'/offers.ini',true):Array();
			uasort($offers,'sort_db');
			$buses = is_file('./db/'.$eventid.'/buses.ini')?parse_ini_file('./db/'.$eventid.'/buses.ini',true):Array();
			$carpools = is_file('./db/'.$eventid.'/carpools.ini')?parse_ini_file('./db/'.$eventid.'/carpools.ini',true):Array();
			$students = Array();
			$classes = Array();
			
			$files = scandir('./db/'.$eventid.'/');
			
			foreach ($files as $file) {
			
				if (substr($file,0,6) == 'class_') {
				
					$classes[substr($file,6,-4)] = parse_ini_file('./db/'.$eventid.'/'.$file,true);
					
					//Schülerdaten bereinigen
					foreach ($classes[substr($file,6,-4)] as $sid => $sdata) {
						if (isset($sdata['offer']) AND (int)$sdata['offer']>0 AND !isset($offers[(int)$sdata['offer']])) {
							$classes[substr($file,6,-4)][$sid]['offer'] = 0;
							$classes[substr($file,6,-4)][$sid]['bus'] = 0;
						}
						if (isset($sdata['bus']) AND (int)$sdata['bus']>0) {
							
							if (!isset($sdata['offer']) OR (int)$sdata['offer'] <= 0) {						
								$classes[substr($file,6,-4)][$sid]['bus'] = 0;
							} elseif (!isset($buses[$sdata['bus']])) {
								$classes[substr($file,6,-4)][$sid]['bus'] = 0;
							} 
						}
						for ($m = 0; $m < $info['props']['max_wishes']; $m++) {
							if (isset($sdata['wish_'.$m]) AND (int)$sdata['wish_'.$m]>0 AND !isset($offers[(int)$sdata['wish_'.$m]])) $classes[substr($file,6,-4)][$sid]['wish_'.$m] = 0;
						}
					}
					
					uasort($classes[substr($file,6,-4)],'sort_persons');
					
					foreach ($classes[substr($file,6,-4)] as $sid => $sdata) {
						
						$students[$sid] = $sdata;
					
					}
				
				}
			
			}
			uksort($classes,'strnatcasecmp');
			uasort($students,'sort_students_all_classes');
			
			//Lehrerdaten bereinigen
			foreach ($teachers as $sid => $sdata) {
				if (isset($sdata['offer']) AND (int)$sdata['offer']>0 AND !isset($offers[(int)$sdata['offer']])) {
					$teachers[$sid]['offer'] = 0;
					$teachers[$sid]['bus'] = 0;
				}
				if (isset($sdata['bus']) AND (int)$sdata['bus']>0) {
					
					if (!isset($sdata['offer']) OR (int)$sdata['offer'] <= 0) {						
						$teachers[$sid]['bus'] = 0;
					} elseif (!isset($buses[$sdata['bus']])) {
						$teachers[$sid]['bus'] = 0;
					} 
				}
				$clns = isset($sdata['class'])?explode(',',$sdata['class']):Array();
				if (count($clns)) {	
					$ncl = '';
					foreach ($clns as $cln) {
						if (is_file('./db/'.DB.'/class_'.$cln.'.ini')) {
							if ($ncl != '') $ncl .= ',';
							$ncl .= $cln;
						}
					}
					$teachers[$sid]['class'] = $ncl;
				}
				for ($m = 0; $m < $info['props']['max_wishes']; $m++) {
					if (isset($sdata['wish_'.$m]) AND (int)$sdata['wish_'.$m]>0 AND !isset($offers[(int)$sdata['wish_'.$m]])) $classes[substr($file,6,-4)][$sid]['wish_'.$m] = 0;
				}
			}
			
			include 'viewswitch.php';
			
		} else {
			$GLOBALS['output'] = $GLOBALS['output'] . '<div class="fieldset_tr">';
			$GLOBALS['output'] = $GLOBALS['output'] . '<div class="fieldset_td">';
			$GLOBALS['output'] = $GLOBALS['output'] . '<div class="fieldset" style="text-align:center;">';
			$GLOBALS['output'] = $GLOBALS['output'] . '<form action="./" method="POST"><h2>Eventzugriff - Anmeldung erforderlich</h2>';
			$GLOBALS['output'] = $GLOBALS['output'] . '<fieldset>';
			$GLOBALS['output'] = $GLOBALS['output'] . '<p>Zum Zweck des Datenschutzes und der allgemeinen Sicherheit benötigt der Zugriff auf die Events in diesem Verwaltungsskript je nach Zugriffsebene ein Passwort.</p>';
			
			
			if (DB != "N/A" OR AUTH != "N/A" OR PW != "N/A") {
				$GLOBALS['hints'][] = '<span style="color:#900;font-weight:bold;">Die eingegebenen Daten waren nicht korrekt!</span>';
				session_destroy();
				unset($_SESSION['pvs_pw']);
				unset($_SESSION['pvs_auth']);
				unset($_SESSION['pvs_db']);
				unset($_SESSION['pvs_time']);
			}
			
			if (count($dbs) > 0) {
			
				$GLOBALS['output'] = $GLOBALS['output'] . '<table style="margin:auto;" class="login_table"><tr><td><label for="db">Event auswählen:</label></td><td>';
				$GLOBALS['output'] = $GLOBALS['output'] . '<select style="width:250px;" size="1" id="db" name="db"/>';
				
				foreach ($dbs as $temp) {
					$GLOBALS['output'] = $GLOBALS['output'] . '<option value="'.$temp['id'].'">'.$temp['name'].' &nbsp; &nbsp; (id: '.$temp['id'].')</option>';
				}
				if (isset($temp)) unset($temp);
				
				$GLOBALS['output'] = $GLOBALS['output'] . '</select></td></tr>';
				$GLOBALS['output'] = $GLOBALS['output'] . '<tr><td><label for="auth">Zugriffsebene wählen:</label></td><td>';
				$GLOBALS['output'] = $GLOBALS['output'] . '<select style="width:250px;" size="1" id="auth" name="auth"/>';
				$GLOBALS['output'] = $GLOBALS['output'] . '<option value="basic" selected="selected">Schülermanagement</option>';
				$GLOBALS['output'] = $GLOBALS['output'] . '<option value="advanced">Angebotsmanagement</option>';
				$GLOBALS['output'] = $GLOBALS['output'] . '<option value="supervisor">Eventmanagement</option>';
				$GLOBALS['output'] = $GLOBALS['output'] . '</select></td></tr>';
				$GLOBALS['output'] = $GLOBALS['output'] . '<tr><td><label for="password">Passwort eingeben:</label></td><td>';
				$GLOBALS['output'] = $GLOBALS['output'] . '<input style="width:250px;" type="password"  id="password" name="password" value=""/></td></tr>';
				$GLOBALS['output'] = $GLOBALS['output'] . '<tr><td></td><td><input type="submit" value="Anmelden."></td></tr></table></form></div>';
			
			} else {
				
				$GLOBALS['output'] = $GLOBALS['output'] . '<p style="color:#900;font-weight:bold;">Es existieren noch keine Events. Bitte erstellen Sie zunächst ein Event bevor Sie versuchen sich anzumelden.</p></div>';
				
			}
			$GLOBALS['output'] = $GLOBALS['output'] . '</fieldset>';
			
			$GLOBALS['output'] = $GLOBALS['output'] . '</div><div class="fieldset_td">';
			$GLOBALS['output'] = $GLOBALS['output'] . '<div class="fieldset" style="text-align:center;">';
			$GLOBALS['output'] = $GLOBALS['output'] . '<form action="./" method="POST"><h2>Eventerstellung</h2>';
			$GLOBALS['output'] = $GLOBALS['output'] . '<fieldset>';
			
			if (CPW !== null) {	
				
				if (CPW != '') {
					
					if (isset($_REQUEST['creatorpw_input']) AND $_REQUEST['creatorpw_input'] != CPW) {
						$GLOBALS['hints'][] = '<span style="color:#900 !important;font-weight:bold;">Die eingegebenen Daten waren nicht korrekt!</span>';
					}
					
					$GLOBALS['output'] = $GLOBALS['output'] . '<p>Da bei der Erstellung eines Events Schülerdaten wie Vor- und Nachname freigegeben werden, ist auch zur Erstellung eines Events ein Passwort nötig: <br/> <br/>';
					$GLOBALS['output'] = $GLOBALS['output'] . '<label for="creatorpw_input">Passwort:</label> <input type="password" id="creatorpw_input" name="creatorpw_input"/></p>';
					
				} else {
				
					$GLOBALS['output'] = $GLOBALS['output'] . '<p>Der Systemadministrator hat die Eventerstellung ohne Passwort freigegeben.</p>';
					$GLOBALS['output'] = $GLOBALS['output'] . '<input type="hidden" name="creatorpw_input" value=""/></p>';
				
				}
				
				$GLOBALS['output'] = $GLOBALS['output'] . '<input type="hidden" name="action" value="createdb"/><input type="submit" value="Neues Event erstellen."/></form></div>';
				$GLOBALS['output'] = $GLOBALS['output'] . '</div></div>';
			} else {
				$GLOBALS['output'] = $GLOBALS['output'] . '<p style="color:red;">Sicherheitsproblem: Die Eventerstellung ist deaktiviert, da das Erstellungspasswort aus settings.ini nicht eingelesen werden konnte. Kontaktieren Sie den Systemadministrator.</p></form></div>';
			}

			$GLOBALS['output'] = $GLOBALS['output'] . '</fieldset>';
		}
	
	} else {
	
		$ret =& $GLOBALS['output'];
	
		switch (SPECIAL) {
		
			case 'manuals': {
				
				$ret .= '<div class="fieldset">';
					
				$ret .= '<h2>Handbücher</h2>';
				
				$ret .= '<p>Bitte wählen Sie die Handbuchversion:</p>';
				
				$ret .= '<ul>';
				$ret .= '<li><a href="/pvs/vorlagen/special/manual.pdf">Handbuch für Eventmanager und Systemadministratoren</a></li>';
				$ret .= '<li><a href="/pvs/vorlagen/special/handout_ct.pdf">Handreichung für Klassenlehrer</a></li>';
				$ret .= '<li><a href="/pvs/vorlagen/special/handout_or.pdf">Handreichung für Angebotsverantwortliche</a></li>';
				$ret .= '</ul>';
				
				$ret .= '<p><a href="./">Zurück zur Startseite</a></p>';
				$ret .= '</div>';
				
			}	break;
		
			case 'license': {
			
					$ret .= '<div class="fieldset">';
					
					$ret .= '<h2>Lizenz</h2>';
					
					$ret .= '<object style="width:100%;height:120%;border:4px groove red;border-radius:20px" data="./vorlagen/special/license.pdf" type="application/pdf"></object>';
					
					$ret .= '<p><a href="./">Zurück zur Startseite</a></p>';
					
					$ret .= '</div>';
					
				} break;
		
			case 'impressum': {
			
					$ret .= '<div class="fieldset">';
					
					$ret .= '<h2>Impressum und Support</h2>';
					
					$ret .= '<fieldset>';
					$ret .= '<legend>Rechtliches</legend>';
					$ret .= '<p>Das Projektverteilungssystem für Schulen (PVS) wurde im Jahr 2013 entworfen und programmiert von <br/><br/>';
					$ret .= '<span style="text-align:center; font-weight:bold">';
					$ret .= 'Janosch W. Zoller<br/>';
					$ret .= 'Anton-Hegele-Str. 8<br/>';
					$ret .= '73433 Aalen (Wasseralfingen)<br/>';
					$ret .= '</span><br/>';
					$ret .= 'Alle Rechte liegen ausschließlich beim Urheber. Ausnahmen bilden die Rechte, die der Urheber (Lizenzgeber) dem jeweiligen Lizenznehmer in der jeweils erteilten Lizenz ausdrücklich zugesteht. ';
					$ret .= 'Eine solche Lizenz ist nur gültig, wenn eine entsprechend lizenzierte Software (Hinweis: Lizenziert für ...) in Verbindung mit einem unterschriebenen Lizenzdokument in Originalausfertigung vorliegt.';
					$ret .= '</p>';
					$ret .= '<p>';
					$ret .= 'Der Urheber übernimmt keine Haftung für die Sicherheit der in dieses System eingebrachten Daten. Lizenznehmer werden ausdrücklich angewiesen, selbständig für eine solche Sicherheit zu sorgen. Dies umfasst insbesondere:</p>';
					$ret .= '<ul>';
					$ret .= '<li>Betrieb des pvs auf einem einrichtungsinternen Server, der entweder physisch vom Internet getrennt ist oder durch entsprechende Vorsichtsmaßnahmen (Firewall, IP-basierte Zugriffskontrolle) vom öffentlichen Netz getrennt ist.</li>';
					$ret .= '<li>Sicherung bzw. Zugriffskontrolle auf die Ordner, in denen sich datenschutzrechtlich relevante Dateien oder systemkritische Dateien befinden. (Das PVS stellt diesen Schutz standardmäßig über .htaccess für Server der Apache-Familie schon bereit.)</li>';
					$ret .= '<li>Die regelmäßige Änderung des Eventerstellungspasswort und die ausschließlich kontrollierte Verbreitung von Zugriffspasswörtern.</li>';
					$ret .= '</ul>';					
					$ret .= '</fieldset>';
					
					$ret .= '<fieldset>';
					$ret .= '<legend>Support</legend>';
					$ret .= '<p>Sollten sich aus der Nutzung des Systems Probleme ergeben, die der einrichtungseigene Netzwerkverwalter <span class="b">nicht selbständig</span> beheben kann, stehe ich selbstverständlich persönlich zur Verfügung. Sie erreichen mich - zusätzlich zur o.g. postalischen Anschrift - jederzeit unter</p>';
					$ret .= '<ul>';
					$ret .= '<li>E-Mail:	Janosch.Zoller@gmx.de</li>';
					$ret .= '<li>Telefon:	07361/8099608</li>';
					$ret .= '</ul>';
					$ret .= '<p>Sollten technische Probleme im System auftreten bitte ich um zügige Rückmeldung zur raschen Problembeseitigung.</p>';
					$ret .= '<p>Für Anfragen bezüglich einer Lizenzerteilung für eine weitere Einrichtung oder für geänderte Verwendungszwecke stehe ich jederzeit zur Verfügung.</p>';
					$ret .= '</fieldset>';
					
					$ret .= '<fieldset>';
					$ret .= '<legend>Danksagung</legend>';
					
					$sumlines = 0;
					$dateien = Array('./actionswitch.php','./dbinterface.php','./dbinterface_test.php','./dbinterface_msg.php','./functions.php','./index.php','./produce_portal.php','./style.css','./tools.js','./viewswitch.php');
					foreach ($dateien as $datei) {
						$sumlines += count(file($datei));
					}
					unset($datei);
					
					$ret .= '<p>Das PVS ist in mehr als 5 Wochen intensivster Programmierarbeit entstanden. Allein die Skripts des Kernsystems umfassen <span class="b">'.$sumlines.' Zeilen eigenhändig geschriebenen Programmcode</span>. Alle verwendeten Grafiken und das Design stammen komplett aus eigener Feder.</p>';
					$ret .= '<p>Mein Dank geht zuerst an meine Frau und meine Tochter, die von ihrem Papa über einen Monat lang viel weniger hatten als sonst und mich trotzdem die ganze Zeit über tatkräftig bestärkt und unterstützt haben.</p>';
					$ret .= '<p>Vielen Dank an das Autorenteam von FPDF17, die ihren PHP-basierten PDF-Creator komplett lizenzfrei zur Verfügung stellen. Ihr habt mir viel zusätzliche Arbeit erspart! Ein weiterer Dank geht an die Erschaffer von GIMP. Ohne GIMP wäre Grafikerstellung für mich kaum in dieser Form möglich.</p>';
					$ret .= '<p>Ein besonderer Dank geht an dieser Stelle noch an das Margarete-Steiff-Gymnasium in Giengen an der Brenz, an welchem ich mein Praxissemester absolvieren durfte. Vielen Dank an alle Kollegen, die es wohlwissend oder nicht geduldet haben, dass ich nicht nur in meiner Freizeit sondern auch hospitierend in ihrem Unterricht dieses System zur Vollendung bringen konnte und an die Fachschaft Sport, die das Problem, zu dessen Lösung das PVS nun beiträgt, auf den Tisch gebracht hat.</p>';
					$ret .= '</fieldset>';
					
					$ret .= '<p><a href="./">Zurück zur Startseite</a></p>';
					
					$ret .= '</div>';
			
				} break;
			
			case 'changelog': {
			
				$ret .= '<div class="fieldset">';
				$ret .= '<h2>Änderungsprotokoll</h2>';
				
				$ret .= '<pre style="border:2px solid black;padding:20px;background-color:white;box-shadow: 10px 10px 5px #888;">';
				
				$ret .= file_get_contents('changelog.txt');
				
				$ret .= '</pre>';
				
				$ret .= '<p><br/><a href="./">Zurück zur Startseite</a></p>';
				
				$ret .= '</div>';
			
				} break;
		
			default: {
			
					$ret .= '<div class="fieldset">';
					$ret .= '<h2>Spezialseite nicht verfügbar</h2>';
					$ret .= '<fieldset>';
					$ret .= '<legend>Hinweis</legend>';
					$ret .= '<p>Sie haben versucht, eine Spezialseite mit Kennzeichnung <span class="b">'.SPECIAL.'</span> aufzurufen. Eine solche Seite konnte nicht gefunden werden.<br/><a href="./">Kehren Sie zur Startseite zurück.</a></p>';
					$ret .= '</fieldset>';
					$ret .= '</div>';
			
				} break;
		
		}
		
	}
	
	//hints und nav vor output hängen
	if (count($GLOBALS['hints']) > 0 OR $GLOBALS['nav'] != '') {
		$top = '';
		
		if ($GLOBALS['nav'] != '') {
		
			$top .= '<div id="nav">';
			$top .= $GLOBALS['nav'];
			$top .= '</div>';
			$top .= '<div id="navifix">';
		
		}
	
		if (count($GLOBALS['hints']) > 0) {
			$top .= '<div class="fieldset" id="hints">';
			//$top .= '<h2>Hinweise</h2>';
			$top .= '<ul>';
			
			foreach ($GLOBALS['hints'] as $hint) {
				$top .= '<li>'.$hint.'</li>';
			}
			
			$top .= '</ul></div>';
		}
		
		$GLOBALS['output'] = $top.$GLOBALS['output'];
		unset($top);
	}
	
	if (FORMAT == 'HTML' OR $html_override) {
	
		header('Content-type: text/html');
		
		//Output ausgeben
		echo $output_before;
		
		echo $GLOBALS['output'];
		
		//nav-Container-Fix
		if ($GLOBALS['nav'] != '') { echo '</div>'; }
		
		$output_after = str_replace('{--input-vn--}',(is_file('VERSION')?file_get_contents('VERSION'):'1.0'),$output_after);
		
		echo str_replace('{--input-sn--}',$school_name,$output_after);
	} elseif (FORMAT == 'PDF') {
		if (is_a($GLOBALS['output_pdf'],'FPDF')) {
			$GLOBALS['output_pdf']->AliasNbPages();
			$GLOBALS['output_pdf']->Output();
		} else {
			header('Content-type: text/html; charset=UTF-8');
			echo '<p>PDF-Ausgabe nicht möglich: Kein PDF-Dokument wurde erstellt.</p>';
		}
	}
?>
