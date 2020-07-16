<?php

if (in_array(AUTH,Array('supervisor','advanced','basic'))) {
	$GLOBALS['output'] .= '<div class="fieldset" style="text-align:center">';
	if (AUTH == 'supervisor') $GLOBALS['output'] .= '<h2>Portalseite Eventmanagement</h2>';
	if (AUTH == 'advanced') $GLOBALS['output'] .= '<h2>Portalseite Angebotsmanagement</h2>';
	if (AUTH == 'basic') $GLOBALS['output'] .= '<h2>Portalseite Schülermanagement</h2>';
	$GLOBALS['output'] .= '<p>Bitte wählen Sie die gewünschte Anzeige aus:</p>';
	
	$GLOBALS['output'] .= '<div style="width:100%;">';

	$GLOBALS['output'] .= '<div style="width:50%;float:right;">';
	
	{	//DRUCKANSICHTEN
		
		if ( isset($GLOBALS['views']) AND isset($GLOBALS['views']['cat']) AND isset($GLOBALS['views']['cat']['pr'])) {
		
			$tbool = false; $tout = ''; $actvcl = '';
			
			$tout .= '<div style="width:98%;border:1px solid black;background-color:#eee;margin:0px auto;">';
			$tout .= '<h3>Druckansichten</h3>';

			
			foreach ($GLOBALS['views']['cat']['pr'] as $tview => $tvdata) {
			
			
				if ($tvdata['show'] AND AUTH == 'supervisor' OR (AUTH == 'basic' AND $tvdata['basallowed']) OR (AUTH == 'advanced' AND $tvdata['advallowed'])) {
				
					if ($tvdata['vcl'] != $actvcl) {	
						if ($actvcl != '') {
							$tout .= '</ul>';
						}
						$tout .= '<ul class="portal '.$tvdata['vcl'].'">';
						$actvcl = $tvdata['vcl'];
					}
					
					$tbool = true;
					
					$tout .= '<li>';
					if (count($tvdata['filters']) > 0) {
					
						//Aktuell nur Unterstützung für einen Filter
						switch ($tvdata['filters'][0]) {
							case 'class':
							
								if (count($classes)) {
			
									$cns = array_keys($classes);
								
									$tout .= '<a id="'.$tview.'_a" href="./?view='.$tview.'&'.$tvdata['filters'][0].'='.$cns[0].'">'.$tvdata['pref'].'</a>';
									$tout .= '<select id="'.$tview.'_select" size="1">';
									
									foreach ($cns as $cn) { 
										$tout .= '<option';
										if ($cn == $cns[0]) $tout .= ' selected="selected"';
										$tout .= '>'.$cn.'</option>'; 
									}
									
									$tout .= '</select>'.$tvdata['postf'];
									
								} else { $tout .= '(Keine Klassen verfügbar!)'; }
								
								break;
							
							case 'offer':
							
								if (count($offers)) {
			
									$ons = array_keys($offers);
								
									$tout .= '<a id="'.$tview.'_a" href="./?view='.$tview.'&'.$tvdata['filters'][0].'='.$ons[0].'">'.$tvdata['pref'].'</a>';
									$tout .= '<select id="'.$tview.'_select" size="1">';
									
									foreach ($ons as $oid) { 
										$tout .= '<option';
										if ($oid == $ons[0]) $tout .= ' selected="selected"';
										$tout .= ' value="'.$oid.'">'.$offers[$oid]['name'].'</option>'; 
									}
									
									$tout .= '</select>'.$tvdata['postf'];
									
								} else { $tout .= '(Keine Angebote verfügbar!)'; }
								
								break;
							
							default:
								$tout .= '<a href="./?view='.$tview.'">'.$tvdata['pref'].$tvdata['postf'].'</a>';
								break;
						}
					} else {
						$tout .= '<a href="./?view='.$tview.'">'.$tvdata['pref'].$tvdata['postf'].'</a>';
					}
					$tout .= '</li>';
				
				}
				
			}
			
			$tout .= '</ul>';
			$tout .= '</div>';
		
			if ($tbool) $GLOBALS['output'] .= $tout;
		
		}

	}
	
	$GLOBALS['output'] .= '</div>';

	$GLOBALS['output'] .= '<div style="width:50%;">';

	{ 	//STATUS UND ÜBERSICHTEN
		
		if ( isset($GLOBALS['views']) AND isset($GLOBALS['views']['cat']) AND isset($GLOBALS['views']['cat']['ov'])) {
		
			$tbool = false; $tout = ''; $actvcl = '';
			
			$tout .= '<div style="width:98%;border:1px solid black;background-color:#eee;margin:0px auto;">';
			$tout .= '<h3>Status und Übersichten</h3>';
			
			foreach ($GLOBALS['views']['cat']['ov'] as $tview => $tvdata) {
			
				if ($tvdata['show'] AND AUTH == 'supervisor' OR (AUTH == 'basic' AND $tvdata['basallowed']) OR (AUTH == 'advanced' AND $tvdata['advallowed'])) {
				
					if ($actvcl == '') {	
						$tout .= '<ul class="portal">';
						$actvcl = $tvdata['vcl'];
					}
					
					$tbool = true;
					
					$tout .= '<li class="'.$tvdata['vcl'].'">';
					if (count($tvdata['filters']) > 0) {
					
						//Aktuell nur Unterstützung für einen Filter
						switch ($tvdata['filters'][0]) {
							case 'class':
							
								if (count($classes)) {
			
									$cns = array_keys($classes);
								
									$tout .= '<a id="'.$tview.'_a" href="./?view='.$tview.'&'.$tvdata['filters'][0].'='.$cns[0].'">'.$tvdata['pref'].'</a>';
									$tout .= '<select id="'.$tview.'_select" size="1">';
									
									foreach ($cns as $cn) { 
										$tout .= '<option';
										if ($cn == $cns[0]) $tout .= ' selected="selected"';
										$tout .= '>'.$cn.'</option>'; 
									}
									
									$tout .= '</select>'.$tvdata['postf'];
									
								} else { $tout .= '(Keine Klassen verfügbar!)'; }
								
								break;
							
							case 'offer':
							
								if (count($offers)) {
			
									$ons = array_keys($offers);
								
									$tout .= '<a id="'.$tview.'_a" href="./?view='.$tview.'&'.$tvdata['filters'][0].'='.$cns[0].'">'.$tvdata['pref'].'</a>';
									$tout .= '<select id="'.$tview.'_select" size="1">';
									
									foreach ($ons as $oid) { 
										$tout .= '<option';
										if ($oid == $ons[0]) $tout .= ' selected="selected"';
										$tout .= ' value="'.$oid.'">'.$offers[$oid]['name'].'</option>'; 
									}
									
									$tout .= '</select>'.$tvdata['postf'];
									
								} else { $tout .= '(Keine Angebote verfügbar!)'; }
								
								break;
							
							default:
								$tout .= '<a href="./?view='.$tview.'">'.$tvdata['pref'].$tvdata['postf'].'</a>';
								break;
						}
					} else {
						$tout .= '<a href="./?view='.$tview.'">'.$tvdata['pref'].$tvdata['postf'].'</a>';
					}
					$tout .= '</li>';
				
				}
				
			}
			
			$tout .= '</ul>';
			$tout .= '</div>';
		
			if ($tbool) $GLOBALS['output'] .= $tout;
		
		}
		
	}	
	$GLOBALS['output'] .= '<p style="margin:0px;padding:0px"><br/></p>';

	{	//DATENBANKBEARBEITUNG
	
		if ( isset($GLOBALS['views']) AND isset($GLOBALS['views']['cat']) AND isset($GLOBALS['views']['cat']['ed'])) {
		
			$tbool = false; $tout = ''; $actvcl = '';
			
			$tout .= '<div style="width:98%;border:1px solid black;background-color:#eee;margin:0px auto;">';
			$tout .= '<h3>Datenbankbearbeitung</h3>';
			
			foreach ($GLOBALS['views']['cat']['ed'] as $tview => $tvdata) {
			
				if ($tvdata['show'] AND AUTH == 'supervisor' OR (AUTH == 'basic' AND $tvdata['basallowed']) OR (AUTH == 'advanced' AND $tvdata['advallowed'])) {
				
					if ($tvdata['vcl'] != $actvcl) {	
						if ($actvcl != '') {
							$tout .= '</ul>';
						}
						$tout .= '<ul class="portal '.$tvdata['vcl'].'">';
						$actvcl = $tvdata['vcl'];
					}
					
					$tbool = true;
					
					$tout .= '<li>';
					if (count($tvdata['filters']) > 0) {
					
						//Aktuell nur Unterstützung für einen Filter
						switch ($tvdata['filters'][0]) {
							case 'class':
							
								if (count($classes)) {
			
									$cns = array_keys($classes);
								
									$tout .= '<a id="'.$tview.'_a" href="./?view='.$tview.'&'.$tvdata['filters'][0].'='.$cns[0].'">'.$tvdata['pref'].'</a>';
									$tout .= '<select id="'.$tview.'_select" size="1">';
									
									foreach ($cns as $cn) { 
										$tout .= '<option';
										if ($cn == $cns[0]) $tout .= ' selected="selected"';
										$tout .= '>'.$cn.'</option>'; 
									}
									
									$tout .= '</select>'.$tvdata['postf'];
									
								} else { $tout .= '(Keine Klassen verfügbar!)'; }
								
								break;
							
							case 'offer':
							
								if (count($offers)) {
			
									$ons = array_keys($offers);
								
									$tout .= '<a id="'.$tview.'_a" href="./?view='.$tview.'&'.$tvdata['filters'][0].'='.$cns[0].'">'.$tvdata['pref'].'</a>';
									$tout .= '<select id="'.$tview.'_select" size="1">';
									
									foreach ($ons as $oid) { 
										$tout .= '<option';
										if ($oid == $ons[0]) $tout .= ' selected="selected"';
										$tout .= ' value="'.$oid.'">'.$offers[$oid]['name'].'</option>'; 
									}
									
									$tout .= '</select>'.$tvdata['postf'];
									
								} else { $tout .= '(Keine Angebote verfügbar!)'; }
								
								break;
							
							default:
								$tout .= '<a href="./?view='.$tview.'">'.$tvdata['pref'].$tvdata['postf'].'</a>';
								break;
						}
					} else {
						$tout .= '<a href="./?view='.$tview.'">'.$tvdata['pref'].$tvdata['postf'].'</a>';
					}
					$tout .= '</li>';
				
				}
				
			}
			
			$tout .= '</ul>';
			$tout .= '</div>';
		
			if ($tbool) $GLOBALS['output'] .= $tout;
		
		}
		
	}	
	
	$GLOBALS['output'] .= '</div>';

	$GLOBALS['output'] .= '</div>';
	$GLOBALS['output'] .= '<p style="clear:both;margin:0px;padding:0px"><br/></p>';
	
	{	//MANAGEMENTFUNKTIONEN
		if ( isset($GLOBALS['views']) AND isset($GLOBALS['views']['cat']) AND isset($GLOBALS['views']['cat']['mg'])) {
		
			$tbool = false; $tout = ''; $actvcl = '';
			
			$tout .= '<div style="width:99%;margin:10px auto;border:1px solid black;background-color:#eee;">';
			$tout .= '<h3>Managementfunktionen</h3>';
			
			foreach ($GLOBALS['views']['cat']['mg'] as $tview => $tvdata) {
			
				if ($tvdata['show'] AND AUTH == 'supervisor' OR (AUTH == 'basic' AND $tvdata['basallowed']) OR (AUTH == 'advanced' AND $tvdata['advallowed'])) {
				
					if ($actvcl == '') {	
						$tout .= '<ul class="portal">';
						$actvcl = $tvdata['vcl'];
					}
					
					$tbool = true;
					
					$tout .= '<li class="'.$tvdata['vcl'].'">';
					if (count($tvdata['filters']) > 0) {
					
						//Aktuell nur Unterstützung für einen Filter
						switch ($tvdata['filters'][0]) {
							case 'class':
							
								if (count($classes)) {
			
									$cns = array_keys($classes);
								
									$tout .= '<a id="'.$tview.'_a" href="./?view='.$tview.'&'.$tvdata['filters'][0].'='.$cns[0].'">'.$tvdata['pref'].'</a>';
									$tout .= '<select id="'.$tview.'_select" size="1">';
									
									foreach ($cns as $cn) { 
										$tout .= '<option';
										if ($cn == $cns[0]) $tout .= ' selected="selected"';
										$tout .= '>'.$cn.'</option>'; 
									}
									
									$tout .= '</select>'.$tvdata['postf'];
									
								} else { $tout .= '(Keine Klassen verfügbar!)'; }
								
								break;
							
							case 'offer':
							
								if (count($offers)) {
			
									$ons = array_keys($offers);
								
									$tout .= '<a id="'.$tview.'_a" href="./?view='.$tview.'&'.$tvdata['filters'][0].'='.$cns[0].'">'.$tvdata['pref'].'</a>';
									$tout .= '<select id="'.$tview.'_select" size="1">';
									
									foreach ($ons as $oid) { 
										$tout .= '<option';
										if ($oid == $ons[0]) $tout .= ' selected="selected"';
										$tout .= ' value="'.$oid.'">'.$offers[$oid]['name'].'</option>'; 
									}
									
									$tout .= '</select>'.$tvdata['postf'];
									
								} else { $tout .= '(Keine Angebote verfügbar!)'; }
								
								break;
							
							default:
								$tout .= '<a href="./?view='.$tview.'">'.$tvdata['pref'].$tvdata['postf'].'</a>';
								break;
						}
					} else {
						$tout .= '<a href="./?view='.$tview.'">'.$tvdata['pref'].$tvdata['postf'].'</a>';
					}
					$tout .= '</li>';
				
				}
				
			}
			
			$tout .= '</ul>';
			$tout .= '</div>';
		
			if ($tbool) $GLOBALS['output'] .= $tout;
		
		}
	}
	
	
	
	$GLOBALS['output'] .= '</div>';
} else {
	$GLOBALS['output'] .= '<div class="fieldset" style="text-align:center">';
	$GLOBALS['output'] .= '<h2>Fehler</h2>';
	$GLOBALS['output'] .= '<p>Oops, da ist was schief gelaufen! Melden Sie sich bitte ab und erneut wieder an. Bei wiederholtem Auftreten informieren Sie bitte den Systemadministrator.</p>';
	$GLOBALS['output'] .= '</div>';
}		

	

?>