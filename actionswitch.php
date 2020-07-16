<?php


	if (
		(AUTH == 'supervisor' OR ACTION == 'N/A')
		OR
		(
			isset($GLOBALS['views']) AND isset($GLOBALS['views']['all']) AND isset($GLOBALS['views']['all'][ACTION])
			AND
			(AUTH == 'supervisor' OR (AUTH == 'basic' AND $GLOBALS['views']['all'][ACTION]['basallowed']) OR (AUTH == 'advanced' AND $GLOBALS['views']['all'][ACTION]['advallowed']))
		)
	) {
		switch (ACTION) {
			case 'manage_classes': {
					
					if (isset($_REQUEST['cm_counter'])) {
						$n = (int)$_REQUEST['cm_counter'];
						
						for ($i = 1; $i <= $n; $i++) {
						
							if (
								isset($_REQUEST['cm_'.$i.'_name']) AND
								isset($_REQUEST['cm_'.$i.'_oldStatus']) AND
								isset($_REQUEST['cm_'.$i.'_status'])
								) {
									
									$mn = '';
									$ms = '';
									$mos = '';
									$bool = false;
									
									if ((int)$_REQUEST['cm_'.$i.'_oldStatus'] == 1) {
										$oldpath = './db/'.DB.'/class_'.$_REQUEST['cm_'.$i.'_name'].'.ini';
										$mos = 'Aktiviert';
									} else {
										$oldpath = './db/'.DB.'/deactivated/class_'.$_REQUEST['cm_'.$i.'_name'].'.ini';
										$mos = 'Deaktiviert';
									}
									
									if ((int)$_REQUEST['cm_'.$i.'_status'] == 1) {
										$newpath = './db/'.DB.'/class_';
										$ms = 'Aktiviert';
									} else {
										$newpath = './db/'.DB.'/deactivated/class_';
										$ms = 'Deaktiviert';
										$bool = true;
									}
									
									if (isset($_REQUEST['cm_'.$i.'_rename']) AND $_REQUEST['cm_'.$i.'_rename'] != '' AND $_REQUEST['cm_'.$i.'_rename'] != $_REQUEST['cm_'.$i.'_name']) {
										$newpath .= $_REQUEST['cm_'.$i.'_rename'];
										$mn = $_REQUEST['cm_'.$i.'_rename'];
									} else {
										$newpath .= $_REQUEST['cm_'.$i.'_name'];
										$mn = $_REQUEST['cm_'.$i.'_name'];
									}
									
									if ($oldpath != $newpath.'.ini') {
										$add = '';
										$temp = $newpath;
										
										$newpath .= '.ini';
										
										while (file_exists($newpath)) {
											
											if ($add === '') {
												$add = 0;
											} else {
												$add++;
											}
											$newpath = $temp.$add.'.ini';
										
										}
										
										$mn .= $add;
										
										if ($bool AND !is_dir('./db/'.DB.'/deactivated')) { mkdir('./db/'.DB.'/deactivated'); }
										
										if (is_file($oldpath)) {
											rename($oldpath,$newpath);
										
											//Klassenlehrer bei Umbenennung fixen
											if ($_REQUEST['cm_'.$i.'_name'] != $mn) {
												$temp = parse_ini_file('./db/'.DB.'/teachers.ini',true);
												$ct = get_class_teacher($_REQUEST['cm_'.$i.'_name'],$temp);
												if ($ct AND isset($temp[$ct])) {
													echo $_REQUEST['cm_'.$i.'_name'].' - '.$ct;
													$classes = explode(',',$temp[$ct]['class']);
													$temp[$ct]['class'] = '';
													foreach ($classes as $class) {
														if ($class == $_REQUEST['cm_'.$i.'_name']) {
														
															if ($temp[$ct]['class'] != '') $temp[$ct]['class'] .= ',';
															$temp[$ct]['class'] .= $mn;
														
														} else {
															if ($temp[$ct]['class'] != '') $temp[$ct]['class'] .= ',';
															$temp[$ct]['class'] .= $class;
														}
													}
													
													write_ini_file('./db/'.DB.'/teachers.ini',$temp);
												}
											}
											
											$GLOBALS['hints'][] = '<span style="font-weight:bold;color:#009;">Der Klassenstatus ['.$_REQUEST['cm_'.$i.'_name'].'|'.$mos.'] wurde verschoben nach ['.$mn.'|'.$ms.'].</span>';
										} else {
											$GLOBALS['hints'][] = '<span style="font-weight:bold;color:#900;">Partieller Fehlschlag - Klassenstatus ['.$_REQUEST['cm_'.$i.'_name'].'|'.$mos.'] wurde schon anderweitig verschoben.</span>';
										}
									}
									
									
							} else {
								$GLOBALS['hints'][] = '<span style="font-weight:bold;color:#900;">Partieller Fehlschlag - Nicht alle Eingabefelder für Counternummer '.$i.' gefunden. Versuchen Sie es erneut oder informieren Sie den Systemadministrator.</span>';
							}
						}
						
						$GLOBALS['hints'][] = '<span style="font-weight:bold;color:#090;">Erfolg - Der Klassenstatus wurde den Eingaben gemäß angepasst.</span>';
				
					} else {
						$GLOBALS['hints'][] = '<span style="font-weight:bold;color:#900;">Fehlschlag - Kein Counter gefunden. Versuchen Sie es erneut oder informieren Sie den Systemadministrator.</span>';
					}
					
			} break;
			case 'edit_teachers': {
					
					//Sammle Daten
					$input = Array();
					$save_teachers = Array();
					$control_teachers = Array();
					
					$defaults = Array('gender' => 'm','type' => 'teacher', 'forename' => 'Maximilian', 'sign' => '', 'surname' => 'Musterlehrer', 'class' => '', 'offer' => 0, 'available' => 1, 'bus' => 0, 'phone'=>'');
					
					for ($i = 1; $i <= $info['props']['max_wishes']; $i++) {
						$defaults['wish_'.($i-1)] = '';
					}
					
					foreach ($_REQUEST as $key => $data) {
						
						if (substr($key,0,14) == 'edit_teachers_') {
						
							$input[substr($key,14)] = $data;
							
						}
						
					}
					
					if (isset($input['control_teachers'])) {
						file_put_contents('./TEMP',$input['control_teachers']);
						$control_teachers = parse_ini_file('./TEMP',true);
						unlink('./TEMP');
					}
					
					foreach ($input as $key => $data) {
						if (strpos($key,'_') AND (
							is_numeric($id = substr($key,0,(strpos($key,'_')))) OR substr($id,0,4) == 'TEMP'
							)) {
							
							$pn = substr($key,(strpos($key,'_') + 1));
														
							if (
								!isset($control_teachers) 
								OR 
								!isset($control_teachers[$id])
								OR 
								!isset($control_teachers[$id][$pn])
								OR
								(
									$control_teachers[$id][$pn] != $data
								)
								) {
								
								if (!isset($save_teachers[$id])) {
									$save_teachers[$id] = Array();
								}
								
								$save_teachers[$id][$pn] = $data;
								
							}
						}
							
					}
					
					
					
					//Neue Einträge ausfiltern
					foreach ($save_teachers as $id => $sdata) {
						if (substr($id,0,4) == 'TEMP') {
							if ($sdata['forename'] != '' OR $sdata['surname'] != '') { 
								$nid = get_DB_ct(DB);
								$sdata['id'] = $nid;
								$save_teachers[$nid] = $sdata;
								unset($save_teachers[$id]);
								
								foreach ($defaults as $defk => $defd) {
									if (!isset($save_teachers[$nid][$defk])) {
										$save_teachers[$nid][$defk] = $defd;
									}
								}
						
								$GLOBALS['hints'][] = '<span style="font-weight:bold;color:#090;">LehrerIn ';
								$GLOBALS['hints'][count($GLOBALS['hints']) - 1] .= isset($save_teachers[$nid]['forename'])?$save_teachers[$nid]['forename']:'?';							
								$GLOBALS['hints'][count($GLOBALS['hints']) - 1] .= ' ';							
								$GLOBALS['hints'][count($GLOBALS['hints']) - 1] .= isset($save_teachers[$nid]['surname'])?$save_teachers[$nid]['surname']:'?';	
								$GLOBALS['hints'][count($GLOBALS['hints']) - 1] .= ' (ID '.$nid.') wurde erfolgreich hinzugefügt.</span>';							
							}
						} 
					}
					
					//Projektverschiebung und Deaktivierung bei Verantwortlichen verhindern
					$tempt = parse_ini_file('./db/'.DB.'/offers.ini',true);
					$occ = Array();
					foreach ($tempt as $oid => $odata) {
						if (isset($odata['teacher']) AND (int)$odata['teacher'] != 0) {
							$occ[$oid] = (int)$odata['teacher'];
						}
					}
					
					foreach ($save_teachers as $id => $sdata) {
						
						foreach ($sdata as $key => $data) {
							if ($key == 'offer' AND in_array($id,$occ)) {
								unset($save_teachers[$id]['offer']);
								for ($i = 0; $i < $info['props']['max_wishes']; $i++) {
									if (isset($save_teachers[$id]['wish_'.$i])) unset($save_teachers[$id]['wish_'.$i]);
								}
							}
							
							if (false AND $key == 'available' AND (!$data OR $data == '') AND in_array($id,$occ)) {
								$data = 1;
							}
						}
					}
					
					//echo '<pre>Lehrer final:'.print_r($save_teachers,true).'</pre>';
					
					$temp = parse_ini_file('./db/'.DB.'/teachers.ini',true);
					
					foreach ($save_teachers as $id => $sdata) {
						foreach ($sdata as $pn => $data) {
							if (!isset($temp[$id])) { $temp[$id] = Array(); }
							$temp[$id][$pn] = $data;
						}
					}
					
					//Format wenn nötig korrigieren, zum Löschen freigegebene Lehrer löschen.
					foreach ($temp as $id => $sdata) {
						foreach ($defaults as $prop => $value) {
						
							if (!isset($sdata[$prop])) {
								$temp[$id][$prop] = $value;
							}
							
						}
						
						if (isset($sdata['forename']) AND isset($sdata['surname']) AND $sdata['forename'] == '' AND $sdata['surname'] == '') {
							unset($temp[$id]);
							
							if (isset($control_teachers) AND isset($control_teachers[$id]) AND ( isset($control_teachers[$id]['surname']) OR isset($control_teachers[$id]['forename']) ) ) { 
								$GLOBALS['hints'][] = '<span style="font-weight:bold;color:#090;">Die Lehrerdaten mit ID '.$id.' (';
								$GLOBALS['hints'][count($GLOBALS['hints']) - 1] .= isset($control_teachers[$id]['forename'])?$control_teachers[$id]['forename']:'?';							
								$GLOBALS['hints'][count($GLOBALS['hints']) - 1] .= ' ';							
								$GLOBALS['hints'][count($GLOBALS['hints']) - 1] .= isset($control_teachers[$id]['surname'])?$control_teachers[$id]['surname']:'?';							
								$GLOBALS['hints'][count($GLOBALS['hints']) - 1] .= ') wurden erfolgreich gelöscht.</span>';							
							}
						}
					}
					
					write_ini_file('./db/'.DB.'/teachers.ini',$temp);
					
					
				
					
					
					$GLOBALS['hints'][] = '<span style="font-weight:bold;color:#090;">Die Daten der Lehrerliste wurden erfolgreich bearbeitet.</span>';
					
					
					
				} break;
			case 'add_class': {
					
					if (!isset($_REQUEST['edit_students_classname']) OR $_REQUEST['edit_students_classname'] == '') {
						$GLOBALS['hints'][] = '<span class="b cr">Fehlschlag. Da kein Klassenname angegeben wurde konnte die Klasse nicht erstellt werden.</span>';
						break;
					} else {
						$classfilter = $_REQUEST['edit_students_classname'];
						$n = 0;
						while (is_file('./db/'.DB.'/class_'.$classfilter.'.ini')) {
							$classfilter = $_REQUEST['edit_students_classname'].$n;
							$n++;
						}
						if ($n > 0) {
							$GLOBALS['hints'][] = '<span class="b cb">Der gewählte Klassenname existiert schon und wurde deshalb von '.$_REQUEST['edit_students_classname'].' nach '.$classfilter.' geändert.</span>';
						}
					}
					
					//Sammle Daten
					$input = Array();
					$save_students = Array();
					$save_teachers = Array();
					$control_students = Array();
					$control_teacher = Array();
					
					$defaults = Array('surname'=>'Mustermann','forename'=>'Maximilian','gender'=>'m','offer'=>0,'type'=>'student','class'=>$classfilter,'paid'=>0.0,'bus'=>0);
					for ($i = 1; $i <= $info['props']['max_wishes']; $i++) {
						$defaults['wish_'.($i-1)] = '';
					}
						
					for ($i = 0; $i < $info['props']['max_wishes']; $i++) {
						$basallowed[] = 'wish_'.$i; 
					}
					
					foreach ($_REQUEST as $key => $data) {
						
						if (substr($key,0,14) == 'edit_students_') {
						
							$input[substr($key,14)] = $data;
							
						}
						
					}
						
					foreach ($input as $key => $data) {
						if (strpos($key,'_') AND (
							is_numeric($id = substr($key,0,(strpos($key,'_')))) OR substr($id,0,4) == 'TEMP'
							)) {
							
							$pn = substr($key,(strpos($key,'_') + 1));
							
							if (AUTH == 'supervisor' OR in_array($pn,$basallowed)) {
								
								if (!isset($save_students[$id])) {
									$save_students[$id] = Array();
								}
									
								$save_students[$id][$pn] = $data;
				
							}
							
						}
					}
					
					//Neue Einträge ausfiltern
					foreach ($save_students as $id => $sdata) {
						if (substr($id,0,4) == 'TEMP') {
							if ($sdata['forename'] != '' OR $sdata['surname'] != '') { 
								$nid = get_DB_ct(DB);
								$sdata['id'] = $nid;
								$save_students[$nid] = $sdata;
								unset($save_students[$id]);
								
								foreach ($defaults as $defk => $defd) {
									if (!isset($save_students[$nid][$defk])) {
										$save_students[$nid][$defk] = $defd;
									}
								}
						
								$GLOBALS['hints'][] = '<span style="font-weight:bold;color:#090;">SchülerIn ';
								$GLOBALS['hints'][count($GLOBALS['hints']) - 1] .= isset($save_students[$nid]['forename'])?$save_students[$nid]['forename']:'?';							
								$GLOBALS['hints'][count($GLOBALS['hints']) - 1] .= ' ';							
								$GLOBALS['hints'][count($GLOBALS['hints']) - 1] .= isset($save_students[$nid]['surname'])?$save_students[$nid]['surname']:'?';	
								$GLOBALS['hints'][count($GLOBALS['hints']) - 1] .= ' (ID '.$nid.') wurde erfolgreich hinzugefügt.</span>';							
							}
						} 
					}
					
					
					$temp = Array();
					
					foreach ($save_students as $id => $sdata) {
						foreach ($sdata as $pn => $data) {
							if (!isset($temp[$id])) { $temp[$id] = Array(); }
							$temp[$id][$pn] = $data;
						}
					}
					
					//Format wenn nötig korrigieren, zum Löschen freigegebene SuS löschen.
					foreach ($temp as $id => $sdata) {
						foreach ($defaults as $prop => $value) {
						
							if (!isset($sdata[$prop])) {
								$temp[$id][$prop] = $value;
							}
							
							
							if ($prop == 'offer' OR substr($prop,0,5) == 'wish_') {
								
								if (!isset($offers)) $offers = parse_ini_file('./db/'.DB.'/offers.ini',true);
								
								if (!isset($sdata[$prop]) OR !isset($offers[(int)$sdata[$prop]]) OR (int)$sdata[$prop] <= 0 OR (isset($offers[(int)$sdata[$prop]]['excluded']) AND parse_clstring($offers[(int)$sdata[$prop]]['excluded'],CLASSFILTER) ))  {
									$temp[$id][$prop] = 0;
									$sdata[$prop] = 0;
								}
							} 
							
						}
						
						if (isset($sdata['forename']) AND isset($sdata['surname']) AND $sdata['forename'] == '' AND $sdata['surname'] == '') {
							unset($temp[$id]);
							
							if (isset($control_students) AND isset($control_students[$id]) AND ( isset($control_students[$id]['surname']) OR isset($control_students[$id]['forename']) ) ) { 
								$GLOBALS['hints'][] = '<span style="font-weight:bold;color:#090;">Die Schülerdaten mit ID '.$id.' (';
								$GLOBALS['hints'][count($GLOBALS['hints']) - 1] .= isset($control_students[$id]['forename'])?$control_students[$id]['forename']:'?';							
								$GLOBALS['hints'][count($GLOBALS['hints']) - 1] .= ' ';							
								$GLOBALS['hints'][count($GLOBALS['hints']) - 1] .= isset($control_students[$id]['surname'])?$control_students[$id]['surname']:'?';							
								$GLOBALS['hints'][count($GLOBALS['hints']) - 1] .= ') wurden erfolgreich gelöscht.</span>';							
							}
						}
					}
					
					write_ini_file('./db/'.DB.'/class_'.$classfilter.'.ini',$temp);
					
					if (isset($input['classteacher'])) {
						if (isset($input['control_teacher']) AND (int)$input['control_teacher'] != (int)$input['classteacher']) {
							$temp = parse_ini_file('./db/'.DB.'/teachers.ini',true);
							
							$temp[0] = Array('class' => '');
							
							$input['control_teacher'] = (int)$input['control_teacher'];
							$input['classteacher'] = (int)$input['classteacher'];
							
							if (isset($temp[(int)$input['control_teacher']]) AND isset($temp[(int)$input['classteacher']])) {
								$otc = explode(',',$temp[$input['control_teacher']]['class']);
								
								$temp[$input['control_teacher']]['class'] = '';
								
								foreach ($otc as $otcn) {
									if ($otcn != $classfilter) {
										if ($temp[$input['control_teacher']]['class'] == '') {
											$temp[$input['control_teacher']]['class'] = $otcn;
										} else {
											$temp[$input['control_teacher']]['class'] .= ','.$otcn;
										}
									}
								}
								
								if ($temp[$input['classteacher']]['class'] == '') {	
									$temp[$input['classteacher']]['class'] = $classfilter;
								} elseif (!preg_match('/\b'.addslashes($classfilter).'\b/',$temp[$input['classteacher']]['class'])) {								
									$temp[$input['classteacher']]['class'] .= ','.$classfilter;
								}
								
								unset($temp[0]);
								
								write_ini_file('./db/'.DB.'/teachers.ini',$temp);
								
							}
							
							unset($temp);
						}
					} 
				
					
					
					$GLOBALS['hints'][] = '<span style="font-weight:bold;color:#090;">Die Klassendaten für Klasse '.$classfilter.' wurden erfolgreich angelegt und bearbeitet.</span>';
					
					
					
				} break;
				
			case 'edit_students': if (CLASSFILTER != 'N/A') {
					
					//Sammle Daten
					$input = Array();
					$save_students = Array();
					$save_teachers = Array();
					$control_students = Array();
					$control_teacher = Array();
					
					$defaults = Array('surname'=>'Mustermann','forename'=>'Maximilian','gender'=>'m','offer'=>0,'type'=>'student','class'=>CLASSFILTER,'paid'=>0.0,'bus'=>0);
					for ($i = 1; $i <= $info['props']['max_wishes']; $i++) {
						$defaults['wish_'.($i-1)] = '';
					}
					
					$basallowed = Array('forename','surname','gender');
					
					for ($i = 0; $i < $info['props']['max_wishes']; $i++) {
						$basallowed[] = 'wish_'.$i; 
					}
					
					foreach ($_REQUEST as $key => $data) {
						
						if (substr($key,0,14) == 'edit_students_') {
						
							$input[substr($key,14)] = $data;
							
						}
						
					}
					
					if (isset($input['control_students'])) {
						file_put_contents('./TEMP',$input['control_students']);
						$control_students = parse_ini_file('./TEMP',true);
						unlink('./TEMP');
					}
					
					foreach ($input as $key => $data) {
						if (strpos($key,'_') AND (
							is_numeric($id = substr($key,0,(strpos($key,'_')))) OR substr($id,0,4) == 'TEMP'
							)) {
							
							$pn = substr($key,(strpos($key,'_') + 1));
							
							if (AUTH == 'supervisor' OR in_array($pn,$basallowed)) {
							
								
								if (
									!isset($control_students) 
									OR 
									!isset($control_students[$id])
									OR 
									!isset($control_students[$id][$pn])
									OR
									(
										$control_students[$id][$pn] != $data
									)
									) {
									
									if (!isset($save_students[$id])) {
										$save_students[$id] = Array();
									}
																
									$save_students[$id][$pn] = $data;
									
								}
							}
							
						}
					}
					
					//Neue Einträge ausfiltern
					foreach ($save_students as $id => $sdata) {
						if (substr($id,0,4) == 'TEMP') {
							if ($sdata['forename'] != '' OR $sdata['surname'] != '') { 
								$nid = get_DB_ct(DB);
								$sdata['id'] = $nid;
								$save_students[$nid] = $sdata;
								unset($save_students[$id]);
								
								foreach ($defaults as $defk => $defd) {
									if (!isset($save_students[$nid][$defk])) {
										$save_students[$nid][$defk] = $defd;
									}
								}
						
								$GLOBALS['hints'][] = '<span style="font-weight:bold;color:#090;">SchülerIn ';
								$GLOBALS['hints'][count($GLOBALS['hints']) - 1] .= isset($save_students[$nid]['forename'])?$save_students[$nid]['forename']:'?';							
								$GLOBALS['hints'][count($GLOBALS['hints']) - 1] .= ' ';							
								$GLOBALS['hints'][count($GLOBALS['hints']) - 1] .= isset($save_students[$nid]['surname'])?$save_students[$nid]['surname']:'?';	
								$GLOBALS['hints'][count($GLOBALS['hints']) - 1] .= ' (ID '.$nid.') wurde erfolgreich hinzugefügt.</span>';							
							}
						} 
					}
					
					//echo '<pre>Schüler final:'.print_r($save_students,true).'</pre>';
					
					$temp = parse_ini_file('./db/'.DB.'/class_'.CLASSFILTER.'.ini',true);
					
					foreach ($save_students as $id => $sdata) {
						foreach ($sdata as $pn => $data) {
							if (!isset($temp[$id])) { $temp[$id] = Array(); }
							$temp[$id][$pn] = $data;
						}
					}
					
					//Format wenn nötig korrigieren, zum Löschen freigegebene SuS löschen.
					foreach ($temp as $id => $sdata) {
						foreach ($defaults as $prop => $value) {
						
							if (!isset($sdata[$prop])) {
								$temp[$id][$prop] = $value;
							}
							
							if (($prop == 'offer' OR substr($prop,0,5) == 'wish_') AND isset($sdata[$prop])) {
								
								if (!isset($offers)) $offers = parse_ini_file('./db/'.DB.'/offers.ini',true);
								
								if (
									(!isset($offers[(int)$sdata[$prop]]) AND (int)$sdata[$prop] > 0 ) 
									OR (int)$sdata[$prop] < 0 
									OR (isset($offers[(int)$sdata[$prop]]['excluded']) AND parse_clstring($offers[(int)$sdata[$prop]]['excluded'],CLASSFILTER))
									OR (isset($odata['avgender']) AND in_array($gender,Array('m','f')) AND !in_array($gender,explode(',',$odata['avgender'])))
									)  {
									$temp[$id][$prop] = 0;
									$sdata[$prop] = 0;
									$GLOBALS['hints'][] = '<span class="b cb">Information: Die Angebots- bzw. Wunschzuteilung ('.$prop.') von Schüler '.$sdata['surname'].', '.$sdata['forename'].' musste wegen illegaler Angebotszuordnung zurückgesetzt werden.</span>';
								}
							} 
						
						}
						
						
						if (isset($sdata['forename']) AND isset($sdata['surname']) AND $sdata['forename'] == '' AND $sdata['surname'] == '') {
							unset($temp[$id]);
							
							if (isset($control_students) AND isset($control_students[$id]) AND ( isset($control_students[$id]['surname']) OR isset($control_students[$id]['forename']) ) ) { 
								$GLOBALS['hints'][] = '<span style="font-weight:bold;color:#090;">Die Schülerdaten mit ID '.$id.' (';
								$GLOBALS['hints'][count($GLOBALS['hints']) - 1] .= isset($control_students[$id]['forename'])?$control_students[$id]['forename']:'?';							
								$GLOBALS['hints'][count($GLOBALS['hints']) - 1] .= ' ';							
								$GLOBALS['hints'][count($GLOBALS['hints']) - 1] .= isset($control_students[$id]['surname'])?$control_students[$id]['surname']:'?';							
								$GLOBALS['hints'][count($GLOBALS['hints']) - 1] .= ') wurden erfolgreich gelöscht.</span>';							
							}
						}
					}
					
					write_ini_file('./db/'.DB.'/class_'.CLASSFILTER.'.ini',$temp);
					
					if (isset($input['classteacher'])) {
						if (isset($input['control_teacher']) AND (int)$input['control_teacher'] != (int)$input['classteacher']) {
							$temp = parse_ini_file('./db/'.DB.'/teachers.ini',true);
							
							$temp[0] = Array('class' => '');
							
							$input['control_teacher'] = (int)$input['control_teacher'];
							$input['classteacher'] = (int)$input['classteacher'];
							
							if (isset($temp[(int)$input['control_teacher']]) AND isset($temp[(int)$input['classteacher']])) {
								$otc = explode(',',$temp[$input['control_teacher']]['class']);
								
								$temp[$input['control_teacher']]['class'] = '';
								
								foreach ($otc as $otcn) {
									if ($otcn != CLASSFILTER) {
										if ($temp[$input['control_teacher']]['class'] == '') {
											$temp[$input['control_teacher']]['class'] = $otcn;
										} else {
											$temp[$input['control_teacher']]['class'] .= ','.$otcn;
										}
									}
								}
								
								if ($temp[$input['classteacher']]['class'] == '') {	
									$temp[$input['classteacher']]['class'] = CLASSFILTER;
								} elseif (!preg_match('/\b'.addslashes(CLASSFILTER).'\b/',$temp[$input['classteacher']]['class'])) {								
									$temp[$input['classteacher']]['class'] .= ','.CLASSFILTER;
								}
								
								unset($temp[0]);
								
								write_ini_file('./db/'.DB.'/teachers.ini',$temp);
								
							}
							
							unset($temp);
						}
					} 
				
					
					
					$GLOBALS['hints'][] = '<span style="font-weight:bold;color:#090;">Die Klassendaten für Klasse '.CLASSFILTER.' wurden erfolgreich bearbeitet.</span>';
					
					
					
				} else {
					$GLOBALS['hints'][] = '<span style="font-weight:bold;color:#900;">Fehlschlag - Es wurde keine Klasse ausgewählt.</span>';
				} break;
			case 'add_offer': if (isset($_REQUEST['edit_offer_name']) AND $_REQUEST['edit_offer_name']) {
				
					//Sammle Daten
					$input = Array();
					$save = Array();
					$legalKeys = Array('name','longdesc','place','time','maxtn','teacher','meet','preferred','excluded','costs','left','precosts','refundall','refund','eerkl','avgender');
					$intKeys = Array('maxtn','teacher');
					$floatKeys = Array('costs','left','precosts','refundall','refund');
					
					foreach ($_REQUEST as $key => $data) {
					
						if (substr($key,0,11) == 'edit_offer_') {
							$input[substr($key,11)] = $data;
						}				
					}
					
					foreach ($input as $key => $data) {
						if (in_array($key,$legalKeys)) {
							$save[$key] = $data;
							
							if (in_array($key,$intKeys)) {
								$save[$key] = (int)$data;
							}
							if (in_array($key,$floatKeys)) {
								$data = str_replace(',','.',$data);
								$data = preg_replace('/\D*(\d+)\.(\d\d).*/',"$1.$2",$data);
								$save[$key] = (float)$data;
							}
						}
					}
					
					foreach ($legalKeys as $key => $data) {
						if (!isset($save[$data])) {
							if (in_array($data,$intKeys)) {
								$save[$data] = 0;
							} elseif (in_array($key,$floatKeys)) {
								$save[$data] = 0.0;
							} else {
								$save[$data] = "";
							}
						}
					}
					
					if (is_file('./db/'.DB.'/offers.ini')) {
						$temp = parse_ini_file('./db/'.DB.'/offers.ini',true);
					} else {
						$temp = Array();
					}
					$ct = get_DB_ct(DB);
					$temp[$ct] = $save;
					$temp[$ct]['id'] = $ct;
					$temp[$ct]['type'] = 'offer';
					
					
					//Lehrer 'informieren'
					if (isset($save['teacher'])) {
						$key = 'teacher'; $data = $save['teacher'];
						
						$occ = Array();
				
						foreach ($temp as $oid => $odata) {
							if (isset($odata['teacher']) AND $oid != $ct) {
								$occ[] = $odata['teacher'];
							}
						}
						
						$tempt = parse_ini_file('./db/'.DB.'/teachers.ini',true);
						if ((int)$data > 0 AND isset($tempt[(int)$data]) AND $tempt[(int)$data]['available'] AND !in_array($data,$occ)) {
							$tempt[(int)$data]['offer'] = $ct;
							write_ini_file('./db/'.DB.'/teachers.ini',$tempt);
						} else {
							$data = 0;
							$GLOBALS['hints'][] = '<span style="font-weight:bold;color:#900;">Der zugewiesene Lehrer ist nicht mehr verfügbar.</span>';
							$temp[$ct][$key] = $data;
						}
									
					}
					
					write_ini_file('./db/'.DB.'/offers.ini',$temp);
					
					$GLOBALS['hints'][] = '<span style="font-weight:bold;color:#090;">Das Angebot '.$input['name'].' wurde erfolgreich erstellt.</span>';
				
				} else {
					$GLOBALS['hints'][] = '<span style="font-weight:bold;color:#900;">Fehlschlag - Für das Angebot wurde kein Name angegeben!</span>';
				} break;
			case 'edit_offer': if ( OFFER > 0 AND isset($_REQUEST['edit_offer_name']) AND $_REQUEST['edit_offer_name']) {
				
					//Sammle Daten
					$input = Array();
					$save = Array();
					$save_students = Array();
					$legalKeys = Array('id','type','name','longdesc','place','time','maxtn','teacher','meet','preferred','excluded','costs','left','precosts','refundall','refund','eerkl','hints','avgender');
					for ($i = 0; $i < 50; $i++) {
						$legalKeys[] = 'phonetags'.$i;
						$legalKeys[] = 'phonenrs'.$i;
					}
					$intKeys = Array('maxtn','teacher');
					$floatKeys = Array('costs','left','precosts','refundall','refund');
					
					foreach ($_REQUEST as $key => $data) {
					
						if (substr($key,0,11) == 'edit_offer_') {
							$input[substr($key,11)] = $data;
						}				
					}
					
					if (isset($input['control_students']) AND $input['control_students']) {
						file_put_contents('./TEMP',$input['control_students']);
						$cstu = parse_ini_file('./TEMP',true);
						unlink('./TEMP');
					} else { $cstu = Array(); }
					
					if (isset($input['control_offers']) AND $input['control_offers']) {
						file_put_contents('./TEMP',$input['control_offers']);
						$coff = parse_ini_file('./TEMP',true);
						unlink('./TEMP');
					} else { $coff = Array(); }
					
					foreach ($input as $key => $data) {
						if (in_array($key,$legalKeys)) {
							$save[$key] = $data;
							
							if (in_array($key,$intKeys)) {
								$save[$key] = (int)$data;
							}
							if (in_array($key,$floatKeys)) {
								$data = str_replace(',','.',$data);
								$data = preg_replace('/\D*(\d+)\.(\d\d).*/',"$1.$2",$data);
								$save[$key] = (float)$data;
							}
						} elseif (substr($key,0,9) == 'students_') {
							$nk = substr($key,9);
							$t_cn = substr($nk,0,strpos($nk,'_'));
							$nk = substr($nk,(strpos($nk,'_')+1));
							$t_id = substr($nk,0,strpos($nk,'_'));
							$nk = substr($nk,(strpos($nk,'_')+1));
							if ($nk == 'cancelled') {
								if (!isset($cstu[$t_id]) OR !isset($cstu[$t_id]['cancelled']) OR (int)$cstu[$t_id]['cancelled'] != (int)$data ) {
									if (!isset($save_students[$t_cn])) $save_students[$t_cn] = Array();
									if (!isset($save_students[$t_cn][$t_id])) $save_students[$t_cn][$t_id] = Array();
									$save_students[$t_cn][$t_id]['cancelled'] = (int)$data;
								} 
							}
						}
					}
					
					
					
					if (is_file('./db/'.DB.'/offers.ini')) {
					
						$temp = parse_ini_file('./db/'.DB.'/offers.ini',true);
							
						if (isset($temp[OFFER])) {
								
							foreach ($save as $key => $data) {
							
								if (!isset($coff[OFFER]) OR !isset($coff[OFFER][$key]) OR $data != $coff[OFFER][$key]) {
								
									
									//Lehrer muss auch 'informiert' werden...
									if ($key == 'teacher' AND AUTH == 'supervisor') {
									
										$occ = Array();
				
										foreach ($temp as $oid => $odata) {
											if (isset($odata['teacher']) AND $oid != OFFER) {
												$occ[] = $odata['teacher'];
											}
										}
									
										$tempt = parse_ini_file('./db/'.DB.'/teachers.ini',true);
										if (isset($tempt[(int)$data]) AND $tempt[(int)$data]['available'] AND !in_array($data,$occ) ) {
											$tempt[(int)$data]['offer'] = OFFER;
											write_ini_file('./db/'.DB.'/teachers.ini',$tempt);
										} else {
											$data = 0;
											$GLOBALS['hints'][] = '<span style="font-weight:bold;color:#900;">Der zugewiesene Lehrer ist nicht mehr verfügbar.</span>';
										}
										
										$temp[OFFER][$key] = $data;
										
									} elseif ($key == 'teacher') {
										$GLOBALS['hints'][] = '<span style="font-weight:bold;color:#900;">Sie haben keine Berechtigung, Lehrer zu Angeboten zuzuweisen.</span>';
									} else {								
										$temp[OFFER][$key] = $data;
									}
								
								} else { 
									//$GLOBALS['hints'][] = '<span style="font-weight:bold;color:#009;">Die Eigenschaft "'.$key.'" blieb unverändert.</span>';
								}
								
							}
							
							//leere Telefonnummern entsorgen
							for ($i = 0; $i < 50; $i++) {
								if (isset($temp[OFFER]['phonetags'.$i]) AND $temp[OFFER]['phonetags'.$i] == '') {
									unset($temp[OFFER]['phonetags'.$i]);
									if (isset($temp[OFFER]['phonenrs'.$i])) unset($temp[OFFER]['phonenrs'.$i]);
								} elseif (!isset($temp[OFFER]['phonetags'.$i])) {
									if (isset($temp[OFFER]['phonenrs'.$i])) unset($temp[OFFER]['phonenrs'.$i]);
								}
							}
							
							write_ini_file('./db/'.DB.'/offers.ini',$temp);
							
							foreach ($save_students as $cn => $cd) {

								if (is_file('./db/'.DB.'/class_'.$cn.'.ini')) {
									$temp = parse_ini_file('./db/'.DB.'/class_'.$cn.'.ini',true);
								
									foreach ($cd as $id => $data) {
										
										if (isset($temp[$id])) {
											$temp[$id]['cancelled'] = $data['cancelled'];
										} else {
											$GLOBALS['hints'][] = '<span style="font-weight:bold;color:#900;">Einer der bearbeiteten Schüler existiert nicht mehr.</span>';
										}
										
									}
									
									write_ini_file('./db/'.DB.'/class_'.$cn.'.ini',$temp);
								} else {
									$GLOBALS['hints'][] = '<span style="font-weight:bold;color:#900;">Die gesamte Klasse ('.$cn.') eines bearbeiteten Schülers existiert nicht mehr.</span>';
								}
							}
							
							$GLOBALS['hints'][] = '<span style="font-weight:bold;color:#090;">Das Angebot '.$input['name'].' wurde erfolgreich bearbeitet.</span>';
						} else {
							$GLOBALS['hints'][] = '<span style="font-weight:bold;color:#900;">Das gewählte Angebot existiert leider nicht mehr.</span>';
						}
					} else {
						$GLOBALS['hints'][] = '<span style="font-weight:bold;color:#900;">Fataler Fehler: Für die Datenbank wurden keine Angebotsdaten gefunden!</span>';
					}
					
				} elseif (isset($_REQUEST['edit_offer_name']) AND OFFER > 0) {
					if (AUTH == 'supervisor') {
						$temp = parse_ini_file('./db/'.DB.'/offers.ini',true);
						$tn = $temp[OFFER]['name'];
						if (isset($temp[OFFER])) {
							unset($temp[OFFER]);
						}
						write_ini_file('./db/'.DB.'/offers.ini',$temp);
						
						$GLOBALS['hints'][] = '<span style="font-weight:bold;color:#090;">Das Angebot '.$tn.' wurde erfolgreich gelöscht.</span>';
					} else {
						$GLOBALS['hints'][] = '<span style="font-weight:bold;color:#900;">Fehlschlag - Sie besitzen nicht die Berechtigung um Angebote zu löschen (sollten Sie das nicht vorgehabt haben, achten Sie darauf, einen Namen für das Angebot zu vergeben).</span>';
					}
				} else {
					$GLOBALS['hints'][] = '<span style="font-weight:bold;color:#900;">Fehlschlag - Kein Angebot ausgewählt bzw. allgemeiner Fehler.</span>';
				} break;
			case 'edit_buses': {
					
					$input = Array();
					$save_buses = Array();
					$save_carpools = Array();
					$ref_of = Array();
					$control_buses = Array();
					$control_carpools = Array();
					
					$default_bus = Array('type'=>'bus','company'=>'','tag'=>'','capacity'=>0,'carpool'=>0);
					$default_carpool = Array('type'=>'carpool','name'=>'','offers'=>'');
					
					foreach ($_REQUEST as $key => $data) {
						
						if (substr($key,0,11) == 'edit_buses_') {
							$input[substr($key,11)] = $data; 
						}
					
					}
					
					if (isset($input['control_buses'])) {
						file_put_contents('./TEMP',$input['control_buses']);
						$control_buses = parse_ini_file('./TEMP',true);
						unlink('./TEMP');
					}
					
					if (isset($input['control_carpools'])) {
						file_put_contents('./TEMP',$input['control_carpools']);
						$control_carpools = parse_ini_file('./TEMP',true);
						unlink('./TEMP');
					}
					
					foreach ($input as $key => $data) {
						
						if (substr($key,0,4) == 'bus_') {
							$bid = strstr(substr($key,4),'_',true);
							$prop = substr(strstr(substr($key,4),'_'),1);
							
							if (array_key_exists($prop,$default_bus)) {
								if (
									!isset($control_buses[$bid])
									OR
									!isset($control_buses[$bid][$prop])
									OR
									$control_buses[$bid][$prop] != $data
									) {
									
									if (!isset($save_buses[$bid])) $save_buses[$bid] = Array();
										
										$save_buses[$bid][$prop] = $data;
									
								}
							}
						}
						
						if (substr($key,0,3) == 'cp_') {
							$bid = strstr(substr($key,3),'_',true);
							$prop = substr(strstr(substr($key,3),'_'),1);
							
							if (array_key_exists($prop,$default_carpool)) {
								if (
									!isset($control_carpools[$bid])
									OR
									!isset($control_carpools[$bid][$prop])
									OR
									$control_carpools[$bid][$prop] != $data
									) {
									
									if (!isset($save_carpools[$bid])) $save_carpools[$bid] = Array();
										
										$save_carpools[$bid][$prop] = $data;
									
								}
							}
						}
						
						if (substr($key,0,4) == 'off_' AND (!is_numeric($data) OR (int)$data > 0)) {
							$oid = substr($key,4);
							
							if (!isset($ref_of[$data])) $ref_of[$data] = Array();	
							$ref_of[$data][] = $oid;
						}
					
					}
					
					foreach ($save_carpools as $bid => $bdata) {
						if (substr($bid,0,4) == 'TEMP') {
							if (isset($bdata['name']) AND $bdata['name']) {
								$ct = get_DB_ct(DB);
								$save_carpools[$ct] = $bdata;
								$save_carpools[$ct]['id'] = $ct;
								if (isset($ref_of[$bid])) {
									$ref_of[$ct] = $ref_of[$bid];
									unset($ref_of[$bid]);
								}
							}
							unset($save_carpools[$bid]);
						}
					}
					
					foreach ($ref_of as $cpid => $oids) {
						natcasesort($oids);
						$temp = '';
						foreach ($oids as $oid) { 
							if ($temp != '') $temp .= ','; 
							$temp .= $oid;
						}
						
						if (
							!isset($control_carpools[$cpid])
							OR
							!isset($control_carpools[$cpid]['offers'])
							OR
							$control_carpools[$cpid]['offers'] != $temp
							) {
							
							if (!isset($save_carpools[$cpid])) $save_carpools[$cpid] = Array();
								
								$save_carpools[$cpid]['offers'] = $temp;
							
						}
						
					}
						
					foreach ($save_buses as $bid => $bdata) {
						if (substr($bid,0,4) == 'TEMP') {
							if (isset($bdata['tag']) AND $bdata['tag']) {
								$ct = get_DB_ct(DB);
								$save_buses[$ct] = $bdata;
								$save_buses[$ct]['id'] = $ct;
							}
							unset($save_buses[$bid]);
						}
					}				
					
					if (count($save_buses)) {
						$temp = parse_ini_file('./db/'.DB.'/buses.ini',true);
						foreach ($save_buses as $bid => $bdata) {
							foreach ($bdata as $prop => $data) {
								if (!isset($temp[$bid])) $temp[$bid] = Array();
								$temp[$bid][$prop] = $data;
							}
							foreach ($default_bus as $prop => $data) {
								if (!isset($temp[$bid])) $temp[$bid] = Array();
								if (!isset($temp[$bid][$prop])) $temp[$bid][$prop] = $data;
							}
						}
						
						foreach ($temp as $bid => $bdata) {
							if (isset($temp[$bid]['tag']) AND $temp[$bid]['tag'] == '') {
								unset($temp[$bid]);
								$GLOBALS['hints'][] = '<span style="font-weight:bold;color:#090">Der Bus '.$control_buses[$bid]['tag'].' wurde ordnungsgemäß gelöscht.</span>';
							}
						}
						
						write_ini_file('./db/'.DB.'/buses.ini',$temp);
						
						$GLOBALS['hints'][] = '<span style="font-weight:bold;color:#090">Die Änderungen an der Busliste wurden erfolgreich vorgenommen.</span>';
					} else {
						$GLOBALS['hints'][] = '<span style="font-weight:bold;color:#009">An der Busliste wurden keine Änderungen vorgenommen.</span>';
					}
					
					if (count($save_carpools)) {
						$temp = parse_ini_file('./db/'.DB.'/carpools.ini',true);
						foreach ($save_carpools as $bid => $bdata) {
							foreach ($bdata as $prop => $data) {
								if (!isset($temp[$bid])) $temp[$bid] = Array();
								$temp[$bid][$prop] = $data;
							}
							foreach ($default_carpool as $prop => $data) {
								if (!isset($temp[$bid])) $temp[$bid] = Array();
								if (!isset($temp[$bid][$prop])) $temp[$bid][$prop] = $data;
							}
						}
						
						foreach ($temp as $bid => $bdata) {
							if (isset($temp[$bid]['name']) AND $temp[$bid]['name'] == '') {
								unset($temp[$bid]);
								$GLOBALS['hints'][] = '<span style="font-weight:bold;color:#090">Die Fahrgemeinschaft '.$control_carpools[$bid]['name'].' wurde ordnungsgemäß gelöscht.</span>';
							}
						}
						
						write_ini_file('./db/'.DB.'/carpools.ini',$temp);

						$GLOBALS['hints'][] = '<span style="font-weight:bold;color:#090">Die Änderungen an den Fahrgemeinschaften wurden erfolgreich vorgenommen.</span>';
					} else {
						$GLOBALS['hints'][] = '<span style="font-weight:bold;color:#009">An den Fahrgemeinschaften wurden keine Änderungen vorgenommen.</span>';
					}
					
				} break;
		
			case 'management_archive': {
			
					$zip = new ZipArchive();
					$filename = 'archiv/'.urlencode($info['info']['name']).'__id__'.DB.'.zip';
					
					$i = 0;
					while (is_file($filename)) {
						$filename = 'archiv/'.urlencode($info['info']['name']).'_'.$i.'__id__'.DB.'.zip';
						$i++;
					}
					
					$res = $zip->open($filename, ZipArchive::CREATE);
			
					if (!empty($_SERVER['HTTPS']) && ('on' == $_SERVER['HTTPS'])) {
						$uri = 'https://';
					} else {
						$uri = 'http://';
					}
					$uri .= $_SERVER['HTTP_HOST'];
					
					
					$resf = true;
					
					if ($res === true) {
						
						$folders = Array('');//,'deactivated/');
						
						foreach ($folders as $folder) {
						
							$files = scandir('db/'.DB.'/'.$folder);					
									
							foreach ($files as $file) {
								if (is_file('db/'.DB.'/'.$folder.$file)) {							
									$resft = $zip->addFile('db/'.DB.'/'.$folder.$file,$folder.$file);
									$resf = ($resf AND $resft);
									$rest = $zip->close();
									$res = ($res AND $rest);
									
									$rest = $zip->open($filename);
									$res = ($res AND $rest);
								} elseif (is_dir('db/'.DB.'/'.$folder.$file)) {
									$folders[] = $folder.$file.'/';
								}
							}
						}
							
						if ($res AND $resf) {	
						
							$res = rename('./db/'.DB,'./trashcan/'.DB);
							if ($res) {
								$bdir = './trashcan/'.DB;
							} else {
								$bdir = './db/'.DB;
							}
							
							$dirs = Array();
							$ndirs = Array($bdir);
							$dircounter = Array();
							$success = true;
							while (count($ndirs) > 0) {
								
								$dirs = $ndirs;
								$ndirs = Array();
								
								foreach ($dirs as $dir) {
									if (is_dir($dir) AND substr($dir,0,11) == './trashcan/') {
										$files = scandir($dir);
										foreach ($files as $file) {
											if ($file != '.' AND $file != '..') {
												if (is_file($dir.'/'.$file)) unlink($dir.'/'.$file);
												if (is_dir($dir.'/'.$file))	$ndirs[] = $dir.'/'.$file;	
											}
										}
										
										$res = rmdir($dir);
										
										if ($res === false AND $dircounter[$dir] < 10) {
											$ndirs[] = $dir;
											if (!isset($dircounter[$dir])) $dircounter[$dir] = 0;
											$dircounter[$dir]++;
										} elseif ($res === false) {
											$success = false;
										}
									} else {
										break 2;
									}
								}
									
								
								
							}
							
							if ($success) {
								header('Location: '.$uri.'/pvs/?logout=true&hint='.urlencode('Das Event '.$info['info']['name'].' wurde erfolgreich archiviert.'));
							} else {
								header('Location: '.$uri.'/pvs/?logout=true&hint='.urlencode('Das Event '.$info['info']['name'].' wurde erfolgreich archiviert. Datenrückstände konnten leider nicht vermieden werden; es wurde versucht, den Datenmüll in den PVS-Papierkorb einzuschleußen. Bitte informieren Sie den Systemadministrator, damit die vollständige Löschung vorgenommen werden kann.'));
							}
						} else {
							header('Location: '.$uri.'/pvs/?view=default&hint='.urlencode('Das Event '.$info['info']['name'].' konnte nicht archiviert werden, da bei der Komprimierung ein Fehler aufgetreten ist.'));
						}
					} else {
						header('Location: '.$uri.'/pvs/?view=default&hint='.urlencode('Das Event '.$info['info']['name'].' konnte nicht archiviert werden, da das Eröffnen eines ZIP-Archivs vom System abgelehnt wurde.'));
					}
				} break;
			
			case 'edit_payments': if (CLASSFILTER != 'N/A') {
					
					$classdata = parse_ini_file('./db/'.DB.'/class_'.CLASSFILTER.'.ini',true);
					
					$bool = false;
					
					foreach ($classdata as $sid => $sdata) {
						
						if (isset($_REQUEST['edit_payments_'.$sid.'_deposit'])) {
						
							$deposit = 0.0;
							
							$erg = Array();
							
							$string = preg_replace('/[,]/','.',$_REQUEST['edit_payments_'.$sid.'_deposit']);
							
							
							if (preg_match('/(\D*)(\d+(\.\d{0,2})?)\D*/',$string,$erg)) {
								
								if (isset($erg[2]) AND is_numeric($erg[2]) AND (float)$erg[2] > 0.0) {
								
									$deposit = (float)$erg[2];
								
									
								} 
								
								if (isset($erg[1]) AND preg_match('/\D*-\s*/',$erg[1])) {
									
									$deposit = 0.0 - $deposit;
									
								}
								
							}
						
							if (!isset($sdata['paid'])) $classdata[$sid]['paid'] = 0.0;
							
							if ($deposit != 0.0) {
								
								$classdata[$sid]['paid'] = ((float)$classdata[$sid]['paid']) + $deposit;
								$bool = true;
							
							}
						}					
					
					}
					
					if ($bool) {
					
						write_ini_file('./db/'.DB.'/class_'.CLASSFILTER.'.ini',$classdata);
					
					}
					
					$GLOBALS['hints'][] = '<span style="font-weight:bold;color:#090;">Die Zahlungsdaten für Klasse '.CLASSFILTER.' wurden erfolgreich bearbeitet.</span>';
					
					
					
				} else {
					$GLOBALS['hints'][] = '<span style="font-weight:bold;color:#900;">Fehlschlag - Es wurde keine Klasse ausgewählt.</span>';
				} break;
				
			case 'management_autofill_offers': {
					
					//Datenbanken laden
					$eventid = DB;
					$offers = is_file('./db/'.$eventid.'/offers.ini')?parse_ini_file('./db/'.$eventid.'/offers.ini',true):Array();
					uasort($offers,'sort_db');
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
					
					$zugeteilt = Array();
					$wishes = Array();
					$avwishes = Array();
					
					$counters = Array();
					$counters['pre'] = 0; 
					$counters['not'] = 0; 
					for ($i = 0; $i < $info['props']['max_wishes']; $i++) $counters['w'.$i] = 0;
					
					$continuance = isset($_REQUEST['flags_continuance'])?(bool)$_REQUEST['flags_continuance']:true;
					
					//Arrays initialisieren: $zugeteilt, $wishes
					foreach ($offers as $oid => $odata) {
						$zugeteilt[$oid] = Array();
						$wishes[$oid] = Array();
						for ($i = 0; $i < $info['props']['max_wishes']; $i++) $wishes[$oid][$i] = Array();
					}
					
					//Alle Schüler durchgehen; Bestandsschutz und Wünsche eintragen
					foreach ($students as $sid => $sdata) {
						
						if ($continuance AND isset($sdata['offer']) AND $sdata['offer'] > 0 AND isset($zugeteilt[$sdata['offer']])  AND (!isset($offers[$sdata['offer']]['avgender']) OR !isset($students[$sid]['gender']) OR !in_array($students[$sid]['gender'],Array('m','f')) OR in_array($students[$sid]['gender'],explode(',',$offers[$sdata['offer']]['avgender'])))) {
							
							$zugeteilt[$sdata['offer']][] = $sid;
							$counters['pre']++;
						
						} else {
						
							$tcounter = 0;
						
							for ($i = 0; $i < $info['props']['max_wishes']; $i++) {
							
								if (isset($sdata['wish_'.$i]) AND $sdata['wish_'.$i] > 0 AND isset($wishes[$sdata['wish_'.$i]]) AND (!isset($offers[$sdata['wish_'.$i]]['avgender']) OR !isset($students[$sid]['gender']) OR !in_array($students[$sid]['gender'],Array('m','f')) OR in_array($students[$sid]['gender'],explode(',',$offers[$sdata['wish_'.$i]]['avgender'])) )) {
								
									$wishes[$sdata['wish_'.$i]][$i][] = $sid;
									if (!isset($avwishes[$sid])) $avwishes[$sid] = Array();
									$avwishes[$sid][$sdata['wish_'.$i]] = $i;
									$tcounter++;
								
								}
							
							}
							
							if ($tcounter == 0) {
								$counters['not']++;
								if (isset($avwishes[$sid])) unset($avwishes[$sid]);
							}
						
						}
						
					}
	
					//Problematik-Faktor iniziieren
					$probl = Array();
					
					for ($durchgang = 0; $durchgang < count($zugeteilt); $durchgang++) {
					
						//wenn nichts passiert ist kein weiterer Durchgang nötig
						$somethinghappened = false;
					
						//Zuteilung freier Plätze - Stufe 1: Angebote mit ausreichender Platzanzahl, Zuteilung Erstwünsche!
						foreach ($zugeteilt as $oid => $zdata) {
							
							//Problematik zunächst initialisieren
							$probl[$oid] = 0;
								
							$frei = $offers[$oid]['maxtn'] - count($zugeteilt[$oid]);
							
							if ($frei > 0) {
								
								$tstd = Array();
								
								//Summiere alle Schüler, die hier rein wollen - Egal ob Erstwunsch, Zweitwunsch oder Drittwunsch
								foreach ($wishes[$oid] as $wnr => $wst) {
								
									//Wir können alle zugeteilten aus den Wunscharrays schmeißen.
									$tzz = Array();
									foreach ($zugeteilt as $tzdata) {
										foreach ($tzdata as $tid) {
											$tzz[] = $tid;
										}
									}
									foreach ($wishes as $woid => $wdata) {
										foreach ($wdata as $twnr => $twst) {
											foreach ($twst as $key => $tsid) {
												if (in_array($tsid,$tzz)) {
													unset($wishes[$woid][$twnr][$key]);
												}
											}
										}
									}
									
									unset($tzz);
								
									//Überprüfe nun pro Wunschnummer, ob es Interessenten gibt
									foreach ($wishes[$oid][$wnr] as $sid) {
										if (!in_array($sid,$tstd) AND !in_array($sid,$zugeteilt[$oid])) {
											$tstd[] = $sid;
										}
									}
								}
								
								//Prüfe nun, ob das Angebot ein unproblematisches ist
								if ($frei >= count($tstd)) {
									// Wenn ja, dann teile alle Interessenten mit Erstwunsch zu!
									if (isset($wishes[$oid][0]) AND count($wishes[$oid][0]) > 0) {						
										foreach ($wishes[$oid][0] as $key => $sid) {
											$zugeteilt[$oid][] = $sid;
											if (isset($avwishes[$sid])) unset($avwishes[$sid]);
											$frei = $offers[$oid]['maxtn'] - count($zugeteilt[$oid]); 
											$counters['w0']++;
										}
									}
									
									//und das Angebot ist endgültig unproblematisch...
									$probl[$oid] = 0;
									
									//und es ist etwas passiert, was eventuell einen neuen Durchlauf nötig macht
									$somethinghappened = true;
								
								} else {
								
									//Zwischenfrage: Passiert hier was?
									if ($probl[$oid] != (count($tstd) - $frei)) { $somethinghappened = true; }
								
									//Ansonsten ist das Angebot problematisch.
									$probl[$oid] = count($tstd) - $frei;
								
								}							
								
								unset($tstd);
								
							}
						}
					
						if (!$somethinghappened) {
							break;
						}
					
					}
					
					//Sortiere Problematik-Array... Das problematischste zuerst.
					arsort($probl);
					
					//Zuteilung freier Plätze - Stufe 2: Problematische Angebote, Suche nach bestem 'Match' - Geordnet nach Problematik
					foreach ($probl as $oid => $tprobl) {
					
						$zdata = $zugeteilt[$oid];
					
						$frei = $offers[$oid]['maxtn'] - count($zugeteilt[$oid]);
						
						if ($frei > 0) {
							//wenn freie plätze, dann gehe wunsch für wunsch vor
							foreach ($wishes[$oid] as $wnr => $wst) {
						
								//Wir können alle zugeteilten aus den Wunscharrays schmeißen.
								$tzz = Array();
								foreach ($zugeteilt as $tzdata) {
									foreach ($tzdata as $tid) {
										$tzz[] = $tid;
									}
								}
								foreach ($wishes as $woid => $wdata) {
									foreach ($wdata as $twnr => $twst) {
										foreach ($twst as $key => $tsid) {
											if (in_array($tsid,$tzz)) {
												unset($wishes[$woid][$twnr][$key]);
											}
										}
									}
								}
						
								$pref = Array();
								$npref = Array();
							
								//sortiere nach Präferenz
								foreach ($wishes[$oid][$wnr] as $sid) {
									
									if (!in_array($sid,$zugeteilt[$oid])) {
										
										if (isset($students[$sid]['class']) AND isset($offers[$oid]['preferred']) AND parse_clstring($offers[$oid]['preferred'],$students[$sid]['class']) ) {
											$pref[] = $sid;
										} else {
											$npref[] = $sid;
										}
										
									}
								}
		
		
								//Wenn weniger bevorzugte als freie, dann werfe alle bevorzugten hinein
								if (count($pref) <= $frei) {
									foreach ($pref as $key => $sid) {
										$zugeteilt[$oid][] = $sid;
										unset($pref[$key]);
										if (isset($avwishes[$sid])) unset($avwishes[$sid]);
										$frei = $offers[$oid]['maxtn'] - count($zugeteilt[$oid]); 
										$counters['w'.$wnr]++;
									}
								} else {
									
									while($frei > 0 AND count($pref)) {
									
										$key = array_rand($pref);
										$sid = $pref[$key];
									
										$zugeteilt[$oid][] = $sid;
										unset($pref[$key]);
										if (isset($avwishes[$sid])) unset($avwishes[$sid]);
										$frei = $offers[$oid]['maxtn'] - count($zugeteilt[$oid]);
										$counters['w'.$wnr]++;
									
									}
								
								}
								
								//Danach selbes Prozedere mit normalos							
								if (count($npref) <= $frei) {
									foreach ($npref as $key => $sid) {
										$zugeteilt[$oid][] = $sid;
										unset($npref[$key]);
										if (isset($avwishes[$sid])) unset($avwishes[$sid]);
										$frei = $offers[$oid]['maxtn'] - count($zugeteilt[$oid]); 
										$counters['w'.$wnr]++;
									}
								} else {
									
									while($frei > 0 AND count($npref)) {
									
										$key = array_rand($npref);
										$sid = $npref[$key];
									
										$zugeteilt[$oid][] = $sid;
										unset($npref[$key]);
										if (isset($avwishes[$sid])) unset($avwishes[$sid]);
										$frei = $offers[$oid]['maxtn'] - count($zugeteilt[$oid]); 
										$counters['w'.$wnr]++;
									
									}
								
								}
							
							}
						
						}
						
						//Wir haben jetzt alle Plätze zugeteilt
						
						if ($frei <= 0) {
						
							foreach ($avwishes as $sid => $twishes) {
							
								//Wenn jemand das aktuelle Angebot will -> Pech gehabt
								if (isset($twishes[$oid])) { unset($avwishes[$sid][$oid]); }
								
								//Wenn nur noch ein Wunschangebot übrig ist...
								if (count($avwishes[$sid]) == 1) {
									
									foreach ($avwishes[$sid] as $woid=>$wnr) {
										//...versuche den armen Schüler da zuzuteilen...
										if (count($zugeteilt[$woid]) < $offers[$woid]['maxtn']) {
											
											$zugeteilt[$woid][] = $sid;
											if (isset($avwishes[$sid])) unset($avwishes[$sid]); 
											$counters['w'.$wnr]++;

										} else {
											//...und wenn nicht dann nicht.
											unset($avwishes[$sid]);
											$counters['not']++;
										}
									}
								
								} elseif (count($avwishes[$sid]) == 0) {
									//Wer schon ganz ausgebucht ist hatte Pech!
									unset($avwishes[$sid]);
									$counters['not']++;
								}
							
							}
						
						}
					
						//Wir können alle zugeteilten aus den Wunscharrays schmeißen.
						$tzz = Array();
						foreach ($zugeteilt as $tzdata) {
							foreach ($tzdata as $tid) {
								$tzz[] = $tid;
							}
						}
						foreach ($wishes as $woid => $wdata) {
							foreach ($wdata as $twnr => $twst) {
								foreach ($twst as $key => $tsid) {
									if (in_array($tsid,$tzz)) {
										unset($wishes[$woid][$twnr][$key]);
									}
								}
							}
						}
					}
					
					foreach ($classes as $cln => $cld) {
						foreach ($cld as $sid => $sd) {
							$classes[$cln][$sid]['offer'] = 0;
						}
					}
					
					foreach ($zugeteilt as $oid => $odata) {
						foreach ($odata as $sid) {
							$classes[$students[$sid]['class']][$sid]['offer'] = $oid;
						}
					}
					
					foreach ($classes as $cln => $cld) {
						write_ini_file('./db/'.DB.'/class_'.$cln.'.ini',$cld);
					}
					
					$GLOBALS['hints'][] = '<span class="b cg">Automatische Schülerverteilung vollzogen.</span>';
					
					$counters['w'] = 0;
					for ($i = 0; $i < $info['props']['max_wishes']; $i++) {
						if (isset($counters['w'.$i])) $counters['w'] = $counters['w'] + $counters['w'.$i];
					}
					
					$GLOBALS['hints'][] = '<span class="b cb">Es wurden von insgesamt '.count($students).' Schülern '.$counters['w'].' ihren Wünschen entsprechend verteilt ('.$counters['pre'].' weitere haben den Einstellungen entsprechend ihre Zuordnung behalten). '.$counters['not'].' Schüler konnten nicht zugeordnet werden.</span>';
					for ($i = 0; $i < $info['props']['max_wishes']; $i++) {
						$GLOBALS['hints'][] = '<span class="b cb">Dabei wurde '.$counters['w'.$i].' mal der '.($i+1).'. Wunsch berücksichtigt.</span>';
					}
					
				
				} break;
		
			case 'management_autofill_busses': {
			
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
					
			
					foreach ($_REQUEST['flags_carpools'] as $cpid) {
					
						if (isset($carpools[$cpid])) {
						
							$cpdata = $carpools[$cpid];
							
							$cpoff = explode(',',$cpdata['offers']);
							
							$tteachers = Array();
							$tclasses = Array();
							$needed = 0;
							
							foreach ($cpoff as $oid) {
							
								foreach ($teachers as $tid => $tdata) {
									if (isset($tdata['offer']) AND $tdata['offer'] > 0 AND isset($offers[$tdata['offer']]) AND $tdata['offer'] == $oid ) {
										if (!isset($tteachers[$tid])) $needed++;
										$tteachers[$tid] = $tdata;
									}
								}
								
								foreach ($students as $tid => $tdata) {
									if (isset($tdata['offer']) AND $tdata['offer'] > 0 AND isset($offers[$tdata['offer']]) AND isset($tdata['class'])  AND $tdata['offer'] == $oid) {
										if (!isset($tclasses[$tdata['class']])) $tclasses[$tdata['class']] = Array();
										if (!isset($tclasses[$tdata['class']][$tid])) $needed++;
										$tclasses[$tdata['class']][$tid] = $tdata;
									}
								}
							
							}
							
							$frei = 0;
							
							uksort($tclasses,'strnatcasecmp');
							
							$tbuses = Array();
							$zugeteilt = Array();
							
							foreach ($buses as $bid => $bdata) {
							
								if (isset($bdata['carpool']) AND $bdata['carpool'] == $cpid) {
									
									$tbuses[$bid] = $bdata;
									$zugeteilt[$bid] = Array();
									$zugeteilt[$bid]['teachers'] = Array();
									$frei += $bdata['capacity'];
									
								}
								
							}
							
							if ($frei >= $needed) {
								
								if (count($tbuses > 0)) {
									if (count($tbuses) == 1) {
										foreach ($tbuses as $bid => $bdata) {
											foreach ($tclasses as $cln => $std) {
												$zugeteilt[$bid][$cln] = $std;
												unset($tclasses[$cln]);
											}
											foreach ($tteachers as $tid => $tdata) {
												$zugeteilt[$bid]['teachers'][$tid] = $tdata;
												unset($tteachers[$tid]);
											}
										}
									} elseif (count($tbuses) > 1) {
									
										foreach ($tbuses as $bid => $bdata) {
										
											$stillNeeded = 0;
											foreach ($tclasses as $tcn => $std) { $stillNeeded += count($std); }
											
											if (($stillNeeded+count($tteachers)) > 0) { 
												$lpl = floor($bdata['capacity']*count($tteachers)/($stillNeeded+count($tteachers)));
											} else { $lpl = 0; }	
												
											$stillFree = $bdata['capacity'] - $lpl;
											
											foreach ($tclasses as $cln => $std) {
												if (count($std) <= ($stillFree - 4)) { 
													$stillFree = $stillFree - count($std);
													$zugeteilt[$bid][$cln] = $std;
													unset($tclasses[$cln]);
												} 
											}

											//Die letzten 4 Plätze füllen
											//suchen nach entsprechender Klasse
											foreach ($tclasses as $cln => $std) {
												if (count($std) == ($stillFree)) { 
													$zugeteilt[$bid][$cln] = $std;
													unset($tclasses[$cln]);
													$stillFree = 0;
													break;
												} 
											}
											//keine entsprechende Klasse gefunden, versuche weitere Klasse mit 4 Schülern mehr zu finden
											if ($stillFree > 0) {
												foreach ($tclasses as $cln => $std) {
													if (count($std) >= ($stillFree+4)) { 
														
														$zugeteilt[$bid][$cln] = Array();
														
														$tcount = 1;
														$keys = array_rand($tclasses[$cln],($stillFree+4));
														foreach ($keys as $key) {
															if ($tcount <= ($stillFree)) {
																$zugeteilt[$bid][$cln][$key] = $tclasses[$cln][$key];
																unset($tclasses[$cln][$key]);
																$tcount++;
															} else {
																break;
															}
														}
														
														$stillFree = 0;
														break;
													} 
												}
											}
											//versuche gesamte Klasse herauszunehmen und suche dann nochmal nach Lösung
											if ($stillFree > 0) {
											
												foreach ($tclasses as $cln => $std) {
													
													$deff = count($std)-$stillFree;
													foreach ($zugeteilt[$bid] as $bcln => $bstd) {
														if (count($bstd) == $deff) {
															//gesetzte Klasse herausnehmen
															if ($isset($tclasses[$bcln])) $tclasses[$bcln] = Array();
															foreach ($bstd as $sid => $sdata) {
																$tclasses[$bcln][$sid] = $sdata;
															}
															unset($zugeteilt[$bid][$bcln]);
															//neue Klasse setzen
															$zugeteilt[$bid][$cln] = $std;
															unset($tclasses[$cln]);
															
															$stillFree = 0;
															break 2;
														}
													}
												}
											}
											//Teile Schüler frei zu, da alle Lösungen gescheitert sind.
											if ($stillFree > 0) {
											
												foreach ($tclasses as $cln => $std) {
													$zugeteilt[$bid][$cln] = Array();
													foreach ($std as $sid => $sdata) {
														$zugeteilt[$bid][$cln][$sid] = $sdata;
														unset($tclasses[$cln][$sid]);
														$stillFree = $stillFree - 1;
														if ($stillFree == 0) break;
													}
													if (count($tclasses[$cln]) == 0) unset($tclasses[$cln]);
													if ($stillFree == 0) break;
												}
												
											}
											
											//Teile jetzt noch Lehrer zu
											$tzt = Array();
											foreach ($zugeteilt[$bid] as $cln => $std) {
												if (count($tzt) < $lpl) {
													if ($key = get_class_teacher($cln,$tteachers)) {
														$tzt[$key] = $tteachers[$key];
														unset($tteachers[$key]);
													}
												} else {
													break;
												}
											}
											if (count($tzt) < $lpl) {
												foreach ($tteachers as $tid => $tdata) {
													if (count($tzt) < $lpl) {
														if (!isset($tdata['class']) OR (int)$tdata['class'] <= 0 OR !array_key_exists($tdata['class'],$tclasses)) {
															$tzt[$tid] = $tteachers[$tid];
															unset($tteachers[$tid]);
														}
													} else {
														break;
													}
												}
											} 
											if (count($tzt) < $lpl AND count($tteachers)) {
												$tmp = array_rand($tteachers,min(($lpl-count($tzt)),count($tteachers)));
												if (!is_array($tmp)) $tmp = Array($tmp);
												foreach ($tmp as $key) {
													$tzt[$key] = $tteachers[$key];
													unset($tteachers[$key]);
												}
											}
											
											$zugeteilt[$bid]['teachers'] = $tzt; 
											
											//Übrige Klassen wieder ordnen!
											uksort($tclasses,'strnatcasecmp');
										
										}
									
									}
								}
								
								foreach ($zugeteilt as $bid => $btn) {
									
									foreach ($btn as $cln => $std) {
									
										if ($cln == 'teachers') {
											
											$temp = parse_ini_file('./db/'.DB.'/teachers.ini',true);
											foreach ($std as $tid => $tdata) {
											
												if (isset($temp[$tid])) {
													$temp[$tid]['bus'] = $bid;
												}
											
											}
											write_ini_file('./db/'.DB.'/teachers.ini',$temp);
											
										} else {
										
											$temp = parse_ini_file('./db/'.DB.'/class_'.$cln.'.ini',true);
											foreach ($std as $tid => $tdata) {
											
												if (isset($temp[$tid])) {
													$temp[$tid]['bus'] = $bid;
												}
											
											}
											write_ini_file('./db/'.DB.'/class_'.$cln.'.ini',$temp);
										
										}
									
									}
								
								}
								
								$GLOBALS['hints'][] = '<span class="b cg">Die Buszuteilung für Fahrgemeinschaft '.$carpools[$cpid]['name'].' ist abgeschlossen. Für detaillierte Informationen können sie die Statusseite zur Verteilung oder die Buslisten prüfen.</span>';
								
							} else {
							
								$GLOBALS['hints'][] = '<span class="b cr">Die Buszuteilung für Fahrgemeinschaft '.$carpools[$cpid]['name'].' ist gescheitert. Für insgesamt '.$needed.' Mitfahrer stehen bislang nur '.$frei.' Plätze zur Verfügung. Beheben Sie zuerst diesen Mangel bevor Sie zur automatischen Verteilung schreiten.</span>';
							
							}
							
							
						} else {
						
							$GLOBALS['hints'][] = '<span class="b cr">Die Buszuteilung für Fahrgemeinschaft '.$cpid.' ist gescheitert, da keine solche Fahrgemeinschaft existiert.</span>';
						
						}
					
					
					}
			
				} break;
			
			case 'management_change_title': {
					if (isset($_REQUEST['management_newtitle']) AND isset($_REQUEST['management_oldtitle'])) {
						if ($_REQUEST['management_newtitle'] != $_REQUEST['management_oldtitle']) {
							$temp = parse_ini_file('./db/'.DB.'/info.ini',true);
							$temp['info']['name'] = $_REQUEST['management_newtitle'];
							write_ini_file('./db/'.DB.'/info.ini',$temp);
							
							if (!empty($_SERVER['HTTPS']) && ('on' == $_SERVER['HTTPS'])) {
								$uri = 'https://';
							} else {
								$uri = 'http://';
							}
							$uri .= $_SERVER['HTTP_HOST'];
							
							header('Location: '.$uri.'/pvs/?view=default&hint='.urlencode('Das Event '.$info['info']['name'].' wurde erfolgreich in '.$_REQUEST['management_newtitle'].' umbenannt.'));
					
						} else {
							$GLOBALS['hints'][] = '<span class="b cb">Anfrage erhalten. Es hat sich nichts geändert.</span>';
						}
					} else {
						$GLOBALS['hints'][] = '<span class="b cr">Fehlschlag - Formulardaten fehlerhaft.</span>';
					}
				} break;		
			
			case 'management_configure_access': {
				
					$tinfo = parse_ini_file('./db/'.DB.'/info.ini',true);
					
					$tbool = false;
				
					if (isset($_POST['crdata_pwbas']) AND $_POST['crdata_pwbas']) {
						$tbool = true;
						$tinfo['auth']['basic'] = $_POST['crdata_pwbas'];
						$GLOBAL['hints'][] = '<span class="cg b">Das Zugriffspasswort Schülermanagement wurde geändert.</span>';
					}
								
					if (isset($_POST['crdata_pwadv']) AND $_POST['crdata_pwadv']) {
						$tbool = true;
						$tinfo['auth']['advanced'] = $_POST['crdata_pwadv'];
						$GLOBAL['hints'][] = '<span class="cg b">Das Zugriffspasswort Angebotsmanagement wurde geändert.</span>';
					}
					
					if (!isset($tinfo['access'])) $tinfo['access'] = Array();
					
					if (isset($_POST['basallowed'])) {
					
						$tbool = true;
						$tinfo['access']['basic'] = $_POST['basallowed'];
						
						foreach ($GLOBALS['views']['all'] as $tview => $tvdata) {
							if (in_array($tview,$tinfo['access']['basic'])) {
								$GLOBALS['views']['all'][$tview]['basallowed'] = true;
							} else {
								$GLOBALS['views']['all'][$tview]['basallowed'] = false;
							}
						}
					
					}
					
					if (isset($_POST['advallowed'])) {
					
						$tbool = true;
						$tinfo['access']['advanced'] = $_POST['advallowed'];
					
						foreach ($GLOBALS['views']['all'] as $tview => $tvdata) {
							if (in_array($tview,$tinfo['access']['advanced'])) {
								$GLOBALS['views']['all'][$tview]['advallowed'] = true;
							} else {
								$GLOBALS['views']['all'][$tview]['advallowed'] = false;
							}
						}						
					}
					
					if ($tbool) {
						
						write_ini_file('./db/'.DB.'/info.ini',$tinfo);
						
						$info = parse_ini_file('./db/'.DB.'/info.ini',true);
						
						$GLOBALS['hints'][] = '<span class="cg b">Die Änderungen wurden erfolgreich gespeichert.</span>';
					
					} else {
						$GLOBALS['hints'][] = '<span class="cb b">Das System hat keine Änderung registriert.</span>';
					}
			
				} break;
			
			case 'hidden_wishes': {
				
					//Datenbanken laden
					$eventid = DB;
					$offers = is_file('./db/'.$eventid.'/offers.ini')?parse_ini_file('./db/'.$eventid.'/offers.ini',true):Array();
					uasort($offers,'sort_db');
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
					
					foreach ($classes as $cln => $tstudents) {
						$oftc = Array();
						
						foreach ($offers as $oid => $odata) {
							if (!parse_clstring($odata['excluded'],$cln)) {
								$oftc[] = $oid;
							}
						}
						
						foreach ($tstudents as $sid => $sdata) {
							$keys = array_rand($oftc,min(count($oftc),$info['props']['max_wishes']));
							if (!is_array($keys)) $keys = Array($keys);
							$j = 0;
							for ($i = 0; $i < $info['props']['max_wishes']; $i++) {
								if ( (!isset($sdata['wish_'.$i]) OR $sdata['wish_'.$i] <= 0 OR !isset($offers[$sdata['wish_'.$i]]) ) AND isset($keys[$j]) ) {
									$classes[$cln][$sid]['wish_'.$i] = $oftc[$keys[$j]];
									$j++;
								}
							}
						}
						
						write_ini_file('./db/'.DB.'/class_'.$cln.'.ini',$classes[$cln]);
						
					}

					$GLOBALS['hints'][] = '<span class="b" style="color:#990;">Geheime Aktion "Wünsche zuweisen" ausgeführt!</span>';
					$portalbool = true;
				
				} break;
		
		}
	} else {
	
		$GLOBALS['hints'][] = '<span style="font-weight:bold;color:#900;">Fehlschlag - Fehlende Berechtigung für diese Aktion.</span>';
	
	}


?>