<?php

/**
* Clase de Logging para el bot
* Path de login configurable pero con valores por defecto
* 
* La rotación podrá ser desde el scritp o desde un script externo con Cron.
* 
*/

class BotLogger 
{
    private $path;
    private $filelog = "botlog.log";
    private $logTarget;

    const INFO = "[INFO]";
    
    const ERROR = "[ERROR]";

    const SECURITY = "[SECURITY]";

    public function __construct() {
        /* Esto esta mal. hay que verificar que esté definido. Corregir el defined acá y en telegram.class algo no está tomando. */
        if (defined('LOGPATH') || empty(LOGPATH)){
            $this->path = LOGPATH;
        } else {
            $this->$path = $_SERVER['DOCUMENT_ROOT']."/logs/";
        }

        if (defined('FILELOG') || empty(FILELOG)) {
            $this->filelog = FILELOG;
        }

        if (!is_dir($this->path) || !file_exists($this->path)) {
            mkdir($this->path, 0755, true);
        }

        $this->logTarget = $this->path.$this->filelog;

        if (!file_exists($this->logTarget)) {
            file_put_contents($this->logTarget, sprintf("[INFO]: Starts logging at %s \n", date('Y-m-d H.i.s')));
        }
    }

    public function log ($logText, $logType = BotLogger::INFO) {

        // DateTime - Type - mensaje de log

        $sLog = sprintf("%s - [%s] - %s\n", $logType, date('Y-m-d H.i.s'), $logText);

        file_put_contents($this->logTarget, $sLog, FILE_APPEND);

        return true;
    }

    public function debugLog($logText) {

        $sLog = "[DEBUG]: \n".$logText. "[\DEBUG]\n";

        file_put_contents($this->logTarget, $sLog, FILE_APPEND);
        return true;
    }
}

?>