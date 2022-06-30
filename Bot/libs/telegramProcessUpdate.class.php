<?php
class TelegramProcessUpdate
{
    /**
    * Declarar como propiedades todos los elementos que voy a manejar (perfil, id, etc)
    * En el constructor analizar el tipo 
    * Dejar en una variable el tipo de función
    */    

    public $oUser;
    public $oMessageContent;
    public $sMsgType;

    public function __construct($oUpdate) {
        /* Buscar propiedades claves para determinar el tipo */

        if (property_exists($oUpdate, 'message')) {
            $this->oMessageContent = $oUpdate->message;
            $this->oUser = $oUpdate->message->from;
            $this->sMsgType = "message";
        } elseif (property_exists($oUpdate, 'edited_message')) {
            $this->oMessageContent = $oUpdate->edited_message;
            $this->oUser = $oUpdate->edited_message->from;
            $this->sMsgType = "message";
        } elseif (property_exists($oUpdate, 'callback_query')) {
            $this->sMsgType = "callback";
            $this->oMessageContent = $oUpdate->callback_query;
            $this->oUser = $oUpdate->callback_query->from;
        }
    }
}
?>