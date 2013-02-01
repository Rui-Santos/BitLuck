BitLuck
=======
A simple Bitcoin-based lottery script.

Installation
------------
Set the configuration variables in config.inc.php.

Add a call in your crontab to run give_prize.php every day at the `$draw_time` time.

Create an address with the label "Lottery Pot" in the Bitcoin wallet you are using for BitLuck.

Make sure the wallet has enough funds to cover transaction fees.

License
-------
This code is licensed under the [GNU Lesser General Public License v3.0](http://www.gnu.org/licenses/lgpl-3.0.txt).
