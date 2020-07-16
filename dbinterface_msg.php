<?php

	{ //DEFAULTS
		$defaults = Array(
		
			'teacher' => Array(
				'gender' => '',
				'type' => 'teacher', 
				'forename' => 'Maximilian',
				'sign' => '',
				'surname' => 'Musterlehrer',
				'class' => '',
				'offer' => 0,
				'available' => 1,
				'bus' => 0,
				'phone'=>''
			),
			
			'student' => Array(
					
				'surname'=>'Mustermann',
				'forename'=>'Maximilian',
				'gender'=>'',
				'offer'=>0,
				'type'=>'student',
				'class'=>'NOCLASS',
				'paid'=>0.0,
				'bus'=>0,
				'type'=>'student'
			
			)
		
		);
	}
	
	for ($i = 0; $i < $info['props']['max_wishes']; $i++) {
		$defaults['teacher']['wish_'.$i] = 0;
		$defaults['student']['wish_'.$i] = 0;
	}
	
	$colinfo = parse_ini_file('./schuldb/colinfo.ini');
	$tinfo = file('./schuldb/lehrer.csv');
	$sinfo = file('./schuldb/schueler.csv');
	$linfo = parse_ini_file('./schuldb/lehrerinfo.ini',true);
	
	foreach ($tinfo as $tdata) {
	
		$info = explode(';',$tdata);
		
		$ct = get_DB_ct($eventid);
		
		$teachers[$ct] = $defaults['teacher'];
		
		$teachers[$ct]['id'] = $ct;
	
		foreach ($colinfo['lehrer_csv'] as $key => $colk) {
			
			if (isset($info[$key]) AND $info[$key] != '') {
				$info[$key] = preg_replace('/^\s+(\S.*)$/','$1',$info[$key]);
				$info[$key] = preg_replace('/^(.*\S)\s+$/','$1',$info[$key]);
				$info[$key] = preg_replace('/(\S\s)\s+(\S)/','$1$2',$info[$key]);
				$teachers[$ct][$colk] = $info[$key];
			}
			
		}		
		
		//Klassenname anpassen
		$teachers[$ct]['class'] = preg_replace('/^(\D)*\b(\w+)\b.*$/i','$2',$teachers[$ct]['class']);
		$teachers[$ct]['class'] = preg_replace('/\s+/','',$teachers[$ct]['class']);
	
		//die lehrerinfo befragen
		if (isset($linfo[$teachers[$ct]['sign']])) {
		
			foreach ($linfo[$teachers[$ct]['sign']] as $key => $idata) {
				
				$teachers[$ct][$key] = $idata;
				
			}
		
		}
	}
	
	foreach ($sinfo as $sdata) {
	
		$info = explode(';',$sdata);
		
		$ct = get_DB_ct($eventid);
		
		$save = $defaults['student'];
		
		$save['id'] = $ct;
	
		foreach ($colinfo['schueler_csv'] as $key => $colk) {
			
			if (isset($info[$key]) AND $info[$key] != '') {
				//unnötige Leerzeichen entfernen
				$info[$key] = preg_replace('/^\s+(\S.*)$/','$1',$info[$key]);
				$info[$key] = preg_replace('/^(.*\S)\s+$/','$1',$info[$key]);
				$info[$key] = preg_replace('/(\S\s)\s+(\S)/','$1$2',$info[$key]);
				$save[$colk] = $info[$key];
			} 
			
		}
		
		//Klassenname anpassen
		$save['class'] = preg_replace('/^(\D)*\b(\w+)\b.*$/i','$2',$save['class']);
		$save['class'] = preg_replace('/\s+/','',$save['class']);
		
		if (!isset($classes[$save['class']])) $classes[$save['class']] = Array();
		
		$classes[$save['class']][$ct] = $save;
		
	}
	
	for ($i = 1; $i <= 4; $i++) {
		
		for ($j = 1; $j <= 9; $j++) {
		
			if (is_file('./schuldb/k'.$i.'m'.$j.'.csv')) {
				
				$kinfo = file('./schuldb/k'.$i.'m'.$j.'.csv');
				
				$classes['K'.$i.'M'.$j] = Array();
				
				if ($ktname = array_shift($kinfo)) {
					$ktname = explode(';',$ktname);
					$ktname = $ktname[0];
				}
				
				foreach ($teachers as $tid => $tdata) {
					if ($tdata['surname'] == $ktname OR $tdata['sign'] == $ktname) {
						
						if ($tdata['class'] != '') { $teachers[$tid]['class'] .= ','; }
						$teachers[$tid]['class'] .= 'K'.$i.'M'.$j;
						
					}
				}
				
				foreach ($kinfo as $strd) {
					
					$data = explode(';',$strd);
					
					if (isset($data[0]) AND isset($data[1])) {
					
						$data[0] = preg_replace('/^\s+(\S.*)$/','$1',$data[0]);
						$data[0] = preg_replace('/^(.*\S)\s+$/','$1',$data[0]);
						$data[0] = preg_replace('/(\S\s)\s+(\S)/','$1$2',$data[0]);
						
						$data[1] = preg_replace('/^\s+(\S.*)$/','$1',$data[1]);
						$data[1] = preg_replace('/^(.*\S)\s+$/','$1',$data[1]);
						$data[1] = preg_replace('/(\S\s)\s+(\S)/','$1$2',$data[1]);
						
						foreach ($classes as $cln => $std) {
						
							foreach ($std as $sid => $sdata) {
								
								if ($sdata['surname'] == $data[0] AND $sdata['forename'] == $data[1]) {
									
									$classes['K'.$i.'M'.$j][$sid] = $sdata;
									$classes['K'.$i.'M'.$j][$sid]['class'] = 'K'.$i.'M'.$j;
									unset($classes[$cln][$sid]);
									
									if (!count($classes[$cln])) {
										unset($classes[$cln]);
									}
									
								}
								
							}
						
						}
					
					}
					
				}
				
			}
		
		}
		
	}
	
?>