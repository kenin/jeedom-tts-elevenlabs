<?php
/* This file is part of Jeedom.
*
* Jeedom is free software: you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation, either version 3 of the License, or
* (at your option) any later version.
*
* Jeedom is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with Jeedom. If not, see <http://www.gnu.org/licenses/>.
*/

require_once dirname(__FILE__) . '/../../../core/php/core.inc.php';
include_file('core', 'authentification', 'php');
if (!class_exists('Elevenlabs')) {
	require_once dirname(__FILE__) . "/../core/class/elevenlabs.class.php";
}
if (!isConnect()) {
  include_file('desktop', '404', 'php');
  die();
}

$apiKey = config::byKey('apiKey', 'elevenlabs');
if(isset($apiKey)){
 $voices = Elevenlabs::getVoice();
}
?>
<form class="form-horizontal">
  <fieldset>
  <div class="form-group">
				<label class="col-sm-4 control-label">{{Clé d'api elevenlabs}}</label>
				<div class="col-sm-6">
					<input type="text" class="configKey form-control" data-l1key="apiKey">
				</div>
			</div>
			<div class="form-group">
				<label class="col-sm-4 control-label"> {{Voix}}<sup><i class="fas fa-question-circle tooltips" title="{{Veuillez sauvegarder la clé d'api avant de pouvoir choisir la voix}}"></i></sup></label>
				<div class="col-sm-6">
					<select class="configKey form-control"  data-l1key="voice" disabled >
                    <option>Veuillez choisir une voix</option>
                    <?php
                    foreach($voices->voices as $voice){
						echo '<option value="'.$voice->voice_id.'">'.$voice->name.' ('.$voice->labels->gender.', '.$voice->labels->age.')</option>';
					}
					?>
					</select>
				</div>
			</div>
              <div class="form-group">
				<label class="col-sm-4 control-label"></label>
				<div class="col-sm-6">
                    <a id="btn_test" >{{Tester}}</a>
				</div>
			</div>
      <div class="form-group">
				<label class="col-sm-4 control-label">{{Durée de vie du cache}}<sup><i class="fas fa-question-circle tooltips" title="{{Durée durant laquelle les fichiers mp3 TTS non utilisés seront conservés}}"></i></sup></label>
				<div class="col-sm-6">
					<select class="configKey form-control" data-l1key="cacheLifetime" disabled>
						<option value="1">1 jour</option>
						<option value="7">1 semaines</option>
						<option value="30">1 mois</option>
						<option value="365">1 année</option>
						<option value="-1">Jamais</option>
					</select>
				</div>
			</div>
  </fieldset>
</form>
<script>
setTimeout(() =>{
	let apiKey=  document.querySelector('.configKey[data-l1key=apiKey]').value;
	if(apiKey != null && apiKey != ""){
		document.querySelector('.configKey[data-l1key=voice]').disabled = false;
		document.querySelector('.configKey[data-l1key=cacheLifetime]').disabled = false;
	} 
	//on click ton bt_savePluginConfig

	document.querySelector("#bt_savePluginConfig").addEventListener("click",function(e){
		setTimeout(() => {
			window.location.reload();
		}, 300);
		
	},false);

	document.querySelector("#btn_test").addEventListener("click",function(e){
    	e.preventDefault();
		let apiKey2 =  document.querySelector('.configKey[data-l1key=apiKey]').value;
		let voiceId = document.querySelector('.configKey[data-l1key=voice]').value;		

		fetchFile("{{Bonjour, je suis le texte de test.}}",voiceId).then((response) => {
			var a = new Audio(response);
    		a.play();
		})
		.catch((error) => {
			console.error(error);
		});
	},true);


},1000);
async function fetchFile(word,voiceId){
	const formData  = new URLSearchParams();
      

    formData.append('action', 'getSample');
    formData.append('text', word);
	formData.append('voiceId', voiceId);
	const requestOptions = {
    method: 'POST',
    body: formData,
	headers: {
      "accept": "application/json",
    },
  };
	var result = await fetch('plugins/elevenlabs/core/ajax/elevenlabs.ajax.php',requestOptions);
	var json = await result.json();                                                      
	console.log(json);
	return json.result;
}

</script>