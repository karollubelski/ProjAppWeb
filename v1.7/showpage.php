<?php
function PokazPodstrone($id) {
    include 'cfg.php';
    
    global $link; 

    $id_clear = htmlspecialchars($id);

    $query = "SELECT page_content FROM page_list WHERE page_title='$id_clear' LIMIT 1";
    $result = mysqli_query($link, $query);

    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_array($result);
        $web = $row['page_content'];
    } else {
        $web = "[nie_znaleziono_strony]";
        echo "Brak wyników dla tytułu: $id_clear<br>";
    }

    return $web;
}   