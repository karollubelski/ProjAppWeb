<?php
function PokazPodstrone($id) {
    // Wczytaj konfigurację bazy danych
    include 'cfg.php'; 

    // Przygotowanie zapytania SQL z użyciem PDO
    $query = "SELECT page_content FROM page_list WHERE id = :id LIMIT 1";
    $stmt = $pdo->prepare($query);
    
    // Bindowanie parametru ID i wykonanie zapytania
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    
    // Pobranie wyniku
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    // Sprawdzanie czy strona istnieje
    if (empty($row)) {
        $web = '[nie_znaleziono_strony]';
    } else {
        $web = $row['page_content'];
    }

    return $web;
}
$id = isset($_GET['idp']) ? $_GET['idp'] : 1;
echo PokazPodstrone($id);