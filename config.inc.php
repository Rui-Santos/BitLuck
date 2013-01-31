<?php

/**
 * @author John "JohnMaguire2013" Maguire <john@leftforliving.com>
 * @package BitLuck
 * @version 0.3
 * @license http://www.gnu.org/licenses/lgpl-3.0.txt GNU Lesser General Public License
 */

// Set to true for development, false for production
$debug = true;

/* Configuration variables for the JSON-RPC server */
$rpc_host = '127.0.0.1';
$rpc_port = '80';
$rpc_user = 'username';
$rpc_pass = 'password';

/* Configuration variables for the MySQL server */
$sql_host = 'localhost';
$sql_user = 'username';
$sql_pass = 'password';
$sql_db   = 'database';

/* Owner's key for fee to be sent to */
$owner_key = '1B7aTxrcaEVopuqrqidTQMxE9mpDUZRwAb';

/* Percentage to send, in decimal */
$owner_fee = .01;

/* Time cron is set to run give_prize.php, (format suggested: 8:30pm UTC-5) */
$draw_time = "8:30pm UTC-5";

/* Ticket cost, in BTC */
$ticket_cost = 1;

/* Set error reporting level */
if($debug) error_reporting(E_ERROR);
else error_reporting(0);

/* Include the JSON-RPC library, and connect to the server */
require_once('classes/jsonRPCClient.php');
$bc = new jsonRPCClient('http://' . $rpc_user . ':' . $rpc_pass . '@' . $rpc_host . ':' . $rpc_port);

?>
