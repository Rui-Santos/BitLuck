<?php

/**
 * @author John "JohnMaguire2013" Maguire <john@leftforliving.com>
 * @package BitLuck
 * @version 0.2
 * @license http://www.gnu.org/licenses/lgpl-3.0.txt GNU Lesser General Public License
 */

/* Include configuration */
require_once('config.inc.php');

/* Include the template engine and set it up */
require_once('classes/template.php');
$template = new template();
$template->set_custom_template('templates', 'default');

/* Find the pot size to entice viewers */
$pot_size = number_format($bc->getbalance("Lottery Pot"), 2) . " BTC";

/* Site name */
$template->assign_vars(array(
    'SITENAME'          => 'BitLuck',
    'DONATE'            => '16nCPK6mx4WxSzbAQQCU7Sh9XTzDHDwB63',
    'TOTALPOT'          => $pot_size,
    'OWNERPERCENTAGE'   => $owner_fee * 100,
    'WINNERPERCENTAGE'  => 100 - ($owner_fee * 100),
    'DRAWTIME'          => $draw_time,
    'COST'              => $ticket_cost,
));

/* Load up the header to be used on each page */
$template->set_filenames(array(
    'header' => 'site_header.html'
));

if(isset($_POST['submit']))
{
    /* Check if the address was valid */
    $resp = $bc->validateaddress($_POST['address']);
    if($resp['isvalid'])
    {
        /* Find or generate the key for them to send to, save it */
        $addr = $bc->getaccountaddress("Incoming from " . $_POST['address']);
        $conn = new mysqli($sql_host, $sql_user, $sql_pass, $sql_db);
        
        if($conn->connect_error)
        {
            /* We had a MySQL error, load intro with error */
            $template->assign_vars(array(
                'ERROR' => 'Could not connect to MySQL database.'
            ));
            $template->set_filenames(array(
                'body' => 'intro.html'
            ));
        }
        else
        {
            /* Check if they are already entered in the database */
            $stmt = $conn->prepare("SELECT `id` FROM `unconfirmed_entries` " .
                                   "WHERE `receiving_address` = ?");
            $stmt->bind_param("s", $_POST['address']);
            $stmt->execute();
            $stmt->store_result();

            /* They exist, send them off */
            if($stmt->num_rows > 0)
            {
                $stmt->bind_result($id);
                $stmt->fetch();
                header("Location: " . $_SERVER['PHP_SELF'] . "?id=" . $id);
            }
            else
            {
                $stmt->close();
                
                /* Insert their entry, and redirect */
                $stmt = $conn->prepare("INSERT INTO `unconfirmed_entries` (`receiving_address`, `winning_address`) " .
                                       "VALUES(?, ?)");
                $stmt->bind_param("ss", $addr, $_POST['address']);
                $stmt->execute();
                header("Location: " . $_SERVER['PHP_SELF'] . "?id=" . $stmt->insert_id);
            }
        }
    }
    else
    {
        /* They entered an invalid email address, load intro with error */
        $template->assign_vars(array(
            'ERROR' => 'You entered an invalid address.'
        ));
        $template->set_filenames(array(
            'body' => 'intro.html'
        ));
    }
}
else
{
    if(isset($_GET['id']))
    {
        /* Load a page telling them where to send money, and check if they have already. */
        $conn = new mysqli($sql_host, $sql_user, $sql_pass, $sql_db);
        
        if($conn->connect_error)
        {
            /* We had a MySQL error, load intro with error */
            $template->assign_vars(array(
                'ERROR' => 'Could not connect to MySQL database.'
            ));
            $template->set_filenames(array(
                'body' => 'intro.html'
            ));
        }
        else
        {
            /* Check if they are already entered in the database */
            $stmt = $conn->prepare("SELECT `receiving_address`, `winning_address` " .
                                   "FROM `unconfirmed_entries` " .
                                   "WHERE `id` = ?");
            $stmt->bind_param("i", $_GET['id']);
            $stmt->execute();
            $stmt->store_result();

            /* They exist */
            if($stmt->num_rows > 0)
            {
                $stmt->bind_result($receiving_address, $winning_address);
                $stmt->fetch();
                $stmt->close();
                
                $btc = $bc->getreceivedbyaddress($receiving_address);
                $passed = true;
                
                if($btc >= $ticket_cost)
                {
                    /* Round the entries down, update db */
                    $entries = floor($btc / $ticket_cost);

                    /* Create a query based on entries */
                    $query = "INSERT INTO `entries` (`winning_address`) VALUES";
                    for($i = 0; $i < $entries; $i++)
                    {
                        $query .= "('" . $winning_address . "'),";
                    }
                    
                    /* Chop off that list comma */
                    $query = substr($query, 0, -1);
                    
                    /* Run the queries for entry */
                    if($conn->query($query))
                    {
                        /* Move the coins out of their account */
                        $bc->move("Incoming from " . $winning_address, "Lottery Pot", $btc);
                        
                        /* Delete from unconfirmed entries */
                        $stmt = $conn->prepare("DELETE FROM `unconfirmed_entries` " .
                                               "WHERE `id` = ?");
                        $stmt->bind_param("i", $_GET['id']);
                        $stmt->execute();
                        
                        /* Show a success page! */
                        $template->assign_vars(array(
                            'ENTRIES' => $entries
                        ));
                        $template->set_filenames(array(
                            'body' => 'success.html'
                        ));
                    }
                    else
                    {
                        $template->assign_vars(array(
                            'FATALERROR' => 'MySQL error occurred. Please post on Bitcoin forums.'
                        ));
                        $template->set_filenames(array(
                            'body' => 'intro.html'
                        ));
                    }
                }
                else
                {
                    /* They still need to send funds. Keep waiting. */
                    $template->assign_vars(array(
                        'REFRESH' => true,
                        'COST' => $ticket_cost,
                        'SELF' => $_SERVER['REQUEST_URI'],
                        'ADDRESS' => $receiving_address
                    ));
                    $template->set_filenames(array(
                        'body' => 'waiting.html'
                    ));
                }
            }
            else
            {
                /* We had a MySQL error, load intro with error */
                $template->assign_vars(array(
                    'ERROR' => 'ID not found.'
                ));
                $template->set_filenames(array(
                    'body' => 'intro.html'
                ));
            }
        }
    }
    else
    {
        /* No action was chosen, load the intro */
        $template->set_filenames(array(
            'body' => 'intro.html'
        ));
    }
}

/* Load up the footer to be used on each page */
$template->set_filenames(array(
    'footer' => 'site_footer.html'
));

/* Parse and display everything */
$template->display('header');
$template->display('body');
$template->display('footer');
