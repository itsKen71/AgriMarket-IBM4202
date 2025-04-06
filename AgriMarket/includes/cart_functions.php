<?php
include_once 'database.php';

// get all item in user cart
function getUserCart($conn, $user_id) {
    $query = "SELECT product_id, quantity FROM cart WHERE user_id = ?";
    $stmt = $conn->prepare($query);

    if (!$stmt) {
        die("Cart Query Preparation Failed: " . $conn->error);
    }

    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $products = [];
    while ($row = $result->fetch_assoc()) {
        $product_id = $row['product_id'];
        $quantity = $row['quantity']; // get cart quantity

        // get item info
        $query_product = "SELECT * FROM product WHERE product_id = ?";
        $stmt_product = $conn->prepare($query_product);

        if (!$stmt_product) {
            die("Product Query Preparation Failed: " . $conn->error);
        }

        $stmt_product->bind_param("i", $product_id);
        $stmt_product->execute();
        $result_product = $stmt_product->get_result();

        if ($product = $result_product->fetch_assoc()) {
            $product['quantity'] = $quantity; // Put the shopping cart quantity into an array
            $products[] = $product;
        }
    }

    return $products;
}


function deleteCartItem($conn, $user_id, $product_id) {
    if (!$product_id || !$user_id) {
        return ['success' => false, 'error' => 'Invalid product_id or user_id'];
    }

    $delete_query = "DELETE FROM cart WHERE user_id = ? AND product_id = ?";
    $stmt_delete = $conn->prepare($delete_query);

    if (!$stmt_delete) {
        return ['success' => false, 'error' => 'Database error: ' . $conn->error];
    }

    $stmt_delete->bind_param("ii", $user_id, $product_id);
    $stmt_delete->execute();

    if ($stmt_delete->affected_rows > 0) {
        $stmt_delete->close();
        return ['success' => true];
    } else {
        return ['success' => false, 'error' => 'No rows affected'];
    }
}

// update cart item quantity
function updateCartQuantity($conn, $user_id, $product_id, $new_quantity, $unit_price) {
    $new_quantity = max(1, (int)$new_quantity);

    $update_query = "UPDATE cart SET quantity = ? WHERE user_id = ? AND product_id = ?";
    $stmt_update = $conn->prepare($update_query);

    if ($stmt_update) {
        $stmt_update->bind_param("iii", $new_quantity, $user_id, $product_id);
        $stmt_update->execute();
        $stmt_update->close();

        return [
            'success' => true,
            'new_quantity' => $new_quantity,
            'subtotal' => number_format($unit_price * $new_quantity, 2)
        ];
    } else {
        return ['success' => false, 'error' => 'Database error'];
    }
}

function getAverageRating($conn, $productId) {
    $query = "SELECT AVG(rating) as avg_rating FROM review WHERE product_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $productId);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    return round($row['avg_rating'], 1) ?? 0; // keep 1 decimal place
}

function switchCartProduct($conn, $user_id, $current_product_id, $compare_product_id) {
    $conn->begin_transaction();

    try {
        // dlt current item
        $delete_query = "DELETE FROM cart WHERE user_id = ? AND product_id = ?";
        $stmt_delete = $conn->prepare($delete_query);
        $stmt_delete->bind_param("ii", $user_id, $current_product_id);
        $stmt_delete->execute();

        // add new item
        $insert_query = "INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, 1)
                        ON DUPLICATE KEY UPDATE quantity = 1";
        $stmt_insert = $conn->prepare($insert_query);
        $stmt_insert->bind_param("ii", $user_id, $compare_product_id);
        $stmt_insert->execute();

        $conn->commit();

        return ['success' => true];
    } catch (Exception $e) {
        $conn->rollback();
        return ['success' => false, 'error' => $e->getMessage()];
    }
}
?>