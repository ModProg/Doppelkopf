<?php
define("APP_GAMEMODULE_PATH", "");

function clienttranslate(string $text)
{
    return "";
}


// Randomness
/**
 * Returns a random int between $min and $max
 */
function bga_rand(int $min, int $max)
{
}

class Table
{

    function __construct()
    {
    }
    static function getNew(string $a): object
    {
        return new Table();
    }
    static function getGameinfos()
    {
    }


    /**
     * Not documented
     */
    static function reattributeColorsBasedOnPreferences(array $players, array $colors)
    {
    }

    /**
     * Not documented
     */
    static function reloadPlayersBasicInfos()
    {
    }

    public Gamestate $gamestate;

    ////// Player

    // Current Player
    /**
     * Get the "current_player". The current player is the one from which the action originated (the one who sent the request).
     *
     * **Be careful**: This is not necessarily the active player!
     * In general, you shouldn't use this method, unless you are in "multiplayer" state.
     *
     * **Very important**: in your setupNewGame and zombieTurn function, you must never use getCurrentPlayerId() or getCurrentPlayerName(), otherwise it will fail with a "Not logged" error message (these actions are triggered from the main site and propagated to the gameserver from a server, not from a browser. As a consequence, there is no current player associated to these actions).
     * @return int
     */
    static function getCurrentPlayerId()
    {
    }

    /**
     * Get the "current_player" name.  The current player is the one from which the action originated (the one who sent the request).
     *
     * **Be careful**: This is not necessarily the active player!
     * In general, you shouldn't use this method, unless you are in "multiplayer" state.
     *
     * **Very important**: in your setupNewGame and zombieTurn function, you must never use getCurrentPlayerId() or getCurrentPlayerName(), otherwise it will fail with a "Not logged" error message (these actions are triggered from the main site and propagated to the gameserver from a server, not from a browser. As a consequence, there is no current player associated to these actions).
     * @return string
     */
    static function getCurrentPlayerName()
    {
    }

    /**
     * Get the "current_player" color.  The current player is the one from which the action originated (the one who sent the request).
     *
     * **Be careful**: This is not necessarily the active player!
     * In general, you shouldn't use this method, unless you are in "multiplayer" state.
     *
     * **Very important**: in your setupNewGame and zombieTurn function, you must never use getCurrentPlayerId() or getCurrentPlayerName(), otherwise it will fail with a "Not logged" error message (these actions are triggered from the main site and propagated to the gameserver from a server, not from a browser. As a consequence, there is no current player associated to these actions).
     * @return string
     */
    static function getCurrentPlayerColor()
    {
    }

    /**
     * Check the "current_player" zombie status. If true, player is zombie, i.e. left or was kicked out of the game.
     * @return bool
     */
    static function isCurrentPlayerZombie()
    {
    }

    // Active Player
    /**
     * Get the "active_player", whatever what is the current state type.
     *
     * Note: it does NOT mean that this player is active right now, because state type could be "game" or "multiplayer"
     *
     * Note: avoid using this method in a "multiplayer" state because it does not mean anything.
     * @return int
     */
    static function getActivePlayerId()
    {
    }
    /**
     * Get the "active_player" name, whatever what is the current state type.
     *
     * Note: it does NOT mean that this player is active right now, because state type could be "game" or "multiplayer"
     *
     * Note: avoid using this method in a "multiplayer" state because it does not mean anything.
     * @return string
     */
    static function getActivePlayerName()
    {
    }

    /**
     * Make the next player active (in the natural player order). 
     *
     * Note: you CANT use this method in a "activeplayer" or "multipleactiveplayer" state. You must use a "game" type game state for this.
     * @return int now active player
     */
    static function activeNextPlayer()
    {
    }


    /**
     * Make the previous player active (in the natural player order). 
     *
     * Note: you CANT use this method in a "activeplayer" or "multipleactiveplayer" state. You must use a "game" type game state for this.
     * @return int now active player
     */
    static function activePrevPlayer()
    {
    }
    // Players overall
    /**
     * Returns the number of players playing at the table
     *
     * Note: doesn't work in setupNewGame (use count($players) instead).
     * @return int
     */
    static function getPlayersNumber()
    {
    }

    /**
     * Get an associative array with generic data about players (ie: not game specific data).
     *
     * The key of the associative array is the player id. The returned table is cached, so ok to call multiple times without performance concerns.
     *
     * The content of each value is:
     *  - player_name - the name of the player
     *  - player_color (ex: ff0000) - the color code of the player
     *  - player_no - the position of the player at the start of the game in natural table order, i.e. 1,2,3
     * @return array
     */
    static function loadPlayersBasicInfos()
    {
    }

    /**
     * Eliminate a player from the game in order he/she can start another game without waiting for the current game end
     * Player to eliminate should NOT be active anymore (preferably use the feature in a "game" type game state).
     */
    static function eliminatePlayer(int $player_id)
    {
    }

    /**
     * Give standard extra time to this player. 
     * 
     * Standard extra time depends on the speed of the game (small with "slow" game option, bigger with other options). 
     * 
     * You can also specify an exact time to add, in seconds, with the "specified_time" argument (rarely used). 
     */
    static function  giveExtraTime( int $player_id, float $specific_time=null ) {}
    // Turn Order

    /**
     * Return an associative array which associate each player with the next player around the table.
     *
     * In addition, key 0 is associated to the first player to play. 
     * @return array
     */
    static function getNextPlayerTable()
    {
    }


    /**
     * Return an associative array which associate each player with the previous player around the table.
     *
     * In addition, key 0 is associated to the first player to play. 
     * @return array
     */
    static function getPrevPlayerTable()
    {
    }

    /**
     * Get player playing after given player in natural playing order. 
     * @return int player id
     */
    static function getPlayerAfter(int $player_id)
    {
    }

    /**
     * Get player playing before given player in natural playing order. 
     * @return int player id
     */
    static function getPlayerBefore(int $player_id)
    {
    }


    ////// Accessing the Database
    /**
     * This is the generic method to access the database.
     *
     * It can execute any type of SELECT/UPDATE/DELETE/REPLACE query on the database.
     *
     * You should use it for UPDATE/DELETE/REPLACE queries. For SELECT queries, the specialized methods below are much better.
     */
    static function DbQuery(string $sql)
    {
    }

    /**
     * Returns a unique value from DB or null if no value is found.
     *
     * $sql must be a SELECT query.
     * Raise an exception if more than 1 row is returned.
     * @return int|string
     */
    static function getUniqueValueFromDB()
    {
    }

    /**
     * Returns an associative array of rows for a sql SELECT query.
     *
     * The key of the resulting associative array is the first field specified in the SELECT query.
     * The value of the resulting associative array if an associative array with all the field specified in the SELECT query and associated values.
     *
     * First column must be a primary or alternate key.
     * The resulting collection can be empty.
     *
     * If you specified $bSingleValue=true and if your SQL query request 2 fields A and B, the method returns an associative array "A=>B"
     * @return array
     */
    static function getCollectionFromDB(string $sql, bool $singleValue = false)
    {
    }

    /**
     * Returns an associative array of rows for a sql SELECT query, but raise an exception if the collection is empty.
     *
     * The key of the resulting associative array is the first field specified in the SELECT query.
     * The value of the resulting associative array if an associative array with all the field specified in the SELECT query and associated values.
     *
     * First column must be a primary or alternate key.
     * The resulting collection can be empty.
     *
     * If you specified $bSingleValue=true and if your SQL query request 2 fields A and B, the method returns an associative array "A=>B"
     * @return array
     */
    static function getNonEmptyCollectionFromDB(string $sql, bool $singleValue = false)
    {
    }

    /**
     * Returns one row for the sql SELECT query as an associative array or null if there is no result
     *
     * Raise an exception if the query return more than one row
     * @return array
     */
    static function getObjectFromDB(string $sql)
    {
    }

    /**
     * Returns one row for the sql SELECT query as an associative array
     *
     * Raise an exception if the query returns more than one row
     * Raise an exception if the query returns no result
     * @return array
     */
    static function getNonEmptyObjectFromDB(string $sql)
    {
    }

    /**
     * Return an array of rows for a sql SELECT query.
     *
     * The result is the same as "getCollectionFromDB" except that the result is a simple array (and not an associative array).
     * The result can be empty.
     *
     * If you specified $bUniqueValue=true and if your SQL query request 1 field, the method returns directly an array of values.
     * @return array
     */
    static function getObjectListFromDB(string $sql, bool $uniqueValue = false)
    {
    }

    /**
     * Return the PRIMARY key of the last inserted row (see PHP mysql_insert_id function).
     */
    static function DbGetLastId()
    {
    }

    /**
     * Return the number of row affected by the last operation
     */
    static function DbAffectedRow()
    {
    }

    /**
     * This method makes sure that no SQL injection will be done through the string used.
     *
     * Note: if you using standard types in ajax actions, like AT_alphanum it is sanitized before arrival,
     * this is only needed if you manage to get unchecked string, like in the games where user has to enter text as a response.
     */
    static function escapeStringForDB(string $string)
    {
    }

    ///// Global Variables
    /**
     * his method should be located at the beginning of yourgamename.php. This is where you define the globals used in your game logic, by assigning them IDs.
     *
     * You can define up to 79 globals, with IDs from 10 to 89 (inclusive). You must not use globals outside this range, as those values are used by other components of the framework.
     * @param array $game_state_labels associative array: label => id
     */
    static function initGameStateLabels(array $game_state_labels)
    {
    }

    /**
     * Initialize your global value. Must be called before any use of your global, so you should call this method from your "setupNewGame" method.
     */
    static function setGameStateInitialValue(string $value_label, int $value_value)
    {
    }

    /**
     * Retrieve the current value of a global.
     * @return int
     */
    static function getGameStateValue(string $value_label)
    {
    }

    /**
     * Set the current value of a global.
     */
    static function setGameStateValue(string $value_label, int $value_value)
    {
    }

    /**
     * Increment the current value of a global. If increment is negative, decrement the value of the global.
     * @return int Return the final value of the global.
     */
    static function incGameStateValue(string $value_label, int $increment)
    {
    }


    // Notifications
    /**
     * Send a notification to all players of the game. 
     * @param string $notification_type A string that defines the type of your notification.
     * 
     * Your game interface Javascript logic will use this to know what is the type of the received notification (and to trigger the corresponding method).  
     * 
     * @param string $notification_log A string that defines what is to be displayed in the game log.
     *
     * You can use an empty string here (""). In this case, nothing is displayed in the game log.
     *
     * If you define a real string here, you should use "clienttranslate" method to make sure it can be translate.
     *
     * You can use arguments in your notification_log strings, that refers to values defines in the "notification_args" argument (see below). Note: Make sure you only use single quotes ('), otherwise PHP will try to interpolate the variable and will ignore the values in the args array. 
     * 
     * **Important**: the variable for player name must be ${player_name} in order to be highlighted with the player color in the game log 
     * 
     * @param array $notification_args 
     * 
     * The arguments of your notifications, as an associative array.
     * 
     * This array will be transmitted to the game interface logic, in order the game interface can be updated. 
     */
    static function notifyAllPlayers(string $notification_type, string $notification_log, array $notification_args)
    {
    }

    /**
     * Send a notification to one player. 
     * 
     * This method must be used each time some private information must be transmitted to a player. 
     * 
     * @param int $player_id Player to be notified
     * 
     * @param string $notification_type A string that defines the type of your notification.
     * 
     * Your game interface Javascript logic will use this to know what is the type of the received notification (and to trigger the corresponding method).  
     * 
     * @param string $notification_log A string that defines what is to be displayed in the game log.
     *
     * You can use an empty string here (""). In this case, nothing is displayed in the game log.
     *
     * If you define a real string here, you should use "clienttranslate" method to make sure it can be translate.
     *
     * You can use arguments in your notification_log strings, that refers to values defines in the "notification_args" argument (see below). Note: Make sure you only use single quotes ('), otherwise PHP will try to interpolate the variable and will ignore the values in the args array. 
     * 
     * **Important**: the variable for player name must be ${player_name} in order to be highlighted with the player color in the game log 
     * 
     * @param array $notification_args 
     * 
     * The arguments of your notifications, as an associative array.
     * 
     * This array will be transmitted to the game interface logic, in order the game interface can be updated. 
     */
    static function notifyPlayer(int $player_id, string $notification_type, string $notification_log, array $notification_args)
    {
    }


    // Game statistics

    /**
     * Create a statistic entry with a default value. This method must be called for each statistics of your game, in your setupNewGame method.
     *
     * @param string $table_or_player must be set to "table" if this is a table statistics, or "player" if this is a player statistics.
     *
     * @param string $name is the name of your statistics, as it has been defined in your stats.inc.php file.
     *
     * @param int|float $value is the initial value of the statistics. 
     * 
     * @param int|null $player_id If this is a player statistics and if the player is not specified by "$player_id" argument, the value is set for ALL players. 
     */
    static function initStat($table_or_player, $name, $value, $player_id = null)
    {
    }

    /**
     * Set a statistic $name to $value.
     * 
     * If "$player_id" is not specified, setStat considers it as a TABLE statistic.
     * 
     * If "$player_id" is specified, setStat considers it as a PLAYER statistic. 
     * @param int|float $value
     */

    static function setStat($value, string $name, int $player_id = null)
    {
    }

    /**
     * Increment (or decrement) specified statistic value by $delta value.
     * 
     * If "$player_id" is not specified, incStat considers it as a TABLE statistic.
     * 
     * If "$player_id" is specified, incStat considers it as a PLAYER statistic. 
     * @param int|float $delta
     */

    static function incStat($delta, string $name, int $player_id = null)
    {
    }

    /**
     * Return the value of statistic specified by $name. Useful when creating derivative statistics such as average. 
     * 
     * If "$player_id" is not specified, getStat considers it as a TABLE statistic.
     * 
     * If "$player_id" is specified, getStat considers it as a PLAYER statistic. 
     */
    static function getStat(string $name, int $player_id = null)
    {
    }



    // Actions
    static function checkAction(string $action)
    {
    }

    // Debug
    static function error(string $message)
    {
    }

    static function dump(string $message, $object)
    {
    }
}

class Gamestate
{
    // Active Player
    /**
     * You can call this method to make any player active. 
     *
     * Note: you CANT use this method in a "activeplayer" or "multipleactiveplayer" state. You must use a "game" type game state for this.
     */
    function changeActivePlayer()
    {
    }
    // Multiactive player handling
    /**
     * All playing players are made active. Update notification is sent to all players (triggers onUpdateActionButtons). 
     * Usually, you use this method at the beginning (ex: "st" action method) of a multiplayer game state when all players have to do some action. 
     */
    function setAllPlayersMultiactive()
    {
    }
    /**
     * Make a specific list of players active during a multiactive gamestate. Update notification is sent to all players who's state changed. 
     * "players" is the array of player id that should be made active. 
     * 
     * If "exclusive" parameter is not set or false it doesn't deactivate other previously active players. If its set to true, the players who will be multiactive at the end are only these in "$layers" array 
     * 
     * In case "players" is empty, the method trigger the "next_state" transition to go to the next game state. 
     * @return bool true if state transition happened, false otherwise 
     */
    function setPlayersMultiactive($players, $next_state, $bExclusive = false)
    {
    }
    /**
     * During a multiactive game state, make the specified player inactive. 
     * Usually, you call this method during a multiactive game state after a player did his action. 
     * 
     * If this player was the last active player, the method trigger the "next_state" transition to go to the next game state. 
     * @return bool true if state transition happened, false otherwise 
     */
    function setPlayerNonMultiactive($player_id, $next_state)
    {
    }

    /**
     * Sends update notification about multiplayer changes. 
     * 
     * All multiactive set* functions above do that, however if you want 
     * to change state manually using db queries for complex calculations, 
     * you have to call this yourself after. 
     * 
     * Do not call this if you calling one of the other setters above. 
     */
    function updateMultiactiveOrNextState($next_state_if_none)
    {
    }
    /**
     * With this method you can retrieve the list of the active player at any time. 
     * 
     * During a:
     * - "game" type gamestate, it will return a void array. 
     * - "activeplayer" type gamestate, it will return an array with one value (the active player id). 
     * - "multipleactiveplayer" type gamestate, it will return an array of the active players id. 
     * 
     * Note: you should only use this method in the latter case. 
     */
    function getActivePlayerList()
    {
    }

    // States functions
    /**
     * Change current state to a new state. 
     * 
     * **Important**: the $transition parameter is the name of the transition, 
     * and NOT the name of the target game state.
     */
    function nextState(string $transition = "")
    {
    }
    /**
     * Check if an action is valid for the current game state, and optionally, throw an exception if it isn't. 
     * 
     * The action is valid if it is listed in the "possibleactions" array for the current game state (see game state description). 
     * 
     * This method MUST be the first one called in ALL your PHP methods that handle player actions, in order to make sure a player doesn't perform an action not allowed by the rules at the point in the game. 
     * 
     * If "bThrowException" is set to "false", the function returns false in case of failure instead of throwing an exception. This is useful when several actions are possible, in order to test each of them without throwing exceptions. 
     * @return bool
     */
    function checkAction(string $actionName, bool $bThrowException = true)
    {
    }


    /**
     * This works exactly like "checkAction" (above), except that it does NOT check if the current player is active. 
     * 
     * This is used specifically in certain game states when you want to authorize additional actions for players that are not active at the moment. 
     * 
     * Example: in Libertalia, you want to authorize players to change their mind about the card played. They are of course not active at the time they change their mind, so you cannot use "checkAction"; use "checkPossibleAction" instead. 
     */
    function checkPossibleAction($action)
    {
    }

    /**
     * Get an associative array of current game state attributes, see Your game state machine: states.inc.php for state attributes. 
     * @return array
     */
    function state()
    {
    }
}
// Exceptions

class BgaUserException
{
    function __construct(string $message)
    {
    }
}

class feException
{
    function __construct(string $message)
    {
    }
}

// .action.php

class APP_GameAction
{
    static function isArg()
    {
    }
    static function getArg()
    {
    }
    static function trace()
    {
    }
    static function setAjaxMode()
    {
    }
    static function ajaxResponse()
    {
    }
}

// .view.php

define("APP_BASE_PATH", "");

class game_view
{
    static function _()
    {
    }
}

//////// Argument Types

/**
 * An argument type.
 * 'AT_alphanum' for a string with 0-9a-zA-Z_ and space
 */
define('AT_alphanum', '');

/**
 * An argument type.
 * 'AT_numberlist' for a list of several numbers separated with "," or ";" (ex: exemple: 1,4;2,3;-1,2).
 */
define('AT_numberlist', '');

/**
 * An argument type.
 * 'AT_posint' for a positive integer
 */
define('AT_posint', '');

/**
 * An argument type.
 * 'AT_float' for a float
 */
define('AT_float', '');

/**
 * An argument type.
 * 'AT_bool' for 1/0/true/false
 */
define('AT_bool', '');

/**
 * An argument type.
 * 'AT_enum' for an enumeration (argTypeDetails list the possible values as an array)
 */
define('AT_enum', '');

/**
 * An argument type.
 * 'AT_int' for an integer
 */
define('AT_int', '');