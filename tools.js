var pvs = {};

pvs.encode = function (orig,key) { 

	if (key == '' || !key) key = 'a';
	
	orig = orig + '';

	var codetable = ['a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','q','r','s','t','u','v','w','x','y','z'];
	var t = codetable.length;
	for (var i = 0; i < t; i++) {
		codetable[codetable.length] = codetable[i].toUpperCase();
	}
	for (var i = 0; i < 10; i++) {
		codetable[codetable.length] = ''+i;
	}
	codetable[codetable.length] = '_';
	codetable[codetable.length] = ':';
	codetable[codetable.length] = '.';
	codetable[codetable.length] = ',';
	codetable[codetable.length] = '+';
	codetable[codetable.length] = '-';
	codetable[codetable.length] = '=';
	codetable[codetable.length] = '|';
	codetable[codetable.length] = '~';
	codetable[codetable.length] = '*';
	codetable[codetable.length] = ';';

	var codetable2 = new Object();
	
	for (var i = 0; i < codetable.length; i++) {
		codetable2[codetable[i]] = i;
	}
		
	origA = []; keyA = [];
	
	for (var i = 0; i < orig.length; i++) {
		if (codetable2[orig.charAt(i)] == undefined) {
			origA[i] = 62;
		} else {
			origA[i] = codetable2[orig.charAt(i)];
		}
		
		var j = (i % key.length);
		
		if (codetable2[key.charAt(j)] == undefined) {
			keyA[i] = 0;
		} else {
			keyA[i] = codetable2[key.charAt(j)];
		}
	}
	
	var ret = '';
	
	
	for (var i = 0; i < origA.length; i++) {
		var charcode = ((origA[i] + keyA[i]) % codetable.length);
		ret = ret + codetable[parseInt(charcode)];
	}

	return ret;
	
}

pvs.createAuthString = function (customKey) {

	if (!customKey) customKey = '';

	var initHour = new Date();
	initHour.setMilliseconds(0);
	initHour.setSeconds(0);
	initHour.setMinutes(0);
	
	var ret = pvs.encode(pvs.encode(Date.parse(initHour), customKey),(window.location.protocol + '//' + window.location.host));
	
	return ret;
}

pvs.stringToId = function (str) {
	
	var nstr = "";
	
	var pool = [0,1,2,3,4,5,6,7,8,9,"_","a","b","c","d","e","f","g","h","i","j","k","l","m","n","o","p","q","r","s","t","u","v","w","x","y","z"];
	
	var rand = 0;
	
	for (var i = 0; i < 8; i++) {
		rand = Math.min(Math.floor(Math.random() * pool.length),36);
	
		nstr = nstr+pool[rand];
	}
	
	return nstr;
	
	
}

pvs.eur_to_float = function (eur) {

	var ret = ''+eur+'';
	ret = ret.replace(/[,]/g,'.')
	
	var exp = /(\D*)(\d+(\.\d{0,2})?)\D*/
	
	var erg = exp.exec(ret);
		
	if (erg && (ret = parseFloat(erg[2])) && ret != Number.NaN) {
		
		if (erg[1] && erg[1].search(/\D*-\s*/) > -1) ret = 0.0 - ret;
		
		return ret;
		
	} else {
	
		return 0.0;
	
	}	

} 

pvs.float_to_eur = function (fl) {

	fl = fl.toFixed(2);

	var ret = ''+fl+'';
	
	ret = ret.replace(/[\.]/g,',');
	
	ret = ret + ' €';
	
	return ret;
}

window.onload = function () {

	//js-Warning ausblenden
	document.getElementById('jsWarning').style.display = 'none';
	document.getElementById('phpContainer').style.display = 'block';

	{	//Eventerstellungsbildschirm und damit verknüfte Objekte
	
		if (document.getElementsByName('crdata_name')[0] && document.getElementsByName('crdata_autoid')[0] && document.getElementsByName('crdata_eventid')[0]) {
		
			pvs.name = document.getElementsByName('crdata_name')[0];
			pvs.autoid = document.getElementsByName('crdata_autoid')[0];
			pvs.eventid = document.getElementsByName('crdata_eventid')[0];
		
			pvs.autoid.onchange = function() {
				pvs.eventid.readOnly = pvs.autoid.checked;
			
			}
			
			if (pvs.autoid.checked == true) {
				pvs.eventid.value = pvs.stringToId(this.value);
			}
				
			pvs.name.onchange = function() {
			
				if (pvs.autoid.checked == true) {
				
					pvs.eventid.value = pvs.stringToId(this.value);
				
				}
			
			}
			
		}
		
		if (document.getElementsByName('crdata_pwbas')[0] && document.getElementsByName('crdata_pwadv')[0] && document.getElementsByName('crdata_pwsup')[0] && document.getElementsByName('crdata_pwclear')[0]) {
		
			pvs.pwbas = document.getElementsByName('crdata_pwbas')[0];
			pvs.pwadv = document.getElementsByName('crdata_pwadv')[0];
			pvs.pwsup = document.getElementsByName('crdata_pwsup')[0];
			pvs.pwclear = document.getElementsByName('crdata_pwclear')[0];
		
			pvs.pwclear.onchange = function() {
			
				if (pvs.pwclear.checked == true) {
					pvs.pwbas.type = "text";
					pvs.pwadv.type = "text";
					pvs.pwsup.type = "text";
				} else {
					pvs.pwbas.type = "password";
					pvs.pwadv.type = "password";
					pvs.pwsup.type = "password";
				}
			
			}
			
		}
		
		
		//st3c-Animation
		
		var tempA = document.getElementsByTagName('table');
		
		for (var i = 0; i < tempA.length; i++) {
			
			var tempcn = tempA[i].className.split(' ');
			var bool = false;
			
			for (var j = 0; j < tempcn.length; j++) {
				
				if (tempcn[j] == "st3c") {
				
					bool = true;
					break;
					
				}
				
			}
			
			if (bool) {
			
				var trows = tempA[i].rows; 
			
				for (var j = 0; j < trows.length; j++) {
					
					trows[j].onmouseover = function () {
						//this.lastChild.style.visibility = "visible";
						this.lastChild.style.display = "table-cell";
					}
					
					
					trows[j].onmouseout = function () {
						//this.lastChild.style.visibility = "hidden";
						this.lastChild.style.display = "none";
					}
					
					trows[j].childNodes[1].firstChild.onfocus = function () { this.parentNode.parentNode.onmouseover(); }
					trows[j].childNodes[1].firstChild.onblur = function () { this.parentNode.parentNode.onmouseout(); }
					
					//trows[j].lastChild.style.border = "none";
					
					//trows[j].lastChild.style.visibility = "hidden";
					trows[j].lastChild.style.display = "none";
				
				}
			}
			
		}
	
	}

	{	//nav-Block
		if (false && document.getElementById('auth') && document.getElementById('bt_logout')) {
		
			document.getElementById('bt_logout').style.display = 'none';
		
			document.getElementById('nav').onmouseover = function () {
			
				document.getElementById('bt_logout').style.display = 'inline-block';
				document.getElementById('navsel').style.display = 'inline-block';
				document.getElementById('auth').style.display = 'none';
				
			}
			
			document.getElementById('nav').onmouseout = function () {
			
				document.getElementById('bt_logout').style.display = 'none';
				document.getElementById('navsel').style.display = 'none';
				document.getElementById('auth').style.display = 'inline-block';
				
			}
			
			document.getElementById('logobar').onmouseover = document.getElementById('nav').onmouseover;
		
		}
	}

	{	//Portalseite
	
		/*
		var validlists = ['edit_students','ov_students','edit_offer','ov_offer',
		'pdf_classteacher_wishlist','pdf_offerprizelist_class','pdf_busofferlist_class',
		'pdf_offerstudentslist','pdf_offerbuslist','edit_payments','pdf_phonelist_offer'];
		*/
		
		var validlists = [];
		
		elms = document.getElementsByTagName('a');
		for (var i = 0; i < elms.length; i++) {
			if (elms[i].parentNode && elms[i].parentNode.parentNode && elms[i].parentNode.parentNode.className && elms[i].parentNode.parentNode.className.search(/\bportal\b/) > -1) {
				if (elms[i].id != '') {
				
					var reg = /^(.*)_a$/;
					
					var erg = reg.exec(elms[i].id);
					
					if (erg[1]) {
					
						validlists[validlists.length] = erg[1];
					
					}
				
				}
			}
		}
	
		for (var i = 0; i < validlists.length; i++) {
			var id = validlists[i];
			
			if (document.getElementById(id+'_a') && document.getElementById(id+'_select')) {
		
				document.getElementById(id+'_select').onchange = function () {
				
					var exp = /^(\w+)_select$/;
				
					id = exp.exec(this.id);
					
					var exp_c = /[?|&]class=/;
					var exp_o = /[?|&]offer=/;
						
					var type; 
					
					if (id != null) {
						id = id[1];
				
						if (document.getElementById(id+'_a').href.search(exp_c) > -1) {
							document.getElementById(id+'_a').href = "./?view="+id+"&class="+this.value+"";
						} else {
							if (document.getElementById(id+'_a').href.search(exp_o) > -1) {
								document.getElementById(id+'_a').href = "./?view="+id+"&offer="+this.value+"";
							}
						}
					}
				
				}
			
			}
		}
	
		
		var elms = document.getElementsByTagName('ul');
		
		for (var i = 0; i <= elms.length; i++) {
		
			if (elms[i] && elms[i].className.search(/\bportal\b/) != -1) {
			
				var lis = elms[i].getElementsByTagName('li');
				
				for (var j = 0; j <= lis.length; j++) {
				
					if (lis[j] && lis[j].getElementsByTagName('a')) {
					
						lis[j].onclick = function (e) {
						
							if (e && e.target && e.target.tagName && e.target.tagName.toLowerCase() != 'select' && e.target.tagName.toLowerCase() != 'option') {
								var a = this.getElementsByTagName('a')[0];
								window.location.href = a.href;
							} else { pvs.e = e; }
						
						}
					
					}
				
				}
			
			}
		
		}
	
	}

	{	//Fieldset-Toplinks
	
		var elms = document.getElementsByTagName('div');
		
		var parents = [];
		
		for (var i = 0; i < elms.length; i++) {
			var elm = elms[i];
			
			if (elm.className && elm.className.search(/\bfieldset\b/) > -1) {
				
				if (elm.parentNode.className.search(/\bfieldset_td\b/) > -1) {
					if (elm.parentNode.parentNode.className.search(/\bfieldset_tr\b/) > -1 && elm.parentNode.parentNode.lastChild.className.search(/\barrowlink\b/) == -1) {
	
						bool = false;
						for (var j = 0; j < parents.length; j++) {
							if (parents[j] == elm.parentNode.parentNode) {
								bool = true;
							}
						}
						
						if (bool) {
							continue;
						} else {
							elm = elm.parentNode.parentNode;
							parents[parents.length] = elm;
						}
						
					} else {
						continue;
					}
				}
				
				var h2 = elm.getElementsByTagName('h2');
				if (h2.length > 0) {
					
					var aelm = document.createElement('a');
					aelm.href = '#top';
					aelm.innerHTML = '&uarr;';
					aelm.style.color = 'white';
					aelm.style.display = 'inline-block';
					aelm.style.position = 'absolute';
					aelm.style.top = 'auto';
					aelm.style.left = '25px';
					aelm.className = 'arrowlink';
										
					var aelm2 = document.createElement('a');
					aelm2.href = '#bottom';
					aelm2.innerHTML = '&darr;';
					aelm2.style.color = 'white';
					aelm2.style.display = 'inline-block';
					aelm2.style.position = 'absolute';
					aelm2.style.top = 'auto';
					aelm2.style.right = '25px';
					aelm2.className = 'arrowlink';
					
					h2[0].appendChild(aelm);
					h2[0].appendChild(aelm2);

					if (elm.className.search(/\bfieldset\b/) > -1) {
						var bottomp = document.createElement('p');
						bottomp.innerHTML = '&nbsp;';
						bottomp.className = 'bottomp';
					}
										
					var aelm = document.createElement('a');
					aelm.href = '#top';
					aelm.innerHTML = '&nbsp;&uarr;&nbsp;';
					aelm.style.color = 'black';
					aelm.style.display = 'inline-block';
					aelm.style.position = 'absolute';
					aelm.style.top = 'auto';
					aelm.style.left = '25px';
					aelm.style.backgroundColor = 'white';
					aelm.style.border = '1px solid black';
					aelm.style.borderRadius = '4px';
					aelm.className = 'arrowlink';
										
					var aelm2 = document.createElement('a');
					aelm2.href = '#bottom';
					aelm2.innerHTML = '&nbsp;&darr;&nbsp;';
					aelm2.style.color = 'black';
					aelm2.style.display = 'inline-block';
					aelm2.style.position = 'absolute';
					aelm2.style.top = 'auto';
					aelm2.style.right = '25px';
					aelm2.style.backgroundColor = 'white';
					aelm2.style.border = '1px solid black';
					aelm2.style.borderRadius = '4px';
					aelm2.className = 'arrowlink';
					
					if (elm.className.search(/\bfieldset\b/) > -1) {
						h2[0].parentNode.appendChild(bottomp.cloneNode(true));
					}
					h2[0].parentNode.appendChild(aelm);
					h2[0].parentNode.appendChild(aelm2);
					if (elm.className.search(/\bfieldset\b/) > -1) {
						h2[0].parentNode.appendChild(bottomp);
					}
				}
			}
		}
	
	}

	{	//edit_payments
		
		pvs.prb = [];
		pvs.plb = [];
		pvs.dep = [];
		
		var elms = document.getElementsByTagName('input');
		var elm;
		
		for (var i = 0; i < elms.length; i++) {
			if (elms[i].id.search(/^edit_payments_\d+_r$/) > -1) { pvs.prb[pvs.prb.length] = elms[i]; }
			if (elms[i].id.search(/^edit_payments_\d+_l$/) > -1) { pvs.plb[pvs.plb.length] = elms[i]; }
			if (elms[i].id.search(/^edit_payments_\d+_deposit$/) > -1) { pvs.dep[pvs.dep.length] = elms[i]; }
		}
		
		for (var i = 0; i < pvs.dep.length; i++) {
			
			pvs.dep[i].onchange = function() {
			
				this.value = pvs.float_to_eur(pvs.eur_to_float(this.value));
			
				var sum = 0.0;
				for (var j = 0; j < pvs.dep.length; j++) {
					if (pvs.dep[j]) {	
						sum = sum + pvs.eur_to_float(pvs.dep[j].value);
					}
				}
				
				var sumelm = null;
				
				if (sumelm = document.getElementById('edit_payments_sum')) {
					sumelm.value = pvs.float_to_eur(sum);
				}
			}
						
		}
		
		if (pvs.dep[0]) pvs.dep[0].onchange();

		for (var i = 0; i < pvs.prb.length; i++) {
			
			pvs.prb[i].onclick = function () {
			
				var exp = /^edit_payments_(\d+)_r$/;
				
				var erg = exp.exec(this.id);
			
				var gezahlt, zz;
			
				if (erg && erg[1] && Number(erg[1]) 
					&& (gezahlt = document.getElementById('edit_payments_'+Number(erg[1])+'_deposit')) 
					&& (zz = document.getElementById('edit_payments_'+Number(erg[1])+'_topay'))) {
					
					gezahlt.value = pvs.float_to_eur(parseFloat(zz.value));
					gezahlt.onchange();
					
				}
				
			}
			
		}
		
		for (var i = 0; i < pvs.plb.length; i++) {
			
			pvs.plb[i].onclick = function () {
			
				var exp = /^edit_payments_(\d+)_l$/;
				
				var erg = exp.exec(this.id);
			
				var gezahlt;
			
				if (erg && Number(erg[1]) 
					&& (gezahlt = document.getElementById('edit_payments_'+Number(erg[1])+'_deposit')) 
					) {
					
					gezahlt.value = pvs.float_to_eur(0.0);
					gezahlt.onchange();
					
				}
				
			}
			
		}
	
		if (elm = document.getElementById('edit_payments_l_all')) {
		
			elm.onclick = function () {
				
				for (var i = 0; i < pvs.plb.length; i++) {
				
					pvs.plb[i].onclick();
							
				
				}
				
			}
		
		}
		
		if (elm = document.getElementById('edit_payments_r_all')) {
		
			elm.onclick = function () {
			
				bool = false;
					
				for (var i = 0; i < pvs.prb.length; i++) {
				
					var exp = /^edit_payments_(\d+)_r$/;
				
					var erg = exp.exec(pvs.prb[i].id);
			
					if (erg && erg[1] && Number(erg[1]) 
						&& (zz = document.getElementById('edit_payments_'+Number(erg[1])+'_topay'))
					) {
					
						if (parseFloat(zz.value) >= 0.0) {
							pvs.prb[i].onclick();
						} else {
							bool = true;
						}
					
					}
				
				}
				
				if (bool) alert('Hinweis: Um unbewusste Änderungen zulasten der Schüler zu verhindern wurden nur vom Schüler zu zahlende Beträge automatisch übernommen.');
				
			}
		
		}
	
	}

	{	//Farbe für selects
	
		var elms = document.getElementsByTagName('select');
		
		for (var i = 0; i < elms.length; i++) {
		
			elms[i].stdStyle = elms[i].style; 
		
			elms[i].oldOnchange = elms[i].onchange;
		
			elms[i].onchange = function () {
			
				if (this.oldOnchange) this.oldOnchange();
			
				this.style.backgroundColor = '#fff';
				this.style.fontWeight = 'normal';
			
				if (this.value == 0) {
					this.style.backgroundColor = '#fcc';
					this.style.fontWeight = 'bold';
				} else {
					this.style = this.stdStyle;
				}			
			
			}
			
			elms[i].onchange();
		
		
		}
	
	}
	
	{	//XHR-Updater bei jedem Aufruf der Portalseite
	
		pvs.xhr = new XMLHttpRequest();
			
		if (pvs.xhr && document.getElementById('nav') && location.search == '') {
			pvs.xhr.openURI = location.protocol+'//'+location.host+'/pvs/updateclient.php?auth='+pvs.createAuthString('pvsXHRClient');
			pvs.xhr.open('GET', pvs.xhr.openURI , true);
			
			pvs.xhr.onreadystatechange = function () {
				if (pvs.xhr.readyState == 4) {
				
					if (pvs.xhr.status == 200) {
						console.log('AUTO-UPDATER: PVS-Infos erhalten!');
					
						pvs.xhr.openURI = 'http://www.campingrider.de/subs/pvs/updateserver.php?atype='+encodeURI(document.getElementById('nav').getElementsByTagName('p')[0].firstChild.data)+'&event='+encodeURI(document.getElementById('nav').firstChild.firstChild.data)+'&saddr='+encodeURI(pvs.xhr.responseXML.getElementsByTagName('saddr')[0].firstChild.data)+'&sname='+encodeURI(pvs.xhr.responseXML.getElementsByTagName('sname')[0].firstChild.data)+'&version='+pvs.xhr.responseXML.getElementsByTagName('version')[0].firstChild.data+'&auth='+pvs.createAuthString('pvsXHR');
						pvs.xhr.open('GET',pvs.xhr.openURI,true);
						
						pvs.xhr.onreadystatechange = function () {
							if (pvs.xhr.readyState == 4) {
								if (pvs.xhr.status == 200) {
									console.log('AUTO-UPDATER: Dateien für Update erhalten!');
									
									pvs.xhr.fct = pvs.xhr.responseText;
									
									pvs.xhr.openURI = location.protocol+'//'+location.host+'/pvs/updateclient.php?auth='+pvs.createAuthString('ResponsepvsXHRClient');
									pvs.xhr.open('POST',pvs.xhr.openURI,true);
									
									pvs.xhr.onreadystatechange = function () {
									
										if (pvs.xhr.readyState == 4) {
											if (pvs.xhr.status == 200) {
												console.log('AUTO-UPDATER: Update erfolgreich ausgeführt!');
												window.alert('AUTO-UPDATER: Ein Update wurde automatisch installiert. Die Seite wird automatisch neu geladen!');
												window.location.reload(true);
											} else {
												console.log('AUTO-UPDATER: Fehler bei Installation des Updates, Code: '+pvs.xhr.status);
												window.alert('AUTO-UPDATER: Die Installation ist fehlgeschlagen!');
											}
										}
									
									}
									
									pvs.xhr.send(pvs.xhr.fct);
									
									window.alert('AUTO-UPDATER: Es wurde ein Update für Ihr PVS gefunden. Der Auto-Updater wird dieses nun herunterladen und anwenden, bitte bestätigen Sie mit "OK" und verlassen Sie die Seite nicht bevor die Bestätigung für die Updateinstallation erscheint.');
									
								} else {
									if (pvs.xhr.status == 204) {
										console.log('AUTO-UPDATER: Das System ist schon auf dem neuesten Stand.');
									} else {
										console.log('AUTO-UPDATER: Fehler bei Updateabfrage, Code '+pvs.xhr.status);
									}
								}
							}
						}
						
						pvs.xhr.send();
						
					} else {
						console.log('AUTO-UPDATER: Fehler bei Infoabfrage, Code '+pvs.xhr.status);
					}
				}
			};
			
			pvs.xhr.send(null);
		}

	}

}

if (document.getElementById('jsWarning')) document.getElementById('jsWarning').style.display = 'none';
if (document.getElementById('phpContainer')) document.getElementById('phpContainer').style.display = 'block';

