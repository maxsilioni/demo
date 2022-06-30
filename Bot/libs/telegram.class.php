<?php

require_once('botLogger.class.php');
/**
 * Clase para realizar las conexiones con Telegram.
 *  Determinar si Armamos un método por tipo de llamada.
 *  Determinar sistema de logeo de errores.
 *  Base de datos para cola de comandos.
 *  Script para ejecutar los comandos.
 *  Script para el envío de respuestas desde consola.
 *  Generar una forma de procesar los tipos de pedidos
 *  
 */
class Telegram
{
    
   private $curl;
   public $log;
   private $authIds;
   
   public function __construct($iUserID)
   {
      $this->log = new BotLogger();

      if (defined('AUTHID')) {
         $this->authIds = explode(",", AUTHID);
      }

      if (defined('API_URL')) {
         $this->curl = curl_init(API_URL);
         curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, true);
         curl_setopt($this->curl, CURLOPT_CONNECTTIMEOUT, 5);
         curl_setopt($this->curl, CURLOPT_TIMEOUT, 60);
         curl_setopt($this->curl, CURLOPT_HTTPHEADER, array("Content-Type: multipart/form-data"));
      } else {
         $this->log->log("No se encuentra API_URL en la configuración", BotLogger::ERROR);
         die();
      }

      $this->authResponse($iUserID);
   }

   /**
    * Función para autorizar si responder a la acción de un mensajes que llega 
   */

   private function authResponse($iUserID = "local")
   {
      if (in_array($iUserID, $this->authIds) || $iUserID == 'local') {
         $this->sendAction($iUserID);
         return true;
      } else {
         $this->log->log("El usuario con ID $iUserID, intentó utilizar el bot. Saliendo", BotLogger::SECURITY);
         die();
      }
   }

   /**
    * Envia un texto a un chat
    * 
    * @param chatid = Id Del usuario a enviarle el texto
    * @param text = Texto a enviar
    * @return True / False
    * 
    */

   public function sendText($chatid, $text)
   {
      $parameters = array("method"=>"sendMessage", 'chat_id'=>$chatid, 'text'=>$text);

      curl_setopt($this->curl, CURLOPT_POSTFIELDS, $parameters);

      $response = curl_exec($this->curl);

      $this->log->debugLog($response);

      return true;

   }

   public function sendKeyMap($chatid, $text='', $keysMap='')
   {
      if($keysMap == ""){
         $keysMap = array(
            "inline_keyboard" => array(array(
               array("text"=>"1", "callback_data"=>"1"),
               array("text"=>"2", "callback_data"=>"2"),
               array("text"=>"3", "callback_data"=>"3"),
               array("text"=>"4", "callback_data"=>"4"),
               array("text"=>"5", "callback_data"=>"5"),
            ))
         );
      }

      $this->log->debugLog(var_export($keysMap, true));


      $parameters = array(
         "method" => 'sendMessage',
         "chat_id" => $chatid,
         "text" => $text,
         "reply_markup" => json_encode($keysMap)
      );

      curl_setopt($this->curl, CURLOPT_POSTFIELDS, $parameters);

      $response = curl_exec($this->curl);

      $this->log->debugLog($response);

      return true;      
   }

   /* Si el usuario está autorizado el bot mostrará escribiendo mientras procesa el pedido */
   public function sendAction($chatid)
   {
      $parameters = array("method"=>"sendChatAction", 'chat_id'=>$chatid, 'action'=>'typing');

      curl_setopt($this->curl, CURLOPT_POSTFIELDS, $parameters);

      $response = curl_exec($this->curl);

      return true;

   }

}

?>