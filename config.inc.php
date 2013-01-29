<?php

/**
 * @author John "lulzplzkthx" Maguire <johnmaguire2013@gmail.com>
 * @package BitLuck
 * @version 0.2
 * @license http://www.gnu.org/licenses/lgpl-3.0.txt GNU Lesser General Public License
 */

// Set to E_ERROR for development, 0 for production
error_reporting(E_ERROR);

/* Configuration variables for the JSON-RPC server */
$rpc_host = '127.0.0.1';
$rpc_port = '8332';
$rpc_user = 'username';
$rpc_pass = 'password';

/* Configuration variables for the MySQL server */
$sql_host = 'localhost';
$sql_user = 'root';
$sql_pass = 'your_passowrd';
$sql_db   = 'lottery';

/* Owner's key for fee to be sent to */
$owner_key = '17e4VkKpUdu9AP63feCgtYokFSfvcXL1QW';

/* Percentage to send, in decimal */
$owner_fee = .01;

/* Time cron is set to run give_prize.php, (format suggested: 8:30pm UTC-5) */
$draw_time = "8:30pm UTC-5";

/* Ticket cost, in BTC */
$ticket_cost = 1;

/* Include the JSON-RPC library, and connect to the server */
require_once('classes/jsonRPCClient.php');
$bc = new jsonRPCClient('http://' . $rpc_user . ':' . $rpc_pass . '@' . $rpc_host . ':' . $rpc_port);

?>
