<?php

	$tot = $teachers;
	$tcl = $classes;
	
	foreach ($teachers as $tid => $tdata) {
		
		$takefrom = array_rand($tot);
		
		$teachers[$tid]['forename'] = $tot[$takefrom]['forename'];
		$teachers[$tid]['gender'] = $tot[$takefrom]['gender'];
		$teachers[$tid]['phone'] = $tot[$takefrom]['phone'];
		
	}
	
	foreach ($classes as $cln => $cldata) {
	
		foreach ($cldata as $sid => $sdata) {
		
			$takefromcl = array_rand($tcl);
			$takefrom = array_rand($tcl[$takefromcl]);
			
			$classes[$cln][$sid]['forename'] = $tcl[$takefromcl][$takefrom]['forename'];
			$classes[$cln][$sid]['gender'] = $tcl[$takefromcl][$takefrom]['gender'];
			
		}
		
	}

?>