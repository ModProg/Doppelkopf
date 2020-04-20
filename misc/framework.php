<?php
define("APP_GAMEMODULE_PATH","");

function clienttranslate(string $text){return "";}

class Table{

    function __construct(){}
    static function initGameStateLabels(){}
    static function getNew(string $a):object{return new Table();}
    static function getGameinfos(){}
    static function reattributeColorsBasedOnPreferences(array $players,array $colors){}
    static function reloadPlayersBasicInfos(){}
    // Player
    /**
     * @return int
     */
    static function getCurrentPlayerID(){}
    /**
     * Activates next Player
     * @return int
     */
    static function activeNextPlayer(){}
    /**
     * @return int
     */
    static function getActivePlayerID(){}
    /**
     * @return string
     */
    static function getActivePlayerName(){}
    /**
     * @return array
     */
    static function loadPlayersBasicInfos(){}

    /**
     * @return int
     */
    static function getPlayersNumber(){}

    static function giveExtraTime(int $player_id){}

    // Global Variables
    static function setGameStateInitialValue(string $name, int $value){}
    /**
     * @return int
     */
    static function getGameStateValue(string $name){}
    static function setGameStateValue(string $name, int $value){}
    static function incGameStateValue(string $name, int $amount){}

    // Database
    /**
     * 
     */
    static function DBQuery(string $sql){}
    /**
     * @return int|string
     */
    static function getUniqueValueFromDB(){}
    /**
     * @return array
     */
    static function getCollectionFromDB(string $sql){}
    /**
     * @return array
     */
    static function getObjectListFromDB(string $sql){}

    /**
     * @return array
     */
    static function getObjectFromDB(string $sql){}

    // Notifications
    static function notifyAllPlayers(string $id,string $text, array $args){}

    static function notifyPlayer(int $player_id, string $notification_id,string $text, array $args){}

    // Actions
    static function checkAction(string $action){}

    // Debug
    static function error(string $message){}

    static function dump(string $message, $object){}
}

class feException{
    function __construct(string $message){}
}
