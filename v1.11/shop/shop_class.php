<?php
class Shop {
    private $db;
    
    public function __construct($link) {
        $this->db = $link;
        session_start();
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }
    }

    // Wyświetlanie sklepu
    public function displayShop() {
        $this->displayHeader();
        
        echo "<div class='shop-container' style='display: flex; max-width: 1200px; margin: 0 auto; padding: 20px;'>";
        
        // Panel kategorii
        echo "<div class='categories-panel' style='width: 250px; padding-right: 20px;'>";
        $this->displayCategories();
        echo "</div>";
        
        // Lista produktów
        echo "<div class='products-grid' style='flex: 1;'>";
        $this->displayProducts();
        echo "</div>";
        
        echo "</div>";
        
        // Modal koszyka
        $this->displayCartModal();
    }

    // Wyświetlanie nagłówka z ikoną koszyka
    private function displayHeader() {
        $cartCount = array_sum(array_column($_SESSION['cart'], 'quantity'));
        echo "
        <div style='position: fixed; top: 0; right: 0; padding: 20px; z-index: 1000;'>
            <button onclick='toggleCart()' style='background: #4CAF50; color: white; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer;'>
                🛒 Koszyk ($cartCount)
            </button>
        </div>";
    }

    // Wyświetlanie kategorii
    private function displayCategories() {
        $query = "SELECT * FROM kategorie ORDER BY nazwa";
        $result = $this->db->query($query);
        
        echo "<div class='categories' style='background: white; padding: 15px; border-radius: 5px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);'>";
        echo "<h3>Kategorie</h3>";
        echo "<ul style='list-style: none; padding: 0;'>";
        echo "<li style='margin: 5px 0;'><a href='?page=shop' style='text-decoration: none; color: #333;'>Wszystkie produkty</a></li>";
        
        while ($category = $result->fetch_assoc()) {
            $activeClass = (isset($_GET['category']) && $_GET['category'] == $category['id']) ? 'background: #f0f0f0;' : '';
            echo "<li style='margin: 5px 0;'>";
            echo "<a href='?page=shop&category={$category['id']}' style='text-decoration: none; color: #333; display: block; padding: 5px; border-radius: 3px; {$activeClass}'>";
            echo htmlspecialchars($category['nazwa']);
            echo "</a></li>";
        }
        echo "</ul></div>";
    }

    // Wyświetlanie produktów
    private function displayProducts() {
        $where = [];
        $params = [];
        $types = "";

        if (isset($_GET['category'])) {
            $where[] = "p.kategoria_id = ?";
            $params[] = $_GET['category'];
            $types .= "i";
        }

        $whereClause = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";
        
        $query = "SELECT p.*, k.nazwa as kategoria_nazwa 
                 FROM produkty p 
                 LEFT JOIN kategorie k ON p.kategoria_id = k.id 
                 $whereClause 
                 ORDER BY p.data_utworzenia DESC";

        $stmt = $this->db->prepare($query);
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $result = $stmt->get_result();

        echo "<div style='display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 20px;'>";
        
        while ($product = $result->fetch_assoc()) {
            $cena_brutto = $product['cena_netto'] * (1 + $product['vat'] / 100);
            $availability = $this->checkAvailability($product);
            
            echo "<div class='product-card' style='background: white; padding: 15px; border-radius: 5px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);'>";
            
            // Zdjęcie produktu
            if (!empty($product['zdjecie'])) {
                echo "<img src='" . htmlspecialchars($product['zdjecie']) . "' style='width: 100%; height: 200px; object-fit: cover; border-radius: 5px;' alt='" . htmlspecialchars($product['tytul']) . "'>";
            } else {
                echo "<div style='width: 100%; height: 200px; background: #f0f0f0; border-radius: 5px; display: flex; align-items: center; justify-content: center;'>Brak zdjęcia</div>";
            }
            
            echo "<h3 style='margin: 10px 0;'>" . htmlspecialchars($product['tytul']) . "</h3>";
            echo "<p style='color: #666; margin: 5px 0;'>" . htmlspecialchars($product['kategoria_nazwa']) . "</p>";
            echo "<p style='font-weight: bold; margin: 10px 0;'>" . number_format($cena_brutto, 2) . " zł</p>";
            
            if ($availability === 'Dostępny') {
                echo "<button onclick='addToCart({$product['id']})' style='width: 100%; padding: 10px; background: #4CAF50; color: white; border: none; border-radius: 5px; cursor: pointer;'>Dodaj do koszyka</button>";
            } else {
                echo "<button disabled style='width: 100%; padding: 10px; background: #ccc; color: white; border: none; border-radius: 5px;'>{$availability}</button>";
            }
            
            echo "</div>";
        }
        
        echo "</div>";
    }

    // Modal koszyka
    private function displayCartModal() {
        echo "
        <div id='cartModal' style='display: none; position: fixed; top: 0; right: 0; bottom: 0; width: 400px; background: white; box-shadow: -2px 0 5px rgba(0,0,0,0.1); z-index: 1001; overflow-y: auto;'>
            <div style='padding: 20px;'>
                <h2 style='margin-bottom: 20px;'>Koszyk</h2>
                <div id='cartContent'></div>
            </div>
        </div>
        
        <script>
        function toggleCart() {
            const modal = document.getElementById('cartModal');
            if (modal.style.display === 'none') {
                modal.style.display = 'block';
                updateCart();
            } else {
                modal.style.display = 'none';
            }
        }

        function updateCart() {
            fetch('ajax_cart.php?action=get_cart')
                .then(response => response.text())
                .then(html => {
                    document.getElementById('cartContent').innerHTML = html;
                });
        }

        function addToCart(productId) {
            fetch('ajax_cart.php?action=add&product_id=' + productId)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        updateCart();
                        // Aktualizacja licznika w przycisku koszyka
                        const cartButton = document.querySelector('button[onclick=\"toggleCart()\"]');
                        cartButton.innerHTML = '🛒 Koszyk (' + data.cartCount + ')';
                    }
                });
        }

        function updateQuantity(productId, change) {
            fetch('ajax_cart.php?action=update&product_id=' + productId + '&change=' + change)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        updateCart();
                        const cartButton = document.querySelector('button[onclick=\"toggleCart()\"]');
                        cartButton.innerHTML = '🛒 Koszyk (' + data.cartCount + ')';
                    }
                });
        }

        function removeFromCart(productId) {
            if (confirm('Czy na pewno chcesz usunąć ten produkt z koszyka?')) {
                fetch('ajax_cart.php?action=remove&product_id=' + productId)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            updateCart();
                            const cartButton = document.querySelector('button[onclick=\"toggleCart()\"]');
                            cartButton.innerHTML = '🛒 Koszyk (' + data.cartCount + ')';
                        }
                    });
            }
        }
        </script>";
    }

    private function checkAvailability($product) {
        if ($product['status'] == 'niedostepny') {
            return 'Niedostępny';
        }
        if ($product['ilosc'] <= 0) {
            return 'Brak w magazynie';
        }
        if (!empty($product['data_wygasniecia']) && strtotime($product['data_wygasniecia']) < time()) {
            return 'Wygasły';
        }
        return 'Dostępny';
    }
}