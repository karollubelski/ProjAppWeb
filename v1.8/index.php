<?php
// cfg.php - konfiguracja projektu, zawiera ustawienia globalne
include 'cfg.php';
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <!-- Metadane strony -->
    <meta http-equiv="Content-type" content="text/html; charset=UTF-8">
    <meta http-equiv="Content-Language" content="pl" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="Author" content="Karol Lubelski">

    <!-- Tytuł strony -->
    <title>Największe budynki świata</title>

    <!-- Style CSS -->
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/gallery.css">

    <!-- Skrypty JS -->
    <script src="js/kolorujtlo.js" type="text/javascript"></script>
    <script src="js/timedata.js" type="text/javascript"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- Czcionki Google -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Parkinsans:wght@300..800&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">
</head>

<body onload="startclock()">

    <!-- Zegarek na menu barze -->
    <div class="top">
        <data style="width: 33vw;"></data>
        <h1>Największe Budynki Świata</h1>
        <div style="text-align: right; width: 31vw;">
            <div id="zegarek"></div>
            <div id="data"></div>
        </div>
    </div>

    <!-- Menu bar -->
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
    
    <!-- Sekcja treści -->
    <div class="content">
        <?php
        // Wyłączenie ostrzeżen i notyfikacji, aby zapobiec wyświetlaniu błędów użytkownikowi końcowemu
        error_reporting(E_ALL ^ E_NOTICE ^ E_WARNING);

        // Pobieranie podstrony na podstawie parametru 'idp' z URL
        include 'showpage.php';
        $idp = isset($_GET['idp']) ? htmlspecialchars($_GET['idp']) : 'glowna';

        // Funkcja wyświetlająca treść podstrony
        $content = PokazPodstrone($idp);
        echo $content;
        ?>
    </div>

    <!-- Jedno z zadań z php-a -->
    <?php
    $nr_indeksu = '169330';
    $nrGrupy = '3';
    echo '<p>Autor: Karol Lubelski ' . htmlspecialchars($nr_indeksu) . ' grupa ' . htmlspecialchars($nrGrupy) . '</p>';
    ?>

    <!-- Footer -->
    <div class="footer"><p>Karol Lubelski</p></div>
</body>
</html>
