<?php
    require 'config/config.php';
    require "libs/botLogger.class.php";
    require "libs/telegram.class.php";
    require 'libs/telegramProcessUpdate.class.php';

    $content = file_get_contents("php://input");
    $update = json_decode($content);

    //file_put_contents("input.txt", $content);
    //file_put_contents("post.txt", var_export($_REQUEST, true));

    //$botLogger = new BotLogger();

    $oProcessUpdate = new TelegramProcessUpdate($update);


    file_put_contents('/var/www/html/palantirBotv2/logs/debugUpdate.log', var_export($oProcessUpdate, true), FILE_APPEND);

    $oTelegramBot = new Telegram($oProcessUpdate->oUser->id);

    $oTelegramBot->sendText($oProcessUpdate->oUser->id, "You Wrote: ". $oProcessUpdate->oMessageContent->text);

    
    /*file_put_contents('/var/www/html/palantirBotv2/logs/debugUpdate.log', var_export(property_exists($update, "message"), true), FILE_APPEND);

    file_put_contents('/var/www/html/palantirBotv2/logs/debugUpdate.log', var_export(property_exists($update->message->from, "username"), true), FILE_APPEND);*/

    /*$telegramBot = new Telegram($update['message']['chat']['id']);
    
    $botLogger->debugLog(var_export($update, true));

    if (array_key_exists('message', $update)) {
        $telegramBot->sendText($update['message']['chat']['id'], "Escribiste: ".$update['message']['text']);
        $telegramBot->sendKeyMap($update['message']['chat']['id'], "Eliga su veneno");
        $telegramBot->log->log("Recibí un mensaje de ".$update['message']['chat']['id']."--".$update['message']['chat']['username']." y le contesté");
    } elseif (array_key_exists('edited_message', $update)) {
        $telegramBot->sendText($update['edited_message']['chat']['id'], "Lo editaste a: ".$update['edited_message']['text']);
    } else {
        $telegramBot->sendText('107392361', 'Se mandó algo que no entiendo. Voy a loguear el mensaje para análisis');
        $telegramBot->log->debugLog("Esto no sé que es:\n\n\n".var_export($update, true));
    }*/

?>