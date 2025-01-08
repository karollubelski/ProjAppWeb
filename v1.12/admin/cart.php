<?php
// Połączenie z bazą danych (uzupełnij dane)
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "moja_strona";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Funkcja do obliczania ceny brutto
function cenaBrutto($cenaNetto, $vat) {
    return $cenaNetto * (1 + $vat/100);
}

// Pobieranie kategorii
$sqlKategorie = "SELECT * FROM kategorie";
$resultKategorie = $conn->query($sqlKategorie);

// Obsługa koszyka (sesja)
session_start();
if (!isset($_SESSION['koszyk'])) {
    $_SESSION['koszyk'] = [];
}

// Dodawanie do koszyka
if (isset($_POST['dodaj_do_koszyka'])) {
    $produktId = $_POST['produkt_id'];
    $ilosc = $_POST['ilosc'];

    if(isset($_SESSION['koszyk'][$produktId])) {
        $_SESSION['koszyk'][$produktId]['ilosc'] += $ilosc;
    } else {
        // Pobierz dane produktu z bazy
        $sqlProdukt = "SELECT * FROM produkty WHERE id = $produktId";
        $resultProdukt = $conn->query($sqlProdukt);
        $produkt = $resultProdukt->fetch_assoc();
        $_SESSION['koszyk'][$produktId] = [
            'id' => $produkt['id'],
            'tytul' => $produkt['tytul'],
            'cena_netto' => $produkt['cena_netto'],
            'vat' => $produkt['vat'],
            'ilosc' => $ilosc,
            'zdjecie' => $produkt['zdjecie']
        ];
    }
}


// Usuwanie z koszyka
if (isset($_GET['usun_z_koszyka'])) {
  $produktId = $_GET['usun_z_koszyka'];
  unset($_SESSION['koszyk'][$produktId]);
}

// Zmiana ilości w koszyku
if (isset($_POST['aktualizuj_ilosc'])) {
  $produktId = $_POST['produkt_id'];
  $nowaIlosc = $_POST['ilosc'];

    if ($nowaIlosc <= 0) {
        unset($_SESSION['koszyk'][$produktId]);
    } else {
        $_SESSION['koszyk'][$produktId]['ilosc'] = $nowaIlosc;
    }
}


// Filtrowanie po kategorii
$kategoriaId = isset($_GET['kategoria']) ? $_GET['kategoria'] : null;

$sqlProdukty = "SELECT * FROM produkty";
if ($kategoriaId) {
    $sqlProdukty .= " WHERE kategoria_id = $kategoriaId";
}


$resultProdukty = $conn->query($sqlProdukty);

?>


<!DOCTYPE html>
<html>
<head>
  <title>Sklep</title>
    <style> /* Dodaj stylowanie wg uznania */  </style>
</head>
<body>
  <div class="container">

    <h1>Sklep</h1>

    <div class="kategorie">
        <h2>Kategorie</h2>
        <ul>
          <li><a href="sklep.php">Wszystkie</a></li>
          <?php while($row = $resultKategorie->fetch_assoc()): ?>
            <li><a href="sklep.php?kategoria=<?php echo $row['id']; ?>"><?php echo $row['nazwa']; ?></a></li>
          <?php endwhile; ?>
        </ul>
    </div>

    <div class="produkty">
        <?php while($row = $resultProdukty->fetch_assoc()): ?>
          <div class="produkt">
            <h3><?php echo $row['tytul']; ?></h3>
            <img src="<?php echo $row['zdjecie']; ?>" alt="<?php echo $row['tytul']; ?>" width="100">
            <p><?php echo $row['opis']; ?></p>
            <p>Cena brutto: <?php echo cenaBrutto($row['cena_netto'], $row['vat']); ?> zł</p>
            <form method="post" action="">
                <input type="hidden" name="produkt_id" value="<?php echo $row['id']; ?>">
                <input type="number" name="ilosc" value="1" min="1">
                <button type="submit" name="dodaj_do_koszyka">Dodaj do koszyka</button>
            </form>
          </div>
        <?php endwhile; ?>
    </div>

      <div class="koszyk-ikona">
          <a href="#koszyk">Koszyk (<?php echo count($_SESSION['koszyk']); ?>)</a>
      </div>


      <div id="koszyk" class="koszyk">
          <h2>Koszyk</h2>
          <?php if (empty($_SESSION['koszyk'])): ?>
              <p>Koszyk jest pusty.</p>
          <?php else: ?>
              <table>
                  <thead>
                  <tr>
                      <th>Produkt</th>
                      <th>Cena brutto</th>
                      <th>Ilość</th>
                      <th>Suma</th>
                      <th>Usuń</th>
                  </tr>
                  </thead>
                  <tbody>
                  <?php $suma = 0; ?>
                  <?php foreach($_SESSION['koszyk'] as $produkt): ?>
                      <tr>
                          <td><?php echo $produkt['tytul']; ?></td>
                          <td><?php echo cenaBrutto($produkt['cena_netto'], $produkt['vat']); ?> zł</td>
                          <td>
                              <form method="post">
                                  <input type="hidden" name="produkt_id" value="<?php echo $produkt['id']; ?>">
                                  <input type="number" name="ilosc" value="<?php echo $produkt['ilosc']; ?>" min="0">
                                  <button type="submit" name="aktualizuj_ilosc">Aktualizuj</button>
                              </form>

                          </td>
                          <td><?php echo  cenaBrutto($produkt['cena_netto'], $produkt['vat']) * $produkt['ilosc']; ?> zł</td>
                          <td><a href="?usun_z_koszyka=<?php echo $produkt['id']; ?>">Usuń</a></td>
                      </tr>

                    <?php $suma +=  cenaBrutto($produkt['cena_netto'], $produkt['vat']) * $produkt['ilosc'];  ?>

                  <?php endforeach; ?>
                  </tbody>
              </table>
              <p>Suma: <?php echo $suma; ?> zł</p>
          <?php endif; ?>
      </div>




  </div>

</body>
</html>

<?php
$conn->close();
?>