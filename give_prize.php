<?php

/**
 * @author John "JohnMaguire2013" Maguire <john@leftforliving.com>
 * @package BitLuck
 * @version 0.3
 * @license http://www.gnu.org/licenses/lgpl-3.0.txt GNU Lesser General Public License
 */

/* Include the configuration */
require_once('config.inc.php');

/* Find a random entry in the table */
$query = "SELECT `winning_address` FROM `entries` ORDER BY RAND() LIMIT 1";
$res = $conn->query($query);
$arr = $res->fetch_assoc();

/* Now we want to attempt to send them the cash */
$winning_address = $arr['winning_address'];
if(!empty($winning_address)) {
    try {
        /* Calculate pot size */
        $pot_size = $bc->getbalance("Lottery Pot");
        
        /* Calculate amount to send to owner, then winner */
        $bonus = $pot_size * $owner_fee;
        $give  = $pot_size - $bonus;

        /* Now send the money if there actually was any */
        if($give > 0)
            $bc->sendfrom("Lottery Pot", $winning_address, $give);

        if($pot_size > 0)
            $bc->sendfrom("Lottery Pot", $owner_key, $bonus);
        
        /* Remove everything from the tables to start fresh for a new day */
        $query1 = "TRUNCATE `entries`";
        $query2 = "TRUNCATE `unconfirmed_entries`;";
        $conn->query($query1);
        $conn->query($query2);
        
        echo "SUCCESS";
    } catch(Exception $e) {
        echo "ERROR: " . $e->getMessage();
    }
} else {
    echo "ERROR: No players; no winners.";
}