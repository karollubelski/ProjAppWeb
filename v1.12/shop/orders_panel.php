<?php
require_once '../shop/auth.php';
require_once '../cfg.php';
requireLogin();

// Pobierz wszystkie zamówienia użytkownika
function getUserOrders($userId) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("
            SELECT 
                o.id, 
                o.first_name,
                o.last_name,
                o.address,
                o.phone,
                o.email,
                o.total_amount,
                o.status,
                o.created_at
            FROM orders o
            WHERE o.user_id = :user_id
            ORDER BY o.created_at DESC
        ");
        
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error fetching user orders: " . $e->getMessage());
        return [];
    }
}

// Pobierz szczegóły konkretnego zamówienia
function getOrderDetails($orderId, $userId) {
    global $pdo;
    
    try {
        // Sprawdź, czy zamówienie należy do użytkownika
        $stmt = $pdo->prepare("
            SELECT * FROM orders 
            WHERE id = :order_id AND user_id = :user_id
        ");
        $stmt->execute([
            'order_id' => $orderId,
            'user_id' => $userId
        ]);
        $order = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$order) {
            return null;
        }
        
        // Pobierz produkty z zamówienia
        $stmt = $pdo->prepare("
            SELECT * FROM order_items
            WHERE order_id = :order_id
        ");
        $stmt->execute(['order_id' => $orderId]);
        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return [
            'order' => $order,
            'items' => $items
        ];
    } catch (PDOException $e) {
        error_log("Error fetching order details: " . $e->getMessage());
        return null;
    }
}

$orders = getUserOrders($_SESSION['user_id']);
$selectedOrder = null;

if (isset($_GET['order_id'])) {
    $selectedOrder = getOrderDetails($_GET['order_id'], $_SESSION['user_id']);
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Moje Zamówienia</title>
    <link rel="stylesheet" href="../css/shop.css">
    <script src="../js/orderDetails.js"></script>
</head>
<body>
    <div class="orders-container">
        <button class="back-button" onclick="window.location.href='../shop/cart.php'">Powrót do sklepu</button>
        <h1>Moje Zamówienia</h1>
        
        <?php if (empty($orders)): ?>
            <p>Nie masz jeszcze żadnych zamówień.</p>
        <?php else: ?>
            <?php foreach ($orders as $order): ?>
                <div class="order-card" onclick="toggleOrderDetails(<?php echo $order['id']; ?>)">
                    <div class="order-header">
                        <div>
                            <strong>Zamówienie #<?php echo $order['id']; ?></strong>
                            <div><?php echo date('d.m.Y H:i', strtotime($order['created_at'])); ?></div>
                        </div>
                        <div>
                            <span class="status-badge status-<?php echo strtolower($order['status']); ?>">
                                <?php echo ucfirst($order['status']); ?>
                            </span>
                        </div>
                    </div>
                    <div>Kwota: <?php echo number_format($order['total_amount'], 2); ?> zł</div>
                    
                    <div class="order-details" id="order-<?php echo $order['id']; ?>">
                        <h3>Szczegóły zamówienia</h3>
                        <p><strong>Adres dostawy:</strong><br>
                        <?php echo $order['first_name'] . ' ' . $order['last_name']; ?><br>
                        <?php echo nl2br(htmlspecialchars($order['address'])); ?><br>
                        Tel: <?php echo $order['phone']; ?><br>
                        Email: <?php echo $order['email']; ?></p>
                        
                        <div class="order-items">
                            <h4>Zamówione produkty:</h4>
                            <?php 
                            $orderDetails = getOrderDetails($order['id'], $_SESSION['user_id']);
                            if ($orderDetails && !empty($orderDetails['items'])):
                                foreach ($orderDetails['items'] as $item):
                                    $cenaBrutto = $item['price_net'] * (1 + $item['vat'] / 100);
                            ?>
                                <div class="order-item">
                                <img src="<?php echo $item['image']; ?>" alt="<?php echo $item['title']; ?>" class="scaled-image">
                                <div>
                                        <div><strong><?php echo $item['title']; ?></strong></div>
                                        <div>Ilość: <?php echo $item['quantity']; ?></div>
                                        <div>Cena: <?php echo number_format($cenaBrutto, 2); ?> zł</div>
                                    </div>
                                </div>
                            <?php 
                                endforeach;
                            endif;
                            ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
    
</body>
</html>