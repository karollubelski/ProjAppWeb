<?php
$nr_indeksu = '169330';
$nr_grupy = '4';

echo 'Karol Lubelski ' .$nr_indeksu. ' Grupa' .$nr_grupy. '<br><br>';
echo 'Zastosowanie metody include()<br><br><br>';

echo 'a)<br>';
echo 'Metoda include() - załączy plik, jeżeli istnieje <br>';
echo 'Metoda require_once() - załączy plik tylko raz, zapobieganie wielokrotnemu załączeniu<br><br>';

echo 'include()<br>';
include 'include_doc.php';
echo "Wynik: " .$str. '<br><br>';

echo 'require_once()<br>';
require_once 'config.php';
echo 'Wynik: ' .$db_host. '<br><br>';

echo("<br> b) <br><br>");


echo("---- if, else,<br><br>");
$a = 5;
$b = 10;

if($a > $b){
    echo "Wartość A jest wieksza od wartosci B<br>";
} elseif($a == $b){
    echo "Wartości są sobie równe<br>";
}else{
    echo "Wartość B jest wieksza od wartosci A<br>";
}

echo("<br><br>  ----switch() <br><br>");

$kolor = 'czerwony';

switch($kolor){
    case 'czerwony':
            echo "Wybrany kolor to czerwony<br>";
            break;
    case 'zielony':
        echo "Wybrany kolor to zielony<br>";
        break;
    default:
        echo "Nieznany kolor<br>";
}

echo "<br><br> c) <br><br>";

echo("<br> ---Pętla while()<br><br>");
$temp = 0;

while($temp<=10){
    echo "Wartość: $temp <br>";
    $temp++;
}

echo "<br><br> ---Pętla for() <br><br>";
for($i = 0; $i <=5; $i++)
{
    echo "i = $i <br>";
}

echo "<br><br> d) Typy zmiennych \$_GET, \$_POST, \$_SESSION <br><br> ";
echo "GET: " .(isset($_GET['param']) ? $_GET['param'] : 'brak'). "<br>";
echo "POST: " .(isset($_POST['data']) ? $_POST['data'] : 'brak'). '<br>';
session_start();
$_SESSION['user'] = 'User1';
echo "SESSION: " .$_SESSION['user']. '<br><br>';

// echo "Hello " . htmlspecialchars($_GET["imie"]) . '!';

// session_start();

// if ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['imie'])) {
//     echo "Witaj, " . htmlspecialchars($_GET['imie']) . " (Dane przesłane przez \$_GET)<br>";
// }

// if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['imie'])) {
//     echo "Witaj, " . htmlspecialchars($_POST['imie']) . "! (Dane przesłane przez \$_POST)<br>";
// }

// $_SESSION['user'] = 'Jan Kowalski';
// echo "Zmienna sesji użytkownika: " . $_SESSION['user'] . "<br>";

?>


