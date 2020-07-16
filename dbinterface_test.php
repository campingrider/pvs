<?php

	for($i = 0; $i < 60; $i++) {
	
		$ct = get_DB_ct($eventid);
	
		$teachers[$ct] = Array('id' => $ct, 'gender' => 'm','type' => 'teacher', 'forename' => 'Maximilian', 'sign' => 'MM'.$ct, 'surname' => 'Musterlehrer', 'class' => '', 'offer' => 0, 'available' => true, 'bus' => 0 );
		
		if (bcmod($i,2)) {
		
			$teachers[$ct]['gender'] = 'f';
			$teachers[$ct]['forename'] = 'Erika';
			$teachers[$ct]['surname'] = 'Musterlehrerin';
			$teachers[$ct]['sign'] = 'ME'.$ct;
		
		}
		
		for ($j = 0; $j < $info['props']['max_wishes']; $j++) {
		
			$teachers[$ct]['wish_'.$j] = '';
		
		}
	}

	for ($i = 0; $i < 10; $i++) {
		$rd = array_rand($teachers);
		
		$teachers[$rd]['available'] = false;
	}
	
	for ($i = 5; $i <= 12; $i++) {
	
		for ($j = 1; $j <= 4; $j++) {
		
			$abc = Array(1 => 'a', 2 => 'b', 3 => 'c', 4 => 'd');
			
			if ($i <= 10) {
				$classname = $i.$abc[$j];
			} else {
				$classname = 'K'.($i - 10).'-LKM'.$j;
			}
			
			$classes[$classname] = Array();
			
			for ($k = 0; $k < 24; $k++) {
			
				$ct = get_DB_ct($eventid);
			
				$classes[$classname][$ct] = Array('id' => $ct, 'gender' => 'm', 'type' => 'student', 'forename' => 'Maximilian', 'surname' => 'Musterschüler', 'class' => $classname, 'offer' => 0, 'paid' => 0.0, 'cancelled' => 0, 'bus' => 0 );
		
				if (bcmod($k,2)) {
		
					$classes[$classname][$ct]['gender'] = 'f';
					$classes[$classname][$ct]['forename'] = 'Erika';
					$classes[$classname][$ct]['surname'] = 'Musterschülerin';
				
				}
		
				for ($l = 0; $l < $info['props']['max_wishes']; $l++) {
				
					$classes[$classname][$ct]['wish_'.$l] = '';
				
				}
			
			}
			
			$rd = array_rand($teachers);
			while($teachers[$rd]['class'] != '') {
				$rd = array_rand($teachers);
			}
			
			$teachers[$rd]['class'] = $classname;
		
		}
	}
	
	$ct = get_DB_ct($eventid);
	$buses[$ct] = Array('id'=>$ct,'type'=>'bus','company'=>'ReiseBUS','tag'=>'HPD-RB-'.$ct,'capacity'=>(50+rand(0,9)));
	$ct = get_DB_ct($eventid);
	$buses[$ct] = Array('id'=>$ct,'type'=>'bus','company'=>'ReiseBUS','tag'=>'HPD-RB-'.$ct,'capacity'=>(50+rand(0,9)));
	$ct = get_DB_ct($eventid);
	$buses[$ct] = Array('id'=>$ct,'type'=>'bus','company'=>'ReiseBUS','tag'=>'HPD-RB-'.$ct,'capacity'=>(50+rand(0,9)));
	
	$ct = get_DB_ct($eventid);
	$carpools[$ct] = Array('id'=>$ct,'type'=>'carpool','name'=>'','offers'=>'');
	$buses[($ct-3)]['carpool'] = $ct;
	$buses[($ct-2)]['carpool'] = $ct;
	$ct = get_DB_ct($eventid);
	$carpools[$ct] = Array('id'=>$ct,'type'=>'carpool','name'=>'','offers'=>'');
	$buses[($ct-2)]['carpool'] = $ct;
	
	$oe = parse_ini_file('./vorlagen/offers_example.ini',true);
	
	foreach ($oe as $odata) {
		
		$ct = get_DB_ct($eventid);
		
		$offers[$ct] = $odata;
		
		$rd = array_rand($teachers);
		while($teachers[$rd]['offer'] != '' OR !$teachers[$rd]['available']) {
			$rd = array_rand($teachers);
		}
		
		$offers[$ct]['teacher'] = $rd;
		$teachers[$rd]['offer'] = $ct;
		
		$cp = array_rand($carpools);
		if ($carpools[$cp]['name'] != '') $carpools[$cp]['name'] .= ', '; 
		$carpools[$cp]['name'] .= substr($odata['name'],0,10).'.'; 
		if ($carpools[$cp]['offers'] != '') $carpools[$cp]['offers'] .= ', '; 
		$carpools[$cp]['offers'] .= $ct; 
		
	}

?>