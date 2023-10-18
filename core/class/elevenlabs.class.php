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

/* * ***************************Includes********************************* */
require_once __DIR__  . '/../../../../core/php/core.inc.php';
require_once __DIR__ . "/../php/elevenlabs.inc.php";

class Elevenlabs extends eqLogic {

  /*     * *************************Attributs****************************** */
  public static function tts($_filename,$_text) {
    try {
      $voice = config::byKey("voice","elevenlabs");
      $clarity = config::byKey("clarity","elevenlabs",0.5);
      $stability = config::byKey("stability","elevenlabs",0.75);
      $model = config::byKey("model","elevenlabs",ElevenlabsConstant::$DEFAULT_MODEL);
      log::add('elevenlabs', 'debug', 'input text : ' .$_text);      
      log::add('elevenlabs', 'debug', 'voice :  ' .$voice);

      $file = ElevenlabsConstant::getMp3($_text,$voice,$stability,$clarity,$model);
      if(!Helpers::isNotNullOrEmpty($file)){
        $path = ElevenlabsConstant::$MP3_SYSTEM_PATH.basename($file);
        log::add('elevenlabs', 'debug', 'copy' .$path. ' to '.$file);
        copy($path,$_filename);
      }
    } catch (Exception $e) {
      log::add('elevenlabs', 'error', 'exception tts : ' . $e->getMessage());
    }
  }

  //Create static method to get different voice from elevenlabs
  public static function getVoice() {
    
      $apiKey = config::byKey("apiKey","elevenlabs");
      $url = ElevenlabsConstant::$BASEAPI_URL.ElevenlabsConstant::$VOICES_API;   
      //query a get request to elevenlabs with curl
      $ch = curl_init();     
      curl_setopt($ch, CURLOPT_URL, $url);
      //add xi api to header
      curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'xi-api-key: '.$apiKey,
        'accept: application/json'
      ));
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
      $result = curl_exec($ch);
      curl_close($ch);
      //todo gerer erreur
      return json_decode($result);
  }
  
  public static function getMp3($text,$voiceId,$stability = 0.5,$similarity_boost =0.75,$model = "eleven_multilingual_v2")
  {
    $apiKey = config::byKey("apiKey","elevenlabs");
    if(Helpers::isNullOrEmpty($apiKey)){
      log::add('elevenlabs', 'error', 'api key not defined');
      return;
    }
    if(Helpers::isNullOrEmpty($voiceId)){
      log::add('elevenlabs', 'error', 'voice not defined');
      return;
    }
    
    if(!file_exists(ElevenlabsConstant::$MP3_SYSTEM_PATH)){
      mkdir (ElevenlabsConstant::$MP3_SYSTEM_PATH,0755,true);
    }
    $filename = $voiceId.'_'.hash('md5',$stability.'_'.$similarity_boost.'_'.$model).'_'.hash('md5', $text).'.mp3';
    $path = ElevenlabsConstant::$MP3_SYSTEM_PATH.$filename;

    if(file_exists($path))
    {
      log::add('elevenlabs', 'debug', 'tts : ' .$text. ' existing');
      touch($path, time());
      return ElevenlabsConstant::$MP3_PLUGIN_PATH.$filename;
    }
    log::add('elevenlabs', 'debug', 'tts : ' .$text. ' requested');
    log::add('elevenlabs', 'debug', 'url : ' .ElevenlabsConstant::$BASEAPI_URL.ElevenlabsConstant::$TTS_API.$voiceId);
    $ch = curl_init(ElevenlabsConstant::$BASEAPI_URL.ElevenlabsConstant::$TTS_API.$voiceId);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_NOBODY, 0);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
      'xi-api-key: '.$apiKey,
      'accept: audio/mpeg',
      'Content-Type: application/json'
    ));
    $post_data = array(				
      'text' => $text,
      'model_id' => $model,
      'stability' => $stability,
      'similarity_boost' => $similarity_boost
    );
    log::add('elevenlabs', 'debug', 'query parameter : ' .json_encode($post_data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    
    curl_setopt( $ch, CURLOPT_POSTFIELDS, json_encode($post_data) );
    $output = curl_exec($ch);
    $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
   
    if ($status == 200) {
        
        file_put_contents($path, $output);
        return ElevenlabsConstant::$MP3_PLUGIN_PATH.$filename;
    }
    log::add('elevenlabs', 'error', 'status code : '.$status.' message : '.$output);
    return null;
  }

  /*
  * Permet de définir les possibilités de personnalisation du widget (en cas d'utilisation de la fonction 'toHtml' par exemple)
  * Tableau multidimensionnel - exemple: array('custom' => true, 'custom::layout' => false)
  public static $_widgetPossibility = array();
  */

  /*
  * Permet de crypter/décrypter automatiquement des champs de configuration du plugin
  * Exemple : "param1" & "param2" seront cryptés mais pas "param3"
  public static $_encryptConfigKey = array('param1', 'param2');
  */

  /*     * ***********************Methode static*************************** */

  /*
  * Fonction exécutée automatiquement toutes les minutes par Jeedom
  public static function cron() {}
  */

  /*
  * Fonction exécutée automatiquement toutes les 5 minutes par Jeedom
  public static function cron5() {}
  */

  /*
  * Fonction exécutée automatiquement toutes les 10 minutes par Jeedom
  public static function cron10() {}
  */

  /*
  * Fonction exécutée automatiquement toutes les 15 minutes par Jeedom
  public static function cron15() {}
  */

  /*
  * Fonction exécutée automatiquement toutes les 30 minutes par Jeedom
  public static function cron30() {}
  */

  /*
  * Fonction exécutée automatiquement toutes les heures par Jeedom
  public static function cronHourly() {}
  */

  /*
  * Fonction exécutée automatiquement tous les jours par Jeedom*/
  public static function cronDaily() {
    $lifeTime = config::byKey("cacheLifetime","elevenlabs");
    $intLifeTime = intval($lifeTime);
    if($intLifeTime  >= 1){
      log::add('elevenlabs', 'debug', 'lifetime : '.$lifeTime);
      $files = scandir(ElevenlabsConstant::$MP3_SYSTEM_PATH);
      foreach($files as $file) {
        log::add('elevenlabs', 'debug', 'file : '.$file);
        $dateTime = new DateTime();
        $dateTimeFile = new DateTime();
        $dateTimeFile->setTimestamp(filemtime(ElevenlabsConstant::$MP3_SYSTEM_PATH.$file));
        $interval = $dateTime->diff($dateTimeFile);
        log::add('elevenlabs', 'debug', 'last modification days diff : '.$interval->d);
        if($interval->d >= $intLifeTime){
          log::add('elevenlabs', 'info', 'delete file : '.$file);
          unlink(ElevenlabsConstant::$MP3_SYSTEM_PATH.$file);
        }
        //do your work here
      }
    }else{
      log::add('elevenlabs', 'debug', 'files neved deleted');
    }
  }
  

  /*     * *********************Méthodes d'instance************************* */

  // Fonction exécutée automatiquement avant la création de l'équipement
  public function preInsert() {
  }

  // Fonction exécutée automatiquement après la création de l'équipement
  public function postInsert() {
  }

  // Fonction exécutée automatiquement avant la mise à jour de l'équipement
  public function preUpdate() {
  }

  // Fonction exécutée automatiquement après la mise à jour de l'équipement
  public function postUpdate() {
  }

  // Fonction exécutée automatiquement avant la sauvegarde (création ou mise à jour) de l'équipement
  public function preSave() {
  }

  // Fonction exécutée automatiquement après la sauvegarde (création ou mise à jour) de l'équipement
  public function postSave() {
    $tts = $this->getCmd(null, 'TTS');
    if (!is_object($tts)) {
      $tts = new ElevenlabsCmd();
      $tts->setName(__('TTS', __FILE__));
    }
    $tts->setLogicalId('TTS');
    $tts->setEqLogic_id($this->getId());
    $tts->setType('action');
    $tts->setSubType('message');
    $tts->save();
  
    $mp3 = $this->getCmd(null, 'MP3');
    if (!is_object($mp3)) {
      $mp3 = new ElevenlabsCmd();
      $mp3->setName(__('Fichier MP3', __FILE__));
    }
    $mp3->setEqLogic_id($this->getId());
    $mp3->setLogicalId('mp3');
    $mp3->setType('info');
    $mp3->setSubType('string');
    $mp3->save();
  }

  // Fonction exécutée automatiquement avant la suppression de l'équipement
  public function preRemove() {
  }

  // Fonction exécutée automatiquement après la suppression de l'équipement
  public function postRemove() {
  }

  /*
  * Permet de crypter/décrypter automatiquement des champs de configuration des équipements
  * Exemple avec le champ "Mot de passe" (password)
  public function decrypt() {
    $this->setConfiguration('password', utils::decrypt($this->getConfiguration('password')));
  }
  public function encrypt() {
    $this->setConfiguration('password', utils::encrypt($this->getConfiguration('password')));
  }
  */

  /*
  * Permet de modifier l'affichage du widget (également utilisable par les commandes)
  public function toHtml($_version = 'dashboard') {}
  */

  /*
  * Permet de déclencher une action avant modification d'une variable de configuration du plugin
  * Exemple avec la variable "param3"
  public static function preConfig_param3( $value ) {
    // do some checks or modify on $value
    return $value;
  }
  */

  /*
  * Permet de déclencher une action après modification d'une variable de configuration du plugin
  * Exemple avec la variable "param3"
  public static function postConfig_param3($value) {
    // no return value
  }
  */

  /*     * **********************Getteur Setteur*************************** */

}
if (!class_exists('Helpers')) {
  require_once dirname(__FILE__) . "/../php/helpers.php";
}
class ElevenlabsCmd extends cmd {


  /*     * *************************Attributs****************************** */

  /*
  public static $_widgetPossibility = array();
  */

  /*     * ***********************Methode static*************************** */


  /*     * *********************Methode d'instance************************* */

  /*
  * Permet d'empêcher la suppression des commandes même si elles ne sont pas dans la nouvelle configuration de l'équipement envoyé en JS
  public function dontRemoveCmd() {
    return true;
  }
  */

  // Exécution d'une commande
  public function execute($_options = array()) {
    switch ($this->getLogicalId()) {
      case 'TTS': //LogicalId de la commande pour charger le TTS
        $eqlogic = $this->getEqLogic(); //Récupération de l’eqlogic
        $text = "";
        if(!(!isset($_options['title']) || trim($_options['title'])==='')){
          $text = $_options['title'] . " ";
        }
        if(!(!isset($_options['message']) || trim($_options['message'])==='')){
          $text = $text.$_options['message'];
        }
        if(!isset($text) || trim($text)==='')
        {
          log::add('elevenlabs', 'error', 'title and message are empty');
          return;
        }
        log::add('elevenlabs', 'debug', 'text : '.  $text);
        $voice = $eqlogic->getConfiguration('voice');
        $clarity = $eqlogic->getConfiguration('clarity');
        $stability = $eqlogic->getConfiguration('stability');   
        $stability = $eqlogic->getConfiguration('stability');  
        $model = $eqlogic->getConfiguration('model',ElevenlabsConstant::$DEFAULT_MODEL);    
        try{ 
        $file = $eqlogic->getMp3($text,$voice,$stability,$clarity,$model);
          if(!(!isset($file) || trim($file)===''))
          {
            $path = ElevenlabsConstant::$MP3_SYSTEM_PATH.basename($file);
            log::add('elevenlabs', 'debug', 'set mp3 cmd to '.$file);
            $mp3Cmd = $eqlogic->getCmd(null, 'MP3');
            $mp3Cmd->event($file);
          }else{
            log::add('elevenlabs', 'error', 'no file generated');
          }
        }catch(Exception $e){
          log::add('elevenlabs', 'error', 'exception tts : ' . $e->getMessage());
        }
        
      break;
    }
  }

  /*     * **********************Getteur Setteur*************************** */

}