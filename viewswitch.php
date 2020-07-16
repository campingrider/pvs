<?php

	$ret =& $GLOBALS['output'];
	$pdf =& $GLOBALS['output_pdf'];

	$portalbool = false;

	if (
		(VIEW == 'default')
		OR
		(
		isset($GLOBALS['views']) AND isset($GLOBALS['views']['all']) AND isset($GLOBALS['views']['all'][VIEW])
		AND
		(AUTH == 'supervisor' OR (AUTH == 'basic' AND $GLOBALS['views']['all'][VIEW]['basallowed']) OR (AUTH == 'advanced' AND $GLOBALS['views']['all'][VIEW]['advallowed']))
		)
	) {
	
		switch (VIEW) {
			
			//HTML-Ansichten Übersicht und Bearbeitung
			
			case 'ov_teachers': {
					
					$GLOBALS['output'] .= '<div class="fieldset" style="text-align:center">';
					$GLOBALS['output'] .= '<h2>Übersicht Lehrkräfte</h2>';
					
					$temp = Array(Array(),Array());
					
					foreach ($teachers as $tid => $tdata) {
					
						if (!isset($tdata['available']) OR $tdata['available'] === '1' OR $tdata['available'] === true OR $tdata['available'] === 1) {
							$temp[0][$tid] = $tdata; 
						} else {
							$temp[1][$tid] = $tdata; 
						}
					
					}
					
					for ($j = 0; $j < 2; $j++){
						
						if (!count($temp[$j])) {
							break;
						}
						
						if ($j == 0) {
							$GLOBALS['output'] .= '<h3>Teilnehmende Lehrkräfte</h3>';
						} else {
							$GLOBALS['output'] .= '<h3>Nicht teilnehmende Lehrkräfte</h3>';
						}
						
						$GLOBALS['output'] .= '<table class="ov_table">';
						$GLOBALS['output'] .= '<thead>';
						$GLOBALS['output'] .= '<tr>';
						$GLOBALS['output'] .= '<th>#</th>';
						$GLOBALS['output'] .= '<th>Kürzel</th>';
						$GLOBALS['output'] .= '<th>Nachname</th>';
						$GLOBALS['output'] .= '<th>Vorname</th>';
						$GLOBALS['output'] .= '<th>Geschlecht</th>';
						$GLOBALS['output'] .= '<th>Klassen-<br/>lehrer</th>';
						if ($j == 0) {
							$GLOBALS['output'] .= '<th>zugeteiltes</br>Angebot</th>';
							for ($i = 1; $i <= $info['props']['max_wishes']; $i++) {
								$GLOBALS['output'] .= '<th>Wunsch '.$i.'</th>';
							}
							$GLOBALS['output'] .= '<th>Buszuordnung</th>';
						}
						$GLOBALS['output'] .= '</tr>';
						$GLOBALS['output'] .= '</thead><tbdoy>';
						
						$n = 1;
						
						foreach($temp[$j] as $tid => $tdata) {
							
							if (bcmod($n,2) == 1) {
								$GLOBALS['output'] .= '<tr class="tr_class_0">';
							} else {
								$GLOBALS['output'] .= '<tr class="tr_class_1">';
							}
							$GLOBALS['output'] .= '<td>'.$n.'</td>';
							$GLOBALS['output'] .= '<td>'.$tdata['sign'].'</td>';
							$GLOBALS['output'] .= '<td>'.$tdata['surname'].'</td>';
							$GLOBALS['output'] .= '<td>'.$tdata['forename'].'</td>';
							if ($tdata['gender'] == 'm') {
								$GLOBALS['output'] .= '<td>männlich</td>';
							} elseif ($tdata['gender'] == 'f') {
								$GLOBALS['output'] .= '<td>weiblich</td>';
							} else {
								$GLOBALS['output'] .= '<td>?</td>';
							}
							$GLOBALS['output'] .= '<td>'.$tdata['class'].'</td>';
							if ($j == 0) {
								if (isset($offers[$tdata['offer']]) AND isset($offers[$tdata['offer']]['teacher']) AND (int)$offers[$tdata['offer']]['teacher'] == (int)$tid ) {
									$GLOBALS['output'] .= '<td colspan="'.(1+$info['props']['max_wishes']).'"><span style="font-weight:bold">'.$offers[$tdata['offer']]['name'].'</span> (verantwortl.)</td>';
								} else {
									if ($tdata['offer'] == '' OR $tdata['offer'] == 0) {
										$GLOBALS['output'] .= '<td>-</td>';
									} else {
										$GLOBALS['output'] .= '<td>'.$offers[$tdata['offer']]['name'].'</td>';
									}
									for ($i = 1; $i <= $info['props']['max_wishes']; $i++) {
										$GLOBALS['output'] .= '<td>'.($tdata['wish_'.($i-1)] != 0?$offers[$tdata['wish_'.($i-1)]]['name']:'-').'</td>';
									}
								}
								
								if (isset($tdata['bus']) AND (int)$tdata['bus'] > 0 AND isset($buses[$tdata['bus']])) {
									$ret .= '<td>'.$buses[$tdata['bus']]['tag'].' ('.$buses[$tdata['bus']]['company'].')</td>';
								} else {
									$ret .= '<td>Noch nicht zugeteilt</td>';
								}
							}
							$GLOBALS['output'] .= '</tr>';
							
							$n++;
						}
						
						$GLOBALS['output'] .= '</tbody></table>';
					}
					
					$GLOBALS['output'] .= '</div>';
					
					
				} break;
			case 'ov_students': if (isset($classes[CLASSFILTER])) {
					
					$GLOBALS['output'] .= '<div class="fieldset" style="text-align:center">';
					$GLOBALS['output'] .= '<h2>Übersicht Klasse '.CLASSFILTER.'</h2>';
						
					$GLOBALS['output'] .= '<table class="ov_table">';
					$GLOBALS['output'] .= '<thead>';
					$GLOBALS['output'] .= '<tr>';
					$GLOBALS['output'] .= '<th colspan="'.(8 + $info['props']['max_wishes']).'">Klasse '.CLASSFILTER.' - ';
					$GLOBALS['output'] .= 'Klassenlehrer/in: ';
					$found = get_class_teacher(CLASSFILTER,$teachers);
					if ($found) {
						$GLOBALS['output'] .= id_to_address($teachers[$found]);
					} else {
						$GLOBALS['output'] .= '(nicht angegeben)';
					}
					$GLOBALS['output'] .= '</th>';
					$GLOBALS['output'] .= '<th colspan="4">Teilnahmestatus</th>';
					$GLOBALS['output'] .= '</tr>';
					$GLOBALS['output'] .= '<tr>';
					$GLOBALS['output'] .= '<th>#</th>';
					$GLOBALS['output'] .= '<th>Nachname</th>';
					$GLOBALS['output'] .= '<th>Vorname</th>';
					$GLOBALS['output'] .= '<th>Geschlecht</th>';
					$GLOBALS['output'] .= '<th>zugeteiltes<br/>Angebot</th>';
					for ($i = 1; $i <= $info['props']['max_wishes']; $i++) {
						$GLOBALS['output'] .= '<th>Wunsch '.$i.'</th>';
					}
					$GLOBALS['output'] .= '<th>Betrag bezahlt</th>';
					$GLOBALS['output'] .= '<th>Angebotskosten<br/>(Vorauskasse)</th>';			
					$GLOBALS['output'] .= '<th>Buszuordnung</th>';			
					$GLOBALS['output'] .= '<th title="teilgenommen">TN</th>';
					$GLOBALS['output'] .= '<th title="Absage bis 1 Woche vorher">1w</th>';
					$GLOBALS['output'] .= '<th title="Entschuldigt nicht teilgenommen">e.</th>';
					$GLOBALS['output'] .= '<th title="Unentschuldigt nicht teilgenommen">n.e.</th>';
					$GLOBALS['output'] .= '</tr>';
					$GLOBALS['output'] .= '</thead><tbdoy>';
					
					$n = 1;
					
					foreach($classes[CLASSFILTER] as $tid => $tdata) {
						
						if (bcmod($n,2) == 1) {
							$GLOBALS['output'] .= '<tr class="tr_class_0">';
						} else {
							$GLOBALS['output'] .= '<tr class="tr_class_1">';
						}
						$GLOBALS['output'] .= '<td>'.$n.'</td>';
						$GLOBALS['output'] .= '<td>'.$tdata['surname'].'</td>';
						$GLOBALS['output'] .= '<td>'.$tdata['forename'].'</td>';
						
						if ($tdata['gender'] == 'm') {
							$GLOBALS['output'] .= '<td>männlich</td>';
						} elseif ($tdata['gender'] == 'f') {
							$GLOBALS['output'] .= '<td>weiblich</td>';
						} else {
							$GLOBALS['output'] .= '<td>?</td>';
						}
						
						if ($tdata['offer'] == '' OR $tdata['offer'] == 0) {
							$GLOBALS['output'] .= '<td>-</td>';
						} else {
							$GLOBALS['output'] .= '<td>'.$offers[$tdata['offer']]['name'].'</td>';
						}
						
						for ($i = 1; $i <= $info['props']['max_wishes']; $i++) {
							if ($tdata['wish_'.($i-1)] == 0 OR $tdata['wish_'.($i-1)] == '') {
								$GLOBALS['output'] .= '<td>-</td>';
							} else {
								$GLOBALS['output'] .= '<td>'.$offers[$tdata['wish_'.($i-1)]]['name'].'</td>';
							}
						}
						
						if (!isset($tdata['paid'])) {
							$tdata['paid'] = 0.0;
						}
						
						$paid = float_to_eur($tdata['paid']); 
						$GLOBALS['output'] .= '<td>'.$paid.'</td>';
						if ($tdata['offer'] == '' OR (int)$tdata['offer'] == 0) {
							$topay = float_to_eur(0.0);
						} else {
							$topay = float_to_eur($offers[$tdata['offer']]['costs']);
						}
						$GLOBALS['output'] .= '<td>'.$topay.'</td>';
					
						if (isset($tdata['bus']) AND (int)$tdata['bus'] > 0 AND isset($buses[$tdata['bus']])) {
							$ret .= '<td>'.$buses[$tdata['bus']]['tag'].' ('.$buses[$tdata['bus']]['company'].')</td>';
						} else {
							$ret .= '<td>Noch nicht zugeteilt</td>';
						}
						
						if (!isset($tdata['cancelled'])) $tdata['cancelled'] = 0;
						for ($j = 1; $j <= 4; $j++) {
							
							if ($j == (int)$tdata['cancelled']) {
								$GLOBALS['output'] .= '<td>X</td>';
							} else { $GLOBALS['output'] .= '<td></td>'; }
						
						}
						$GLOBALS['output'] .= '</tr>';
						
						$n++;
					}
					
					$GLOBALS['output'] .= '</tbody></table>';
					
					$GLOBALS['output'] .= '</div>';
					
				} else {
					$GLOBALS['hints'][] = '<span style="color:#900;font-weight:bold">Fehler: Die gewählte Klasse existiert nicht. Versuchen Sie es erneut.</span>';
					$portalbool = true;
				} break;
			case 'ov_offers': {
			
					$teilnehmer = Array();
					$teachers_count = Array();
					$wishes = Array();
					$cpo = Array();
					$bcp = Array();
					$ocp = Array();
					$btn = Array();
					
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
						if (!isset($btn[$bid])) $btn[$bid] = Array();
					}
					
					for ($i = 0; $i < $info['props']['max_wishes']; $i++) { $wishes[$i] = Array(); }
					
					foreach ($teachers as $tid => $tdata) {
						if (isset($tdata['bus']) AND (int)$tdata['bus'] > 0) {
							if (!isset($btn[(int)$tdata['bus']])) $btn[(int)$tdata['bus']] = Array();
							$btn[(int)$tdata['bus']][] = $tdata;
						}
						if (isset($tdata['offer']) AND (int)$tdata['offer'] > 0) {
							if (!isset($teilnehmer[$tdata['offer']])) $teilnehmer[$tdata['offer']] = Array();
							$teilnehmer[$tdata['offer']][] = $tdata;
							if (!isset($teachers_count[$tdata['offer']])) $teachers_count[$tdata['offer']] = 0;
							$teachers_count[$tdata['offer']]++;
						}
						foreach ($wishes as $wid => $wdata) {
							if (isset($tdata['wish_'.$wid]) AND (int)$tdata['wish_'.$wid] > 0) {
								if (!isset($wishes[$wid][$tdata['wish_'.$wid]])) $wishes[$wid][$tdata['wish_'.$wid]] = Array();
								$wishes[$wid][$tdata['wish_'.$wid]][] = $tdata;
							}
						}
					}
					foreach ($students as $tid => $tdata) {
						if (isset($tdata['bus']) AND (int)$tdata['bus'] > 0) {
							if (!isset($btn[(int)$tdata['bus']])) $btn[(int)$tdata['bus']] = Array();
							$btn[(int)$tdata['bus']][] = $tdata;
						}
						if (isset($tdata['offer']) AND (int)$tdata['offer'] > 0) {
							if (!isset($teilnehmer[$tdata['offer']])) $teilnehmer[$tdata['offer']] = Array();
							$teilnehmer[$tdata['offer']][] = $tdata;
						}
						foreach ($wishes as $wid => $wdata) {
							if (isset($tdata['wish_'.$wid]) AND (int)$tdata['wish_'.$wid] > 0) {
								if (!isset($wishes[$wid][$tdata['wish_'.$wid]])) $wishes[$wid][$tdata['wish_'.$wid]] = Array();
								$wishes[$wid][$tdata['wish_'.$wid]][] = $tdata;
							}
						}
					}
					foreach ($offers as $oid => $odata) {
						if (!isset($teilnehmer[$oid])) $teilnehmer[$oid] = Array();
						if (!isset($teachers_count[$oid])) $teachers_count[$oid] = 0;
						foreach ($wishes as $wid => $wdata) {
							if (!isset($wishes[$wid][$oid])) $wishes[$wid][$oid] = Array();
						}
					}
					
					$ret =& $GLOBALS['output'];
					
					$ret .= '<div class="fieldset">';
					$ret .= '<h2>Schnellzugriff</h2>';
					$ret .= '<p>Springe zu Angebot: ';
					foreach ($offers as $oid => $odata) {
						if (substr($ret,-2) != ': ') $ret .= ', ';
						$ret .= '<a href="#ov_offer_'.$oid.'">'.$odata['name'].'</a>';
					}
					$ret .= '</p>';
					$ret .= '</div>';
					
					foreach ($offers as $oid => $odata) {
						
						$ret .= '<div class="fieldset">';
						$ret .= '<h2 id="ov_offer_'.$oid.'">Angebotsübersicht: '.$odata['name'].'</h2>';
						
						$ret .= '<fieldset><legend>Angebotsdaten</legend>';
						$ret .= '<table class="ov_table" style="text-align:center"><thead>';
						$ret .= '<tr>';
						$ret .= '<th colspan="5" style="font-weight:bold">'.$odata['name'].'</th>';
						$ret .= '</tr>';
						$ret .= '</thead><tbody>';
						$ret .= '<tr>';
						$ret .= '<td>Wo? </td>';
						$ret .= '<td style="font-weight:bold">'.$odata['place'].'</td>';
						$ret .= '<td></td>';
						$ret .= '<td>Wann? </td>';
						$ret .= '<td style="font-weight:bold">'.$odata['time'].'</td>';
						$ret .= '</tr>';
						$ret .= '<tr><td colspan="5"><p>'.$odata['longdesc'].'</p></td></tr>';
						$ret .= '<tr><td colspan="2">Max. Teilnehmer: '.$odata['maxtn'].'</td>';
						$ret .= '<td colspan="2"></td>';
						$ret .= '<td>Teilnehmerbeitrag: <span style="text-align:right;font-weight:bold;text-decoration:underline">'.float_to_eur($odata['costs']).'</span></td></tr>';
						$ret .= '<tr><td colspan="5" class="th"></td></tr>';
						$ret .= '<tr><td colspan="3">Ansprechpartner:</td><td colspan="2">'.($odata['teacher']==0?'-':id_to_address($teachers[$odata['teacher']])).'</td></tr>';
						$ret .= '<tr><td colspan="3">Einverständniserklärung:</td><td colspan="2">';
						if (!isset($odata['eerkl']) OR $odata['eerkl']) { $ret .= 'Wird benötigt.'; } else { $ret .= 'Wird nicht benötigt.'; }
						$ret .= '</td></tr>';
						$ret .= '<tr><td colspan="3">Zugel. Geschlechter:</td><td colspan="2">';
						if (!isset($odata['avgender']) OR $odata['avgender'] == 'm,f') {
							$ret .= 'männlich & weiblich';
						} elseif ($odata['avgender'] == 'm') {
							$ret .= 'männlich';
						} elseif ($odata['avgender'] == 'f') {
							$ret .= 'weiblich';
						}
						$ret .= '</td></tr>';
						$ret .= '<tr><td colspan="3">Bevorzugte Klassen:</td><td colspan="2">'.($odata['preferred']==''?'-':$odata['preferred']).'</td></tr>';
						$ret .= '<tr><td colspan="3"><span style="font-weight:bold">NICHT</span> möglich für Klassen:</td><td colspan="2">'.($odata['excluded']==''?'-':$odata['excluded']).'</td></tr>';
						$ret .= '</tbody></table></fieldset>';
						
						$sums = Array('off'=>0);
						foreach ($students as $sid => $sdata) {
							if (isset($sdata['offer']) AND $sdata['offer'] == $oid) $sums['off']++;
							for ($i = 0; $i < $info['props']['max_wishes']; $i++) {
								if (!isset($sums[$i])) $sums[$i] = 0;
								if (isset($sdata['wish_'.$i]) AND $sdata['wish_'.$i] == $oid) $sums[$i]++;
							}
						}
						
						$ret .= '<fieldset><legend>Wunschverteilung / Interessenten</legend>';
						$ret .= '<table class="ov_table" style="text-align:center">';
						$ret .= '<tr>';
						$ret .= '<td>Wunschnummer</td>';
						$ret .= '<td></td>';
						for ($i = 0; $i < $info['props']['max_wishes']; $i++) {
							$ret .= '<td>'.($i + 1).'</td>';
						}
						$ret .= '<td></td>';
						$ret .= '<td>zugeteilt</td>';
						$ret .= '</tr>';
						$ret .= '<tr>';
						$ret .= '<td>Anzahl Schüler</td>';
						$ret .= '<td></td>';
						for ($i = 0; $i < $info['props']['max_wishes']; $i++) {
							$ret .= '<td>'.$sums[$i].'</td>';
						}
						$ret .= '<td></td>';
						$ret .= '<td>'.$sums['off'].'</td>';
						$ret .= '</tr>';
						$ret .= '</table>';
						$ret .= '</fieldset>';
						
						$ret .= '<fieldset><legend>Teilnehmer</legend>';
						$ret .= '<table class="ov_table" style="text-align:center"><thead>';
						$ret .= '<tr>';
						$ret .= '<th colspan="7" style="font-weight:bold">Teilnehmer '.$odata['name'].'</th>';
						$ret .= '</tr><tr>';
						$ret .= '<th colspan="3">Belegte Plätze: '.(count($teilnehmer[$oid])-$teachers_count[$oid]).'</th>';
						$ret .= '<th colspan="2"></th>';
						$ret .= '<th colspan="2">Freie Plätze: '.($odata['maxtn']-count($teilnehmer[$oid])+$teachers_count[$oid]).'</th>';
						$ret .= '</tr>';
						$ret .= '<tr>';
						$ret .= '<th>#</th>';
						$ret .= '<th>Klasse</th>';
						$ret .= '<th>Kürzel</th><th>Nachname</th>';
						$ret .= '<th>Vorname</th>';
						$ret .= '<th>Geschlecht</th>';
						$ret .= '<th>Buszuordnung</th>';
						$ret .= '</tr>';
						$ret .= '</thead><tbody>';
						$n = 1;
						foreach ($teilnehmer[$oid] as $tndata) {
							
							if (bcmod($n,2) == 1) {
								$GLOBALS['output'] .= '<tr class="tr_class_0">';
							} else {
								$GLOBALS['output'] .= '<tr class="tr_class_1">';
							}
							$GLOBALS['output'] .= '<td>'.$n.'</td>';
							if ($tndata['type'] == 'student') {
								$GLOBALS['output'] .= '<td>'.$tndata['class'].'</td><td colspan="2">';
							} elseif ($tndata['type'] == 'teacher') {
								$GLOBALS['output'] .= '<td>Lehrkraft</td><td>'.$tndata['sign'].'</td><td>';
							} else {
								$GLOBALS['output'] .= '<td></td><td colspan="2">';
							}
							$GLOBALS['output'] .= $tndata['surname'].'</td>';
							$GLOBALS['output'] .= '<td>'.$tndata['forename'].'</td>';
							
							if ($tndata['gender'] == 'm') {
								$GLOBALS['output'] .= '<td>männlich</td>';
							} elseif ($tndata['gender'] == 'f') {
								$GLOBALS['output'] .= '<td>weiblich</td>';
							} else {
								$GLOBALS['output'] .= '<td>?</td>';
							}
							
							if (isset($tndata['bus']) AND (int)$tndata['bus'] > 0 AND isset($buses[(int)$tndata['bus']])) {
								$ret .= '<td>'.$buses[(int)$tndata['bus']]['tag'].' ('.$buses[(int)$tndata['bus']]['company'].')</td>';
							} else {
								$ret .= '<td>Noch nicht zugeteilt</td>';
							}
							
							$n++;
						}
						$ret .= '</tbody></table></fieldset>';
						
						$ret .= '<fieldset><legend>Fahrgemeinschaft und Busse</legend>';
						if (isset($cpo[(int)$oid]) AND isset($carpools[(int)$cpo[(int)$oid]])) {				
							$ret .= '<p>Das Angebot ist Teil der Fahrgemeinschaft <span class="b">'.$carpools[$cpo[$oid]]['name'].'</span>.<br/>';
							$ret .= 'Diese Fahrgemeinschaft beinhaltet folgende Angebote...:';
							$ret .= '<ul>';
							$ttn = 0;
							foreach ($ocp[$cpo[$oid]] as $toid) {
								$toid = (int)$toid;
								if (isset($offers[$toid])) {	
									$ret .= '<li><span class="b">'.$offers[$toid]['name'].'</span> mit aktuell <span class="b">'.count($teilnehmer[$toid]).'</span> Teilnehmern.</li>';
									$ttn += count($teilnehmer[$toid]);
								}
							}
							$ret .= '</ul>';
							$ret .= '...und muss dementsprechend aktuell insgesamt <span class="b">'.$ttn.'</span> Plätze zur Verfügung stellen.</p>';
							$ret .= '<table class="ov_table"><thead>';
							$ret .= '<tr>';
							$ret .= '<th>#</th>';
							$ret .= '<th>Busunternehmen</th>';
							$ret .= '<th>Eindeutige Bezeichnung</th>';
							$ret .= '<th>Plätze...</th>';
							$ret .= '<th>...davon schon<br/>vergeben</th>';
							$ret .= '<th>...davon<br/>noch frei</th>';
							$ret .= '</tr></thead><tbdoy>';
							
							$n = 1;
							$tpg = 0;
							$tpv = 0;
							$tpf = 0;
							foreach ($bcp[(int)$cpo[(int)$oid]] as $bid) {
								$bid = (int)$bid;
								
								if (bcmod($n,2) == 1) {
									$GLOBALS['output'] .= '<tr class="tr_class_0">';
								} else {
									$GLOBALS['output'] .= '<tr class="tr_class_1">';
								}
								
								$ret .= '<td>'.$n.'</td>';
								$ret .= '<td>'.$buses[$bid]['company'].'</td>';
								$ret .= '<td>'.$buses[$bid]['tag'].'</td>';
								$ret .= '<td>'.$buses[$bid]['capacity'].'</td>';
								$tpg += $buses[$bid]['capacity'];
								$ret .= '<td>'.count($btn[$bid]).'</td>';
								$tpv += count($btn[$bid]);
								$ret .= '<td>'.($buses[$bid]['capacity'] - count($btn[$bid])).'</td>';
								$tpf += ($buses[$bid]['capacity'] - count($btn[$bid]));
								
								$ret .= '</tr>';
								$n++;
							}
							
							if (bcmod($n,2) == 1) {
								$GLOBALS['output'] .= '<tr class="tr_class_0">';
							} else {
								$GLOBALS['output'] .= '<tr class="tr_class_1">';
							}
								
							$ret .= '<td>&sum;</td><td></td><td></td><td>'.$tpg.'</td><td>'.$tpv.'</td><td>'.$tpf.'</td></tr>';
							
							$ret .= '</tbody></table>';
						} else {
							$ret .= 'Das Angebot gehört noch keiner Fahrgemeinschaft an und ist dementsprechend auch keinem Bus zugeordnet.';
						}
						$ret .= '</fieldset>';
						
						$ret .= '</div>';
					}
					
				} break; 
			case 'ov_payments': {
			
					$offerlist = Array();
					
					foreach ($students as $sid => $sdata) {

						
						if (isset($sdata['offer']) AND isset($offers[$sdata['offer']]) AND (int)$sdata['offer'] > 0) {
							$oid = $sdata['offer'];
						
							if (!isset($offerlist[$oid])) $offerlist[$oid] = Array();
							if (!isset($offerlist[$oid]['tn'])) $offerlist[$oid]['tn'] = 0;
							if (!isset($offerlist[$oid]['1w'])) $offerlist[$oid]['1w'] = 0;
							if (!isset($offerlist[$oid]['e'])) $offerlist[$oid]['e'] = 0;
							if (!isset($offerlist[$oid]['ne'])) $offerlist[$oid]['ne'] = 0;
							if (!isset($offerlist[$oid]['tncb'])) $offerlist[$oid]['tncb'] = 0;
							if (!isset($offerlist[$oid]['1wcb'])) $offerlist[$oid]['1wcb'] = 0;
							if (!isset($offerlist[$oid]['ecb'])) $offerlist[$oid]['ecb'] = 0;
							
							if (isset($sdata['cancelled'])) {
								if ($sdata['cancelled'] == 4) $offerlist[$oid]['ne']++;
								if ($sdata['cancelled'] == 3) $offerlist[$oid]['e']++;
								if ($sdata['cancelled'] == 2) $offerlist[$oid]['1w']++;
								if ($sdata['cancelled'] == 1 OR $sdata['cancelled'] == 0) $offerlist[$oid]['tn']++;
							} else {
								$offerlist[$oid]['tn']++;
							}
						}
					}
					
					foreach ($offers as $oid => $odata) {
						
						if (!isset($offerlist[$oid])) $offerlist[$oid] = Array();
						if (!isset($offerlist[$oid]['tn'])) $offerlist[$oid]['tn'] = 0;
						if (!isset($offerlist[$oid]['1w'])) $offerlist[$oid]['1w'] = 0;
						if (!isset($offerlist[$oid]['e'])) $offerlist[$oid]['e'] = 0;
						if (!isset($offerlist[$oid]['ne'])) $offerlist[$oid]['ne'] = 0;
						if (!isset($offerlist[$oid]['tncb'])) $offerlist[$oid]['tncb'] = 0;
						if (!isset($offerlist[$oid]['1wcb'])) $offerlist[$oid]['1wcb'] = 0;
						if (!isset($offerlist[$oid]['ecb'])) $offerlist[$oid]['ecb'] = 0;
						
						$offerlist[$oid]['precosts'] = (float)$odata['precosts'];
						
						$offerlist[$oid]['sum'] = (float)$odata['left'];
						$left = $offerlist[$oid]['sum'];
						$tn = $offerlist[$oid]['tn'];
						$w = $offerlist[$oid]['1w'];
						$wcb = $odata['refundall'];
						$e = $offerlist[$oid]['e'];
						$ecb = $odata['refund'];
						
						if ($left > 0.0) {
						
							if ($left <= ($w*$wcb + $e*$ecb)) {
							
								$p = $left/($w*$wcb + $e*$ecb);
								
								$offerlist[$oid]['1wcb'] = (float)(floor($wcb*$p*100)/100);
								$offerlist[$oid]['ecb'] = (float)(floor($ecb*$p*100)/100);
								$offerlist[$oid]['tncb'] = 0;						
							
							} else {
								$offerlist[$oid]['1wcb'] = $wcb;
								$offerlist[$oid]['ecb'] = $ecb;
								$left = $left - ($w*$wcb + $e*$ecb);
								$offerlist[$oid]['tncb'] = (float)(floor(($left / $tn)*100)/100);
							}
						
						}
						
					}
			
					$bdata = Array();
					
					foreach ($students as $sid => $sdata) {
						if (isset($sdata['offer']) AND isset($offers[$sdata['offer']]) AND $sdata['offer'] > 0) {
							if (!isset($bdata[$sdata['offer']])) $bdata[$sdata['offer']] = Array();
							$bdata[$sdata['offer']][$sid] = $sdata;
						}
					}
					
					$ret .= '<div class="fieldset">';
					
					$ret .= '<h2>Finanzstatus nach Klassen</h2>';
					
					$ret .= '<table class="ov_table">';
					$ret .= '<thead>';
					$ret .= '<tr>';
					$ret .= '<th>Klasse</th>';
					$ret .= '<th>KlassenlehrerIn</th>';
					$ret .= '<th>Anzahl<br/>Schüler...</th>';
					$ret .= '<th>...ohne<br/>Zuordnung</th>';
					$ret .= '<th>...mit voll-<br/>ständiger Zahlung</th>';
					$ret .= '<th>...mit unvoll-<br/>ständiger Zahlung</th>';
					$ret .= '<th>Fehlbetrag</th>';
					$ret .= '</tr>';
					$ret .= '</thead><tbody>';
					
					$n = 1;
					$gall = 0;
					$gsum = 0.0;
					$gctwo = 0;
					$gctpd = 0;
					$gcttp = 0;
					
					foreach ($classes as $clid => $cldata) {
						
						if (bcmod($n,2) == 1) {
							$GLOBALS['output'] .= '<tr class="tr_class_0">';
						} else {
							$GLOBALS['output'] .= '<tr class="tr_class_1">';
						}
						
						$ret .= '<td>'.$clid.'</td>';
						if (($ctid = get_class_teacher($clid,$teachers)) > 0 AND isset($teachers[$ctid])) {
							$ret .= '<td style="text-align:left">'.id_to_address($teachers[$ctid]).'</td>';
						} else {
							$ret .= '<td style="text-align:left">(Nicht zugeteilt)</td>';
						}
						$ret .= '<td>'.count($cldata).'</td>';
						$gall += count($cldata);
						
						$sum = 0.0;
						$ctwo = 0;
						$ctpd = 0;
						$cttp = 0;
						
						foreach ($cldata as $sid => $sdata) {
							if (isset($sdata['offer']) AND isset($offers[$sdata['offer']]) AND (int)$sdata['offer'] > 0) {
								if (isset($sdata['paid']) AND $sdata['paid'] >= $offers[$sdata['offer']]['precosts']) {
									$ctpd = $ctpd + 1;
								} else {
									$cttp = $cttp + 1;
									$sum = $sum + ((float)$offers[$sdata['offer']]['precosts'] - (float)$sdata['paid']);
								}
							} else {
								$ctwo = $ctwo + 1;
							}
						}
						
						$ret .= '<td>'.$ctwo.'</td>';
						$ret .= '<td>'.$ctpd.'</td>';
						$ret .= '<td>'.$cttp.'</td>';
						$ret .= '<td>'.float_to_eur($sum).'</td>';
						
						$ret .= '</tr>';
						
						$gsum += $sum;
						$gctwo += $ctwo;
						$gctpd += $ctpd;
						$gcttp += $cttp;
						
						$n++;
					}
					
					if (bcmod($n,2) == 1) {
						$GLOBALS['output'] .= '<tr class="th tr_class_0">';
					} else {
						$GLOBALS['output'] .= '<tr class="th tr_class_1">';
					}
					
					$ret .= '<td>&sum;</td>';
					$ret .= '<td></td>';
					$ret .= '<td>'.$gall.'</td>';
					$ret .= '<td>'.$gctwo.'</td>';
					$ret .= '<td>'.$gctpd.'</td>';
					$ret .= '<td>'.$gcttp.'</td>';
					$ret .= '<td>'.float_to_eur($gsum).'</td>';
					
					$ret .= '</tr>';
					
					$ret .= '</tbody>';
					$ret .= '</table>';
					
					$ret .= '</div>';
					
					$ret .= '<div class="fieldset">';
					
					$ret .= '<h2>Finanzstatus nach Angeboten</h2>';
					
					foreach ($offers as $oid => $odata) {
					
						if (!isset($bdata[$oid])) $bdata[$oid] = Array();
					
						$ret .= '<fieldset>';
						$ret .= '<legend>'.$odata['name'].'</legend>';
						
						$ret .= '<p>';
						$ret .= 'Das Angebot hat aktuell unter der Schülerschaft <span class="b">'.count($bdata[$oid]).'</span> eingetragene Teilnehmer und damit bei Vorauskasse von je <span class="b">'.float_to_eur($odata['precosts']).'</span> ein zu erwartendes Gesamtvolumen von <span class="b">'.float_to_eur(($odata['precosts']*count($bdata[$oid]))).'</span>.';
						
						$paidsum = 0.0;
						
						foreach ($bdata[$oid] as $sid => $sdata) {
							
							$paidsum = $paidsum + min($odata['precosts'],$sdata['paid']);
							
						}
						
						$ret .= '<br/>Davon sind momentan schon <span class="b">'.float_to_eur($paidsum).'</span> eingezahlt.</p>';
						$ret .= '<table class="ov_table">';
						$ret .= '<thead>';
						$ret .= '<tr>';
						$ret .= '<th colspan="5">Zahlungen fehlen noch von:</th>';
						$ret .= '</tr>';
						$ret .= '<tr>';
						$ret .= '<th>#</th>';
						$ret .= '<th>Klasse</th>';
						$ret .= '<th>Nachname</th>';
						$ret .= '<th>Vorname</th>';
						$ret .= '<th>Noch zu zahlen</th>';
						$ret .= '</tr>';
						$ret .= '</thead><tbody>';
						
						$n = 1;
						$sum = 0.0;
						foreach ($bdata[$oid] as $sid => $sdata) {
							
							if ($sdata['paid'] < $odata['precosts']) {
								if (bcmod($n,2) == 1) {
									$GLOBALS['output'] .= '<tr class="tr_class_0">';
								} else {
									$GLOBALS['output'] .= '<tr class="tr_class_1">';
								}
								
								$ret .= '<td>'.$n.'</td>';
								$ret .= '<td>'.$sdata['class'].'</td>';
								$ret .= '<td>'.$sdata['surname'].'</td>';
								$ret .= '<td>'.$sdata['forename'].'</td>';
								$ret .= '<td>'.float_to_eur(($odata['precosts']-$sdata['paid'])).'</td>';
								$sum += $odata['precosts']-$sdata['paid'];
								$ret .= '</tr>';
								
								$n++;
							}
							
						}
						
						if (bcmod($n,2) == 1) {
							$GLOBALS['output'] .= '<tr class="th tr_class_0">';
						} else {
							$GLOBALS['output'] .= '<tr class="th tr_class_1">';
						}
						
						$ret .= '<td>&sum;</td>';
						$ret .= '<td colspan="3"></td>';
						$ret .= '<td>'.float_to_eur($sum).'</td>';
						
						$ret .= '</tr>';
						
						$ret .= '</tbody></table>';
						
						$ret .= '<p>Nach Durchführung des Angebots blieb ein Betrag von <span class="b">'.float_to_eur((float)$odata['left']).'</span> übrig. Dieser wird wie folgt aufgeteilt:</p>';
						
						$ret .= '<ul>';
						$ret .= '<li>Insgesamt '.float_to_eur(($offerlist[$oid]['1w'] * $offerlist[$oid]['1wcb'])).' für die '.$offerlist[$oid]['1w'].' langfristig Entschuldigten zu je '.float_to_eur($offerlist[$oid]['1wcb']).'</li>';
						$ret .= '<li>Insgesamt '.float_to_eur(($offerlist[$oid]['e'] * $offerlist[$oid]['ecb'])).' für die '.$offerlist[$oid]['e'].' kurzfristig Entschuldigten zu je '.float_to_eur($offerlist[$oid]['ecb']).'</li>';
						$ret .= '<li>Insgesamt '.float_to_eur(($offerlist[$oid]['tn'] * $offerlist[$oid]['tncb'])).' für die '.$offerlist[$oid]['tn'].' tatsächlichen Teilnehmer zu je '.float_to_eur($offerlist[$oid]['tncb']).' (ergibt sich durch Aufsplitten des Restbetrags).</li>';
						$ret .= '</ul>';
						
						$ret .= '</fieldset>';
						
					}
					
					$ret .= '</div>';
					
				} break;
			case 'ov_autofill': {
					
					$stat = Array('man'=>0);
					$notfilled = Array();
					$manually = Array();
					
					for ($i = 0; $i < $info['props']['max_wishes']; $i++) {
						$stat['w'.$i] = 0;
					}
					
					foreach ($students as $sid => $sdata) {
						
						if (!isset($sdata['offer']) OR $sdata['offer'] <= 0 OR !isset($offers[$sdata['offer']]) ) {
							$notfilled[$sid] = $sdata;
						} else {
						
							$bool = false;
							
							for ($i = 0; $i <= $info['props']['max_wishes']; $i++) {
								if (isset($sdata['wish_'.$i]) AND $sdata['wish_'.$i] > 0 AND isset($offers[$sdata['wish_'.$i]]) AND $sdata['wish_'.$i] == $sdata['offer']) {
								
									$stat['w'.$i]++;
									$bool = true;
									break;
								
								}
							}
							
							if (!$bool) {
								$stat['man']++;
								$manually[$sid] = $sdata;
							}
								
						}
					
					}
					
					$ret .= '<div class="fieldset">';
					$ret .= '<p>Springe zu: <a href="#h2_offers">Übersicht Angebotszuteilung</a>, <a href="#h2_buses">Übersicht Buszuteilung</a></p>';
					$ret .= '</div>';
					
					{ //Übersicht Angebotszuteilung
						$ret .= '<div class="fieldset">';
						$ret .= '<h2 id="h2_offers">Übersicht Angebotszuteilung</h2>';
						$ret .= '<fieldset>';
						$ret .= '<legend>Wunschübersicht</legend>';
						$ret .= '<p>Die berücksichtigten Wünsche sind folgendermaßen verteilt:</p>';
						$ret .= '<table class="ov_table" style="text-align:center">';
						$ret .= '<tr>';
						$ret .= '<td>Wunschnummer</td>';
						$ret .= '<td></td>';
						for ($i = 0; $i < $info['props']['max_wishes']; $i++) {
							$ret .= '<td>'.($i+1).'</td>';
						}
						$ret .= '<td></td>';
						$ret .= '<td>Rein manuell zugeteilt</td>';
						$ret .= '<td>Nicht zugeteilt</td>';
						$ret .= '<td></td>';
						$ret .= '<td>&sum;</td>';
						$ret .= '</tr>';
						
						$ret .= '<tr>';
						$ret .= '<td>Anzahl Zuteilungen</td>';
						$ret .= '<td></td>';
						$sum = 0;
						for ($i = 0; $i < $info['props']['max_wishes']; $i++) {
							$ret .= '<td>'.$stat['w'.$i].'</td>';
							$sum += $stat['w'.$i];
						}
						$ret .= '<td></td>';
						$ret .= '<td>'.$stat['man'].'</td>';
						$sum += $stat['man'];
						$ret .= '<td>'.count($notfilled).'</td>';
						$sum += count($notfilled);
						$ret .= '<td></td>';
						$ret .= '<td>'.$sum.'</td>';
						$ret .= '</tr>';
						$ret .= '</table>';
						$ret .= '</fieldset>';
						
						$ret .= '<fieldset>';
						$ret .= '<legend>Verteilungsmatrix</legend>';
						$ret .= '<table class="ov_table">';
						
						$oclasses = Array();

						$toffers = $offers; $toffers[0] = Array('name'=>'Nicht zugeteilt');
						
						$sumdata = Array();
							
						foreach ($students as $sid => $sdata) {
							if (!isset($oclasses[$sdata['class']])) $oclasses[$sdata['class']] = Array();
							if (isset($sdata['offer']) AND isset($offers[$sdata['offer']])) { 
								if (!isset($oclasses[$sdata['class']][$sdata['offer']])) $oclasses[$sdata['class']][$sdata['offer']] = Array();
								$oclasses[$sdata['class']][$sdata['offer']][$sid] = $sdata;
								if (!isset($sumdata[$sdata['offer']])) $sumdata[$sdata['offer']] = Array();
								$sumdata[$sdata['offer']][$sid] = $sdata;
							} else {
								if (!isset($oclasses[$sdata['class']][0])) $oclasses[$sdata['class']][0] = Array();
								$oclasses[$sdata['class']][0][$sid] = $sdata;
								if (!isset($sumdata[0])) $sumdata[0] = Array();
								$sumdata[0][$sid] = $sdata;
							}
						}

						foreach ($offers as $oid => $odata) {
							if (!isset($sumdata[$oid])) $sumdata[$oid] = Array();
						}
						
						if (!isset($sumdata[0])) $sumdata[0] = Array();

						$oclasses['&sum;'] = $sumdata;
						
						$ret .= '<thead><tr><th colspan="2" style="visibility:hidden"></th>';
						foreach ($toffers as $oid => $odata) {
							$ret .= '<th colspan="2">'.$odata['name'].'</th>';
						}
						$ret .= '<th colspan="2">&sum;</th></tr></thead><tbody>';
						
						$n = 0;
						foreach ($oclasses as $cln => $odata) {
							
							if (bcmod($n,2) == 1) {
								$GLOBALS['output'] .= '<tr class="tr_class_0">';
							} else {
								$GLOBALS['output'] .= '<tr class="tr_class_1">';
							}
							
							if ($cln != '&sum;') {
								$cladd = '';
							} else {
								$cladd = ' class="th"';
							}
							
							$sum = 0;
							$mct = Array();
							$smct = 0;
							$fct = Array();
							$sfct = 0;
							$nct = Array();
							$snct = 0;
							
							$ret .= '<td rowspan="3" class="th">'.$cln.'</td>';
							$ret .= '<td class="th">männl.</td>';
							foreach ($toffers as $oid => $od) {
								$mct[$oid] = 0;
								$nct[$oid] = 0;
								$fct[$oid] = 0;
								if (isset($odata[$oid])) {
									foreach ($oclasses[$cln][$oid] as $sid => $sdata) {
										if (!isset($sdata['gender']) OR !in_array($sdata['gender'],Array('m','f'))) {
											$nct[$oid]++;
										} elseif ($sdata['gender'] == 'm') {
											$mct[$oid]++;
										} elseif ($sdata['gender'] == 'f') {
											$fct[$oid]++;
										}
									}
									$ret .= '<td';
									if (isset($od['preferred']) AND parse_clstring($od['preferred'],$cln)) { $ret .= ' style="background-color:#cec"';	}
									if (isset($od['excluded']) AND parse_clstring($od['excluded'],$cln)) { $ret .= ' style="background-color:#ecc"';	}
									$ret .= ' rowspan="3"'.$cladd.'>'.count($odata[$oid]).'</td>';
									$sum += count($odata[$oid]);
								} else {
									$ret .= '<td';
									if (isset($od['preferred']) AND parse_clstring($od['preferred'],$cln)) { $ret .= ' style="background-color:#cec"';	}
									if (isset($od['excluded']) AND parse_clstring($od['excluded'],$cln)) { $ret .= ' style="background-color:#ecc"';	} 
									$ret .= ' rowspan="3"'.$cladd.'></td>';
								}
								if ($mct[$oid] > 0) { 
									$ret .= '<td';
									if (isset($od['preferred']) AND parse_clstring($od['preferred'],$cln)) { $ret .= ' style="background-color:#cec"';	}
									if (isset($od['excluded']) AND parse_clstring($od['excluded'],$cln)) { $ret .= ' style="background-color:#ecc"';	} 							
									if (isset($od['avgender']) AND !in_array('m',explode(',',$od['avgender']))) { $ret .= ' style="background-color:#ecc"';	} 
									$ret .= $cladd.'>'.$mct[$oid].'</td>'; 
								} else { 
									$ret .= '<td';
									if (isset($od['preferred']) AND parse_clstring($od['preferred'],$cln)) { $ret .= ' style="background-color:#cec"';	}
									if (isset($od['excluded']) AND parse_clstring($od['excluded'],$cln)) { $ret .= ' style="background-color:#ecc"';	} 
									if (isset($od['avgender']) AND !in_array('m',explode(',',$od['avgender']))) { $ret .= ' style="background-color:#ecc"';	} 
									$ret .= $cladd.'></td>'; 
								}
								$smct += $mct[$oid];
							}
							
							$ret .= '<td rowspan="3" class="th">'.$sum.'</td>';
							$ret .= '<td class="th">'.$smct.'</td>';
							
							$ret .= '</tr>';
							
							if (bcmod($n,2) == 1) {
								$GLOBALS['output'] .= '<tr class="tr_class_0">';
							} else {
								$GLOBALS['output'] .= '<tr class="tr_class_1">';
							}

							$ret .= '<td class="th">weibl.</td>';
							foreach ($toffers as $oid => $od) {
								if ($fct[$oid] > 0) {
									$ret .= '<td';
									if (isset($od['preferred']) AND parse_clstring($od['preferred'],$cln)) { $ret .= ' style="background-color:#cec"';	}
									if (isset($od['excluded']) AND parse_clstring($od['excluded'],$cln)) { $ret .= ' style="background-color:#ecc"';	} 
									if (isset($od['avgender']) AND !in_array('f',explode(',',$od['avgender']))) { $ret .= ' style="background-color:#ecc"';	} 
									$ret .= $cladd.'>'.$fct[$oid].'</td>'; 
								} else {
									$ret .= '<td';
									if (isset($od['preferred']) AND parse_clstring($od['preferred'],$cln)) { $ret .= ' style="background-color:#cec"';	}
									if (isset($od['excluded']) AND parse_clstring($od['excluded'],$cln)) { $ret .= ' style="background-color:#ecc"';	} 
									if (isset($od['avgender']) AND !in_array('f',explode(',',$od['avgender']))) { $ret .= ' style="background-color:#ecc"';	} 
									$ret .= $cladd.'></td>'; 
								}
								$sfct += $fct[$oid];
							}
							
							$ret .= '<td class="th">'.$sfct.'</td>';
							$ret .= '</tr>';
							
							
							if (bcmod($n,2) == 1) {
								$GLOBALS['output'] .= '<tr class="tr_class_0">';
							} else {
								$GLOBALS['output'] .= '<tr class="tr_class_1">';
							}
							$ret .= '<td class="th">-?-</td>';
							
							foreach ($toffers as $oid => $od) {
								if ($nct[$oid] > 0) { 
									$ret .= '<td';
									if (isset($od['preferred']) AND parse_clstring($od['preferred'],$cln)) { $ret .= ' style="background-color:#cec"';	}
									if (isset($od['excluded']) AND parse_clstring($od['excluded'],$cln)) { $ret .= ' style="background-color:#ecc"';	} 
									$ret .= $cladd.'>'.$nct[$oid].'</td>'; 
								} else {
									$ret .= '<td';
									if (isset($od['preferred']) AND parse_clstring($od['preferred'],$cln)) { $ret .= ' style="background-color:#cec"';	}
									if (isset($od['excluded']) AND parse_clstring($od['excluded'],$cln)) { $ret .= ' style="background-color:#ecc"';	} 
									$ret .= $cladd.'></td>'; 
								}
								$snct += $nct[$oid];
							}
						
							$ret .= '<td class="th">'.$snct.'</td>';
							$ret .= '</tr>';
							
							
							$n++;
							
						}
						
						$tsum = 0;
						$ret .= '<tr style="background-color:#eec">';
						$ret .= '<td colspan="2" class="th">Kapazität</td>';
						foreach ($toffers as $oid => $odata) {
							if ($oid != 0) {
								$ret .= '<td class="th" colspan="2">'.($offers[$oid]['maxtn']).'</td>';
								$tsum += $offers[$oid]['maxtn'];
							} else {
								$ret .= '<td class="th" colspan="2">'.(0).'</td>';
							}
						}
						$ret .= '<td colspan="2" class="th">'.$tsum.'</td>';
						$ret .= '</tr>';
						
						$ttsum = 0;
						$ret .= '<tr style="background-color:#cce">';
						$ret .= '<td colspan="2" class="th">Freie Plätze</td>';
						foreach ($toffers as $oid => $odata) {
							if ($oid != 0) {
								$ret .= '<td class="th" colspan="2">'.($offers[$oid]['maxtn'] - count($oclasses['&sum;'][$oid])).'</td>';
								$ttsum += ($offers[$oid]['maxtn'] - count($oclasses['&sum;'][$oid]));
							} else {
								$ret .= '<td class="th" colspan="2">'.(0 - count($oclasses['&sum;'][$oid])).'</td>';
								$ttsum += (0 - count($oclasses['&sum;'][$oid]));
							}
						}
						$ret .= '<td colspan="2" class="th">'.$ttsum.'</td>';
						$ret .= '</tr>';
						
						$ret .= '<tr style="text-align:center;font-weight:bold"><td class="th" colspan="2" style="visibility:hidden"></td>';
						foreach ($toffers as $oid => $odata) {
							$ret .= '<td class="th" colspan="2">'.$odata['name'].'</td>';
						}
						$ret .= '<td class="th" colspan="2">&sum;</td></tr>';
						
						
						$ret .= '</tbody></table>';
						$ret .= '</fieldset>';
						
						$ret .= '<fieldset>';
						$ret .= '<legend>Nicht zugeteilte Schüler</legend>';
						$ret .= '<p>Folgende Schüler konnten automatisch nicht zugeteilt werden:</p>';
						$ret .= '<table class="ov_table" style="text-align:center">';
						
						$ret .= '<thead>';
						$ret .= '<tr>';
						$ret .= '<th>#</th>';
						$ret .= '<th>Klasse</th>';
						$ret .= '<th>Nachname</th>';
						$ret .= '<th>Vorname</th>';
						$ret .= '<th>Klassenlehrer</th>';
						$ret .= '</tr>';
						$ret .= '</thead>';
						
						$ret .= '<tbody style="border:3px solid black">';
						
						$n = 1;
						$lastchange = 1;
						$ncl = 1;
						foreach ($notfilled as $sid => $sdata) {
						
							if (!isset($actclass)) $actclass = $sdata['class'];
							
							if ($sdata['class'] != $actclass) {
								$ret .= '</tbody>';
								$ret .= '<tbody style="border:3px solid black">';
								$actclass = $sdata['class'];
								
								$ret = str_replace('{rspan-class}',($n-$lastchange),$ret);
								
								$lastchange = $n;
							}
						
							if (bcmod($n,2) == 1) {
								$GLOBALS['output'] .= '<tr class="tr_class_0">';
							} else {
								$GLOBALS['output'] .= '<tr class="tr_class_1">';
							}
							$ret .= '<td>'.$n.'</td>';
							if ($n == $lastchange) {
								$ret .= '<td';
								if (bcmod($ncl,2) == 1) {
									$GLOBALS['output'] .= ' style="background-color:#fff;"';
								} else {
									$GLOBALS['output'] .= ' style="background-color:#ddf;"';
								}
								$ret .= ' rowspan="{rspan-class}">';
								$ret .= '<a href="./?view=edit_students&class='.$sdata['class'].'">'.$sdata['class'].'</a></td>';
								$ncl++;
							}
							$ret .= '<td>'.$sdata['surname'].'</td>';
							$ret .= '<td>'.$sdata['forename'].'</td>';
							if ($n == $lastchange) {
								$ret .= '<td';
								if (bcmod($ncl,2) == 0) {
									$GLOBALS['output'] .= ' style="background-color:#fff;"';
								} else {
									$GLOBALS['output'] .= ' style="background-color:#ddf;"';
								}
								$ret .= ' rowspan="{rspan-class}">';
								
								$ct = get_class_teacher($sdata['class'],$teachers);
								$addr = ($ct > 0 AND isset($teachers[$ct]))?id_to_address($teachers[$ct]):'(Nicht angegeben)';
								$ret .= $addr.'</td>';
							}
							$ret .= '</tr>';
							
							$n++;
						}
						
						$ret = str_replace('{rspan-class}',($n-$lastchange),$ret);
						
						$ret .= '</tbody>';
						
						$ret .= '</table>';
						$ret .= '</fieldset>';
						
						$ret .= '<fieldset>';
						$ret .= '<legend>Manuell zugeteilte Schüler</legend>';
						$ret .= '<p>Folgende Schüler wurden manuell zugeteilt (keine Übereinstimmung von Zuteilung und Wünschen):</p>';
						$ret .= '<table class="ov_table" style="text-align:center">';
						
						$ret .= '<thead>';
						$ret .= '<tr>';
						$ret .= '<th>#</th>';
						$ret .= '<th>Klasse</th>';
						$ret .= '<th>Nachname</th>';
						$ret .= '<th>Vorname</th>';
						$ret .= '<th>Klassenlehrer</th>';
						$ret .= '</tr>';
						$ret .= '</thead>';
						
						$ret .= '<tbody style="border:3px solid black">';
						
						$n = 1;
						$lastchange = 1;
						$ncl = 1;
						foreach ($manually as $sid => $sdata) {
						
							if (!isset($actclass)) $actclass = $sdata['class'];
							
							if ($sdata['class'] != $actclass) {
								$ret .= '</tbody>';
								$ret .= '<tbody style="border:3px solid black">';
								$actclass = $sdata['class'];
								
								$ret = str_replace('{rspan-class}',($n-$lastchange),$ret);
								
								$lastchange = $n;
							}
						
							if (bcmod($n,2) == 1) {
								$GLOBALS['output'] .= '<tr class="tr_class_0">';
							} else {
								$GLOBALS['output'] .= '<tr class="tr_class_1">';
							}
							$ret .= '<td>'.$n.'</td>';
							if ($n == $lastchange) {
								$ret .= '<td';
								if (bcmod($ncl,2) == 1) {
									$GLOBALS['output'] .= ' style="background-color:#fff;"';
								} else {
									$GLOBALS['output'] .= ' style="background-color:#ddf;"';
								}
								$ret .= ' rowspan="{rspan-class}">';
								$ret .= '<a href="./?view=edit_students&class='.$sdata['class'].'">'.$sdata['class'].'</a></td>';
								$ncl++;
							}
							$ret .= '<td>'.$sdata['surname'].'</td>';
							$ret .= '<td>'.$sdata['forename'].'</td>';
							if ($n == $lastchange) {
								$ret .= '<td';
								if (bcmod($ncl,2) == 0) {
									$GLOBALS['output'] .= ' style="background-color:#fff;"';
								} else {
									$GLOBALS['output'] .= ' style="background-color:#ddf;"';
								}
								$ret .= ' rowspan="{rspan-class}">';
								
								$ct = get_class_teacher($sdata['class'],$teachers);
								$addr = ($ct > 0 AND isset($teachers[$ct]))?id_to_address($teachers[$ct]):'(Nicht angegeben)';
								$ret .= $addr.'</td>';
							}
							$ret .= '</tr>';
							
							$n++;
						}
						
						$ret = str_replace('{rspan-class}',($n-$lastchange),$ret);
						
						$ret .= '</tbody>';
						
						$ret .= '</table>';
						$ret .= '</fieldset>';
						
						$ret .= '</div>';
					}
					
					{ //Übersicht Buszuteilung
					
						$ret .= '<div class="fieldset">';
						$ret .= '<h2 id="h2_buses">Übersicht Buszuteilung</h2>';
						
						foreach ($carpools as $cpid => $cpdata) {
						
							$offstr = '';
							$oids = explode(',',$cpdata['offers']);
							foreach ($oids as $key => $oid) {
								if (isset($offers[(int)$oid])) {
									if ($offstr != '') $offstr .= ', ';
									$offstr .= $offers[(int)$oid]['name'];
									$oids[$key] = (int)$oid;
								}
							}
							
							$cpbuses = Array(); $cpclasses = Array(); $sumtn = Array();
							foreach ($buses as $bid => $bdata) {
								if ($bdata['carpool'] == $cpid) {
									$cpbuses[$bid] = $bdata;
								}
							}
							$cpclasses['Lehrkräfte'] = Array();
							foreach ($classes as $cln => $std) {
								foreach ($std as $sid => $sdata) {
									if (isset($sdata['offer']) AND $sdata['offer'] > 0 AND in_array($sdata['offer'],$oids) AND isset($offers[$sdata['offer']])) {
										if (!isset($cpclasses[$cln])) $cpclasses[$cln] = Array('sum'=>0);
										if (!isset($cpclasses[$cln][$sdata['offer']])) $cpclasses[$cln][$sdata['offer']] = 0;
										if (!isset($sumtn[$sdata['offer']])) $sumtn[$sdata['offer']] = 0;
										if (!isset($sumtn['sum'])) $sumtn['sum'] = 0;
										$cpclasses[$cln]['sum']++;
										$cpclasses[$cln][$sdata['offer']]++;
										$sumtn[$sdata['offer']]++;
										$sumtn['sum']++;
										if (isset($sdata['bus']) AND $sdata['bus'] > 0 AND array_key_exists($sdata['bus'],$cpbuses)) {
											if (!isset($cpclasses[$cln][$sdata['bus']])) $cpclasses[$cln][$sdata['bus']] = 0;
											$cpclasses[$cln][$sdata['bus']]++;
											if (!isset($sumtn[$sdata['bus']])) $sumtn[$sdata['bus']] = 0;
											$sumtn[$sdata['bus']]++;
										} else {
											if (!isset($cpclasses[$cln][0])) $cpclasses[$cln][0] = 0;
											$cpclasses[$cln][0]++;
											if (!isset($sumtn[0])) $sumtn[0] = 0;
											$sumtn[0]++;
										}
									}
								}
							}
							$cln = 'Lehrkräfte';
							foreach ($teachers as $sid => $sdata) {
								if (isset($sdata['offer']) AND $sdata['offer'] > 0 AND in_array($sdata['offer'],$oids) AND isset($offers[$sdata['offer']])) {
									if (!isset($cpclasses[$cln])) $cpclasses[$cln] = Array('sum'=>0);
									if (!isset($cpclasses[$cln]['sum'])) $cpclasses[$cln]['sum'] = 0;
									if (!isset($cpclasses[$cln][$sdata['offer']])) $cpclasses[$cln][$sdata['offer']] = 0;
									if (!isset($sumtn[$sdata['offer']])) $sumtn[$sdata['offer']] = 0;
									if (!isset($sumtn['sum'])) $sumtn['sum'] = 0;
									$cpclasses[$cln]['sum']++;
									$cpclasses[$cln][$sdata['offer']]++;
									$sumtn[$sdata['offer']]++;
									$sumtn['sum']++;
									if (isset($sdata['bus']) AND $sdata['bus'] > 0 AND array_key_exists($sdata['bus'],$cpbuses)) {
										if (!isset($cpclasses[$cln][$sdata['bus']])) $cpclasses[$cln][$sdata['bus']] = 0;
										$cpclasses[$cln][$sdata['bus']]++;
										if (!isset($sumtn[$sdata['bus']])) $sumtn[$sdata['bus']] = 0;
										$sumtn[$sdata['bus']]++;
									} 
								}
							}
						
							$ret .= '<fieldset>';
							$ret .= '<legend>Fahrgemeinschaft "'.$cpdata['name'].'" (Angebote: '.$offstr.')</legend>';
							
							$ret .= '<table class="ov_table">';
							$ret .= '<thead>';
							
							$th = '';
							
							$ret .= '<tr>';
							$th .= '<th style="visibility:hidden">X</th>';
							
							foreach ($oids as $oid) {
								if (isset($offers[$oid])) {
									$th .= '<th>'.$offers[$oid]['name'].'</th>';
								}
							}
							
							$th .= '<th>Gesamt</th>';
							
							foreach ($cpbuses as $bid => $bct) {
								$th .= '<th>'.$buses[$bid]['tag'].'</th>';
							}
							
							$th .= '<th style="visibility:hidden">X</th>';
							
							$ret .= $th;
							
							$ret .= '</tr>';
							
							$ret .= '</thead>';
							$ret .= '<tbody style="text-align:center">';
							$n = 1;
							foreach ($cpclasses as $cln => $ctdata) {
							
								
								if (bcmod($n,2) == 1) {
									$GLOBALS['output'] .= '<tr class="tr_class_0">';
								} else {
									$GLOBALS['output'] .= '<tr class="tr_class_1">';
								}
								
								$ret .= '<td class="th" style="text-align:right">'.$cln.'</td>';
								
								foreach ($oids as $oid) {
									if (isset($offers[$oid])) {
										if (isset($ctdata[$oid]) AND $ctdata[$oid] > 0) {
											$ret .= '<td>'.$ctdata[$oid].'</td>';
										} else {
											$ret .= '<td></td>';
										}
									}
								}
								
								$ret .= '<td class="th">'.$ctdata['sum'].'</td>';
								
								foreach ($cpbuses as $bid => $bct) {
									if (isset($ctdata[$bid]) AND $ctdata[$bid]>0) {
										$ret .= '<td>'.$ctdata[$bid].'</td>';
									} else {
										$ret .= '<td></td>';
									}
								}
								
								$ret .= '<td class="th" style="text-align:left">'.$cln.'</td>';
								
								$ret .= '</tr>';
								
								$n++;
							}
							
							
							if (bcmod($n,2) == 1) {
								$GLOBALS['output'] .= '<tr class="th tr_class_0" style="font-weight:bold">';
							} else {
								$GLOBALS['output'] .= '<tr class="th tr_class_1" style="font-weight:bold">';
							}
							
							$ret .= str_replace('</th>','</td>',str_replace('<th','<td class="th" ',$th));
							
							$ret .= '</tr>';
							
							$n++;
							
							if (bcmod($n,2) == 1) {
								$GLOBALS['output'] .= '<tr class="tr_class_0">';
							} else {
								$GLOBALS['output'] .= '<tr class="tr_class_1">';
							}
							
							$ret .= '<td class="th" style="text-align:right">Summe</td>';
							
							foreach ($oids as $oid) {
								if (isset($offers[$oid])) {
									if (isset($sumtn[$oid]) AND $sumtn[$oid] > 0) {
										$ret .= '<td>'.$sumtn[$oid].'</td>';
									} else {
										$ret .= '<td>0</td>';
									}
								}
							}
							
							$ret .= '<td class="th">'.$sumtn['sum'].'</td>';
							
							foreach ($cpbuses as $bid => $bct) {
								if (isset($sumtn[$bid]) AND $sumtn[$bid]>0) {
									$ret .= '<td>'.$sumtn[$bid].'</td>';
								} else {
									$ret .= '<td>0</td>';
								}
							}
							
							$ret .= '<td class="th" style="text-align:left">Summe</td>';
							
							$ret .= '</tr>';
							
							$n++;
							
							if (bcmod($n,2) == 1) {
								$GLOBALS['output'] .= '<tr class="tr_class_0">';
							} else {
								$GLOBALS['output'] .= '<tr class="tr_class_1">';
							}
							
							$ret .= '<td class="th" style="text-align:right">Kapazität</td>';
							
							foreach ($oids as $oid) {
								if (isset($offers[$oid])) {
									if (isset($offers[$oid]['maxtn']) AND $offers[$oid]['maxtn'] > 0) {
										$ret .= '<td>'.($offers[$oid]['maxtn']+(isset($cpclasses['Lehrkräfte'][$oid])?$cpclasses['Lehrkräfte'][$oid]:0)).'</td>';
									} else {
										$ret .= '<td>0</td>';
									}
								}
							}
							
							$ret .= '<td class="th">'.$sumtn['sum'].'</td>';
							
							foreach ($cpbuses as $bid => $bct) {
								if (isset($buses[$bid]['capacity']) AND $buses[$bid]['capacity']>0) {
									$ret .= '<td>'.$buses[$bid]['capacity'].'</td>';
								} else {
									$ret .= '<td>0</td>';
								}
							}
							
							$ret .= '<td class="th" style="text-align:left">Kapazität</td>';
							
							$ret .= '</tr>';
							
							$n++;
							
							$ret .= '</tbody>';
							$ret .= '</table>';
							
							$ret .= '</fieldset>';
						
						}
						
						$ret .= '</div>';
					
					}
					
				} break;
			
			
			case 'edit_teachers': {
			
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
			
					$GLOBALS['output'] .= '<div class="fieldset">';
					$GLOBALS['output'] .= '<h2>Bearbeitungsansicht Lehrer</h2>';
					
					$GLOBALS['output'] .= '<form action="./?view=edit_teachers" method="POST">';
					
					$ret .= '<fieldset>';
					$ret .= '<legend>Hinweis:</legend>';
					$ret .= '<p>Zum Löschen eines Lehrers einfach Vor- und Nachnamen entfernen.</p>';
					$ret .= '</fieldset>';
					
					$GLOBALS['output'] .= '<table class="ov_table ed_table">';
					$GLOBALS['output'] .= '<thead>';
					$GLOBALS['output'] .= '<tr>';
					$GLOBALS['output'] .= '<th>#</th>';
					$GLOBALS['output'] .= '<th>Kürzel</th>';
					$GLOBALS['output'] .= '<th>Nachname</th>';
					$GLOBALS['output'] .= '<th>Vorname</th>';
					$GLOBALS['output'] .= '<th>Geschlecht</th>';
					$GLOBALS['output'] .= '<th>Verfüg-<br/>barkeit</th>';
					$GLOBALS['output'] .= '<th>zugeteiltes</br>Angebot</th>';
					for ($i = 1; $i <= $info['props']['max_wishes']; $i++) {
						$GLOBALS['output'] .= '<th>Wunsch '.$i.'</th>';
					}
					$GLOBALS['output'] .= '<th>Klassen-<br/>lehrer</th>';
					$GLOBALS['output'] .= '<th>Buszuordnung</th>';
					$GLOBALS['output'] .= '<th>Erreichbarkeit<br/>Mobilnummer</th>';
				
					$GLOBALS['output'] .= '</tr>';
					$GLOBALS['output'] .= '</thead><tbdoy>';
					
					$n = 1;
					
					for ($l = 0; $l < 5; $l++) {
						$teachers['TEMP'.$l] = Array('id'=>'TEMP'.$l,'sign'=>'','surname'=>'','forename'=>'','gender'=>'','offer'=>'','class'=>'','available'=>true);
						for ($i = 1; $i <= $info['props']['max_wishes']; $i++) {
							$teachers['TEMP'.$l]['wish_'.($i-1)] = '';
						}
					}
					
					foreach($teachers as $tid => $tdata) {
						
						if ($tid == 'TEMP0') {
							$GLOBALS['output'] .= '<tr>';
							$GLOBALS['output'] .= '<td class="th" colspan="11" style="font-weight:bold;">Lehrer hinzufügen: (Angabe von Nachname oder Vorname ist Pflicht)</td>';
							$GLOBALS['output'] .= '</tr>';
						}
						
						if (bcmod($n,2) == 1) {
							$GLOBALS['output'] .= '<tr class="tr_class_0">';
						} else {
							$GLOBALS['output'] .= '<tr class="tr_class_1">';
						}
						$GLOBALS['output'] .= '<td>'.$n.'</td>';
						$GLOBALS['output'] .= '<td><input type="text" class="text_small" name="edit_teachers_'.$tid.'_sign" value="'.$tdata['sign'].'"/></td>';
						$GLOBALS['output'] .= '<td><input type="text" name="edit_teachers_'.$tid.'_surname" value="'.$tdata['surname'].'"/></td>';
						$GLOBALS['output'] .= '<td><input type="text" name="edit_teachers_'.$tid.'_forename" value="'.$tdata['forename'].'"/></td>';
						
						$GLOBALS['output'] .= '<td><input ';
						if ($tdata['gender'] == 'm') $GLOBALS['output'] .= 'checked="checked" ';
						$GLOBALS['output'] .= 'type="radio" name="edit_teachers_'.$tid.'_gender" id="edit_teachers_'.$tid.'_gender_m" value="m"/><label for="edit_teachers_'.$tid.'_gender_m">männlich</label><br/>';
						$GLOBALS['output'] .= '<input ';
						if ($tdata['gender'] == 'f') $GLOBALS['output'] .= 'checked="checked" ';
						$GLOBALS['output'] .= 'type="radio" name="edit_teachers_'.$tid.'_gender" id="edit_teachers_'.$tid.'_gender_f" value="f"/><label for="edit_teachers_'.$tid.'_gender_f">weiblich</label></td>';
						
						if ((bool)$tdata['available']) {
							if (isset($offers[$tdata['offer']]) AND isset($offers[$tdata['offer']]['teacher']) AND (int)$offers[$tdata['offer']]['teacher'] == (int)$tid ) {
								$GLOBALS['output'] .= '<td colspan="'.(2+$info['props']['max_wishes']).'"><input type="hidden" name="edit_teachers_'.$tid.'_offer" value="'.$tdata['offer'].'" />Lehrkraft verantwortl. für <span style="font-weight:bold">'.$offers[$tdata['offer']]['name'].'</span>.</td>';
							} else {
								$GLOBALS['output'] .= '<td style="text-align:left"><input type="radio" value="1" id="edit_teachers_'.$tid.'_available0" name="edit_teachers_'.$tid.'_available"'.($tdata['available']?' checked="checked"':'').'/><label for="edit_teachers_'.$tid.'_available0">Ja</label><br/>';
								$GLOBALS['output'] .= '<input type="radio" value="0" id="edit_teachers_'.$tid.'_available1" name="edit_teachers_'.$tid.'_available"'.($tdata['available']?'':' checked="checked"').'/><label for="edit_teachers_'.$tid.'_available1">Nein</label></td>';
								$GLOBALS['output'] .= '<td>'.produce_offer_list($offers,'edit_teachers_'.$tid.'_offer',0,'',$tdata['offer']).'</td>';
								
								for ($i = 1; $i <= $info['props']['max_wishes']; $i++) {
									$GLOBALS['output'] .= '<td>'.produce_offer_list($offers,'edit_teachers_'.$tid.'_wish_'.($i-1),0,'',$tdata['wish_'.($i-1)]).'</td>';
								}
							}
						} else {
							$GLOBALS['output'] .= '<td style="text-align:left"><input type="radio" value="1" id="edit_teachers_'.$tid.'_available0" name="edit_teachers_'.$tid.'_available"'.($tdata['available']?' checked="checked"':'').'/><label for="edit_teachers_'.$tid.'_available0">Ja</label><br/>';
							$GLOBALS['output'] .= '<input type="radio" value="0" id="edit_teachers_'.$tid.'_available1" name="edit_teachers_'.$tid.'_available"'.($tdata['available']?'':' checked="checked"').'/><label for="edit_teachers_'.$tid.'_available1">Nein</label></td>';
							$GLOBALS['output'] .= '<td colspan="'.(1+$info['props']['max_wishes']).'">Lehrkraft nicht verfügbar.</td>';
						}
						
						$GLOBALS['output'] .= '<td>';
						$cns = explode(',',$tdata['class']);
						if ($cns[0] != '') $cns[] = '';
						$counter = 0;
						foreach ($cns as $cn) {
							if ($counter > 0) $GLOBALS['output'] .= '<br/>';
							$GLOBALS['output'] .= $cn;
							$counter++;
						}
						$GLOBALS['output'] .= '</td>';
						
						if (isset($tdata['offer']) AND (int)$tdata['offer'] > 0) {
						
							$ret .= '<td><select class="select_offer" name="edit_teachers_'.$tid.'_bus" size="1">';
							
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
						
						if (!isset($tdata['phone'])) $tdata['phone'] = '';
						
						$ret .= '<td><input type="text" name="edit_teachers_'.$tid.'_phone" value="'.$tdata['phone'].'" /></td>';
						
						$GLOBALS['output'] .= '</tr>';			
						
						$n++;
					}
					
					$GLOBALS['output'] .= '</tbody></table>';
					
					
					for ($l = 0; $l < 5; $l++) {
						unset($teachers['TEMP'.$l]);
					}
					
					$GLOBALS['output'] .= '<fieldset><legend>Optionen</legend>';
					
					$GLOBALS['output'] .= '<textarea style="display:none;" rows="5" readonly="readonly" cols="25" name="edit_teachers_control_teachers">'.(file_get_contents('./db/'.DB.'/teachers.ini')).'</textarea>';

					$GLOBALS['output'] .= '<input type="hidden" name="action"  value="edit_teachers" />';
					
					$GLOBALS['output'] .= '<input type="submit" value="Daten speichern." />';
					
					$GLOBALS['output'] .= '</fieldset>';
					
					$GLOBALS['output'] .= '</form>';		
					$GLOBALS['output'] .= '</div>';
				} break;
			case 'add_class': {
				
					$ncl = Array('NeueKlasse' => Array());
				
					$GLOBALS['output'] .= produce_class_edit(VIEW,AUTH,'NeueKlasse',$info,$ncl,$teachers,$offers,$carpools,$buses);
			
				} break;
			case 'edit_students': if (isset($classes[CLASSFILTER])) {
			
					$GLOBALS['output'] .= produce_class_edit(VIEW,AUTH,CLASSFILTER,$info,$classes,$teachers,$offers,$carpools,$buses);
					
				} break;	
			case 'edit_payments': if (isset($classes[CLASSFILTER])) {
					
					$classfilter = CLASSFILTER;
					
					$ret .= '<div class="fieldset">';
					$ret .= '<h2>Teilnehmerbeiträge Klasse '.$classfilter.'</h2>';
					
					$ret .= '<form action="./?class='.$classfilter.'&view=edit_payments" method="POST">';
					
					
					$ret .= '<fieldset>';	
						$ret .= '<legend>Hinweis</legend>';	
						$ret .= '<p>Um die zu zahlenden Beträge schnell und komfortabel als bezahlt zu markieren können Sie die Pfeile zum automatischen Ausfüllen der Felder nutzen. In diesem Eingabeformular verstehen sich von Schülern gezahlte Beträge als positiv, an Schüler zurückgegebene Beträge als negativ. Verwenden Sie auf keinen Fall Tausendertrennzeichen.</p>';
					$ret .= '</fieldset>';	
					
					
					$ret .= '<table class="ov_table ed_table">';
					$ret .= '<thead>';
					$ret .= '<tr>';
					
					$ret .= '<th colspan="'.(6 + $info['props']['max_wishes']).'">Klasse '.$classfilter.' - ';
					
					$ret .= 'Klassenlehrer/in: ';
					
					$found = get_class_teacher($classfilter,$teachers);

					
					if ($found > 0 AND isset($teachers[$found])) $ret .= id_to_address($teachers[$found]); else $ret .= 'N/A';
							
				
					$ret .= '</th>';
					$ret .= '</tr>';
					$ret .= '<tr>';
					$ret .= '<th>#</th>';
					$ret .= '<th>Nachname</th>';
					$ret .= '<th>Vorname</th>';
					
					$ret .= '<th>Teilnehmerbeitrag<br/>(Vorauskasse)</th>';
					$ret .= '<th>Bereits bezahlt</th>';
					$ret .= '<th>Noch zu zahlen</th>';
					$ret .= '<th>';
					$ret .= '<input type="button" value="&lArr;" id="edit_payments_l_all" />';
					$ret .= '<input type="button" value="&rArr;" id="edit_payments_r_all" />';
					$ret .= '</th>';
					$ret .= '<th>Hat jetzt eingezahlt</th>';
						
					$ret .= '</tr>';
					
					$ret .= '</thead><tbdoy>';
					
					
					$n = 1;
					
					$sumc = 0.0;
					$sump = 0.0;
					
					foreach($classes[$classfilter] as $tid => $tdata) {
							
						if (bcmod($n,2) == 1) {
							$ret .= '<tr class="tr_class_0">';
						} else {
							$ret .= '<tr class="tr_class_1">';
						}
					
						$ret .= '<td style="{N-TD-STYLEINPUT}">'.$n.'</td>';
						$ret .= '<td>'.$tdata['surname'].'</td>';
						$ret .= '<td>'.$tdata['forename'].'</td>';
						
						$pc = 0.0; $pd = 0.0;
						
						if (isset($tdata['offer']) AND isset($offers[$tdata['offer']])) {
							$ret .= '<td><input type="hidden" value="'.$offers[$tdata['offer']]['precosts'].'" id="edit_payments_'.$tid.'_precosts" />'.float_to_eur($offers[$tdata['offer']]['precosts']).'</td>';
							$sumc += $offers[$tdata['offer']]['precosts'];
							$pc = $offers[$tdata['offer']]['precosts'];
						} else {
							$ret .= '<td><input type="hidden" value="0.0" id="edit_payments_'.$tid.'_precosts" />(nicht zugewiesen)</td>';
							$pc = 0.0;
						}
						
						if (isset($tdata['paid'])) {
							$ret .= '<td><input type="hidden" value="'.$tdata['paid'].'" id="edit_payments_'.$tid.'_paid" />'.float_to_eur($tdata['paid']).'</td>';
							$sump += $tdata['paid'];
							$pd = $tdata['paid'];
						} else {
							$pd = 0.0;
							$ret .= '<td><input type="hidden" value="0.0" id="edit_payments_'.$tid.'_paid" />'.float_to_eur(0.0).'</td>';
						}
						
						
						$ret .= '<td';
						if (($pc-$pd)>0) { $ret .= ' style="font-weight:bold;color:#900;"'; $ret = str_replace('"{N-TD-STYLEINPUT}"','"background-color:#900;color:white;font-weight:bold"',$ret); }
						if (($pc-$pd)==0) { $ret .= ' style="color:#009;"'; $ret = str_replace('"{N-TD-STYLEINPUT}"','"background-color:#009;color:white;font-weight:bold"',$ret); }
						if (($pc-$pd)<0) { $ret .= ' style="font-weight:bold;color:#090;"'; $ret = str_replace('"{N-TD-STYLEINPUT}"','"background-color:#090;color:white;font-weight:bold"',$ret); }
						$ret = str_replace('"{N-TD-STYLEINPUT}"','""',$ret);
						$ret .= '><input type="hidden" value="'.($pc - $pd).'" id="edit_payments_'.$tid.'_topay" />'.float_to_eur(($pc - $pd)).'</td>';
						
						
						$ret .= '<td>';
						$ret .= '<input type="button" value="&lArr;" id="edit_payments_'.$tid.'_l" />';
						$ret .= '<input type="button" value="&rArr;" id="edit_payments_'.$tid.'_r" />';
						$ret .= '</td>';
						
						$ret .= '<td><input type="text" value="'.float_to_eur(0.0).'" name="edit_payments_'.$tid.'_deposit" id="edit_payments_'.$tid.'_deposit" /></td>';
						
						$ret .= '</tr>';
						
						$n++;
					} 
					
					if (bcmod($n,2) == 1) {
						$ret .= '<tr class="th tr_class_0">';
					} else {
						$ret .= '<tr class="th tr_class_1">';
					}
					$ret .= '<td>&sum;</td>';
					$ret .= '<td></td>';
					$ret .= '<td></td>';
					$ret .= '<td>'.float_to_eur($sumc).'</td>';
					$ret .= '<td>'.float_to_eur($sump).'</td>';
					$ret .= '<td>'.float_to_eur($sumc-$sump).'</td>';
					$ret .= '<td></td>';
					$ret .= '<td><input type="text" readonly="readonly" id="edit_payments_sum"'.float_to_eur(0.0).'</td>';
					$ret .= '</tr>';
					
					$ret .= '</tbody></table>';

					$ret .= '<fieldset><legend>Optionen</legend>';
						
					
					$ret .= '<input type="hidden" name="action" value="edit_payments" />';
					$ret .= '<input type="submit" value="Daten speichern." />';
						
					$ret .= '</fieldset>';
					
					$ret .= '</form>';					
					$ret .= '</div>';
					
						
					
				} else {
					$GLOBALS['hints'][] = '<span style="color:#900;font-weight:bold;">Die gewählte Klasse existiert nicht. Versuchen Sie es noch einmal.</span>';
					$portalbool = true;
				} break;	
			
			
			case 'add_offer': {
					
					$no = $offers;
					$no['NeuesAngebot'] = Array('name'=>'','longdesc'=>'', 'place'=>'', 'time' => '', 'maxtn'=>'', 'teacher' => '', 'meet'=>'','preferred'=>'','excluded' => '','costs'=>0.0, 'left'=>0.0,'precosts'=>0.0,'refundall'=>0.0,'refund'=>0.0);

					$GLOBALS['output'] .= produce_offer_edit(VIEW,AUTH,'NeuesAngebot',$students,$teachers,$no);

				} break;
			case 'edit_offer': if ((int)OFFER > 0 AND isset($offers[OFFER])) {
			
					$GLOBALS['output'] .= produce_offer_edit(VIEW,AUTH,OFFER,$students,$teachers,$offers);
				
				} else {
					$GLOBALS['hints'][] = '<span style="color:#900;font-weight:bold;">Das gewählte Angebot existiert nicht (mehr)!</span>';
					$portalbool = true;
				} break;
			case 'manage_classes': {
					$GLOBALS['output'] .= '<div class="fieldset">';
					$GLOBALS['output'] .= '<h2>Klassenmanagement</h2>';
					
					$GLOBALS['output'] .= '<form action="./?view=manage_classes" method="POST">';
					
					$GLOBALS['output'] .= '<fieldset>';
					$GLOBALS['output'] .= '<legend>Hinweis</legend>';
					$GLOBALS['output'] .= '<p>Das Löschen von Klassen ist momentan NICHT möglich, um ein etwaiges Löschen einer falschen Klasse zu vermeiden. Wollen Sie Klassen effektiv aus dem PVS entferenen, genügt es, sie zu deaktivieren. Deaktivierte Klassen verschwinden bis auf diese Bearbeitungsseite komplett aus dem Event. Deaktivierte Klassen verlieren die Zuordnung zu ihrem Klassenlehrer. Umbenannte Klassen fallen (je nach neuem Namen) evtl. bei der Angebotszuteilung durch das Raster aus gesperrten und bevorzugten Klassen. (Beispiel: Klasse K1-LKM1 war mit Sperreintrag "K1" von Angebot "Schneballschlacht" ausgeschlossen und kann unter neuem Namen Jgst1 wieder teilnehmen, weil der Sperreintrag nicht mehr zutrifft.)</p>';
					$GLOBALS['output'] .= '</fieldset>';
					
					$GLOBALS['output'] .= '<table class="ov_table ed_table">';
					$GLOBALS['output'] .= '<thead>';
					$GLOBALS['output'] .= '<tr>';
					$GLOBALS['output'] .= '<th>Klasse</th>';
					$GLOBALS['output'] .= '<th>Status</th>';
					$GLOBALS['output'] .= '<th>Umbenennen</th>';
					$GLOBALS['output'] .= '<th></th>';
					$GLOBALS['output'] .= '<th>Klasse</th>';
					$GLOBALS['output'] .= '<th>Status</th>';
					$GLOBALS['output'] .= '<th>Umbenennen</th>';
					$GLOBALS['output'] .= '</tr>';
					$GLOBALS['output'] .= '</thead><tbody>';
					
					$tClasses = Array();
					
					if (is_dir('./db/'.DB.'/deactivated')) {
						$files = scandir('./db/'.DB.'/deactivated');
						foreach ($files as $file) {
							if (substr($file,0,6) == 'class_' AND substr($file,-4) == '.ini' ) {
								$tClasses[] = Array('name' => substr($file,6,-4), 'status' => 0);
							}
						}
					} 
					
					foreach ($classes as $class => $data) {
						$tClasses[] = Array('name' => $class, 'status' => 1);
					}
					
					usort($tClasses,'sort_db');
					
					$n = 1;
					$i = 1;
					
					foreach ($tClasses as $class) {
					
						if (bcmod($n,2) == 1) {
							if (bcmod($i,2) == 1) {
								$GLOBALS['output'] .= '<tr class="tr_class_0">';
							} else {
								$GLOBALS['output'] .= '<tr class="tr_class_1">';
							}
						}
						$GLOBALS['output'] .= '<td>'.$class['name'];
						$GLOBALS['output'] .= '<input type="hidden" name="cm_'.$n.'_name" value="'.$class['name'].'">';
						$GLOBALS['output'] .= '<input type="hidden" name="cm_'.$n.'_oldStatus" value="'.$class['status'].'">';
						$GLOBALS['output'] .= '</td>';
						$GLOBALS['output'] .= '<td style="text-align:left;">';
						$GLOBALS['output'] .= '<input type="radio" name="cm_'.$n.'_status" value="1" id="cm_'.$n.'_status1" '.($class['status']?' checked="checked"':'').'><label for="cm_'.$n.'_status1">Aktiviert</label><br/>';
						$GLOBALS['output'] .= '<input type="radio" name="cm_'.$n.'_status" value="0" id="cm_'.$n.'_status0" '.($class['status']?'':' checked="checked"').'><label for="cm_'.$n.'_status0">Deaktiviert</label>';
						$GLOBALS['output'] .= '</td>';
						$GLOBALS['output'] .= '<td><input type="text" name="cm_'.$n.'_rename" value="" /></td>';
						
						if (bcmod($n,2) == 0) {
							$GLOBALS['output'] .= '</tr>';
							$i++;
						} else {
							$GLOBALS['output'] .= '<td></td>';
						}
						
						$n++;
					}
					
					if (bcmod($n,2) == 0) { $ret .= '<td colspan="3"></td>'; }
					
					$GLOBALS['output'] .= '</tbody></table>';
					
					$GLOBALS['output'] .= '<fieldset><legend>Optionen</legend>';
					
					$GLOBALS['output'] .= '<input type="hidden" name="action"  value="manage_classes" />';
					$GLOBALS['output'] .= '<input type="hidden" name="cm_counter"  value="'.($n - 1).'" />';
					
					$GLOBALS['output'] .= '<input type="submit" value="Daten speichern." />';
					
					$GLOBALS['output'] .= '</fieldset>';
					
					
					
					
					$GLOBALS['output'] .= '</form>';
				} break;
			case 'edit_buses': {
					$GLOBALS['output'] .= '<form action="./?view=edit_buses" method="POST">';
					
					$GLOBALS['output'] .= '<div class="fieldset">';
					$GLOBALS['output'] .= '<h2>Busliste bearbeiten</h2>';
					
					
					$GLOBALS['output'] .= '<fieldset>';
					$GLOBALS['output'] .= '<legend>Hinweis</legend>';
					$GLOBALS['output'] .= '<p>Zum Löschen eines Busses einfach seine eindeutige Bezeichnung löschen. Busse können Fahrgemeinschaften zugeordnet werden, die ein oder mehrere Angebote abdecken. Das Speichern speichert sowohl Änderungen an Bussen als auch an Fahrgemeinschaften.</p>';
					$GLOBALS['output'] .= '</fieldset>';
					
					$GLOBALS['output'] .= '<table class="ov_table ed_table">';
					$GLOBALS['output'] .= '<thead><tr>';
					$GLOBALS['output'] .= '<th>#</th>';
					$GLOBALS['output'] .= '<th>Busunternehmen</th>';
					$GLOBALS['output'] .= '<th>Eindeutige<br/>Bezeichnung</th>';
					$GLOBALS['output'] .= '<th>Fahrgemeinschaft</th>';
					$GLOBALS['output'] .= '<th>Verfügbare Plätze<br/>(Schüler u. Lehrer)</th>';
					$GLOBALS['output'] .= '</tr></thead><tbody>';
					
					$ret =& $GLOBALS['output'];
					
					$tbuses = $buses;
					for ($i = 0; $i < 5; $i++) {
						$tbuses['TEMP'.$i] = Array('type'=>'bus','company'=>'','tag'=>'','capacity'=>0,'carpool'=>0);
					}
					
					$n = 1;
					
					foreach ($tbuses as $tid => $bdata) {
						
						if ($tid == 'TEMP0') {
							$ret .= '<tr>';
							$ret .= '<td class="th" colspan="5" style="font-weight:bold;">Busse hinzufügen: (Angabe einer eindeutigen Bezeichnung ist Pflicht)</td>';
							$ret .= '</tr>';
						}
						
						if (bcmod($n,2) == 1) {
							$ret .= '<tr class="tr_class_0">';
						} else {
							$ret .= '<tr class="tr_class_1">';
						}
						$ret .= '<td>'.$n.'</td>';
						$ret .= '<td><input type="text" name="edit_buses_bus_'.$tid.'_company" value="'.$bdata['company'].'" /></td>';
						$ret .= '<td><input type="text" name="edit_buses_bus_'.$tid.'_tag" value="'.$bdata['tag'].'" /></td>';
						
						$ret .= '<td><select size="1" name="edit_buses_bus_'.$tid.'_carpool">';
						
						$ret .= '<option value="0"';
						if ($bdata['carpool'] == 0) { $ret .= ' selected="selected"'; }
						$ret .= '>Bitte wählen</option>';
						
						foreach ($carpools as $pid => $pdata) {
							
							$ret .= '<option value="'.$pid.'"';
							if ($bdata['carpool'] == $pid) { $ret .= ' selected="selected"'; }
							$ret .= '>'.$pdata['name'].'</option>';
						
						}
						
						$ret .= '</select></td>';
						
						$ret .= '<td><input type="text" name="edit_buses_bus_'.$tid.'_capacity" value="'.$bdata['capacity'].'" /></td>';
						
						$ret .= '</tr>';
						
						$n++;
						
					}
					
					$GLOBALS['output'] .= '</tbody></table>';
					
					$GLOBALS['output'] .= '<fieldset><legend>Optionen</legend>';
					
					$GLOBALS['output'] .= '<textarea style="display:none;" rows="5" readonly="readonly" cols="25" name="edit_buses_control_buses">'.(file_get_contents('./db/'.DB.'/buses.ini')).'</textarea>';

					$GLOBALS['output'] .= '<input type="hidden" name="action"  value="edit_buses" />';
					
					$GLOBALS['output'] .= '<input type="submit" value="Busse und Fahrgemeinschaften speichern." />';
					
					$GLOBALS['output'] .= '</fieldset>';
					
					$GLOBALS['output'] .= '</div>';
					
					$GLOBALS['output'] .= '<div class="fieldset">';
					$GLOBALS['output'] .= '<h2>Fahrgemeinschaften bearbeiten</h2>';
					
					
					$GLOBALS['output'] .= '<fieldset>';
					$GLOBALS['output'] .= '<legend>Hinweis</legend>';
					$GLOBALS['output'] .= '<p>Jedes Angebot kann maximal einer Fahrgemeinschaft zugeordnet werden. Pflichtangabe ist die Bezeichnung. Wird diese gelöscht, wird die Fahrgemeinschaft zum Löschen freigegeben.</p>';
					$GLOBALS['output'] .= '</fieldset>';
					
					
					
					$GLOBALS['output'] .= '<table class="ov_table ed_table">';
					$GLOBALS['output'] .= '<thead><tr>';
					$GLOBALS['output'] .= '<th colspan="2">Fahrgemeinschaften</th>';
					if (count($offers)) $GLOBALS['output'] .= '<th colspan="'.count($offers).'">Zugeordnete Angebote</th>';
					$ret .= '<th rowspan="2">Aktuell<br/>benötigte Plätze</th>';
					$ret .= '<th rowspan="2">Aktuell<br/>verfügbare Plätze</th>';
					$GLOBALS['output'] .= '</tr><tr>';
					$GLOBALS['output'] .= '<th>#</th>';
					$GLOBALS['output'] .= '<th>Bezeichnung</th>';
					foreach ($offers as $odata) {
						$GLOBALS['output'] .= '<th style="overflow:hidden;max-width:150px;">'.$odata['name'].'</th>';
					}
					$GLOBALS['output'] .= '</tr>';
					$GLOBALS['output'] .= '</thead><tbody>';
					
					$tcp = $carpools;
					for ($i = 0; $i < 2; $i++) {
						$tcp['TEMP'.$i] = Array('type'=>'carpool','name'=>'','offers'=>'');
					}
					
					$n = 1;
					
					$ret .= '<tr>';
					$ret .= '<td colspan="2">Nicht zugewiesen</td>';
					
					$sumtn = Array();
				
					foreach ($offers as $oid => $odata) {
						$ret .= '<td style="text-align:center"><input type="radio" name="edit_buses_off_'.$oid.'" value="0" ';
						$bool = false;
						foreach ($tcp as $tid => $pdata) {
							if (preg_match('/\b'.$oid.'\b/',$pdata['offers'])) { $bool = true; break; }
						}
						if (!$bool) { $ret .= 'checked="checked" '; } 
						$ret .= '/></td>';
						
						//Kalkuliere sumtn
						$sumtn[$oid] = 0;
						foreach ($teachers as $tid => $tdata) {
							if (isset($tdata['offer']) AND $tdata['offer'] == $oid) $sumtn[$oid]++; 
						}
						foreach ($students as $tid => $tdata) {
							if (isset($tdata['offer']) AND $tdata['offer'] == $oid) $sumtn[$oid]++; 
						}
						
					}
					
					$ret .= '<td class="th" colspan="2" style="text-align:center"></td>';
					$ret .= '</tr>';
					
					foreach ($tcp as $tid => $pdata) {
						
						if ($tid == 'TEMP0') {
						
							if (bcmod($n,2) == 1) {
								$ret .= '<tr class="th tr_class_0">';
							} else {
								$ret .= '<tr class="th tr_class_1">';
							}
							
							$ret .= '<td colspan="2">Mitfahrer (aktuell)</td>';
							
							foreach ($offers as $oid => $odata) {
								$ret .= '<td>';
								$ret .= isset($sumtn[$oid])?$sumtn[$oid]:'';
								$ret .= '</td>';
							}
							$ret .= '<td class="th" colspan="2"></td>';
							$n++;
						
							$ret .= '<tr>';
							$ret .= '<td class="th" colspan="'.(4+count($offers)).'" style="font-weight:bold;">Fahrgemeinschaft hinzufügen: (Angabe einer Bezeichnung ist Pflicht)</td>';
							$ret .= '</tr>';
						}
						
						if (bcmod($n,2) == 1) {
							$ret .= '<tr class="tr_class_0">';
						} else {
							$ret .= '<tr class="tr_class_1">';
						}
						$ret .= '<td>'.$n.'</td>';
						$ret .= '<td><input type="text" name="edit_buses_cp_'.$tid.'_name" value="'.$pdata['name'].'" /></td>';
						
						$cpsum = 0;
						
						foreach ($offers as $oid => $odata) {
							$ret .= '<td><input type="radio" name="edit_buses_off_'.$oid.'" value="'.$tid.'" ';
							if (preg_match('/\b'.$oid.'\b/',$pdata['offers'])) { $ret .= ' checked="checked"'; $cpsum += $sumtn[$oid]; }
							$ret .= '/></td>';
						}
						
						$sumcap = 0;
						foreach ($buses as $bid => $bdata) {
							if (isset($bdata['carpool']) AND $bdata['carpool'] == $tid) {
								$sumcap = $sumcap + $bdata['capacity'];
							}
						}
						$ret .= '<td class="th">'.$cpsum.'</td>';
						$ret .= '<td class="th">'.$sumcap.'</td>';
						
						$ret .= '</tr>';
						
						$n++;
						
					}
					
					
					$GLOBALS['output'] .= '</tbody></table>';
					
					$GLOBALS['output'] .= '<fieldset><legend>Optionen</legend>';
					
					$GLOBALS['output'] .= '<textarea style="display:none;" rows="5" readonly="readonly" cols="25" name="edit_buses_control_carpools">'.(file_get_contents('./db/'.DB.'/carpools.ini')).'</textarea>';
					
					$GLOBALS['output'] .= '<input type="submit" value="Busse und Fahrgemeinschaften speichern." />';
					
					$GLOBALS['output'] .= '</fieldset>';
					
					$GLOBALS['output'] .= '</div>';
					
					$GLOBALS['output'] .= '</form>';
				} break;
			
			//PDF-Ansichten
			case 'pdf_classteacher_wishlist': if (isset($classes[CLASSFILTER])) {
					
					$para['header'] = 1;
					$para['data'] = Array();
					$para['data'][] = Array('#',Array('content'=>'Name, Vorname','align'=>'C'));
					for ($i = 1; $i <= $info['props']['max_wishes']; $i++) $para['data'][count($para['data'])-1][] = 'Wunsch '.$i;
					$para['format'] = Array('align_col_2'=>'L','repeat_header'=>true,'color2ndrow'=>Array(221,221,255),'width_col_1'=>7,'width_col_2'=>60,'fontsize'=>9,'rowheight'=>6.1,'table-align'=>'C');
					for ($i = 3; $i <= 2 + $info['props']['max_wishes']; $i++) $para['format']['width_col_'.$i] = round(110/$info['props']['max_wishes']);
					
					$tstudents = $classes[CLASSFILTER];
					$ct = count($tstudents);
					
					for ($i = $ct; $i < 32; $i++) $tstudents[] = Array('surname'=>'','forename'=>'');
					
					$n = 1;
					foreach ($tstudents as $sid => $sdata) {
						$tr =& $para['data'][];
						$tr = Array();
						$tr[] = $n; 
						$tr[] = (isset($sdata['surname'])?$sdata['surname']:'').', '.(isset($sdata['forename'])?$sdata['forename']:'');
						if ($tr[count($tr)-1] == ', ') $tr[count($tr)-1] = '';
						
						if (!isset($sdata['offer']) OR (int)$sdata['offer'] <= 0 OR !isset($offers[(int)$sdata['offer']])) {
							for ($i = 1; $i <= $info['props']['max_wishes']; $i++) $tr[] = '';
						} else {
							$tr[] = Array('colspan' => $info['props']['max_wishes'],'content'=>'Zugeordnet zu Angebot '.$offers[(int)$sdata['offer']]['name']);
						}
						$n++;
					}
					
					if(count($para['data']) > 33) {
						$GLOBALS['pdfOptions']['pagecount'] = true;
					} else {
						$GLOBALS['pdfOptions']['pagecount'] = false;
					}
					
					
					$pdf = new PVSPDF();
					$pdf->SetMargins(15,60);
					$pdf->SetTitle(utf8_decode('Wunschliste Klasse '.CLASSFILTER));
					$pdf->AddPage();
					$pdf->SetFont('Arial','B',22);
					$pdf->Ln(2);
					$pdf->Cell(0,10,utf8_decode('Wunschliste Klasse '.CLASSFILTER));
					$pdf->SetFont('Arial','B',12);
					$pdf->Ln(1);
					if (isset($teachers[get_class_teacher(CLASSFILTER,$teachers)])) {	
						$pdf->Cell(0,10,utf8_decode(id_to_address($teachers[get_class_teacher(CLASSFILTER,$teachers)])),0,0,'R');
					} else {
						$pdf->Cell(0,10,utf8_decode("(Kein Lehrer)"),0,0,'R');
					}
					$pdf->Ln(10);
					
					$pdf->Table($para['header'],$para['data'],$para['format']);
					
					//*/
				} else {
					$GLOBALS['hints'][] = '<span class="b cr">Fehlschlag - Die Klassenliste konnte nicht erzeugt werden, da die Klasse nicht gefunden wurde. Versuchen Sie es erneut.</span>';
					$portalbool = true;
					$html_override = true;
				} break;
			case 'pdf_all_wishlists': {
					$pdf = new PVSPDF();
					$pdf->SetMargins(15,60);
					$pdf->SetTitle(utf8_decode('Wunschliste alle Klassen'));
					foreach ($classes as $classfilter => $classdata) {
						$pdf->AddPage();
						$GLOBALS['pdfOptions']['pagecount'] = '{nb-class-'.$classfilter.'}';
						$pdf->SetFont('Arial','B',22);
						$pdf->Ln(2);
						$pdf->Cell(0,10,utf8_decode('Wunschliste Klasse '.$classfilter));
						$pdf->SetFont('Arial','B',12);
						$pdf->Ln(1);
						if (isset($teachers[get_class_teacher($classfilter,$teachers)])) {	
							$pdf->Cell(0,10,utf8_decode(id_to_address($teachers[get_class_teacher($classfilter,$teachers)])),0,0,'R');
						} else {
							$pdf->Cell(0,10,utf8_decode("(Kein Lehrer)"),0,0,'R');
						}
						$pdf->Ln(10);
						
						$para['header'] = 1;
						$para['data'] = Array();
						$para['data'][] = Array('#',Array('content'=>'Name, Vorname','align'=>'C'));
						for ($i = 1; $i <= $info['props']['max_wishes']; $i++) $para['data'][count($para['data'])-1][] = 'Wunsch '.$i;
						$para['format'] = Array('align_col_2'=>'L','repeat_header'=>true,'color2ndrow'=>Array(221,221,255),'width_col_1'=>7,'width_col_2'=>60,'fontsize'=>9,'rowheight'=>6.1,'table-align'=>'C');
						for ($i = 3; $i <= 2 + $info['props']['max_wishes']; $i++) $para['format']['width_col_'.$i] = round(110/$info['props']['max_wishes']);
						
						$tstudents = $classes[$classfilter];
						$ct = count($tstudents);
						
						for ($i = $ct; $i < 32; $i++) $tstudents[] = Array('surname'=>'','forename'=>'');
						
						$n = 1;
						foreach ($tstudents as $sid => $sdata) {
							$tr =& $para['data'][];
							$tr = Array();
							$tr[] = $n; 
							$tr[] = (isset($sdata['surname'])?$sdata['surname']:'').', '.(isset($sdata['forename'])?$sdata['forename']:'');
							if ($tr[count($tr)-1] == ', ') $tr[count($tr)-1] = '';
							
							if (!isset($sdata['offer']) OR (int)$sdata['offer'] <= 0 OR !isset($offers[(int)$sdata['offer']])) {
								for ($i = 1; $i <= $info['props']['max_wishes']; $i++) $tr[] = '';
							} else {
								$tr[] = Array('colspan' => $info['props']['max_wishes'],'content'=>'Zugeordnet zu Angebot '.$offers[(int)$sdata['offer']]['name']);
							}
							$n++;
						}
						
						$pdf->Table($para['header'],$para['data'],$para['format']);
					}
					
					$pdf->InFooter = true;
					$pdf->Footer();
					$pdf->InFooter = false;
					$GLOBALS['pdfOptions']['suppressfooter'] = true;
					$nb = $pdf->page;
					$nbpagesoid = Array();
					$oidcounter = Array();
					$pagenroid = Array();
					
					for ($i = 1; $i <= $nb; $i++) {
						$treffer = Array();
						preg_match('/\{nb-class-([\w|-]+)\}/',$pdf->pages[$i],$treffer);
						if (isset($treffer[1]) AND is_string($treffer[1])) {
							if (!isset($nbpagesoid[$treffer[1]])) $nbpagesoid[$treffer[1]] = 0;
							if (!isset($oidcounter[$treffer[1]])) $oidcounter[$treffer[1]] = 1;
							$nbpagesoid[$treffer[1]]++;
							$pagenroid[$i] = $treffer[1];
						}
					}
					
					for ($i = 1; $i <= $nb; $i++) {
						if (isset($pagenroid[$i])) {
							if ($nbpagesoid[$pagenroid[$i]] > 1) {
								$pdf->pages[$i] = str_replace('{nb-class-'.$pagenroid[$i].'}','Seite '.$oidcounter[$pagenroid[$i]].' von '.$nbpagesoid[$pagenroid[$i]],$pdf->pages[$i]);
								$oidcounter[$pagenroid[$i]]++;
							} else {
								$pdf->pages[$i] = str_replace('{nb-class-'.$pagenroid[$i].'}','',$pdf->pages[$i]);
							}
						}
					}
					
					//*/
				} break;
			case 'pdf_teachers_wishlist': {
					
					$para['header'] = 1;
					$para['data'] = Array();
					$para['data'][] = Array('#','Kürzel',Array('content'=>'Name, Vorname','align'=>'C'));
					for ($i = 1; $i <= $info['props']['max_wishes']; $i++) $para['data'][count($para['data'])-1][] = 'Wunsch '.$i;
					$para['data'][count($para['data'])-1][] = 'Mobilnummer';
					$para['format'] = Array('align_col_3'=>'L','repeat_header'=>true,'color2ndrow'=>Array(221,221,255),'width_col_1'=>7,'width_col_2'=>12,'width_col_3'=>55,'fontsize'=>9,'rowheight'=>6.1,'table-align'=>'C');
					for ($i = 4; $i <= 3 + $info['props']['max_wishes']; $i++) $para['format']['width_col_'.$i] = round(72/$info['props']['max_wishes']);
					$para['format']['width_col_'.(4 + $info['props']['max_wishes'])] = 35;
					
					$tteachers = $teachers;
					$ct = count($tteachers);
					$nat = Array();
					
					for ($i = 0; $i < 5; $i++) $tteachers[] = Array('sign'=>'','surname'=>'','forename'=>'');
					
					$n = 1;
					foreach ($tteachers as $sid => $sdata) {
						if (!isset($sdata['available']) OR (bool)$sdata['available']) {
							$tr =& $para['data'][];
							$tr = Array();
							$tr[] = $n; 
							$tr[] = isset($sdata['sign'])?$sdata['sign']:''; 
							$tr[] = (isset($sdata['surname'])?$sdata['surname']:'').', '.(isset($sdata['forename'])?$sdata['forename']:'');
							if ($tr[count($tr)-1] == ', ') $tr[count($tr)-1] = '';
						
							if (!isset($sdata['offer']) OR (int)$sdata['offer'] <= 0 OR !isset($offers[(int)$sdata['offer']])) {
								for ($i = 1; $i <= $info['props']['max_wishes']; $i++) $tr[] = '';
							} else {
								$tr[] = Array('colspan' => $info['props']['max_wishes'],'content'=>'Zugeordnet zu Angebot '.$offers[(int)$sdata['offer']]['name']);
							}
							
							$tr[] = (isset($sdata['phone']) AND $sdata['phone'] != '')?$sdata['phone']:''; 
							
							$n++;
						} else {
							$nat[$sid] = $sdata;
						}
					}
					
					if(count($para['data']) > 33) {
						$GLOBALS['pdfOptions']['pagecount'] = true;
					} else {
						$GLOBALS['pdfOptions']['pagecount'] = false;
					}
					
					
					$pdf = new PVSPDF();
					$pdf->SetMargins(15,60);
					$pdf->SetTitle(utf8_decode('Wunschliste Lehrkräfte'));
					$pdf->AddPage();
					$pdf->SetFont('Arial','B',22);
					$pdf->Ln(2);
					$pdf->Cell(0,10,utf8_decode('Wunschliste Lehrkräfte'));
					$pdf->Ln(7);
					$pdf->SetFont('Arial','',9);
					$pdf->Cell(0,10,utf8_decode('Hinweis: Mobilnummern werden nicht an Schüler weitergegeben sondern nur an begleitende Kollegen.'));
					$pdf->SetFont('Arial','B',12);
					$pdf->Ln(10);
					
					$pdf->Table($para['header'],$para['data'],$para['format']);
					
					if (count($nat)) {
						$pdf->SetFont('Arial','B',15);
						$pdf->Ln(10);
						$pdf->Cell(0,0,utf8_decode('Folgende Lehrkräfte sind für das Event nicht verfügbar:'),0,1,'L');
						$pdf->SetX(20);
						$pdf->Ln(5);
						
						$pdf->SetFont('Arial','',10);
						$add = '';
						$str = '';
						foreach ($nat as $sid => $sdata) {
							$str .= utf8_decode($add.id_to_address($sdata));
							$add = ', ';
						}
						$pdf->MultiCell(0,5,$str);
					}
					
					//*/
				} break;
			case 'pdf_ov_offers': {
								
					$pdf = new PVSPDF();
					$pdf->SetMargins(15,60);
					$pdf->SetTitle(utf8_decode('Überblick über alle Angebote'));
					
					$n = 0;
					foreach ($offers as $oid => $odata) {
					
						$da = Array(); 
						$fo = Array('repeat_header'=>true,'color2ndrow'=>Array(221,221,255),'fontsize'=>11,'rowheight'=>6.1,'table-align'=>'C');
						
						$fo['width_col_1'] = 40;
						$fo['width_col_2'] = 40;
						$fo['width_col_3'] = 40;
						$fo['width_col_4'] = 40;
						
						$da[] = Array(Array('colspan'=>4,'content'=>$odata['name']));
						$da[] = Array('Wo?',Array('content'=>$odata['place'],'fontstyle'=>'B'),'Wann?',Array('content'=>$odata['time'],'fontstyle'=>'B'));
						$da[] = Array(Array('colspan'=>4,'content'=>''));
						$da[] = Array(Array('colspan'=>4,'fontstyle'=>'I','content'=>$odata['longdesc'],'multi'=>true));
						$da[] = Array(Array('colspan'=>4,'content'=>''));
						$da[] = Array('Max.Teilnehmer:',Array('content'=>$odata['maxtn'],'fontstyle'=>'B'),'Teilnehmerbeitrag:',Array('content'=>substr(float_to_eur($odata['costs']),0,-6).'EUR','fontstyle'=>'B'));
						$da[] = Array(Array('colspan'=>4,'content'=>''));
						if ((int)$odata['teacher'] > 0 AND $teachers[(int)$odata['teacher']]) {	
							$da[] = Array(Array('colspan'=>2,'content'=>'Ansprechpartner: '),Array('colspan'=>2,'content'=>id_to_address($teachers[(int)$odata['teacher']])));
						} else {
							$da[] = Array(Array('colspan'=>2,'content'=>'Ansprechpartner: '),Array('colspan'=>2,'content'=>'(Noch nicht eingetragen)'));
						}
						$da[] = Array(Array('colspan'=>2,'content'=>'Einverständniserklärung: '),Array('colspan'=>2,'content'=>((!isset($odata['eerkl']) OR $odata['eerkl']))?'Wird benötigt.':'Wird nicht benötigt.'));
						if (!isset($odata['avgender']) OR $odata['avgender'] == 'm,f') {
							$tret = 'männlich & weiblich';
						} elseif ($odata['avgender'] == 'm') {
							$tret = 'männlich';
						} elseif ($odata['avgender'] == 'f') {
							$tret = 'weiblich';
						} else {
							$tret = '';
						}
						$da[] = Array(Array('colspan'=>2,'content'=>'Zugel. Geschlechter: '),Array('colspan'=>2,'content'=>$tret));
						$da[] = Array(Array('colspan'=>2,'content'=>'Bevorzugt für Klassen: '),Array('colspan'=>2,'content'=>$odata['preferred']));
						$da[] = Array(Array('colspan'=>2,'content'=>'NICHT für Klassen: '),Array('colspan'=>2,'content'=>$odata['excluded']));
						
						if (bcmod($n,2) == 0) {
							$pdf->AddPage();
							$pdf->SetY(60);
						} else {
							$pdf->SetY(165);
						}
						$pdf->Table(1,$da,$fo);
						$n++;
					}
					
				} break;
			case 'pdf_offerprizelist_class': if (isset($classes[CLASSFILTER])) {
				
					$para['header'] = 1;
					$para['data'] = Array();
					$para['data'][] = Array('#',Array('content'=>'Name, Vorname','align'=>'C'),Array('align'=>'C','content'=>'Angebotszuordnung'),Array('align'=>'C','content'=>'Vorauskasse'),Array('align'=>'C','content'=>'bezahlt'),Array('align'=>'C','content'=>'Einv.erkl.'));
					
					$para['format'] = Array('align_col_2'=>'L','align_col_4'=>'R','repeat_header'=>true,'color2ndrow'=>Array(221,221,255),'width_col_1'=>7,'width_col_2'=>60,'width_col_3'=>60,'width_col_4'=>25,'width_col_5'=>15,'width_col_6'=>15,'fontsize'=>9,'rowheight'=>6.1,'table-align'=>'C');
					
					$tstudents = $classes[CLASSFILTER];
					$ct = count($tstudents);
						
					$n = 1;
					$sum = 0.0;
					foreach ($tstudents as $sid => $sdata) {
						$tr =& $para['data'][];
						$tr = Array();
						$tr[] = $n; 
						$tr[] = (isset($sdata['surname'])?$sdata['surname']:'').', '.(isset($sdata['forename'])?$sdata['forename']:'');
						if ($tr[count($tr)-1] == ', ') $tr[count($tr)-1] = '';
						
						if (!isset($sdata['offer']) OR (int)$sdata['offer'] <= 0 OR !isset($offers[(int)$sdata['offer']])) {
							$tr[] = 'NICHT ZUGEORDNET';
							$tr[] = '0,00 EUR';
							$tr[] = '----';
							$tr[] = '----';
						} else {
							$sum = $sum + $offers[(int)$sdata['offer']]['costs'];
							$tr[] = Array('content'=>$offers[(int)$sdata['offer']]['name']);
							$tr[] = Array('content'=>substr(float_to_eur($offers[(int)$sdata['offer']]['costs']),0,-7).' EUR');
							if ($offers[(int)$sdata['offer']]['costs'] == 0.0) { $tr[] = '----'; } else { $tr[] = '[__]'; }
							if (isset($offers[(int)$sdata['offer']]['eerkl']) AND $offers[(int)$sdata['offer']]['eerkl']) {
								$tr[] = '[__]';
							} elseif (isset($offers[(int)$sdata['offer']]['eerkl'])) {
								$tr[] = '----';
							} else {
								$tr[] = '----';
							}
						}
						$n++;
					}
					
					$para['data'][] = Array(Array('colspan'=>3,'content'=>'Summe: ','align'=>'R','fontstyle'=>'B'),substr(float_to_eur($sum),0,-7).' EUR',Array('colspan'=>2,'content'=>''));
								
					if(count($para['data']) > 33) {
						$GLOBALS['pdfOptions']['pagecount'] = true;
					} else {
						$GLOBALS['pdfOptions']['pagecount'] = false;
					}
					
					
					$pdf = new PVSPDF();
					$pdf->SetMargins(15,60);
					$pdf->SetTitle(utf8_decode('Preisliste und Angebotszuordnung Klasse '.CLASSFILTER));
					$pdf->AddPage();
					$pdf->SetFont('Arial','B',22);
					$pdf->Ln(2);
					$pdf->Cell(0,10,utf8_decode('Angebotszuordnung Klasse '.CLASSFILTER));
					$pdf->SetFont('Arial','B',12);
					$pdf->Ln(1);
					if (isset($teachers[get_class_teacher(CLASSFILTER,$teachers)])) {	
						$pdf->Cell(0,10,utf8_decode(id_to_address($teachers[get_class_teacher(CLASSFILTER,$teachers)])),0,0,'R');
					} else {
						$pdf->Cell(0,10,utf8_decode("(Kein Lehrer)"),0,0,'R');
					}
					$pdf->Ln(10);
					
					$pdf->Table($para['header'],$para['data'],$para['format']);
					
					//*/
				} else {
					$GLOBALS['hints'][] = '<span class="b cr">Fehlschlag - Die Klassenliste konnte nicht erzeugt werden, da die Klasse nicht gefunden wurde. Versuchen Sie es erneut.</span>';
					$portalbool = true;
					$html_override = true;
				} break;
			case 'pdf_offerprizelist_all': {
					$pdf = new PVSPDF();
					$pdf->SetMargins(15,60);
					$pdf->SetTitle(utf8_decode('Preisliste und Angebotszuordnung alle Klassen'));
					foreach ($classes as $classfilter => $classdata) {
						$pdf->AddPage();
						$GLOBALS['pdfOptions']['pagecount'] = '{nb-class-'.$classfilter.'}';
						$pdf->SetFont('Arial','B',22);
						$pdf->Ln(2);
						$pdf->Cell(0,10,utf8_decode('Angebotszuordnung Klasse '.$classfilter));
						$pdf->SetFont('Arial','B',12);
						$pdf->Ln(1);
						if (isset($teachers[get_class_teacher($classfilter,$teachers)])) {	
							$pdf->Cell(0,10,utf8_decode(id_to_address($teachers[get_class_teacher($classfilter,$teachers)])),0,0,'R');
						} else {
							$pdf->Cell(0,10,utf8_decode("(Kein Lehrer)"),0,0,'R');
						}
						$pdf->Ln(10);
						
						$para['header'] = 1;
						$para['data'] = Array();
						$para['data'][] = Array('#',Array('content'=>'Name, Vorname','align'=>'C'),Array('align'=>'C','content'=>'Angebotszuordnung'),Array('align'=>'C','content'=>'Vorauskasse'),Array('align'=>'C','content'=>'bezahlt'),Array('align'=>'C','content'=>'Einv.erkl.'));
						
						$para['format'] = Array('align_col_2'=>'L','align_col_4'=>'R','repeat_header'=>true,'color2ndrow'=>Array(221,221,255),'width_col_1'=>7,'width_col_2'=>60,'width_col_3'=>60,'width_col_4'=>25,'width_col_5'=>15,'width_col_6'=>15,'fontsize'=>9,'rowheight'=>6.1,'table-align'=>'C');
						
						$tstudents = $classes[$classfilter];
						$ct = count($tstudents);
							
						$n = 1;
						$sum = 0.0;
						foreach ($tstudents as $sid => $sdata) {
							$tr =& $para['data'][];
							$tr = Array();
							$tr[] = $n; 
							$tr[] = (isset($sdata['surname'])?$sdata['surname']:'').', '.(isset($sdata['forename'])?$sdata['forename']:'');
							if ($tr[count($tr)-1] == ', ') $tr[count($tr)-1] = '';
							
							if (!isset($sdata['offer']) OR (int)$sdata['offer'] <= 0 OR !isset($offers[(int)$sdata['offer']])) {
								$tr[] = 'NICHT ZUGEORDNET';
								$tr[] = '0,00 EUR';
								$tr[] = '----';
								$tr[] = '----';
							} else {
								$sum = $sum + $offers[(int)$sdata['offer']]['costs'];
								$tr[] = Array('content'=>$offers[(int)$sdata['offer']]['name']);
								$tr[] = Array('content'=>substr(float_to_eur($offers[(int)$sdata['offer']]['costs']),0,-7).' EUR');
								if ($offers[(int)$sdata['offer']]['costs'] == 0.0) { $tr[] = '----'; } else { $tr[] = '[__]'; }
								if (isset($offers[(int)$sdata['offer']]['eerkl']) AND $offers[(int)$sdata['offer']]['eerkl']) {
									$tr[] = '[__]';
								} elseif (isset($offers[(int)$sdata['offer']]['eerkl'])) {
									$tr[] = '----';
								} else {
									$tr[] = '----';
								}
							}
							$n++;
						}
						
						$para['data'][] = Array(Array('colspan'=>3,'content'=>'Summe: ','align'=>'R','fontstyle'=>'B'),substr(float_to_eur($sum),0,-7).' EUR',Array('colspan'=>2,'content'=>''));
						
						$pdf->Table($para['header'],$para['data'],$para['format']);
					}
					
					
					$pdf->InFooter = true;
					$pdf->Footer();
					$pdf->InFooter = false;
					$GLOBALS['pdfOptions']['suppressfooter'] = true;
					$nb = $pdf->page;
					$nbpagesoid = Array();
					$oidcounter = Array();
					$pagenroid = Array();
					
					for ($i = 1; $i <= $nb; $i++) {
						$treffer = Array();
						preg_match('/\{nb-class-([\w|-]+)\}/',$pdf->pages[$i],$treffer);
						if (isset($treffer[1]) AND is_string($treffer[1])) {
							if (!isset($nbpagesoid[$treffer[1]])) $nbpagesoid[$treffer[1]] = 0;
							if (!isset($oidcounter[$treffer[1]])) $oidcounter[$treffer[1]] = 1;
							$nbpagesoid[$treffer[1]]++;
							$pagenroid[$i] = $treffer[1];
						}
					}
					
					for ($i = 1; $i <= $nb; $i++) {
						if (isset($pagenroid[$i])) {
							if ($nbpagesoid[$pagenroid[$i]] > 1) {
								$pdf->pages[$i] = str_replace('{nb-class-'.$pagenroid[$i].'}','Seite '.$oidcounter[$pagenroid[$i]].' von '.$nbpagesoid[$pagenroid[$i]],$pdf->pages[$i]);
								$oidcounter[$pagenroid[$i]]++;
							} else {
								$pdf->pages[$i] = str_replace('{nb-class-'.$pagenroid[$i].'}','',$pdf->pages[$i]);
							}
						}
					}
					//*/
				} break;
			case 'pdf_busofferlist_all': {
					$pdf = new PVSPDF();
					$pdf->SetMargins(15,60);
					$pdf->SetTitle(utf8_decode('Angebots- und Buszuordnung alle Klassen'));
					foreach ($classes as $classfilter => $classdata) {
						$pdf->AddPage();
						$GLOBALS['pdfOptions']['pagecount'] = '{nb-class-'.$classfilter.'}';
						$pdf->SetFont('Arial','B',22);
						$pdf->Ln(2);
						$pdf->Cell(0,10,utf8_decode('Buszuordnung Klasse '.$classfilter));
						$pdf->SetFont('Arial','B',12);
						$pdf->Ln(1);
						if (isset($teachers[get_class_teacher($classfilter,$teachers)])) {	
							$pdf->Cell(0,10,utf8_decode(id_to_address($teachers[get_class_teacher($classfilter,$teachers)])),0,0,'R');
						} else {
							$pdf->Cell(0,10,utf8_decode("(Kein Lehrer)"),0,0,'R');
						}
						$pdf->Ln(10);
						
						$para['header'] = 1;
						$para['data'] = Array();
						$para['data'][] = Array('#',Array('content'=>'Name, Vorname','align'=>'C'),Array('align'=>'C','content'=>'Angebotszuordnung'),Array('align'=>'C','content'=>'Buszuordnung'));
						
						$para['format'] = Array('align_col_2'=>'L','repeat_header'=>true,'color2ndrow'=>Array(221,221,255),'fontsize'=>9,'rowheight'=>6.1,'table-align'=>'C');
						$para['format']['colwidth'] = Array(7,60,60,53);
						
						$tstudents = $classes[$classfilter];
						$ct = count($tstudents);
							
						$n = 1;
						foreach ($tstudents as $sid => $sdata) {
							$tr =& $para['data'][];
							$tr = Array();
							$tr[] = $n;  
							$tr[] = (isset($sdata['surname'])?$sdata['surname']:'').', '.(isset($sdata['forename'])?$sdata['forename']:'');
							if ($tr[count($tr)-1] == ', ') $tr[count($tr)-1] = '';
							
							if (!isset($sdata['offer']) OR (int)$sdata['offer'] <= 0 OR !isset($offers[(int)$sdata['offer']])) {
								$tr[] = Array('content'=>'NICHT ZUGEORDNET','colspan'=>2);
							} else {
								$tr[] = Array('content'=>$offers[(int)$sdata['offer']]['name']);
								if (isset($sdata['bus']) AND (int)$sdata['bus'] > 0 AND isset($buses[(int)$sdata['bus']])) {
									$tr[] = $buses[(int)$sdata['bus']]['tag'].' ('.$buses[(int)$sdata['bus']]['company'].')';
								} else {
									$tr[] = 'NICHT ZUGEORDNET';
								}
							}
							$n++;
						}
						$pdf->Table($para['header'],$para['data'],$para['format']);
					}
					
					$pdf->InFooter = true;
					$pdf->Footer();
					$pdf->InFooter = false;
					$GLOBALS['pdfOptions']['suppressfooter'] = true;
					$nb = $pdf->page;
					$nbpagesoid = Array();
					$oidcounter = Array();
					$pagenroid = Array();
					
					for ($i = 1; $i <= $nb; $i++) {
						$treffer = Array();
						preg_match('/\{nb-class-([\w|-]+)\}/',$pdf->pages[$i],$treffer);
						if (isset($treffer[1]) AND is_string($treffer[1])) {
							if (!isset($nbpagesoid[$treffer[1]])) $nbpagesoid[$treffer[1]] = 0;
							if (!isset($oidcounter[$treffer[1]])) $oidcounter[$treffer[1]] = 1;
							$nbpagesoid[$treffer[1]]++;
							$pagenroid[$i] = $treffer[1];
						}
					}
					
					for ($i = 1; $i <= $nb; $i++) {
						if (isset($pagenroid[$i])) {
							if ($nbpagesoid[$pagenroid[$i]] > 1) {
								$pdf->pages[$i] = str_replace('{nb-class-'.$pagenroid[$i].'}','Seite '.$oidcounter[$pagenroid[$i]].' von '.$nbpagesoid[$pagenroid[$i]],$pdf->pages[$i]);
								$oidcounter[$pagenroid[$i]]++;
							} else {
								$pdf->pages[$i] = str_replace('{nb-class-'.$pagenroid[$i].'}','',$pdf->pages[$i]);
							}
						}
					}
					
					//*/
				} break;
			case 'pdf_busofferlist_class': if (isset($classes[CLASSFILTER])) {
					$para['header'] = 1;
					$para['data'] = Array();
					$para['data'][] = Array('#',Array('content'=>'Name, Vorname','align'=>'C'),Array('align'=>'C','content'=>'Angebotszuordnung'),Array('align'=>'C','content'=>'Buszuordnung'));
					
					$para['format'] = Array('align_col_2'=>'L','repeat_header'=>true,'color2ndrow'=>Array(221,221,255),'fontsize'=>9,'rowheight'=>6.1,'table-align'=>'C');
					$para['format']['colwidth'] = Array(7,60,60,53);
					
					$tstudents = $classes[CLASSFILTER];
					$ct = count($tstudents);
						
					$n = 1;
					foreach ($tstudents as $sid => $sdata) {
						$tr =& $para['data'][];
						$tr = Array();
						$tr[] = $n;  
						$tr[] = (isset($sdata['surname'])?$sdata['surname']:'').', '.(isset($sdata['forename'])?$sdata['forename']:'');
						if ($tr[count($tr)-1] == ', ') $tr[count($tr)-1] = '';
						
						if (!isset($sdata['offer']) OR (int)$sdata['offer'] <= 0 OR !isset($offers[(int)$sdata['offer']])) {
							$tr[] = Array('content'=>'NICHT ZUGEORDNET','colspan'=>2);
						} else {
							$tr[] = Array('content'=>$offers[(int)$sdata['offer']]['name']);
							if (isset($sdata['bus']) AND (int)$sdata['bus'] > 0 AND isset($buses[(int)$sdata['bus']])) {
								$tr[] = $buses[(int)$sdata['bus']]['tag'].' ('.$buses[(int)$sdata['bus']]['company'].')';
							} else {
								$tr[] = 'NICHT ZUGEORDNET';
							}
						}
						$n++;
					}
					
					if(count($para['data']) > 33) {
						$GLOBALS['pdfOptions']['pagecount'] = true;
					} else {
						$GLOBALS['pdfOptions']['pagecount'] = false;
					}
					
					
					$pdf = new PVSPDF();
					$pdf->SetMargins(15,60);
					$pdf->SetTitle(utf8_decode('Angebots- und Buszuordnung Klasse '.CLASSFILTER));
					$pdf->AddPage();
					$pdf->SetFont('Arial','B',22);
					$pdf->Ln(2);
					$pdf->Cell(0,10,utf8_decode('Buszuordnung Klasse '.CLASSFILTER));
					$pdf->SetFont('Arial','B',12);
					$pdf->Ln(1);
					if (isset($teachers[get_class_teacher(CLASSFILTER,$teachers)])) {	
						$pdf->Cell(0,10,utf8_decode(id_to_address($teachers[get_class_teacher(CLASSFILTER,$teachers)])),0,0,'R');
					} else {
						$pdf->Cell(0,10,utf8_decode("(Kein Lehrer)"),0,0,'R');
					}
					$pdf->Ln(10);
					
					$pdf->Table($para['header'],$para['data'],$para['format']);
					
				} else {
					$GLOBALS['hints'][] = '<span class="b cr">Fehlschlag - Die Klassenliste konnte nicht erzeugt werden, da die Klasse nicht gefunden wurde. Versuchen Sie es erneut.</span>';
					$portalbool = true;
					$html_override = true;
				} break;
			case 'pdf_busofferlist_teachers': {
				
					$para['header'] = 1;
					$para['data'] = Array();
					$para['data'][] = Array('#','Kürzel',Array('content'=>'Name, Vorname','align'=>'C'),Array('align'=>'C','content'=>'Angebotszuordnung'),Array('align'=>'C','content'=>'Buszuordnung'));
					
					$para['format'] = Array('align_col_3'=>'L','repeat_header'=>true,'color2ndrow'=>Array(221,221,255),'width_col_1'=>7,'width_col_2'=>12,'width_col_3'=>50,'width_col_4'=>60,'width_col_5'=>50,'fontsize'=>9,'rowheight'=>6.1,'table-align'=>'C');
					
					$tstudents = $teachers;
					$ct = count($tstudents);
					$nat = Array();
						
					$n = 1;
					foreach ($tstudents as $sid => $sdata) {
						if (!isset($sdata['available']) OR (bool)$sdata['available']) {
							$tr =& $para['data'][];
							$tr = Array();
							$tr[] = $n; 
							$tr[] = isset($sdata['sign'])?$sdata['sign']:''; 
							$tr[] = (isset($sdata['surname'])?$sdata['surname']:'').', '.(isset($sdata['forename'])?$sdata['forename']:'');
							if ($tr[count($tr)-1] == ', ') $tr[count($tr)-1] = '';
							
							if (!isset($sdata['offer']) OR (int)$sdata['offer'] <= 0 OR !isset($offers[(int)$sdata['offer']])) {
								$tr[] = Array('content'=>'NICHT ZUGEORDNET','colspan'=>2);
							} else {
								$tr[] = Array('content'=>$offers[(int)$sdata['offer']]['name']);
								if (isset($sdata['bus']) AND (int)$sdata['bus'] > 0 AND isset($buses[(int)$sdata['bus']])) {
									$tr[] = $buses[(int)$sdata['bus']]['tag'].' ('.$buses[(int)$sdata['bus']]['company'].')';
								} else {
									$tr[] = 'NICHT ZUGEORDNET';
								}
							}
							$n++;
						} else {
							$nat[$sid] = $sdata;
						}
					}
					
					if(count($para['data']) > 33) {
						$GLOBALS['pdfOptions']['pagecount'] = true;
					} else {
						$GLOBALS['pdfOptions']['pagecount'] = false;
					}
					
					
					$pdf = new PVSPDF();
					$pdf->SetMargins(15,60);
					$pdf->SetTitle(utf8_decode('Angebots- und Buszuordnung Lehrkräfte'));
					$pdf->AddPage();
					$pdf->SetFont('Arial','B',22);
					$pdf->Ln(2);
					$pdf->Cell(0,10,utf8_decode('Angebots- und Buszuordnung Lehrkräfte'));
					$pdf->SetFont('Arial','B',12);
					$pdf->Ln(12);
					
					$pdf->Table($para['header'],$para['data'],$para['format']);
					
					if (count($nat)) {
						$pdf->SetFont('Arial','B',15);
						$pdf->Ln(10);
						$pdf->Cell(0,0,utf8_decode('Folgende Lehrkräfte sind für das Event nicht verfügbar:'),0,1,'L');
						$pdf->SetX(20);
						$pdf->Ln(5);
						
						$pdf->SetFont('Arial','',10);
						$add = '';
						$str = '';
						foreach ($nat as $sid => $sdata) {
							$str .= utf8_decode($add.id_to_address($sdata));
							$add = ', ';
						}
						$pdf->MultiCell(0,5,$str);
					}
					
					//*/
				} break;
			case 'pdf_offerstudentslist': if (isset($offers[OFFER])) {
					$para['header'] = 1;
					$para['data'] = Array();
					$para['data'][] = Array('#','Klasse',Array('content'=>'Name, Vorname','align'=>'C'),Array('align'=>'C','content'=>'Bus'), Array('content'=>'','fillcolor'=>'1'),'#','Klasse',Array('content'=>'Name, Vorname','align'=>'C'),Array('align'=>'C','content'=>'Bus'));
					
					$para['format'] = Array('forcepagebreak'=>33,'align_col_3'=>'L','align_col_8'=>'L','repeat_header'=>true,'color2ndrow'=>Array(221,221,255),'fontsize'=>9,'rowheight'=>6.1,'table-align'=>'C');
					$para['format']['colwidth'] = Array(7,16,40,27,1,7,16,40,27);
					
					$tstudents = Array();
					
					foreach ($teachers as $tid => $tdata) {
						if (isset($tdata['offer']) AND (int)$tdata['offer'] == OFFER AND (!isset($tdata['available']) OR (bool)$tdata['available'] )) {
							$tstudents[] = $tdata;
						}
					}
					
					foreach ($classes as $cln => $cldata) {
						foreach ($cldata as $tid => $tdata) {
							if (isset($tdata['offer']) AND (int)$tdata['offer'] == OFFER) {
								$tstudents[] = $tdata;
							}
						}
					}
					
					$teiler = 32;		
					$columns = Array();
					
					
					for ($i = 0; $i < count($tstudents); $i++) {
						if (bcmod($i,$teiler) == 0) { $columns[count($columns)] = Array(); }
						$columns[count($columns)-1][] = $tstudents[$i];
					}
					
					if (bcmod(count($columns),2) == 1) {
						$columns[count($columns)] = Array();
					}
					
					$maxrows = Array();
					
					foreach ($columns as $key => $data) {
						$mrk = $key - bcmod($key,2);
						if (!isset($maxrows[$mrk])) $maxrows[$mrk] = 0;
						if (count($data) > $maxrows[$mrk]) $maxrows[$mrk] = count($data); 
					}
					
					foreach ($columns as $key => $data) {
						$mrk = $key - bcmod($key,2);
						$ct = count($data);
						for ($i = $ct; $i < $maxrows[$mrk]; $i++) {
							$columns[$key][$i] = Array('type'=>'empty');
						}
					}
						
					$k = 1;	
						
					for ($l = 0; $l < count($columns); $l = $l+2 ) {
						for ($i = 0; $i < count($columns[$l]); $i++) {
							for ($j = 0; $j < 2; $j++) {
								if ($j == 0) {
									$tr =& $para['data'][];
									$tr = Array();
								} else {
									$tr[] = Array('content'=>'','fillcolor'=>'1');
								}
								
								$sdata = $columns[$l+$j][$i];
							
								if (!isset($sdata['type']) OR $sdata['type'] == 'student') {
									$tr[] = 1+$i+$teiler*($l+$j); 
									$tr[] = isset($sdata['class'])?$sdata['class']:'';
									$tr[] = (isset($sdata['surname'])?$sdata['surname']:'').', '.(isset($sdata['forename'])?$sdata['forename']:'');
									$tr[] = (isset($sdata['bus']) AND isset($buses[$sdata['bus']]) AND $buses[$sdata['bus']] > 0)?$buses[$sdata['bus']]['tag']:'N / A';
									$k++;
								} elseif ($sdata['type'] == 'teacher') {
									$tr[] = 1+$i+$teiler*($l+$j); 
									if (isset($sdata['id']) AND isset($offers[OFFER]['teacher']) AND $sdata['id'] == $offers[OFFER]['teacher']) {
										$tr[] = 'Verantw.';
									} else {
										$tr[] = 'Lehrkraft';
									}
									$tr[] = id_to_address($sdata);
									$tr[] = (isset($sdata['bus']) AND isset($buses[$sdata['bus']]) AND $buses[$sdata['bus']] > 0)?$buses[$sdata['bus']]['tag']:'N / A';
									$k++;
								} else {
									$tr[] = Array('content'=>'','colspan'=>4,'fill'=>false,'drawborder'=>false);
								}
								
							}
						}
					}
					
					if(count($para['data']) > 33) {
						$GLOBALS['pdfOptions']['pagecount'] = true;
					} else {
						$GLOBALS['pdfOptions']['pagecount'] = false;
					}
					
					
					$pdf = new PVSPDF();
					$pdf->SetMargins(15,60);
					$pdf->SetTitle(utf8_decode('Teilnehmer Angebot '.$offers[OFFER]['name']));
					$pdf->AddPage();
					$pdf->SetFont('Arial','B',22);
					$pdf->Ln(2);
					$pdf->Cell(0,10,utf8_decode('Teilnehmerliste '.$offers[OFFER]['name']));
					$pdf->SetFont('Arial','B',12);
					$pdf->Ln(12);
					
					$pdf->Table($para['header'],$para['data'],$para['format']);
					
				} else {
					$GLOBALS['hints'][] = '<span class="b cr">Fehlschlag - Das gewählte Angebot existiert nicht (mehr).</span>';
					$portalbool = true;
					$html_override = true;
				} break;
			case 'pdf_offerstudentslist_all': {
				
					$pdf = new PVSPDF();
					$pdf->SetMargins(15,60);
					$pdf->SetTitle(utf8_decode('Teilnehmerlisten aller Angebote'));
				
					foreach ($offers as $oid => $odata) {
						$para['header'] = 1;
						$para['data'] = Array();
						$para['data'][] = Array('#','Klasse',Array('content'=>'Name, Vorname','align'=>'C'),Array('align'=>'C','content'=>'Bus'), Array('content'=>'','fillcolor'=>'1'),'#','Klasse',Array('content'=>'Name, Vorname','align'=>'C'),Array('align'=>'C','content'=>'Bus'));
						
						$para['format'] = Array('forcepagebreak'=>33,'align_col_3'=>'L','align_col_8'=>'L','repeat_header'=>true,'color2ndrow'=>Array(221,221,255),'fontsize'=>9,'rowheight'=>6.1,'table-align'=>'C');
						$para['format']['colwidth'] = Array(7,16,40,27,1,7,16,40,27);
						
						$tstudents = Array();
						
						foreach ($teachers as $tid => $tdata) {
							if (isset($tdata['offer']) AND (int)$tdata['offer'] == $oid AND (!isset($tdata['available']) OR (bool)$tdata['available'] )) {
								$tstudents[] = $tdata;
							}
						}
						
						foreach ($classes as $cln => $cldata) {
							foreach ($cldata as $tid => $tdata) {
								if (isset($tdata['offer']) AND (int)$tdata['offer'] == $oid) {
									$tstudents[] = $tdata;
								}
							}
						}
						
						$teiler = 32;		
						$columns = Array();
						
						
						for ($i = 0; $i < count($tstudents); $i++) {
							if (bcmod($i,$teiler) == 0) { $columns[count($columns)] = Array(); }
							$columns[count($columns)-1][] = $tstudents[$i];
						}
						
						if (bcmod(count($columns),2) == 1) {
							$columns[count($columns)] = Array();
						}
						
						$maxrows = Array();
						
						foreach ($columns as $key => $data) {
							$mrk = $key - bcmod($key,2);
							if (!isset($maxrows[$mrk])) $maxrows[$mrk] = 0;
							if (count($data) > $maxrows[$mrk]) $maxrows[$mrk] = count($data); 
						}
						
						foreach ($columns as $key => $data) {
							$mrk = $key - bcmod($key,2);
							$ct = count($data);
							for ($i = $ct; $i < $maxrows[$mrk]; $i++) {
								$columns[$key][$i] = Array('type'=>'empty');
							}
						}
							
						$k = 1;	
							
						for ($l = 0; $l < count($columns); $l = $l+2 ) {
							for ($i = 0; $i < count($columns[$l]); $i++) {
								for ($j = 0; $j < 2; $j++) {
									if ($j == 0) {
										$tr =& $para['data'][];
										$tr = Array();
									} else {
										$tr[] = Array('content'=>'','fillcolor'=>'1');
									}
									
									$sdata = $columns[$l+$j][$i];
								
									if (!isset($sdata['type']) OR $sdata['type'] == 'student') {
										$tr[] = 1+$i+$teiler*($l+$j); 
										$tr[] = isset($sdata['class'])?$sdata['class']:'';
										$tr[] = (isset($sdata['surname'])?$sdata['surname']:'').', '.(isset($sdata['forename'])?$sdata['forename']:'');
										$tr[] = (isset($sdata['bus']) AND isset($buses[$sdata['bus']]) AND $buses[$sdata['bus']] > 0)?$buses[$sdata['bus']]['tag']:'N / A';
										$k++;
									} elseif ($sdata['type'] == 'teacher') {
										$tr[] = 1+$i+$teiler*($l+$j); 
										if (isset($sdata['id']) AND isset($offers[$oid]['teacher']) AND $sdata['id'] == $offers[$oid]['teacher']) {
											$tr[] = 'Verantw.';
										} else {
											$tr[] = 'Lehrkraft';
										}
										
										$tr[] = id_to_address($sdata);
										$tr[] = (isset($sdata['bus']) AND isset($buses[$sdata['bus']]) AND $buses[$sdata['bus']] > 0)?$buses[$sdata['bus']]['tag']:'N / A';
										$k++;
									} else {
										$tr[] = Array('content'=>'','colspan'=>4,'fill'=>false,'drawborder'=>false);
									}
									
								}
							}
						}
						
						
						$pdf->AddPage();
						$GLOBALS['pdfOptions']['pagecount'] = '{nb-offer-'.$oid.'}';		
						$pdf->SetFont('Arial','B',22);
						$pdf->Ln(2);
						$pdf->Cell(0,10,utf8_decode('Teilnehmerliste '.$offers[$oid]['name']));
						$pdf->SetFont('Arial','B',12);
						$pdf->Ln(12);
						
						$pdf->Table($para['header'],$para['data'],$para['format']);
					}
					
					$pdf->InFooter = true;
					$pdf->Footer();
					$pdf->InFooter = false;
					$GLOBALS['pdfOptions']['suppressfooter'] = true;
					$nb = $pdf->page;
					$nbpagesoid = Array();
					$oidcounter = Array();
					$pagenroid = Array();
					
					for ($i = 1; $i <= $nb; $i++) {
						$treffer = Array();
						preg_match('/\{nb-offer-(\d+)\}/',$pdf->pages[$i],$treffer);
						if (isset($treffer[1]) AND is_numeric($treffer[1])) {
							if (!isset($nbpagesoid[$treffer[1]])) $nbpagesoid[$treffer[1]] = 0;
							if (!isset($oidcounter[$treffer[1]])) $oidcounter[$treffer[1]] = 1;
							$nbpagesoid[$treffer[1]]++;
							$pagenroid[$i] = $treffer[1];
						}
					}
					
					for ($i = 1; $i <= $nb; $i++) {
						if (isset($pagenroid[$i])) {
							if ($nbpagesoid[$pagenroid[$i]] > 1) {
								$pdf->pages[$i] = str_replace('{nb-offer-'.$pagenroid[$i].'}','Seite '.$oidcounter[$pagenroid[$i]].' von '.$nbpagesoid[$pagenroid[$i]],$pdf->pages[$i]);
								$oidcounter[$pagenroid[$i]]++;
							} else {
								$pdf->pages[$i] = str_replace('{nb-offer-'.$pagenroid[$i].'}','',$pdf->pages[$i]);
							}
						}
					}
					
				} break;
			case 'pdf_offerbuslist': if (isset($offers[OFFER])) {
				
					$pdf = new PVSPDF();
					$pdf->SetMargins(15,60);
					$pdf->SetTitle(utf8_decode('Buslisten Angebot '.$offers[OFFER]['name']));
				
					$oid = OFFER;
				
					foreach ($buses as $bid => $bdata) {
						
						if (isset($bdata['carpool']) AND isset($carpools[$bdata['carpool']]) AND preg_match('/\b'.$oid.'\b/',$carpools[$bdata['carpool']]['offers'])) {
							$para['header'] = 1;
							$para['data'] = Array();
							$para['data'][] = Array('#','Klasse',Array('content'=>'Name, Vorname','align'=>'C'),Array('align'=>'C','content'=>'Anwesenheit'), Array('content'=>'','fillcolor'=>'1'),'#','Klasse',Array('content'=>'Name, Vorname','align'=>'C'),Array('align'=>'C','content'=>'Anwesenheit'));
							
							$para['format'] = Array('forcepagebreak'=>33,'align_col_3'=>'L','align_col_8'=>'L','repeat_header'=>true,'color2ndrow'=>Array(221,221,255),'fontsize'=>9,'rowheight'=>6.1,'table-align'=>'C');
							$para['format']['colwidth'] = Array(7,16,40,27,1,7,16,40,27);
							
							$tstudents = Array();
							
							foreach ($teachers as $tid => $tdata) {
								if (isset($tdata['bus']) AND $tdata['bus'] == $bid AND (!isset($tdata['available']) OR (bool)$tdata['available'] )) {
									$tstudents[] = $tdata;
								}
							}
							
							foreach ($classes as $cln => $cldata) {
								foreach ($cldata as $tid => $tdata) {
									if (isset($tdata['bus']) AND (int)$tdata['bus'] == $bid) {
										$tstudents[] = $tdata;
									}
								}
							}
							
							$teiler = 32;		
							$columns = Array();
							
							
							for ($i = 0; $i < count($tstudents); $i++) {
								if (bcmod($i,$teiler) == 0) { $columns[count($columns)] = Array(); }
								$columns[count($columns)-1][] = $tstudents[$i];
							}
							
							if (bcmod(count($columns),2) == 1) {
								$columns[count($columns)] = Array();
							}
							
							$maxrows = Array();
							
							foreach ($columns as $key => $data) {
								$mrk = $key - bcmod($key,2);
								if (!isset($maxrows[$mrk])) $maxrows[$mrk] = 0;
								if (count($data) > $maxrows[$mrk]) $maxrows[$mrk] = count($data); 
							}
							
							foreach ($columns as $key => $data) {
								$mrk = $key - bcmod($key,2);
								$ct = count($data);
								for ($i = $ct; $i < $maxrows[$mrk]; $i++) {
									$columns[$key][$i] = Array('type'=>'empty');
								}
							}
								
							$k = 1;	
								
							for ($l = 0; $l < count($columns); $l = $l+2 ) {
								for ($i = 0; $i < count($columns[$l]); $i++) {
									for ($j = 0; $j < 2; $j++) {
										if ($j == 0) {
											$tr =& $para['data'][];
											$tr = Array();
										} else {
											$tr[] = Array('content'=>'','fillcolor'=>'1');
										}
										
										$sdata = $columns[$l+$j][$i];
									
										if (!isset($sdata['type']) OR $sdata['type'] == 'student') {
											$tr[] = 1+$i+$teiler*($l+$j); 
											$tr[] = isset($sdata['class'])?$sdata['class']:'';
											$tr[] = (isset($sdata['surname'])?$sdata['surname']:'').', '.(isset($sdata['forename'])?$sdata['forename']:'');
											$tr[] = '[__]    [__]    [__]';
											$k++;
										} elseif ($sdata['type'] == 'teacher') {
											$tr[] = 1+$i+$teiler*($l+$j); 
											$tr[] = 'Lehrkraft'; 
											$tr[] = id_to_address($sdata);
											$tr[] = '-----';
											$k++;
										} else {
											$tr[] = Array('content'=>'','colspan'=>4,'fill'=>false,'drawborder'=>false);
										}
										
									}
								}
							}
							
							
							$pdf->AddPage();
							$GLOBALS['pdfOptions']['pagecount'] = '{nb-'.$bid.'}';		
							$pdf->SetFont('Arial','B',22);
							$pdf->Ln(2);
							$pdf->Cell(0,10,utf8_decode('Busliste '.$bdata['tag'].' ('.$bdata['company'].')'));
							$pdf->Ln(6);
							$pdf->SetFont('Arial','',9);
							$pdf->Cell(0,10,utf8_decode('Fahrgemeinschaft "'.$carpools[$bdata['carpool']]['name'].'"'));
							$pdf->Cell(0,10,utf8_decode('verfügbare Plätze: '.$bdata['capacity']),0,0,'R');
							$pdf->SetFont('Arial','B',12);
							$pdf->Ln(8);
							
							$pdf->Table($para['header'],$para['data'],$para['format']);
						}
					}
					
					$pdf->InFooter = true;
					$pdf->Footer();
					$pdf->InFooter = false;
					$GLOBALS['pdfOptions']['suppressfooter'] = true;
					$nb = $pdf->page;
					$nbpagesoid = Array();
					$oidcounter = Array();
					$pagenroid = Array();
					
					for ($i = 1; $i <= $nb; $i++) {
						$treffer = Array();
						preg_match('/\{nb-(\d+)\}/',$pdf->pages[$i],$treffer);
						if (isset($treffer[1]) AND is_numeric($treffer[1])) {
							if (!isset($nbpagesoid[$treffer[1]])) $nbpagesoid[$treffer[1]] = 0;
							if (!isset($oidcounter[$treffer[1]])) $oidcounter[$treffer[1]] = 1;
							$nbpagesoid[$treffer[1]]++;
							$pagenroid[$i] = $treffer[1];
						}
					}
					
					for ($i = 1; $i <= $nb; $i++) {
						if (isset($pagenroid[$i])) {
							if ($nbpagesoid[$pagenroid[$i]] > 1) {
								$pdf->pages[$i] = str_replace('{nb-'.$pagenroid[$i].'}','Seite '.$oidcounter[$pagenroid[$i]].' von '.$nbpagesoid[$pagenroid[$i]],$pdf->pages[$i]);
								$oidcounter[$pagenroid[$i]]++;
							} else {
								$pdf->pages[$i] = str_replace('{nb-'.$pagenroid[$i].'}','',$pdf->pages[$i]);
							}
						}
					}
					
				} else {
					$GLOBALS['hints'][] = '<span class="b cr">Fehlschlag - Das gewählte Angebot existiert nicht (mehr).</span>';
					$portalbool = true;
					$html_override = true;
				} break;
			case 'pdf_offerbuslist_all': {
				
					$pdf = new PVSPDF();
					$pdf->SetMargins(15,60);
					$pdf->SetTitle(utf8_decode('Buslisten aller Angebote'));
						
					foreach ($buses as $bid => $bdata) {
						
						if (isset($bdata['carpool']) AND isset($carpools[$bdata['carpool']])) {
							$para['header'] = 1;
							$para['data'] = Array();
							$para['data'][] = Array('#','Klasse',Array('content'=>'Name, Vorname','align'=>'C'),Array('align'=>'C','content'=>'Anwesenheit'), Array('content'=>'','fillcolor'=>'1'),'#','Klasse',Array('content'=>'Name, Vorname','align'=>'C'),Array('align'=>'C','content'=>'Anwesenheit'));
							
							$para['format'] = Array('forcepagebreak'=>33,'align_col_3'=>'L','align_col_8'=>'L','repeat_header'=>true,'color2ndrow'=>Array(221,221,255),'fontsize'=>9,'rowheight'=>6.1,'table-align'=>'C');
							$para['format']['colwidth'] = Array(7,16,40,27,1,7,16,40,27);
							
							$tstudents = Array();
							
							foreach ($teachers as $tid => $tdata) {
								if (isset($tdata['bus']) AND $tdata['bus'] == $bid AND (!isset($tdata['available']) OR (bool)$tdata['available'] )) {
									$tstudents[] = $tdata;
								}
							}
							
							foreach ($classes as $cln => $cldata) {
								foreach ($cldata as $tid => $tdata) {
									if (isset($tdata['bus']) AND (int)$tdata['bus'] == $bid) {
										$tstudents[] = $tdata;
									}
								}
							}
							
							$teiler = 32;		
							$columns = Array();
							
							
							for ($i = 0; $i < count($tstudents); $i++) {
								if (bcmod($i,$teiler) == 0) { $columns[count($columns)] = Array(); }
								$columns[count($columns)-1][] = $tstudents[$i];
							}
							
							if (bcmod(count($columns),2) == 1) {
								$columns[count($columns)] = Array();
							}
							
							$maxrows = Array();
							
							foreach ($columns as $key => $data) {
								$mrk = $key - bcmod($key,2);
								if (!isset($maxrows[$mrk])) $maxrows[$mrk] = 0;
								if (count($data) > $maxrows[$mrk]) $maxrows[$mrk] = count($data); 
							}
							
							foreach ($columns as $key => $data) {
								$mrk = $key - bcmod($key,2);
								$ct = count($data);
								for ($i = $ct; $i < $maxrows[$mrk]; $i++) {
									$columns[$key][$i] = Array('type'=>'empty');
								}
							}
								
							$k = 1;	
								
							for ($l = 0; $l < count($columns); $l = $l+2 ) {
								for ($i = 0; $i < count($columns[$l]); $i++) {
									for ($j = 0; $j < 2; $j++) {
										if ($j == 0) {
											$tr =& $para['data'][];
											$tr = Array();
										} else {
											$tr[] = Array('content'=>'','fillcolor'=>'1');
										}
										
										$sdata = $columns[$l+$j][$i];
									
										if (!isset($sdata['type']) OR $sdata['type'] == 'student') {
											$tr[] = 1+$i+$teiler*($l+$j); 
											$tr[] = isset($sdata['class'])?$sdata['class']:'';
											$tr[] = (isset($sdata['surname'])?$sdata['surname']:'').', '.(isset($sdata['forename'])?$sdata['forename']:'');
											$tr[] = '[__]    [__]    [__]';
											$k++;
										} elseif ($sdata['type'] == 'teacher') {
											$tr[] = 1+$i+$teiler*($l+$j); 
											$tr[] = 'Lehrkraft'; 
											$tr[] = id_to_address($sdata);
											$tr[] = '-----';
											$k++;
										} else {
											$tr[] = Array('content'=>'','colspan'=>4,'fill'=>false,'drawborder'=>false);
										}
										
									}
								}
							}
							
							
							$pdf->AddPage();
							$GLOBALS['pdfOptions']['pagecount'] = '{nb-'.$bid.'}';		
							$pdf->SetFont('Arial','B',22);
							$pdf->Ln(2);
							$pdf->Cell(0,10,utf8_decode('Busliste '.$bdata['tag'].' ('.$bdata['company'].')'));
							$pdf->Ln(6);
							$pdf->SetFont('Arial','',9);
							$pdf->Cell(0,10,utf8_decode('Fahrgemeinschaft "'.$carpools[$bdata['carpool']]['name'].'"'));
							$pdf->SetFont('Arial','B',12);
							$pdf->Ln(8);
							
							$pdf->Table($para['header'],$para['data'],$para['format']);
						}
					}
					
					$pdf->InFooter = true;
					$pdf->Footer();
					$pdf->InFooter = false;
					$GLOBALS['pdfOptions']['suppressfooter'] = true;
					$nb = $pdf->page;
					$nbpagesoid = Array();
					$oidcounter = Array();
					$pagenroid = Array();
					
					for ($i = 1; $i <= $nb; $i++) {
						$treffer = Array();
						preg_match('/\{nb-(\d+)\}/',$pdf->pages[$i],$treffer);
						if (isset($treffer[1]) AND is_numeric($treffer[1])) {
							if (!isset($nbpagesoid[$treffer[1]])) $nbpagesoid[$treffer[1]] = 0;
							if (!isset($oidcounter[$treffer[1]])) $oidcounter[$treffer[1]] = 1;
							$nbpagesoid[$treffer[1]]++;
							$pagenroid[$i] = $treffer[1];
						}
					}
					
					for ($i = 1; $i <= $nb; $i++) {
						if (isset($pagenroid[$i])) {
							if ($nbpagesoid[$pagenroid[$i]] > 1) {
								$pdf->pages[$i] = str_replace('{nb-'.$pagenroid[$i].'}','Seite '.$oidcounter[$pagenroid[$i]].' von '.$nbpagesoid[$pagenroid[$i]],$pdf->pages[$i]);
								$oidcounter[$pagenroid[$i]]++;
							} else {
								$pdf->pages[$i] = str_replace('{nb-'.$pagenroid[$i].'}','',$pdf->pages[$i]);
							}
						}
					}
					
				} break;
			case 'pdf_cashback': {
			
					$cbstudents = Array();
					$offerlist = Array();
					
					foreach ($students as $sid => $sdata) {

						
						if (isset($sdata['offer']) AND isset($offers[$sdata['offer']]) AND (int)$sdata['offer'] > 0) {
							$oid = $sdata['offer'];
						
							if (!isset($offerlist[$oid])) $offerlist[$oid] = Array();
							if (!isset($offerlist[$oid]['tn'])) $offerlist[$oid]['tn'] = 0;
							if (!isset($offerlist[$oid]['1w'])) $offerlist[$oid]['1w'] = 0;
							if (!isset($offerlist[$oid]['e'])) $offerlist[$oid]['e'] = 0;
							if (!isset($offerlist[$oid]['ne'])) $offerlist[$oid]['ne'] = 0;
							if (!isset($offerlist[$oid]['tncb'])) $offerlist[$oid]['tncb'] = 0;
							if (!isset($offerlist[$oid]['1wcb'])) $offerlist[$oid]['1wcb'] = 0;
							if (!isset($offerlist[$oid]['ecb'])) $offerlist[$oid]['ecb'] = 0;
							
							if (isset($sdata['cancelled'])) {
								if ($sdata['cancelled'] == 4) $offerlist[$oid]['ne']++;
								if ($sdata['cancelled'] == 3) $offerlist[$oid]['e']++;
								if ($sdata['cancelled'] == 2) $offerlist[$oid]['1w']++;
								if ($sdata['cancelled'] == 1 OR $sdata['cancelled'] == 0) $offerlist[$oid]['tn']++;
							} else {
								$offerlist[$oid]['tn']++;
							}
						}
					}
					
					foreach ($offers as $oid => $odata) {
						
						if (!isset($offerlist[$oid])) $offerlist[$oid] = Array();
						if (!isset($offerlist[$oid]['tn'])) $offerlist[$oid]['tn'] = 0;
						if (!isset($offerlist[$oid]['1w'])) $offerlist[$oid]['1w'] = 0;
						if (!isset($offerlist[$oid]['e'])) $offerlist[$oid]['e'] = 0;
						if (!isset($offerlist[$oid]['ne'])) $offerlist[$oid]['ne'] = 0;
						if (!isset($offerlist[$oid]['tncb'])) $offerlist[$oid]['tncb'] = 0;
						if (!isset($offerlist[$oid]['1wcb'])) $offerlist[$oid]['1wcb'] = 0;
						if (!isset($offerlist[$oid]['ecb'])) $offerlist[$oid]['ecb'] = 0;
						
						$offerlist[$oid]['precosts'] = (float)$odata['precosts'];
						
						$offerlist[$oid]['sum'] = (float)$odata['left'];
						$left = $offerlist[$oid]['sum'];
						$tn = $offerlist[$oid]['tn'];
						$w = $offerlist[$oid]['1w'];
						$wcb = $odata['refundall'];
						$e = $offerlist[$oid]['e'];
						$ecb = $odata['refund'];
						
						if ($left > 0.0) {
						
							if ($left <= ($w*$wcb + $e*$ecb)) {
							
								$p = $left/($w*$wcb + $e*$ecb);
								
								$offerlist[$oid]['1wcb'] = (float)(floor($wcb*$p*100)/100);
								$offerlist[$oid]['ecb'] = (float)(floor($ecb*$p*100)/100);
								$offerlist[$oid]['tncb'] = 0;						
							
							} else {
								$offerlist[$oid]['1wcb'] = $wcb;
								$offerlist[$oid]['ecb'] = $ecb;
								$left = $left - ($w*$wcb + $e*$ecb);
								$offerlist[$oid]['tncb'] = (float)(floor(($left / $tn)*100)/100);
							}
						
						}
						
					}
			
					foreach ($classes as $classfilter => $classdata) {
						if (!isset($cbstudents[$classfilter])) $cbstudents[$classfilter] = Array();
					
						foreach ($classdata as $sid => $sdata) {
						
							$cb1 = false; $cb2 = false; $cb3 = false; $cb4 = false; $cbsum = 0.0;
						
							if (isset($sdata['cancelled'])) {	
								switch ($sdata['cancelled']) {
									case 1:
										$cb4 = true;
										break;
									case 2:
										$cb2 = true;
										break;
									case 3:
										$cb3 = true;
										break;
								}
							
							}
							
							if (!isset($sdata['paid'])) $sdata['paid'] = 0.0;
							
							$cbsum = $sdata['paid'];
							
							if (isset($sdata['offer']) AND (int)$sdata['offer'] > 0 AND isset($offerlist[$sdata['offer']])) {
							
								$old = $offerlist[$sdata['offer']];
								
								if ($old['tncb'] <= 0) $cb4 = false;
									
								if (($sdata['paid']-$old['precosts']) != 0) $cb1 = true; 
							
								$cbsum += ((int)$cb2 * $old['1wcb']);
								$cbsum += ((int)$cb3 * $old['ecb']);
								$cbsum += ((int)$cb4 * $old['tncb']);
								$cbsum = $cbsum - $old['precosts'];
								
							
							} elseif ($sdata['paid'] > 0) {
								$cb1 = true;
							}
							
							if ($cbsum != 0) {
								$cbstudents[$classfilter][$sid] = $sdata;
								$cbstudents[$classfilter][$sid]['cb1'] = $cb1;
								$cbstudents[$classfilter][$sid]['cb2'] = $cb2;
								$cbstudents[$classfilter][$sid]['cb3'] = $cb3;
								$cbstudents[$classfilter][$sid]['cb4'] = $cb4;
								$cbstudents[$classfilter][$sid]['cbsum'] = $cbsum;
							}
						}
					}
			
					$pdf = new PVSPDF();
					$pdf->SetMargins(15,60);
					$pdf->SetTitle(utf8_decode('Rückzahlungslisten für alle Klassen'));
					
					$nacl = Array();
					
					foreach ($classes as $classfilter => $classdata) {
						if (count($cbstudents[$classfilter])) {
							$sum = 0;
							$pdf->AddPage();
							$GLOBALS['pdfOptions']['pagecount'] = '{nb-class-'.$classfilter.'}';
							$pdf->SetFont('Arial','B',22);
							$pdf->Ln(2);
							$pdf->Cell(0,10,utf8_decode('Rückzahlungsliste Klasse '.$classfilter));
							$pdf->SetFont('Arial','B',12);
							$pdf->Ln(1);
							if (isset($teachers[get_class_teacher($classfilter,$teachers)])) {	
								$pdf->Cell(0,10,utf8_decode(id_to_address($teachers[get_class_teacher($classfilter,$teachers)])),0,0,'R');
							} else {
								$pdf->Cell(0,10,utf8_decode("(Kein Lehrer)"),0,0,'R');
							}
							$pdf->Ln(6);
							$pdf->SetFont('Arial','',7);
							$pdf->Cell(0,10,utf8_decode("Rückzahlungsgründe: 1) zu viel bezahlt, 2) lange vorher entschuldigt, 3) entschuldigt nicht teilgenommen, 4) allgemeine Rückzahlung bei diesem Angebot"),0,0,'L');
							$pdf->Ln(9);
							
							$para['header'] = 1;
							$para['data'] = Array();
							$para['data'][] = Array('#',Array('content'=>'Name, Vorname','align'=>'C'),'Angebot','1)','2)','3)','4)','Betrag','erhalten');
							$para['format'] = Array('align_col_2'=>'L','repeat_header'=>true,'color2ndrow'=>Array(221,221,255),'fontsize'=>9,'rowheight'=>6.1,'table-align'=>'C');
							$para['format']['colwidth'] = Array(7,50,50,6,6,6,6,24,26);
							
							$tstudents = $cbstudents[$classfilter];
							$ct = count($tstudents);
												
							$n = 1;
							foreach ($tstudents as $sid => $sdata) {
								$tr =& $para['data'][];
								$tr = Array();
								$tr[] = $n; 
								$tr[] = (isset($sdata['surname'])?$sdata['surname']:'').', '.(isset($sdata['forename'])?$sdata['forename']:'');
								if ($tr[count($tr)-1] == ', ') $tr[count($tr)-1] = '';
								
								if (!isset($sdata['offer']) OR (int)$sdata['offer'] <= 0 OR !isset($offers[(int)$sdata['offer']])) {
									$tr[] = '';
								} else {
									$tr[] = $offers[(int)$sdata['offer']]['name'];
								}
								
								for ($k = 1; $k <= 4; $k++) {
									if ($sdata['cb'.$k]) {
										$tr[] = Array('content'=>'X','fontstyle'=>'B');
									} else {
										$tr[] = '';
									}	
								}
								
								$tr[] = substr(float_to_eur($sdata['cbsum']),0,-7).' EUR';
								
								$sum += $sdata['cbsum'];
								
								$tr[] = '';
								
								$n++;
							}
							
							$para['data'][] = Array(Array('content'=>'Summe','colspan'=>7,'align'=>'L'),substr(float_to_eur($sum),0,-7).' EUR','');
							
							$pdf->Table($para['header'],$para['data'],$para['format']);
						} else {
						
							$nacl[] = $classfilter;
							
						}
					}
					
					if (count($nacl)) {
					
						$pdf->AddPage();
						$GLOBALS['pdfOptions']['pagecount'] = false;
						$pdf->SetFont('Arial','B',22);
						$pdf->Ln(2);
						$pdf->Cell(0,10,utf8_decode('Klassen ohne Rückzahlung'));
						$pdf->SetFont('Arial','B',14);
						$pdf->Ln(12);	
						$pdf->Cell(0,10,utf8_decode('In folgenden Klassen ist keine Rückzahlung nötig:'),0,0,'L');
						
						$pdf->Ln(12);
						$pdf->SetFont('Arial','',12);
						$naclstr = '';
					
						foreach ($nacl as $classfilter) {
						
							if ($naclstr != '') $naclstr .= ', ';
							
							$naclstr .= $classfilter;
						
						}
						
						$pdf->MultiCell(0,12*1.5*MMPPT,$naclstr);
					
					}
					
					$pdf->InFooter = true;
					$pdf->Footer();
					$pdf->InFooter = false;
					$GLOBALS['pdfOptions']['suppressfooter'] = true;
					$nb = $pdf->page;
					$nbpagesoid = Array();
					$oidcounter = Array();
					$pagenroid = Array();
					
					for ($i = 1; $i <= $nb; $i++) {
						$treffer = Array();
						preg_match('/\{nb-class-([\w|-]+)\}/',$pdf->pages[$i],$treffer);
						if (isset($treffer[1]) AND is_string($treffer[1])) {
							if (!isset($nbpagesoid[$treffer[1]])) $nbpagesoid[$treffer[1]] = 0;
							if (!isset($oidcounter[$treffer[1]])) $oidcounter[$treffer[1]] = 1;
							$nbpagesoid[$treffer[1]]++;
							$pagenroid[$i] = $treffer[1];
						}
					}
					
					for ($i = 1; $i <= $nb; $i++) {
						if (isset($pagenroid[$i])) {
							if ($nbpagesoid[$pagenroid[$i]] > 1) {
								$pdf->pages[$i] = str_replace('{nb-class-'.$pagenroid[$i].'}','Seite '.$oidcounter[$pagenroid[$i]].' von '.$nbpagesoid[$pagenroid[$i]],$pdf->pages[$i]);
								$oidcounter[$pagenroid[$i]]++;
							} else {
								$pdf->pages[$i] = str_replace('{nb-class-'.$pagenroid[$i].'}','',$pdf->pages[$i]);
							}
						}
					}
					
					//*/
				} break;
			case 'pdf_statistics': {
				
					$pdf = new PVSPDF();
					$pdf->SetMargins(15,60);
					$pdf->SetTitle(utf8_decode('Gesamtstatistik zum Event '.$info['info']['name']));

					
					$GLOBALS['pdfOptions']['headersubt'] = ' - Statistik';
					$GLOBALS['pdfOptions']['pagecount'] = true;
					$cpb = Array();
					
					foreach ($buses as $bid => $busdata) {
						if (isset($busdata['carpool']) AND isset($carpools[$busdata['carpool']])) {
							if (!isset($cpb[$busdata['carpool']])) $cpb[$busdata['carpool']] = Array();
							$cpb[$busdata['carpool']][$bid] = $busdata;
						}
					}
					
					$bdata = Array('teachers'=>Array(),'students'=>Array());
					
					foreach ($teachers as $tid => $tdata) {
						if (isset($tdata['offer']) AND isset($offers[$tdata['offer']]) ) {
							if (!isset($bdata['teachers']['offer'])) $bdata['teachers']['offer'] = Array();
							if (!isset($bdata['teachers']['offer'][$tdata['offer']])) $bdata['teachers']['offer'][$tdata['offer']] = Array();
							$bdata['teachers']['offer'][$tdata['offer']][$tid] = $tdata;
						}
						for ($i = 0; $i < $info['props']['max_wishes']; $i++) {
							if (isset($tdata['wish_'.$i]) AND isset($offers[$tdata['wish_'.$i]])) {
								if (!isset($bdata['teachers']['wish_'.$i])) $bdata['teachers']['wish_'.$i] = Array();
								if (!isset($bdata['teachers']['wish_'.$i][$tdata['wish_'.$i]])) $bdata['teachers']['wish_'.$i][$tdata['wish_'.$i]] = Array();
								$bdata['teachers']['wish_'.$i][$tdata['wish_'.$i]][$tid] = $tdata;
							}
						}
					}
					
					foreach ($students as $tid => $tdata) {
						if (isset($tdata['offer']) AND isset($offers[$tdata['offer']]) ) {
							if (!isset($bdata['students']['offer'])) $bdata['students']['offer'] = Array();
							if (!isset($bdata['students']['offer'][$tdata['offer']])) $bdata['students']['offer'][$tdata['offer']] = Array();
							$bdata['students']['offer'][$tdata['offer']][$tid] = $tdata;
							if (isset($tdata['class']) ) {
								if (!isset($bdata[$tdata['class']])) $bdata[$tdata['class']] = Array();
								if (!isset($bdata[$tdata['class']]['offer'])) $bdata[$tdata['class']]['offer'] = Array();
								if (!isset($bdata[$tdata['class']]['offer'][$tdata['offer']])) $bdata[$tdata['class']]['offer'][$tdata['offer']] = Array();
								$bdata[$tdata['class']]['offer'][$tdata['offer']][$tid] = $tdata;
							}
						}
						for ($i = 0; $i < $info['props']['max_wishes']; $i++) {
							if (isset($tdata['wish_'.$i]) AND isset($offers[$tdata['wish_'.$i]])) {
								if (!isset($bdata['students']['wish_'.$i])) $bdata['students']['wish_'.$i] = Array();
								if (!isset($bdata['students']['wish_'.$i][$tdata['wish_'.$i]])) $bdata['students']['wish_'.$i][$tdata['wish_'.$i]] = Array();
								$bdata['students']['wish_'.$i][$tdata['wish_'.$i]][$tid] = $tdata;
								if (isset($tdata['class'])) {
									if (!isset($bdata[$tdata['class']]['wish_'.$i])) $bdata[$tdata['class']]['wish_'.$i] = Array();
									if (!isset($bdata[$tdata['class']]['wish_'.$i][$tdata['wish_'.$i]])) $bdata[$tdata['class']]['wish_'.$i][$tdata['wish_'.$i]] = Array();
									$bdata[$tdata['class']]['wish_'.$i][$tdata['wish_'.$i]][$tid] = $tdata;
								}
							}
						}
					}
					
					$tkeys = Array('offer');
					for ($i = 0; $i < $info['props']['max_wishes']; $i++) $tkeys[] = 'wish_'.$i;
					foreach ($bdata as $cln => $cdata) {
						foreach ($tkeys as $tkey) {
							foreach ($offers as $oid => $odata) {
								if (!isset($cdata[$tkey][$oid])) $bdata[$cln][$tkey][$oid] = Array();
							}
						}
					}

					foreach ($bdata as $cln => $cdata) {
						if ($cln != 'teachers' AND $cln != 'students' AND $cln != 'jgst') {
						
							$jgstr = Array();
							
							$erg = preg_match('/^((\D*)?\d+)/',$cln,$jgstr);
							
							if ($erg AND isset($jgstr[1])) {
								if (!isset($bdata['jgst'])) $bdata['jgst'] = Array();
								if (!isset($bdata['jgst'][$jgstr[1]])) $bdata['jgst'][$jgstr[1]] = Array();
								$bdata['jgst'][$jgstr[1]][] = $cln;
							}
						
						}
					}
					
					
					foreach ($offers as $oid => $odata) {
						$pdf->AddPage();
						$pdf->SetX(20);
						$pdf->SetFont('Arial','B',15);
						$pdf->Cell(0,10,utf8_decode($odata['name']));
						$pdf->SetFont('Arial','',12);
						$pdf->Ln(12);
						
						$da = Array(); 
						$fo = Array('repeat_header'=>true,'color2ndrow'=>Array(221,221,255),'fontsize'=>11,'rowheight'=>6.1,'table-align'=>'C');
						
						$fo['width_col_1'] = 40;
						$fo['width_col_2'] = 40;
						$fo['width_col_3'] = 40;
						$fo['width_col_4'] = 40;
						
						$da[] = Array(Array('colspan'=>4,'content'=>$odata['name']));
						$da[] = Array('Wo?',Array('content'=>$odata['place'],'fontstyle'=>'B'),'Wann?',Array('content'=>$odata['time'],'fontstyle'=>'B'));
						//$da[] = Array(Array('colspan'=>4,'content'=>''));
						$da[] = Array(Array('colspan'=>4,'fontstyle'=>'I','content'=>$odata['longdesc'],'multi'=>true));
						//$da[] = Array(Array('colspan'=>4,'content'=>''));
						$da[] = Array('Max.Teilnehmer:',Array('content'=>$odata['maxtn'],'fontstyle'=>'B'),'Teilnehmerbeitrag:',Array('content'=>substr(float_to_eur($odata['costs']),0,-6).'EUR','fontstyle'=>'B'));
						//$da[] = Array(Array('colspan'=>4,'content'=>''));
						if ((int)$odata['teacher'] > 0 AND $teachers[(int)$odata['teacher']]) {	
							$da[] = Array(Array('colspan'=>2,'content'=>'Ansprechpartner: '),Array('colspan'=>2,'content'=>id_to_address($teachers[(int)$odata['teacher']])));
						} else {
							$da[] = Array(Array('colspan'=>2,'content'=>'Ansprechpartner: '),Array('colspan'=>2,'content'=>'(Noch nicht eingetragen)'));
						}
						$da[] = Array(Array('colspan'=>2,'content'=>'Einverständniserklärung: '),Array('colspan'=>2,'content'=>((!isset($odata['eerkl']) OR $odata['eerkl']))?'Wird benötigt.':'Wird nicht benötigt.'));
						if (!isset($odata['avgender']) OR $odata['avgender'] == 'm,f') {
							$tret = 'männlich & weiblich';
						} elseif ($odata['avgender'] == 'm') {
							$tret = 'männlich';
						} elseif ($odata['avgender'] == 'f') {
							$tret = 'weiblich';
						} else {
							$tret = '';
						}
						$da[] = Array(Array('colspan'=>2,'content'=>'Zugel. Geschlechter: '),Array('colspan'=>2,'content'=>$tret));
						$da[] = Array(Array('colspan'=>2,'content'=>'Bevorzugt für Klassen: '),Array('colspan'=>2,'content'=>$odata['preferred']));
						$da[] = Array(Array('colspan'=>2,'content'=>'NICHT für Klassen: '),Array('colspan'=>2,'content'=>$odata['excluded']));
						
						
						$pdf->Table(1,$da,$fo);
						
						$pdf->Ln(4);
						
						$tstr = '';
						
						foreach ($bdata['teachers']['offer'][$oid] as $tdata) {
							if ($tstr != '') $tstr .= ', ';
							$tstr .= id_to_address($tdata);
						}
						
						$pdf->SetX(20);
						$pdf->MultiCell(0,6,utf8_decode('Folgende Lehrer waren am Angebot beteiligt: '.$tstr));
						$pdf->Ln(1);
						$pdf->SetX(20);
						$pdf->Cell(0,10,utf8_decode('Die Teilnehmer bzw. Interessenten waren folgendermaßen verteilt:'));
						
						$da = Array(); $fo = Array('fontsize'=>10,'align_col_1'=>'R','table-align'=>'C','color2ndrow'=>Array(221,221,255));
						$tr =& $da[];
						$tr[] = Array('content'=>'Status \ Jahrgangsstufe','align'=>'C');
						$fo['colwidth'] = Array(50);
						foreach ($bdata['jgst'] as $jgst => $data) {
							$tr[] = $jgst;
							$fo['colwidth'][] = 10;
						}
						$tr[] = '';
						$tr[] = Array('content'=>'å','font'=>'symbol');
						$fo['colwidth'][] = 2;
						$fo['colwidth'][] = 10;
						$sums = Array();
						$stati = Array('zugt'=>'Zugeteilt');
						for ($i = 0; $i < $info['props']['max_wishes']; $i++) $stati['w'.$i] = 'Wunsch '.($i+1);
					
						$stati['leer'] = 'Teilnahmestatus:';
						$stati['st0'] = '(nicht eingetragen)';
						$stati['st1'] = 'teilgenommen';
						$stati['st2'] = 'langfristig entschuldigt';
						$stati['st3'] = 'kurzfristig entschuldigt';
						$stati['st4'] = 'unentschuldigt';
						
						foreach ($bdata['jgst'] as $jgst => $data) {
							$sums[$jgst] = Array('zugt'=>0);
							for ($i = 0; $i < $info['props']['max_wishes']; $i++) $sums[$jgst]['w'.$i] = 0;
							$sums[$jgst]['leer'] = '';
							$sums[$jgst]['st0'] = 0;
							$sums[$jgst]['st1'] = 0;
							$sums[$jgst]['st2'] = 0;
							$sums[$jgst]['st3'] = 0;
							$sums[$jgst]['st4'] = 0;
							
							foreach ($data as $cln) {
								$sums[$jgst]['zugt'] += count($bdata[$cln]['offer'][$oid]);
								for ($i = 0; $i < $info['props']['max_wishes']; $i++) {	
									$sums[$jgst]['w'.$i] += count($bdata[$cln]['wish_'.$i][$oid]);
								}
								foreach ($bdata[$cln]['offer'][$oid] as $sid => $sdata) {
									if (isset($sdata['cancelled'])) {
										$sums[$jgst]['st'.$sdata['cancelled']]++;
									} else {
										$sums[$jgst]['st0']++;
									}
								}
							}						
						}
						
						$tnsum = 0;
						
						foreach ($stati as $key => $status) {
							
							$tr =& $da[];
							
							$actsum = 0;
							
							if ($key != 'leer') {
								$tr[] = $status;
								
								foreach ($bdata['jgst'] as $jgst => $data) {
								
									$actsum += $sums[$jgst][$key];
								
									if ($sums[$jgst][$key] > 0) {	
										$tr[] = $sums[$jgst][$key];
									} else {
										$tr[] = '';
									}
								
								}
								
								$tr[] = '';
								$tr[] = $actsum;
								
								if ($key == 'zugt') {
									$tnsum = $actsum;
								}
								
							} else {
								$tr[] = Array('colspan'=>(count($stati)+2),'content'=>$status,'fontstyle'=>'B','align'=>'C');
							}
							
						}
						
						$pdf->Ln(10);
						
						$pdf->Table(1,$da,$fo);

						$pdf->Ln(2);
						$pdf->SetX(20);
						$pdf->SetFont('Arial','',11);
						$pdf->MultiCell(0,6,utf8_decode('Der Vorkasse-Betrag lag bei '.substr(float_to_eur($odata['precosts']),0,-7).' EUR. Bei insgesamt '.$tnsum.' Teilnehmern ergibt sich ein Gesamtvolumen von '.substr(float_to_eur($odata['precosts']*$tnsum),0,-7).' EUR. Übrig geblieben sind insgesamt '.substr(float_to_eur($odata['left']),0,-7).' EUR. Dieser Betrag wurde auf die Teilnehmer geteilt mit entschuldigungsbedingten Rückzahlungen von max. '.$odata['refundall'].' EUR für langfristig entschuldigte und max. '.$odata['refund'].' EUR für kurzfristig entschuldigte.'));
						
						
					}
					
					$pdf->AddPage();
					
					foreach ($carpools as $cpid => $cpdata) {
						
						$pdf->SetFont('Arial','B',15);
						$pdf->Cell(0,10,utf8_decode('Fahrgemeinschaft '.$cpdata['name']));	

						$onamestr = '';
						if (!isset($cpb[$cpid])) $cpb[$cpid] = Array();
						
						$oids = explode(',',$cpdata['offers']);
						foreach ($oids as $oid) {
							if (isset($offers[$oid])) {
								if ($onamestr != '') $onamestr .= ', ';
								$onamestr .= $offers[$oid]['name'];
							}
						}
						
						$pdf->Ln(5);
						$pdf->SetX(15);
						$pdf->SetFont('Arial','',8);
						$pdf->Cell(0,10,utf8_decode('(Angebote: '.$onamestr.')'));
						
						$temp = '';
						$pdf->Ln(6);
						$pdf->SetX(20);
						$pdf->SetFont('Arial','',10);
						$pdf->Cell(0,10,utf8_decode('Der Fahrgemeinschaft standen folgende Busse zur Verfügung:'));
						
						foreach ($cpb[$cpid] as $bid => $busdata) {
							$pdf->Ln(6);
							$pdf->SetX(25);
							$pdf->SetFont('Symbol','',10);
							$pdf->Cell(2,10,utf8_decode('·'));
							$pdf->SetFont('Arial','',10);
							$pdf->Cell(0,10,utf8_decode(' Bezeichnung: '.$busdata['tag'].', Unternehmen: '.$busdata['company'].', max. Plätze: '.$busdata['capacity']));
						}
						
						
						$pdf->Ln(12);
						
					}				
					

					
			
				} break;
			case 'pdf_phonelist_offer': if (isset($offers[OFFER])) {
				
					$pdf = new PVSPDF();
					$pdf->SetMargins(15,60);
					$pdf->SetTitle(utf8_decode('Hinweise '.$offers[OFFER]['name']));
					$pdf->AddPage();
					$pdf->SetFont('Arial','B',22);
					$pdf->Ln(2);
					$pdf->Cell(0,10,utf8_decode('Hinweisliste '.$offers[OFFER]['name']));
					$pdf->SetFont('Arial','B',12);
					$pdf->Ln(12);
					
					$fo = Array('table-align'=>'C');
					$fo['colwidth'] = Array(45,44,2,45,44);
					$fo['fontsize'] = 9;
					$fo['color2ndrow'] = Array(221,221,255);
					$da = Array();
					$da[] = Array(Array('colspan'=>5,'content'=>'Wichtige Telefonnummern'));
					
					$bd1 = Array(); 
					$bd2 = Array();
					
					$oid = OFFER; $odata = $offers[$oid];

					for ($i = 0; $i < 50; $i++) {
						if (isset($odata['phonetags'.$i]) AND isset($odata['phonenrs'.$i]) AND $odata['phonetags'.$i] != '' AND $odata['phonenrs'.$i] != '') {
							$bd1[] = Array($odata['phonetags'.$i],$odata['phonenrs'.$i]);
						}
					}
					
					$cp = 0;
					foreach ($carpools as $cpid => $cpdata) {
						if (isset($cpdata['offers']) AND in_array($oid,explode(',',$cpdata['offers']))) {
							$cp = $cpid;
							break;
						}
					}
					
					if (isset($carpools[$cp])) {
						$cpdata = $carpools[$cp];
						
						$oids = explode(',',$cpdata['offers']);
						
					} else {
						
						$oids = Array($oid);
						
					}
					
					foreach ($teachers as $tid => $tdata) {
					
						if (isset($tdata['offer']) AND in_array($tdata['offer'],$oids)) {
							$bd2[] = Array(id_to_address($tdata),((isset($tdata['phone']) AND $tdata['phone'] != '')?$tdata['phone']:'(N/A)'));
						}
					}
					
					for ($j = 0; $j < max(count($bd1),count($bd2)); $j++) {
						
						$tr =& $da[];
						
						if (isset($bd1[$j])) {
							$tr[] = $bd1[$j][0];
							$tr[] = $bd1[$j][1];
						} else {
							$tr[] = Array('content'=>'','colspan'=>2);
						}
						
						$tr[] = Array('content'=>'','fillcolor'=>0);
						
						if (isset($bd2[$j])) {
							$tr[] = $bd2[$j][0];
							$tr[] = $bd2[$j][1];
						} else {
							$tr[] = Array('content'=>'','colspan'=>2);
						}
					}
					
					$pdf->Table(1,$da,$fo);
					
					if (isset($odata['hints']) AND $odata['hints'] != '') {
						$pdf->Ln(5);
						$pdf->SetFont('Arial','B','15');
						$pdf->Cell(0,10,'Hinweise vom verantwortlichen Lehrer:');
						$pdf->Ln(12);
						$pdf->SetFont('Arial','','12');
						$pdf->MultiCell(0,5,utf8_decode($odata['hints']));
					}
					
				} else {
					$portalbool = true;
					$html_override = true;
					$GLOBALS['hints'][] = '<span class="b cr">Das gewählte Angebot existiert nicht (mehr)!</span>';
				} break;
			case 'pdf_phonelist_all': {
				
					$pdf = new PVSPDF();
					$pdf->SetMargins(15,60);
					$pdf->SetTitle(utf8_decode('Hinweise für alle Angebote'));
					
					foreach ($offers as $oid => $odata) {
						$pdf->AddPage();
						$pdf->SetFont('Arial','B',22);
						$pdf->Ln(2);
						$pdf->Cell(0,10,utf8_decode('Hinweisliste '.$offers[$oid]['name']));
						$pdf->SetFont('Arial','B',12);
						$pdf->Ln(12);
						
						$fo = Array('table-align'=>'C');
						$fo['colwidth'] = Array(45,44,2,45,44);
						$fo['fontsize'] = 9;
						$fo['color2ndrow'] = Array(221,221,255);
						$da = Array();
						$da[] = Array(Array('colspan'=>5,'content'=>'Wichtige Telefonnummern'));
						
						$bd1 = Array(); 
						$bd2 = Array();
						

						for ($i = 0; $i < 50; $i++) {
							if (isset($odata['phonetags'.$i]) AND isset($odata['phonenrs'.$i]) AND $odata['phonetags'.$i] != '' AND $odata['phonenrs'.$i] != '') {
								$bd1[] = Array($odata['phonetags'.$i],$odata['phonenrs'.$i]);
							}
						}
						
						$cp = 0;
						foreach ($carpools as $cpid => $cpdata) {
							if (isset($cpdata['offers']) AND in_array($oid,explode(',',$cpdata['offers']))) {
								$cp = $cpid;
								break;
							}
						}
						
						if (isset($carpools[$cp])) {
							$cpdata = $carpools[$cp];
							
							$oids = explode(',',$cpdata['offers']);
							
						} else {
							
							$oids = Array($oid);
							
						}
						
						foreach ($teachers as $tid => $tdata) {
						
							if (isset($tdata['offer']) AND in_array($tdata['offer'],$oids)) {
								$bd2[] = Array(id_to_address($tdata),((isset($tdata['phone']) AND $tdata['phone'] != '')?$tdata['phone']:'(N/A)'));
							}
						}
						
						for ($j = 0; $j < max(count($bd1),count($bd2)); $j++) {
							
							$tr =& $da[];
							
							if (isset($bd1[$j])) {
								$tr[] = $bd1[$j][0];
								$tr[] = $bd1[$j][1];
							} else {
								$tr[] = Array('content'=>'','colspan'=>2);
							}
							
							$tr[] = Array('content'=>'','fillcolor'=>0);
							
							if (isset($bd2[$j])) {
								$tr[] = $bd2[$j][0];
								$tr[] = $bd2[$j][1];
							} else {
								$tr[] = Array('content'=>'','colspan'=>2);
							}
						}
						
						$pdf->Table(1,$da,$fo);
						
						if (isset($odata['hints']) AND $odata['hints'] != '') {
							$pdf->Ln(5);
							$pdf->SetFont('Arial','B','15');
							$pdf->Cell(0,10,'Hinweise vom verantwortlichen Lehrer:');
							$pdf->Ln(12);
							$pdf->SetFont('Arial','','12');
							$pdf->MultiCell(0,5,utf8_decode($odata['hints']));
						}
						
					}
				} break;
			
			//HTML-Managementansichten
			case 'management_affirm_archiving': {
					
					$ret .= '<div class="fieldset">';
					$ret .= '<h2>Archivierung bestätigen!</h2>';
					$ret .= '<p>Sie haben angegeben, das Event <span class="b">'.$info['info']['name'].'</span> archivieren zu wollen. Die Archivierung bedeutet, dass der Ordner mit den Eventdaten komprimiert und im Serverarchiv abgelegt wird. <span class="b">Sie werden NICHT mehr in der Lage sein sich einzuloggen. Die Datenbank kann nicht mehr aufgerufen werden.</span> Bestätigen Sie die Archivierung oder kehren Sie zur Portalseite zurück.</p>';
					$ret .= '<form action="./?view=default" method="GET" style="text-align:center;background-color:#090;">';
					$ret .= '<input type="hidden" value="default" name="view"/>';
					$ret .= '<input type="submit" value="Abbrechen" style="font-weight:bold;color:#090;font-size:200%"/>';
					$ret .= '</form>';
					$ret .= '<form action="./" method="GET" style="text-align:center;background-color:#900;">';
					$ret .= '<input type="hidden" value="management_archiving_lastwarning" name="view"/>';
					$ret .= '<input type="submit" value="Ich bin mir der Konsequezen bewusst und möchte das Event archivieren." style="font-size:75%;font-weight:bold;color:#900;"/>';
					$ret .= '</form>';
					$ret .= '</div>';
			
				} break;
			case 'management_archiving_lastwarning': {
					
					$ret .= '<div class="fieldset">';
					$ret .= '<h2>Sind Sie sich wirklich sicher?</h2>';
					$ret .= '<p>Das PVS möchte sich vergewissern, dass Sie sich im vollen Bewusstsein der Konsequenzen entschlossen haben, das Event <span class="b">'.$info['info']['name'].'</span> zu archivieren und damit <span class="b"> alle Ihre Zugriffsrechte aufzugeben.</span> Wenn Sie die Archivierung hier bestätigen wird das PVS keine weitere Warnung erteilen.</p>';
					$ret .= '<form action="./?view=default" method="GET" style="text-align:center;background-color:#090;">';
					$ret .= '<input type="hidden" value="default" name="view"/>';
					$ret .= '<input type="submit" value="Abbrechen" style="font-weight:bold;color:#090;font-size:300%"/>';
					$ret .= '</form>';
					$ret .= '<form action="./" method="POST" style="text-align:center;background-color:#900;">';
					$ret .= '<input type="hidden" value="management_archive" name="action"/>';
					$ret .= '<input type="submit" value="Ich bin mir WIRKLICH der Konsequezen bewusst und möchte das Event archivieren." style="font-size:60%;font-weight:bold;color:#900;"/>';
					$ret .= '</form>';
					$ret .= '</div>';
			
				} break;
			case 'management_autofill_offers': {
				
					$ret .= '<div class="fieldset">';
					$ret .= '<h2>Automatische Angebotszuteilung</h2>';
					$ret .= '<p>Sie können aus nachstehenden Optionen für die Schülerzuteilung wählen:</p>';
					$ret .= '<form action="./?view=default" method="POST">';
					$ret .= '<fieldset>';
					$ret .= '<legend>Optionen</legend>';
					$ret .= '<br/>';
					$ret .= '<table class="st3c">';
					$ret .= '<tr>';
					$ret .= '<td>Bestehende Zuteilungen...</td>';
					$ret .= '<td style="text-align:left">';
					$ret .= '<input type="radio" name="flags_continuance" value="1" checked="checked" id="flags_continuance_1" /><label for="flags_continuance_1">...bleiben bestehen.</label><br/>';
					$ret .= '<input type="radio" name="flags_continuance" value="0" id="flags_continuance_0" /><label for="flags_continuance_0">...werden verworfen.</label>';
					$ret .= '</td>';
					$ret .= '<td>Wählen Sie, ob bestehende Zuteilungen einen Bestandsschutz genießen (dann werden nur Schüler verteilt, die noch nicht zugeteilt waren) oder ob die Verteilung komplett neu berechnet werden soll.</td>';
					$ret .= '</tr>';
					$ret .= '</table>';
					$ret .= '<br/>';
					$ret .= '<input type="hidden" value="management_autofill_offers" name="action"/>';
					$ret .= '<input type="submit" value="Zuteilung vornehmen!"/>';
					$ret .= '</fieldset>';
					$ret .= '</form>';
					$ret .= '</div>';
				
				} break;
			case 'management_autofill_busses': {
				
					$ret .= '<div class="fieldset">';
					$ret .= '<h2>Automatische Buszuteilung</h2>';
					$ret .= '<p>Sie können aus nachstehenden Optionen für die Schülerzuteilung wählen:</p>';
					$ret .= '<form action="./?view=default" method="POST">';
					$ret .= '<fieldset>';
					$ret .= '<legend>Optionen</legend>';
					$ret .= '<br/>';
					$ret .= '<table class="st3c">';
					$ret .= '<tr>';
					$ret .= '<td>Verwende folgende Fahrgemeinschaften:</td>';
					$ret .= '<td style="text-align:left">';
					$str = '<ul style="margin-left:0px;list-style-type:none;text-indent:-21px;padding:0px">';
					foreach ($carpools as $cpid => $cpdata) {
						$str .= '<li style="border-top:1px solid black;border-bottom:1px solid black;padding-left:20px"><input type="checkbox" checked="checked" value="'.$cpid.'" name="flags_carpools[]" id="flags_carpools_'.$cpid.'" /><label for="flags_carpools_'.$cpid.'"">'.$cpdata['name'].'</label></li>';
					}
					$str .= '</ul>';
					$ret .= $str;
					$ret .= '</td>';
					$ret .= '<td>Wählen Sie aus, für welche Fahrgemeinschaft die Busplätze verteilt werden sollen. Beachten Sie, dass bereits verteilte Busplätze KEINEN Bestandsschutz genießen, wenn sie die entsprechende Fahrgemeinschaft hier aktivieren.</td>';
					$ret .= '</tr>';
					$ret .= '</table>';
					$ret .= '<br/>';
					$ret .= '<input type="hidden" value="management_autofill_busses" name="action"/>';
					$ret .= '<input type="submit" value="Zuteilung vornehmen!"/>';
					$ret .= '</fieldset>';
					$ret .= '</form>';
					$ret .= '</div>';
				
				} break;
			case 'management_change_title': {

					$ret .= '<div class="fieldset">';
					$ret .= '<h2>Event-Titel bearbeiten</h2>';
					$ret .= '<form method="POST" action="./?view=management_change_title">';
					$ret .= '<fieldset style="text-align:center">';
					$ret .= '<p>Bitte geben Sie den neuen gewünschten Namen an.</p>';
					$ret .= '<p><input type="text" style="width:300px" name="management_newtitle" value="'.$info['info']['name'].'"/><input type="hidden" name="management_oldtitle" value="'.$info['info']['name'].'"/></p>';
					$ret .= '<p><input type="hidden" name="action" value="management_change_title"/><input type="submit" value="Speichern."/></p>';
					$ret .= '</fieldset>';
					$ret .= '</form>';
					$ret .= '</div>';

					
				} break;
			case 'management_configure_access': {
				
					$ret .= '<div class="fieldset">';
					
					$ret .= '<h2>Konfiguration Zugriffsrechte</h2>';
					
					$ret .= '<form action="./?view=management_configure_access" method="POST">';
					
					$GLOBALS['output'] = $GLOBALS['output'] . '<fieldset>';
					$GLOBALS['output'] = $GLOBALS['output'] . '<legend>Passwörter</legend>';
					$ret .= '<p>Passwörter müssen nur eingetragen werden, wenn eine jeweilige Änderung erwünscht ist.</p>';
					$GLOBALS['output'] = $GLOBALS['output'] . '<table class="st3c">';
					$GLOBALS['output'] = $GLOBALS['output'] . '<tr><td><label for="crdata_pwclear">Passwörter anzeigen:</label></td><td><input type="checkbox" id="crdata_pwclear" name="crdata_pwclear" value="true" checked="checked"/></td><td>Passworteingabe im Klartext anzeigen</td></tr>';
					$GLOBALS['output'] = $GLOBALS['output'] . '<tr><td><label for="crdata_pwbas">Passwort Schülermanagement:</label></td><td><input type="text" id="crdata_pwbas" name="crdata_pwbas" value=""/></td><td>Passwort für Zugriff auf das Schülermanagement: Wünsche von Schülern eintragen, Schülerlisten einsehen und drucken, persönliche Daten bearbeiten</td></tr>';
					$GLOBALS['output'] = $GLOBALS['output'] . '<tr><td><label for="crdata_pwadv">Passwort Angebotsmanagement:</label></td><td><input type="text" id="crdata_pwadv" name="crdata_pwadv" value=""/></td><td>Passwort für Zugriff auf das Angebotsmanagement: Angebotsdaten bearbeiten, Teilnehmerlisten einsehen und drucken, Anwesenheitsliste ausfüllen</td></tr>';
					$GLOBALS['output'] = $GLOBALS['output'] . '</table>';
					$GLOBALS['output'] = $GLOBALS['output'] . '</fieldset>';

					$ret .= '<fieldset><legend>Zugriffsrechte</legend>';
					
					$ret .= '<p>Hinweis: Dieses Feature sollte nur gut bedacht genutzt werden. Bei exotischen Kombinationen können vorher unabsehbare Wirkungen eintreten. Die Standard-Berechtigungen sind jeweils grün unterlegt.</p>';
					
					$ret .= '<table class="ed_table ov_table">';
					
					$ret .= '<thead>';
					
					$ret .= '<tr><th colspan="2">Bezeichnung</th><th colspan="2">Zugriffsberechtigung</th></tr>';
					
					$ret .= '<tr>';
					$ret .= '<th>Kategorie</td>';
					$ret .= '<th>Option</td>';
					$ret .= '<th>Schülerman.</td>';
					$ret .= '<th>Angebotsman.</td>';
					$ret .= '</tr>';
					
					$ret .= '</thead>';
					
					$ret .= '<tbody>';
					
					foreach ($GLOBALS['views']['cat'] as $tcat => $tcdata) {
					
						$tret = '<tr>';
						
						$tret .= '<td rowspan="<-$TCATCOUNT$->" class="th">';
						
						switch ($tcat) {
							
							case 'ov': $tret .= 'Status und Übersichten'; break;
							case 'ed': $tret .= 'Datenbankbearbeitung'; break;
							case 'pr': $tret .= 'Druckansichten'; break;
							case 'mg': $tret .= 'Managementfunktionen'; break;
						
						}
						
						$tret .= '</td>';
					
						$tct = 0;
					
						foreach ($tcdata as $tview => $tvdata) {
						
							if ($tvdata['show']) {
								if ($tct > 0) $tret .= '<tr>';
							
								$tret .= '<td class="'.$tvdata['vcl'].'">';
								$tret .= $tvdata['pref'];
								if (count($tvdata['filters']) > 0) $tret .= ' XYZ ';
								$tret .= $tvdata['postf'];
								$tret .= '</td>';
								
								$tret .= '<td class="'.$tvdata['vcl'].'" style="text-align:center';
								
								
								$tret .= '">';
								
								$tret .= '<input type="checkbox" ';

								if ($tvdata['basallowed']) $tret .= 'checked="checked" ';
								if (in_array($tview,$GLOBALS['views']['basdef'])) $tret .= 'style="outline:3px solid #090;" ';

								$tret .= 'name="basallowed[]" value="'.$tview.'" ';
								
								$tret .= '/>';
								
								$tret .= '</td>';
								
								$tret .= '<td class="'.$tvdata['vcl'].'" style="text-align:center">';
								
								$tret .= '<input type="checkbox" ';
								
								if ($tvdata['advallowed']) $tret .= 'checked="checked" ';
								if (in_array($tview,$GLOBALS['views']['advdef'])) $tret .= 'style="outline:3px solid #090;" ';
								
								$tret .= 'name="advallowed[]" value="'.$tview.'" ';
								
								$tret .= '/>';
								
								$tret .= '</td>';
								
								$tct++;
							
								$tret .= '</tr>';
							}
							
						}
						
						$tret = str_replace('"<-$TCATCOUNT$->"','"'.$tct.'"',$tret);
						

						$ret .= $tret;
					}
					
					$ret .= '</tbody>';
					
					$ret .= '</table>';
					
					$ret .= '</fieldset>';
					
					$ret .= '<fieldset><legend>Optionen</legend>';
					
					$ret .= '<input type="hidden" name="action" value="management_configure_access" />';
					
					$ret .= '<input type="submit" value="Speichern." />';
					
					$ret .= '</fieldset>';
					
					$ret .= '</form>';
					
					$ret .= '</div>';
				
				} break;
			
			default: {

				$portalbool = true;
				$html_override = true;
				
			} break;

		}
		
	} else {
	
		$GLOBALS['hints'][] = '<span style="color:#900;font-weight:bold;">Sie besitzen nicht die nötigen Zugriffsrechte zur Anzeige dieser Ansicht.</span>';
		$portalbool = true;
		$html_override = true;
	
	}

	if ($portalbool) {
		include 'produce_portal.php';
	}

?>