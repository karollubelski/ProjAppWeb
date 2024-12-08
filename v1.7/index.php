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
    <link rel="stylesheet" href="css/gallery.css">
    <script src="js/kolorujtlo.js" type="text/javascript"></script>
    <script src="js/timedata.js" type="text/javascript"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Parkinsans:wght@300..800&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">

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

    <header>
        <nav class="menu">
            <ul>
                <li><a href="index.php?idp=glowna">Strona Główna</a></li>
                <li><a href="index.php?idp=najwieksze">Największe Budynki</a></li>
                <li><a href="index.php?idp=galeria">Galeria</a></li>
                <li><a href="index.php?idp=ciekawostki">Ciekawostki</a></li>
                <li><a href="index.php?idp=contact">Kontakt</a></li>
                <li><a href="index.php?idp=filmy">Filmy</a></li>  
                <li><a href="index.php?idp=skrypty">Skrypty</a></li> 
            </ul>
        </nav>
    </header>
    
    <div class="content">
        <?php
        error_reporting(E_ALL ^ E_NOTICE ^ E_WARNING);

        include 'showpage.php';
        $idp = isset($_GET['idp']) ? $_GET['idp'] : 'glowna';

        $content = PokazPodstrone($idp);

        echo $content;
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
