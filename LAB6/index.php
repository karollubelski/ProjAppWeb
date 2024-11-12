<?php
include 'cfg.php';
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta http-equiv="Content-type" content="text/html; charset=UTF-8">
    <meta http-equiv="Content-Language" content="pl" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="Author" content="Karol Lubelski">
    <title>Największe budynki świata</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/style2.css">
    <link rel="stylesheet" href="css/gallery.css">
    <script src="js/kolorujtlo.js" type="text/javascript"></script>
    <script src="js/timedata.js" type="text/javascript"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
</head>

<body onload="startclock()">

    <div class="top">
        <data style="width: 33vw;"></data>
        <h1>Największe Budynki Świata</h1>
        <div style="text-align: right ; width: 31vw;">
            <div id="zegarek"></div>
            <div id="data"></div>
        </div>
    </div>

    <div class="menu">
    <ul>
        <li><a href="showpage.php?idp=glowna">Strona Główna</a></li>
        <li><a href="showpage.php?idp=najwieksze">Największe Budynki</a></li>
        <li><a href="showpage.php?idp=galeria">Galeria</a></li>
        <li><a href="showpage.php?idp=ciekawostki">Ciekawostki</a></li>
        <li><a href="showpage.php?idp=contact">Kontakt</a></li>
        <li><a href="showpage.php?idp=filmy">Filmy</a></li>  
        <li><a href="showpage.php?idp=js">Skrypty</a></li> 
    </ul>
</div>

    <div class="content">
        <?php
        error_reporting(E_ALL ^ E_NOTICE ^ E_WARNING);

        //dynamiczne ładowanie stron
        $strona = 'html/glowna.html'; //domyslna strona po wejsciu z indexu.php
        if (isset($_GET['idp'])) {
            switch ($_GET['idp']) {
                case 'glowna':
                    $strona = 'html/glowna.html';
                    break;
                case 'najwieksze':
                    $strona = 'html/najwieksze.html';
                    break;
                case 'galeria':
                    $strona = 'html/galeria.html';
                    break;
                case 'ciekawostki':
                    $strona = 'html/ciekawostki.html';
                    break;
                case 'contact':
                    $strona = 'html/contact.html';
                    break;
                case 'filmy':
                    $strona = 'html/filmy.html';
                    break;
                case 'js':
                    $strona = 'html/js.html';
                    break;
            }
        }

        // sprawdza czy plik istnieje
        if (file_exists($strona)) {
            include($strona);
        } else {
            echo "Strona nie istnieje.";
        }
        ?>
    </div>

    <?php
    $nr_indeksu = '169330';
    $nrGrupy = '3';
    echo '  Autor: Karol Lubelski ' . $nr_indeksu . ' grupa ' . $nrGrupy . '<br /><br />';
    ?>
    <div class="footer"><p>Karol Lubelski</p></div>
</body>
</html>
