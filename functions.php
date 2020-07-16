<?php

	function write_ini_file ($src, $content) {
		$string = '';
		foreach ($content as $contentK => $contentElm) {
			if (is_array($contentElm)) {
				$string = $string.'['.$contentK.']'."\n";
				foreach($contentElm as $key => $value) {
					if (is_array($value)) {
						for ($i = 0; $i < count($value); $i++) {
							if (isset($value[$i])) {
								if (!is_numeric($value[$i])) {
									$string = $string.$key.'[] = "'.addcslashes($value[$i],'"')."\"\n";
								} else {
									$string = $string.$key.'[] = '.$value[$i]."\n";	
								}
							}
						}
					} else {
						if (!is_numeric($value)) {
							$string = $string.$key.' = "'.addcslashes($value,'"')."\"\n";
						} else {
							$string = $string.$key.' = '.$value."\n";
						}
					}
				}
			} else {
				if (!is_numeric($contentElm)) {
					$string = $string.$contentK.' = "'.addcslashes($contentElm,'"')."\"\n";
				} else {
					$string = $string.$contentK.' = '.$contentElm."\n";
				}
			}
		}
		file_put_contents($src,$string);
	}
	
	function get_DB_ct($eventid) {
		
		$data = file_get_contents('./db/'.$eventid.'/counter');
		$ret = (int) $data;
		$data = $ret + 1;
		file_put_contents('./db/'.$eventid.'/counter',$data);
		
		return $ret;
	
	}
	
	function id_to_address($data) {
	
		$ret = '';
		$bool = false;
		switch ($data['gender']) {
			case 'm': $ret .= 'Herr '; break;
			case 'f': $ret .= 'Frau '; break;
			default : $bool = true;   break;
		}
		if ($bool) $ret .= substr($data['forename'],0,1).'. ';
		$ret .= $data['surname'];
		$ret .= ' ('.$data['sign'].')';
		
		return $ret;
		
	}

	function get_class_teacher($cln,$teachers) {
		foreach ($teachers as $tid => $tdata) {
			$classes = explode(',',$tdata['class']);
			if ($classes == false) {
				continue;
			} else {
				foreach ($classes as $classn) {
					if ($classn == $cln) {
						return $tid;
					}
				}
		
			}
		}
		
		return false;
	}
	
	function sort_db ($dba,$dbb) {
	
		return strnatcasecmp($dba['name'],$dbb['name']);
	
	}
	
	function sort_persons ($dba,$dbb) {
	
		$ret = strnatcasecmp($dba['surname'],$dbb['surname']);
		
		if ($ret == 0) {
			$ret = strnatcasecmp($dba['forename'],$dbb['forename']);
		}
		
		return $ret;
	
	}
		
	function sort_students_all_classes($dba,$dbb) {
	
		$ret = strnatcasecmp($dba['class'],$dbb['class']);
		
		if ($ret == 0) $ret = sort_persons($dba,$dbb);
	
		return $ret;
	
	}
	
	function float_to_eur ($f) {
	
		return number_format(((float)$f),2,',','.').' &euro;';
	
	}

	function parse_clstring ($avstr,$cmp) {
	
		$avstr = preg_replace('/[^ \w|,|\- ]+/','',$avstr);
		
		$avd = explode(',',$avstr);
		
		foreach ($avd as $avdt) {
			for ($i = 1; $i <= strlen($cmp); $i++) {
				if (strtolower($avdt) == strtolower(substr($cmp,0,$i))) {
					return true;
				}
			}
		}
		
		return false;
	}
	
	function produce_offer_list ($offers,$name = '',$class = 0,$gender = '',$offer = '') { // $name refers to HTML-Attribute
	
		$ret = '';
		
		$ret .= '<select class="select_offer" size="1" name="'.$name.'">';
		
		$ret .= '<option';
		if ($offer == '' OR $offer == 0) { $ret .= ' selected="selected"'; }
		$ret .= ' value="0">Angebot wählen</option>';
		
		foreach ($offers as $oid => $odata) {
			$ret .= '<option';
			if ((int)$offer == (int)$oid) { $ret .= ' selected="selected"'; }
			if (parse_clstring($odata['excluded'],$class) OR (isset($odata['avgender']) AND in_array($gender,Array('m','f')) AND !in_array($gender,explode(',',$odata['avgender']))) ) {
				$ret .= ' disabled="disabled"';
			}
			$ret .= ' value="'.$oid.'">'.$odata['name'].'</option>';
		}
		
		$ret .= '</select>';
		
		return $ret;
	
	}
	
	function produce_class_edit ($view,$auth,$classfilter,$info,$classes,$teachers,$offers,$carpools,$buses) {
		$ret = '';
		
		$cpo = Array();
		$bcp = Array();
		$ocp = Array();
		
		foreach ($carpools as $cpid => $cpdata) {
			$cpid = (int)$cpid;
			$toffers = explode(',',$cpdata['offers']);
			$ocp[$cpid] = Array();
			$bcp[$cpid] = Array();
			foreach ($toffers as $oid) {
				$oid = (int)$oid;
				$cpo[$oid] = $cpid;
				$ocp[$cpid][] = $oid;
			}
		}
		
		foreach ($buses as $bid => $bdata) {
			$bid = (int)$bid;
			if ($bdata['carpool']) {
				if (!isset($bcp[$bdata['carpool']])) $bcp[$bdata['carpool']] = Array();
				$bcp[$bdata['carpool']][] = $bid;
			}
		}
		
		$blankofelder = 5;
		
		if ($view == 'add_class') { $blankofelder = 35; }
		
		if ($view == 'edit_students') {
			$ret .= '<div class="fieldset">';
			$ret .= '<h2>Bearbeitungsansicht Klasse '.$classfilter.'</h2>';
			
			$ret .= '<form action="./?class='.$classfilter.'&view=edit_students" method="POST">';
			
			
			$ret .= '<fieldset>';	
				$ret .= '<legend>Hinweis</legend>';	
				$ret .= '<p>Zum Löschen von Schülern sowohl deren Vor- als auch Nachnamen entfernen. Der Schülerdatensatz wird dann automatisch gelöscht.</p>';
			$ret .= '</fieldset>';	
			
		} elseif ($view == 'add_class') {
			$ret .= '<div class="fieldset">';
			$ret .= '<h2>Neue Klasse erstellen</h2>';
			
			$ret .= '<form action="./?view=default" method="POST">';
		
			$ret .= '<fieldset>';	
				$ret .= '<legend>Hinweis</legend>';	
				$ret .= '<p>Sie müssen einen Namen für die Klasse angeben. Achten Sie darauf, den Namen in jedem Fall zu vergeben und dabei nur alphanumerische Zeichen und den Unterstrich zu verwenden, ansonsten kann die Klasse nicht gespeichert werden! Alle anderen Angaben können gemacht werden, sind aber für die Erstellung der Klasse nicht zwingend erforderlich.</p>';
			$ret .= '</fieldset>';	
			
		} 
		
		$ret .= '<table class="ov_table ed_table">';
		$ret .= '<thead>';
		$ret .= '<tr>';
		
		if ($view == 'add_class') {
			$ret .= '<th colspan="4">Klasse <input type="text" name="edit_students_classname" value="'.$classfilter.'"/> - ';
		} else {
			if ($auth == 'supervisor') {
				$ret .= '<th colspan="'.(6 + $info['props']['max_wishes']).'">Klasse '.$classfilter.' - ';
			} else {
				$ret .= '<th colspan="'.(4 + $info['props']['max_wishes']).'">Klasse '.$classfilter.' - ';
			}
		}
		
		$ret .= 'Klassenlehrer/in: ';
		
		$found = get_class_teacher($classfilter,$teachers);

		$ret .= '<select size="1" name="edit_students_classteacher">';
		
		$ret .= '<option';
		if (!$found) $ret .= ' selected="selected"';
		$ret .= ' value="">auswählen</option>';
		
		foreach ($teachers as $tid => $tdata) {
		
			$ret .= '<option';
			if ($tid == $found) { $ret .= ' selected="selected"'; }
			$ret .= ' value="'.$tid.'">'.id_to_address($tdata).'</option>';
				
		}
		
		$ret .= '</select>';
		
		$ret .= '</th>';
		if (($auth == 'supervisor' OR $auth == 'advanced') AND $view == 'edit_students') $ret .= '<th colspan="5">Teilnahmestatus</th>';
		$ret .= '</tr>';
		$ret .= '<tr>';
		$ret .= '<th>#</th>';
		$ret .= '<th>Nachname</th>';
		$ret .= '<th>Vorname</th>';
		$ret .= '<th>Geschlecht</th>';
		
		if ($view == 'edit_students') {
			if ($auth == 'supervisor') $ret .= '<th>zugeteiltes<br/>Angebot</th>';
			for ($i = 1; $i <= $info['props']['max_wishes']; $i++) {
				$ret .= '<th>Wunsch '.$i.'</th>';
			}
		
			if ($auth == 'supervisor') {
				$ret .= '<th>Buszuordnung</th>';
			}
			
			if ($auth == 'supervisor' OR $auth == 'advanced') {
				$ret .= '<th title="Event hat noch nicht stattgefunden">N/A</th>';
				$ret .= '<th title="teilgenommen">TN</th>';
				$ret .= '<th title="Absage bis 1 Woche vorher">1w</th>';
				$ret .= '<th title="Entschuldigt nicht teilgenommen">e.</th>';
				$ret .= '<th title="Unentschuldigt nicht teilgenommen">n.e.</th>';
			}
		}
			
		$ret .= '</tr>';
		
		$ret .= '</thead><tbdoy>';
		
		$n = 1;

		for ($l = 0; $l < $blankofelder; $l++) {
			$classes[$classfilter]['TEMP'.$l] = Array('id'=>'TEMP'.$l,'surname'=>'','forename'=>'','gender'=>'','offer'=>'');
			for ($i = 1; $i <= $info['props']['max_wishes']; $i++) {
				$classes[$classfilter]['TEMP'.$l]['wish_'.($i-1)] = '';
			}
		}
		
		foreach($classes[$classfilter] as $tid => $tdata) {
			
			if ($tid == 'TEMP0') {
				$ret .= '<tr>';
				$ret .= '<td class="th" colspan="'.(($view == 'edit_students')?(($auth == 'supervisor')?13:7):4).'" style="font-weight:bold;">Schüler hinzufügen: (Angabe von Nachname oder Vorname ist Pflicht)</td>';
				$ret .= '</tr>';
			}
			
			if (bcmod($n,2) == 1) {
				$ret .= '<tr class="tr_class_0">';
			} else {
				$ret .= '<tr class="tr_class_1">';
			}
			$ret .= '<td>'.$n.'</td>';
			$ret .= '<td><input type="text" name="edit_students_'.$tid.'_surname" value="'.$tdata['surname'].'"/></td>';
			$ret .= '<td><input type="text" name="edit_students_'.$tid.'_forename" value="'.$tdata['forename'].'"/></td>';
			
			$ret .= '<td><input ';
			if ($tdata['gender'] == 'm') $ret .= 'checked="checked" ';
			$ret .= 'type="radio" name="edit_students_'.$tid.'_gender" id="edit_students_'.$tid.'_gender_m" value="m"/><label for="edit_students_'.$tid.'_gender_m">männlich</label><br/>';
			$ret .= '<input ';
			if ($tdata['gender'] == 'f') $ret .= 'checked="checked" ';
			$ret .= 'type="radio" name="edit_students_'.$tid.'_gender" id="edit_students_'.$tid.'_gender_f" value="f"/><label for="edit_students_'.$tid.'_gender_f">weiblich</label></td>';
			
			if ($view == 'edit_students') {	
				if ($auth == 'supervisor') {
					$ret .= '<td>'.produce_offer_list($offers,'edit_students_'.$tid.'_offer',$classfilter,$tdata['gender'],$tdata['offer']).'</td>';
				}
				
				for ($i = 1; $i <= $info['props']['max_wishes']; $i++) {
					$ret .= '<td>'.produce_offer_list($offers,'edit_students_'.$tid.'_wish_'.($i-1),$classfilter,$tdata['gender'],$tdata['wish_'.($i-1)]).'</td>';
				}
			
				if ($auth == 'supervisor') {
					
					if (isset($tdata['offer']) AND (int)$tdata['offer'] > 0) {
					
						$ret .= '<td><select class="select_offer" name="edit_students_'.$tid.'_bus" size="1">';
						
						$ret .= '<option value="0"';
						if (!isset($tdata['bus']) OR (int)$tdata['bus'] == 0 OR !in_array((int)$tdata['bus'],$bcp[$cpo[(int)$tdata['offer']]])) { $ret .= ' selected="selected"'; }
						$ret .= '>Noch nicht zugeteilt</option>';

						if (isset($cpo[(int)$tdata['offer']]) AND isset($bcp[$cpo[(int)$tdata['offer']]])) {
							foreach ($bcp[$cpo[(int)$tdata['offer']]] as $bid) {
								$bid = (int)$bid;
								if (isset($buses[$bid])) {
									$ret .= '<option value="'.$bid.'"';
									if ($bid == (int)$tdata['bus']) $ret .= ' selected="selected"';
									$ret .= '>'.$buses[$bid]['tag'].' ('.$buses[$bid]['company'].')</option>';
								}
							}
						}
						
						$ret .= '</select></td>';
					
					} else {
						$ret .= '<td>(Zunächst Angebot zuteilen)</td>';
					}
				
				}
				
				if ($auth == 'supervisor' OR $auth == 'advanced') {
				
					for ($i = 0; $i < 5; $i++) {
						$ret .= '<td><input type="radio" name="edit_students_'.$tid.'_cancelled" value="'.$i.'"';
						if (
							(!isset($tdata['cancelled']) AND $i == 0) 
							OR 
							(isset($tdata['cancelled']) AND $tdata['cancelled'] == $i)
						) {
							$ret .= ' checked="checked"';
						}
						$ret .= '/></td>';
					}
				}
			}
			
			$ret .= '</tr>';
			
			$n++;
		}
		
		$ret .= '</tbody></table>';

		if ($view == 'edit_students' OR $view == 'add_class') {
			
			$ret .= '<fieldset><legend>Optionen</legend>';
			
			$ret .= '<input type="hidden" readonly="readonly" name="edit_students_control_teacher" value="'.(get_class_teacher($classfilter,$teachers)).'"/>';

			if ($view == 'edit_students') {
				$ret .= '<textarea style="display:none;" rows="5" readonly="readonly" cols="25" name="edit_students_control_students">';
				$ret .= file_get_contents('./db/'.DB.'/class_'.$classfilter.'.ini');
				$ret .= '</textarea>';
			}
			
			$ret .= '<input type="hidden" value="'.$view.'" name="action" />';
			$ret .= '<input type="submit" value="Daten speichern." />';
			
			$ret .= '</fieldset>';
		
			$ret .= '</form>';					
			$ret .= '</div>';
		}
			
		for ($l = 0; $l < $blankofelder; $l++) {
			unset($classes[$classfilter]['TEMP'.$l]);
		}
		
		return $ret;
	}
	
	function produce_offer_edit ($view,$auth,$offer,$students,$teachers,$offers) {
		
		$ret = '';
		
		$od = $offers[$offer];
		
		if ($view == 'add_offer') {
			$ret .= '<div class="fieldset">';
			$ret .= '<h2>Angebot erstellen</h2>';
			$ret .= '<form action="./?view=default" method="POST">';
			$ret .= '<fieldset>';
			$ret .= '<legend>Hinweis</legend>';
			$ret .= '<p>Achten Sie in jedem Fall darauf, den Titel des Angebots zu vergeben. Dies ist die einzige Pflichtangabe zur Angebotserstellung, die anderen Felder sind optional.</p>';
			$ret .= '</fieldset>';
			
		} elseif ($view == 'edit_offer') {
			$ret .= '<div class="fieldset">';
			$ret .= '<h2>Bearbeitungsansicht Angebot '.$od['name'].'</h2>';
			$ret .= '<form action="./?offer='.$offer.'&view=edit_offer" method="POST">';
			$ret .= '<fieldset>';
			$ret .= '<legend>Hinweis</legend>';
			if ($auth == 'supervisor') {
				$ret .= '<p>Achten Sie darauf, dass der Titel des Angebots vorhanden ist. Zum Löschen des Angebots einfach den Angebotstitel entfernen, der Datensatz wird nach dem "Speichern" dann automatisch entfernt.</p>';
			} else {
				$ret .= '<p>Achten Sie darauf, dass der Titel des Angebots vorhanden ist. Ein Speichern ist ansonsten nicht möglich.</p>';
			}
			$ret .= '</fieldset>';
		}
		
		$ret .= '<fieldset>';
		$ret .= '<legend>Allgemeine Daten</legend>';
		$ret .= '<table class="st3c">';
		$ret .= '<tr><td>Angebotstitel:</td><td><input type="text" name="edit_offer_name" value="'.$od['name'].'" /></td><td>Titel des Angebots, z.B. Skifahren, Origami, ...</td></tr>';
		$ret .= '<tr><td>Beschreibung:</td><td><textarea rows="7" cols="30" name="edit_offer_longdesc">'.$od['longdesc'].'</textarea></td><td>Ausführliche Beschreibung für die Angebotsübersicht; sollte eine umfassende Beschreibung des Angebots und die Daten, die nicht in den untenstehenden Rahmenbedingungen festgelegt werden, enthalten. </td></tr>';
		$ret .= '</table>';
		$ret .= '</fieldset>';
		
		$ret .= '<fieldset>';
		$ret .= '<legend>Rahmendaten</legend>';
		$ret .= '<table class="st3c">';
		$ret .= '<tr><td>Einverständniserklärung</td><td style="text-align:left">';
		$ret .= '<input type="radio" name="edit_offer_eerkl" id="edit_offer_eerkl_j" value="1" ';
		if (!isset($od['eerkl']) OR (bool)$od['eerkl']) { $ret .= ' checked="checked"'; }
		$ret .= '/><label for="edit_offer_eerkl_j">Nötig</label><br>';
		$ret .= '<input type="radio" name="edit_offer_eerkl" id="edit_offer_eerkl_n" value="0" ';
		if (isset($od['eerkl']) AND !(bool)$od['eerkl']) { $ret .= ' checked="checked"'; }
		$ret .= '/><label for="edit_offer_eerkl_n">Nicht nötig</label>';
		$ret .= '</td><td>Gibt an, ob die Schüler eine Einverständniserklärung der Eltern abgeben müssen oder nicht.</td></tr>';
		$ret .= '<tr><td>Wo?</td><td><input type="text" name="edit_offer_place" value="'.$od['place'].'" /></td><td>Ort oder Raum für das Angebot, z.B. (bei Skiausfahrt) Oberstdorf, (bei Projekt) Raum 125, ...</td></tr>';
		$ret .= '<tr><td>Wann?</td><td><input type="text" name="edit_offer_time" value="'.$od['time'].'" /></td><td>Datum bzw. Zeitraum, an/in dem das Angebot stattfindet.</td></tr>';
		$ret .= '<tr><td>Treffpunkt:</td><td><input type="text" name="edit_offer_meet" value="'.($od['meet']?$od['meet']:'(wird noch bekannt gegeben)').'" /></td><td>Datum, Uhrzeit und Ort zum Beginn des Angebots. Vorschlag wenn noch nicht sicher: (wird noch bekannt gegeben)</td></tr>';
		$ret .= '<tr><td>Max. Teilnehmer:</td><td><input type="text" name="edit_offer_maxtn" value="'.($od['maxtn']?$od['maxtn']:'10').'" /></td><td>Maximale Teilnehmerzahl (ohne Lehrer)</td></tr>';
		if ($auth == 'supervisor') {
			$ret .= '<tr><td>Verantwortl. Lehrer:</td><td><select size="1" name="edit_offer_teacher">';
			$ret .= '<option';
			if (!$od['teacher']) $ret .= ' selected="selected"';
			$ret .= ' value="0">auswählen</option>';
			
			$occ = Array();
			
			foreach ($offers as $oid => $odata) {
				if (isset($odata['teacher']) AND $oid != $offer) {
					$occ[] = $odata['teacher'];
				}
			}
			
			foreach ($teachers as $tid => $tdata) {
			
				if ((bool)$tdata['available'] AND !in_array($tid,$occ)) { 
					$ret .= '<option';
						if ($tid == $od['teacher']) { $ret .= ' selected="selected"'; }
					$ret .= ' value="'.$tid.'">'.id_to_address($tdata).'</option>';
				}
					
			}
			
			$ret .= '</select></td><td>Primärer Ansprechpartner und Bearbeiter für dieses Angebot. Verfügbar sind nur Lehrer, die a) verfügbar sind, b) nicht für ein anderes Angebot verantwortlich sind.</td></tr>';
		}
		$ret .= '<tr><td>Bevorzugte Klassen:</td><td><input type="text" name="edit_offer_preferred" value="'.$od['preferred'].'" /></td><td>Klassen, die bei der Verteilung auf dieses Angebot zuerst berücksichtigt werden. Klassen mit Komma trennen. Für ganze Jahrgangsstufen nur den ersten Teil des Klassennamens notieren. Beispiel: siehe "gesperrte Klassen".</td></tr>';
		$ret .= '<tr><td>Gesperrte Klassen:</td><td><input type="text" name="edit_offer_excluded" value="'.$od['excluded'].'" /></td><td>Klassen, die an diesem Angebot nicht teilnehmen dürfen. Eingaberegeln wie oben. Beispiel: Das Angebot ist für die Klassen 5, die Klasse 6c, den Mathekurs 1 in K1 und die gesamte K2 gesperrt: "5,6c,K1LKM1,K2"</td></tr>';
		$ret .= '<tr><td>Verfügbare Geschlechter:</td><td style="text-align:left">';
		$ret .= '<input type="radio" ';
		if (!isset($od['avgender']) OR $od['avgender'] == 'm,f') $ret .= 'checked="checked"'; 
		$ret .= ' name="edit_offer_avgender" value="m,f" id="edit_offer_avgender_mf" /><label for="edit_offer_avgender_mf">Männl. & Weibl.</label><br/>';
		$ret .= '<input type="radio" ';
		if (isset($od['avgender']) AND $od['avgender'] == 'm') $ret .= 'checked="checked"'; 
		$ret .= ' name="edit_offer_avgender" value="m" id="edit_offer_avgender_m" /><label for="edit_offer_avgender_m">Nur Männlich</label><br/>';
		$ret .= '<input type="radio" ';
		if (isset($od['avgender']) AND $od['avgender'] == 'f') $ret .= 'checked="checked"'; 
		$ret .= ' name="edit_offer_avgender" value="f" id="edit_offer_avgender_f" /><label for="edit_offer_avgender_f">Nur Weiblich</label>';
		$ret .= '</td><td>Wählen Sie, welches Geschlecht die Teilnehmer haben dürfen. Ist bei einem Teilnehmer kein Geschlecht angegeben wird ihm der Zugang gewährt.</td></tr>';
		$ret .= '</table>';
		$ret .= '</fieldset>';
		
		$ret .= '<fieldset>';
		$ret .= '<legend>Kosten</legend>';
		$ret .= '<table class="st3c">';
		$ret .= '<tr><td>Teilnehmerbeitrag:</td><td><input type="text" name="edit_offer_costs" value="'.float_to_eur($od['costs']).'" /></td><td>Teilnehmerbeitrag (komplett). Trennung Dezimalzahlen mit Punkt oder Komma, KEIN Tausendertrennzeichen.</td></tr>';
		$ret .= '<tr><td>...davon Vorauskasse:</td><td><input type="text" name="edit_offer_precosts" value="'.float_to_eur($od['precosts']).'" /></td><td>Höhe des Betrags, der in Vorauskasse mit Abgabe der Einverständniserklärung zu entrichten ist. Der Restbetrag ist vom Schüler zur Veranstaltung mitzubringen.</td></tr>';
		$ret .= '<tr><td>Betrag erstattungsfähig<br/>(rechtzeitige Abmeldung):</td><td><input type="text" name="edit_offer_refundall" value="'.float_to_eur($od['refundall']).'" /></td><td>Erstattungsfähiger Teil des in Vorauskasse gezahlten Betrags bei rechtzeitiger Abmeldung (mind. 1 Woche vorher abgemeldet). Soweit möglich sollte der volle Vorauskasse-Beitrag erstattet werden.</td></tr>';
		$ret .= '<tr><td>Betrag erstattungsfähig<br/>(entschuldigtes Fehlen):</td><td><input type="text" name="edit_offer_refund" value="'.float_to_eur($od['refund']).'" /></td><td>Erstattungsfähiger Teil des in Vorauskasse gezahlten Betrags bei entschuldigtem Fehlen (zwischen 1 Woche vorher und dem Tag der Veranstaltung). Bei unentschuldigtem Fehlen wird kein Betrag zurückerstattet.</td></tr>';
		$ret .= '</table>';
		$ret .= '</fieldset>';
		
		if ($view == 'edit_offer') {
		
			$ret .= '<fieldset>';
			$ret .= '<legend>Hinweise zur Durchführung</legend>';
			$ret .= '<table class="st3c">';
			$ret .= '<tr><td>Wichtige Telefonnummern</td><td style="overflow:auto;white-space:nowrap">';
			
			$i = 0;
			$bool = true;
			for ($i = 0; $i < 50; $i++) {
				if (isset($od['phonetags'.$i])) {
					$ret .= '<input type="text" name="edit_offer_phonetags'.$i.'" value="'.$od['phonetags'.$i].'" />';
					$ret .= '<input type="text" name="edit_offer_phonenrs'.$i.'" value="';
					$ret .= isset($od['phonenrs'.$i])?$od['phonenrs'.$i]:'';
					$ret .= '" /><hr/>';
				} elseif ($bool) {
				
					$ret .= '<input type="text" name="edit_offer_phonetags'.$i.'" value="" />';
					$ret .= '<input type="text" name="edit_offer_phonenrs'.$i.'" value="" />';
					$bool = false;
					
				}
			}
			
			
			$ret .= '</td><td>Tragen Sie jeweils zuerst eine eindeutige Bezeichnung und dann die dazugehörige Telefonnummer ein. Nach jedem Speichervorgang wird ein weiteres Feld freigeschaltet. Z.B.: Nummer des nächsten Krankenhauses, Nummer des Skigebiets, Nummer des Busunternehmens... Angabe der Bezeichnung ist Pflicht, ohne Bezeichnung erfolgt eine Löschung der Nummer.</td></tr>';
			$ret .= '<tr><td>Hinweise</td><td>';
			$ret .= '<textarea name="edit_offer_hints" style="width:350px" cols="50" rows="8">'.(isset($od['hints'])?$od['hints']:'').'</textarea>';
			$ret .= '</td><td>Hinweise an die begleitenden Lehrer, z.B.: Öffnungszeiten, Regeln, Abfahrtszeiten und -ort... </td></tr>';
			$ret .= '</table>';
			$ret .= '</fieldset>';
		
			$ret .= '<fieldset>';
			$ret .= '<legend>Nach Durchführung auszufüllen</legend>';
			$ret .= '<table class="st3c">';
			$ret .= '<tr><td>Übriger Geldbetrag:</td><td><input type="text" name="edit_offer_left" value="'.float_to_eur($od['left']).'" /></td><td>Gesamtbetrag, der nach Durchführung noch übrig geblieben ist und wieder an die Teilnehmer rückerstattet werden kann. <span class="b">Beachten Sie, dass auch die Zahlungen an die (rechtzeitig) entschuldigten Schüler aus diesem Topf finanziert werden!</span></td></tr>';
			$ret .= '</table><br/>';
			
			$ret .= '<table class="ov_table ed_table">';
			$ret .= '<thead>';
			$ret .= '<tr>';
			$ret .= '<th colspan="5">Teilnehmer Angebot '.$od['name'].'</th>';
			$ret .= '<th colspan="5">Teilnahmestatus</th>';
			$ret .= '</tr>';
			$ret .= '<tr>';
			$ret .= '<th>#</th>';
			$ret .= '<th>Kl.</th>';
			$ret .= '<th>Nachname</th>';
			$ret .= '<th>Vorname</th>';
			$ret .= '<th>Geschlecht</th>';
		
			$ret .= '<th title="Event hat noch nicht stattgefunden">N/A</th>';
			$ret .= '<th title="teilgenommen">TN</th>';
			$ret .= '<th title="Absage bis 1 Woche vorher">1w</th>';
			$ret .= '<th title="Entschuldigt nicht teilgenommen">e.</th>';
			$ret .= '<th title="Unentschuldigt nicht teilgenommen">n.e.</th>';
			
			$ret .= '</tr>';
			
			$ret .= '</thead><tbdoy>';
			
			$n = 1;
			
			$teilnehmer = Array();
			
			foreach ($students as $tid => $tdata) {
				if (isset($tdata['offer']) AND $tdata['offer'] == $offer) {
					$teilnehmer[$tdata['id']] = $tdata;
				}
			}
			
			uasort($teilnehmer,'sort_persons');
			
			if (count($teilnehmer)) {
			
				foreach($teilnehmer as $tid => $tdata) {
					
					if (bcmod($n,2) == 1) {
						$ret .= '<tr class="tr_class_0">';
					} else {
						$ret .= '<tr class="tr_class_1">';
					}
					$ret .= '<td>'.$n.'</td>';
					$ret .= '<td>'.$tdata['class'].'</td>';
					$ret .= '<td>'.$tdata['surname'].'</td>';
					$ret .= '<td>'.$tdata['forename'].'</td>';
					if ($tdata['gender'] == 'm') {
						$ret .= '<td>männlich</td>';
					} elseif ($tdata['gender'] == 'f') {
						$ret .= '<td>weiblich</td>';
					} else {
						$ret .= '<td>?</td>';
					}
					
					for ($i = 0; $i < 5; $i++) {
						$ret .= '<td><input type="radio" name="edit_offer_students_'.$tdata['class'].'_'.$tid.'_cancelled" value="'.$i.'"';
						if (
							(!isset($tdata['cancelled']) AND $i == 0) 
							OR 
							(isset($tdata['cancelled']) AND $tdata['cancelled'] == $i)
						) {
							$ret .= ' checked="checked"';
						}
						$ret .= '/></td>';
					}
					
					
					$ret .= '</tr>';
					
					$n++;
				}
				
			} else {
				$ret .= '<tr><td colspan="10">Momentan noch keine Teilnehmer eingetragen.</td></tr>';
			}
			
			$ret .= '</tbody></table>';
			
			$ret .= '</fieldset>';
		}	
				
		if ($view == 'add_offer' OR $view == 'edit_offer') {
			$ret .= '<fieldset>';
			$ret .= '<legend>Optionen</legend>';
			
			if ($view == 'edit_offer') {
				$ret .= '<textarea rows="5" cols="50" style="display:none" name="edit_offer_control_offers">';
				write_ini_file('./temp',$offers);
				$ret .= file_get_contents('./temp');
				unlink('./temp');
				$ret .= '</textarea>';
				$ret .= '<textarea rows="5" cols="50" style="display:none" name="edit_offer_control_students">';
				$temp = Array();
				foreach ($students as $tid => $tdata) {
					if ($tdata['offer'] == $offer) {
						$temp[$tid] = $tdata; 
					}
				}
				
				write_ini_file('./temp',$temp);
				$ret .= file_get_contents('./temp');
				unlink('./temp');
				
				$ret .= '</textarea>';
			}
			
			if ($view == 'add_offer') { $ret .= '<input type="hidden" name="action" value="add_offer"/>'; }
			if ($view == 'edit_offer') { $ret .= '<input type="hidden" name="action" value="edit_offer"/>'; }
			$ret .= '<input type="submit" value="Speichern." />';
			$ret .= '</fieldset>';
			$ret .= '</form></div>';
		}
		
		return $ret;
		
	}
	
	class PVSPDF extends FPDF
	{
	
		function Header()
		{
			$this->SetCreator(utf8_decode('Projektverteilungssystem für Schulen, (C)2013, Janosch Zoller'));
			//Schullogo einfügen
			$this->Image($GLOBALS['school_logo'],20,10,25);
			//Schulname einfügen
			$t = 50;
			$this->SetFont('Arial','B',$t);
			while($this->GetStringWidth(utf8_decode($GLOBALS['school_name'])) > 142 ) {
				$t--;
				$this->SetFontSize($t);
			}
				
			$t1 = $t;
			$t = round((30-6-$t1*MMPPT-2*count($GLOBALS['school_address']))/(MMPPT*count($GLOBALS['school_address'])));
			$this->SetFont('Arial','I',$t);
			
			$k = 0; $l = 0;
			foreach ($GLOBALS['school_address'] as $key => $txt) {
				$lt = $this->GetStringWidth(utf8_decode($txt));
				if ($lt > $l) { $l = $lt; $k = $key; }
			}
			
			while($this->GetStringWidth(utf8_decode($GLOBALS['school_address'][$k])) > 142) {
				$t--;
				$this->SetFontSize($t);
			}
			
			$dy = max((30-$t1*MMPPT-6-(MMPPT*$t+2)*count($GLOBALS['school_address']))/2,0);
			
			$this->SetXY(15+27+5+2,10+($t1*MMPPT/2)+$dy);
			$this->SetFont('Arial','B',$t1);
			$this->Write(0,utf8_decode($GLOBALS['school_name']));
			
			$y = 6+($t1*MMPPT)+($t*MMPPT/2)+6+$dy;
			
			$this->SetFont('Arial','I',$t);
			$this->SetTextColor(153);
			foreach ($GLOBALS['school_address'] as $txt) {
				$this -> SetXY(15+30+5+2,$y);
				$this->Write(0,utf8_decode($txt));
				$y += $t*MMPPT+2;
			}
			
			$this->SetTextColor(0);
			
			$this->SetLineWidth(0.5);
			$this->Line(15+1,38,210-15+1,38);
			
			$this->SetY(39+MMPPT*10);
			$this->SetFont('Arial','B',20);
			$tstr = (isset($GLOBALS['pdfOptions']['headersubt']) AND $GLOBALS['pdfOptions']['headersubt'] != '')?$GLOBALS['pdfOptions']['headersubt']:'';
			$this->Cell(0,0,$GLOBALS['info']['info']['name'].$tstr,0,0,'C');
			
			$this->Line(15+1,47,210-15+1,47);
			$this->Ln(8);
			//Ausgabefenster von 55mm bis 278mm
		}

		// Page footer
		function Footer()
		{
			if (!isset($GLOBALS['pdfOptions']['suppressfooter']) OR !$GLOBALS['pdfOptions']['suppressfooter']) {
				// Logo
				$this->SetLineWidth(0.5);
				$this->Line(15+1,278,210-15+1,278);
				
				$this->SetFont('Arial','B',13);
				$x = 210 - 2*9 - ($this->GetStringWidth(utf8_decode('Projektverteilungssystem für Schulen')));
				$this->Image('./vorlagen/pvs_kompakt.png',$x-10,279.5,9);
				$this->SetXY($x,-15);
				$this->Write(0,utf8_decode('Projektverteilungssystem für Schulen'));
				$this->SetXY($x,-10);
				$this->SetFont('Arial','',8);
				$this->Write(0,utf8_decode('© 2010-2013, Janosch W. Zoller'));
				
				// Pagecount
				if (is_bool($GLOBALS['pdfOptions']['pagecount']) AND $GLOBALS['pdfOptions']['pagecount']) {
					$this->SetXY(15,-13);
					
					$this->SetFont('Arial','I',10);
					if (isset($this->AliasNbPages) AND $this->AliasNbPages != '') {
						$this->Write(0,'Seite '.$this->PageNo().' von '.$this->AliasNbPages);
					} else {
						$this->Write(0,'Seite '.$this->PageNo().' von {nb}');
					}
				} elseif (is_string($GLOBALS['pdfOptions']['pagecount'])) {
					$this->SetXY(15,-13);
					
					$this->SetFont('Arial','I',10);
					$this->Write(0,$GLOBALS['pdfOptions']['pagecount']);
					
				}
			}
		}
	
		function Table($header,$data,$format) {	//$data = Array(row1 = Array( col1= ...)... ) $format = Array(width_col_1,width_col_2...);
			$width = 0;
			$cols = 0;
			
			if (isset($format['colwidth']) AND is_array($format['colwidth'])) {
				$i = 1;
				foreach ($format['colwidth'] as $colwidth) {
					if (!isset($format['width_col_'.$i])) {	
						$format['width_col_'.$i] = $colwidth;
					}
					$i++;
				}				
			}
			
			for ($i = 1; $i < count($format); $i++) {
				if (isset($format['width_col_'.$i])) {
					$width = $width + $format['width_col_'.$i]; 
					$cols++;
				} else {
					break;
				}
			}
			$startY = $this->GetY();
			$startX = $this->GetX();
			
			if (isset($format['table-align'])) {
				switch ($format['table-align']) {
					case 'L':
						$startX = 15;
						break;
					case 'R':
						$startX = -15-$width;
						break;
					case 'C':
						$startX = max((210-$width)/2,0);
						break;
				}
			}
			
			if (isset($format['color2ndrow']) AND is_array($format['color2ndrow'])) {
				$this->SetFillColor($format['color2ndrow'][0],$format['color2ndrow'][1],$format['color2ndrow'][2]);
				$color2ndrow = $format['color2ndrow'];
			} elseif (isset($format['color2ndrow']) AND is_numeric($format['color2ndrow'])) {
				$this->SetFillColor($format['color2ndrow']);
				$color2ndrow = Array($format['color2ndrow'],$format['color2ndrow'],$format['color2ndrow']);
			} elseif (isset($format['color2ndrow']) AND $format['color2ndrow'] === true) {
				$this->SetFillColor(221);
				$color2ndrow = Array(221,221,221);
			} else {
				$this->SetFillColor(255);
				$color2ndrow = Array(255,255,255);
			}
			
			$fontsize = isset($format['fontsize'])?$format['fontsize']:12;
			$rowheight = isset($format['rowheight'])?$format['rowheight']:$fontsize*MMPPT+3;
					
			$pages = Array();
			
			$rowcounter = $header;
			$rowsperpage = floor((270-$startY)/$rowheight);
			$pagecounter = -1;
			
			for ($i = 0; $i < ceil(count($data)*2/($rowsperpage)); $i++) {
				$pagecounter++;
				for ($k = 0; $k < $rowsperpage; $k++) {
					if ($k >= $header OR (isset($format['repeat_header']) AND !$format['repeat_header'] AND $pagecounter > 0)) {
						if (isset($data[$rowcounter])) {
							if (!isset($pages[$pagecounter])) $pages[$pagecounter] = Array();
							$pages[$pagecounter][] = $data[$rowcounter];
							$rowcounter++;
						} else {
							break 2;
						}
					} elseif (isset($data[$rowcounter])) {
						if (!isset($pages[$pagecounter])) $pages[$pagecounter] = Array();
						
						$pages[$pagecounter][] = Array();
						
						foreach($data[$k] as $col) {
							if (is_array($col)) {
								if (!isset($col['fontstyle'])) $col['fontstyle'] = 'B';
								$pages[$pagecounter][count($pages[$pagecounter])-1][] = $col;
							} else {
								$pages[$pagecounter][count($pages[$pagecounter])-1][] = Array('content'=>$col,'fontstyle'=>'B');
							}
						}
					
					} else {
						break 2;
					}
				}
				if ($i == 0) $rowsperpage = floor((270-55)/$rowheight);
				if (isset($format['forcepagebreak']) AND (int)$format['forcepagebreak'] < $rowsperpage) {
					$rowsperpage = (int)$format['forcepagebreak']; 
				} 
	
			}
			
			$this->SetLineWidth(0.2);
			$this->SetFont("Arial","B",$fontsize);
			
			foreach ($pages as $key => $pdata) {
				$n = 1;
				$ydiff = 0;
				foreach ($pdata as $row) {
					$this->SetXY($startX,$startY+($n-1)*$rowheight+$ydiff);
					
					if ($n == ($header + 1) OR (isset($format['repeat_header']) AND !$format['repeat_header'] AND $key > 0)) {
						$this->SetFont("Arial",'',$fontsize);
						$this->SetLineWidth(0.2);
					}
					
					if ( isset($format['color2ndrow']) AND $format['color2ndrow'] AND ($n > $header OR ( (isset($format['repeat_header']) AND !$format['repeat_header'] AND $key > 0) )) AND bcmod(($n-$header),2) == 0) {
						$colorbool = true;
					} else {
						$colorbool = false;
					}
					
					$this->SetXY($startX,$startY+($n-1)*$rowheight+$ydiff);
					$i = 1;
					
					foreach ($row as $col) {
						if (is_string($col) OR is_numeric($col)) {
							$this->Cell($format['width_col_'.$i],$rowheight,utf8_decode($col),1,0,isset($format['align_col_'.$i])?$format['align_col_'.$i]:'C',$colorbool);
							$i++;
						} elseif (is_array($col) AND isset($col['content'])) {
							
							$colspan = isset($col['colspan'])?(int)$col['colspan']:1;
							$align = isset($col['align'])?$col['align']:(isset($format['align_col_'.$i])?$format['align_col_'.$i]:'C');
							
							$multi = isset($col['multi'])?$col['multi']:false;
							
							$fontstyle = isset($col['fontstyle'])?$col['fontstyle']:'';
							
							$font = isset($col['font'])?$col['font']:'Arial';
							
							$fillcolor = isset($col['fillcolor'])?$col['fillcolor']:$colorbool;
							
							$bordercolor = isset($col['bordercolor'])?$col['bordercolor']:0;
							
							$drawborder = isset($col['drawborder'])?(bool)$col['drawborder']:true;
							
							if (isset($col['fill'])) {
								$fill = (bool)$col['fill'];
							} else {
								$fill = ($fillcolor !== false);
							}
							
							if (is_array($bordercolor)) {
								$this->SetDrawColor($bordercolor[0],$bordercolor[1],$bordercolor[2]);
							} else {
								$this->SetDrawColor($bordercolor);
							}
							
							if (is_array($fillcolor)) {
								$this->SetFillColor($fillcolor[0],$fillcolor[1],$fillcolor[2]);
							}
							
							if (is_numeric($fillcolor)) {
								$this->SetFillColor($fillcolor);
							}
							
							$this->SetFont($font,$fontstyle,$fontsize);
							
							$cw = 0;
							for ($l = 0; $l < $colspan; $l++) {
								if (isset($format['width_col_'.($i+$l)])) {
									$cw = (int)$cw + (int)$format['width_col_'.($i+$l)];
								}
							}
							
							if ($multi) {
								//echo $this->GetStringWidth(utf8_decode($col['content']));
								$height = $rowheight * ceil(($this->GetStringWidth(utf8_decode($col['content']))+20)/$cw);
								$ydiff = max($height - $rowheight,0);
								$this->MultiCell($cw,$rowheight,utf8_decode($col['content']),$drawborder,$align,$fill);
								$this->Ln();
							} else {
								$this->Cell($cw,$rowheight,utf8_decode($col['content']),$drawborder,0,$align,$fill);
							}
							
							$this->SetFillColor($color2ndrow[0],$color2ndrow[1],$color2ndrow[2]);
							$this->SetDrawColor(0);
							$this->SetFont("Arial","",$fontsize);
							
							$i = $i + $colspan;
							
						} else {
							$this->Cell($format['width_col_'.$i],$rowheight,utf8_decode(''),1,0,isset($format['align_col_'.$i])?$format['align_col_'.$i]:'C',$colorbool);
							$i++;
						}
						
					}
					
					$n++;
				}
				
				
				if ( $header > 0 AND ($key == 0 OR (isset($format['repeat_header']) AND $format['repeat_header']))) {
					//Header-Rahmen erzeugen
					$this->SetXY($startX,$startY);
					$this->SetLineWidth(0.8);
					$this->Cell($width,$rowheight*$header,'',1,1);
				}
				
				//Äußeren Rahmen erzeugen
				$this->SetXY($startX,$startY);
				$this->SetLineWidth(1);
				$this->Cell($width,$rowheight*(count($pdata))+$ydiff,'',1,1);
				
				$this->SetLineWidth(0.2);
				
				if (($key + 1) < count($pages)) {
					$this->AddPage();
					$startY = 55;
				}
			}	
		}
	
	}
	
?>