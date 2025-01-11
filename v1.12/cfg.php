<?php
$dbhost = 'localhost';
$dbuser = 'root';
$dbpass = '';
$baza = 'moja_strona';


$link = mysqli_connect($dbhost, $dbuser, $dbpass, $baza);
if (!$link) {
    echo '<b>przerwane połączenie</b>';
}
?>
