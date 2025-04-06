<?php
include_once 'database.php';// product_page_functions.php

function addToCart($conn, $product_id, $quantity, $user_id) {
    // 验证数量至少为1
    $quantity = max(1, intval($quantity));
    
    // 检查库存
    $stock_query = "SELECT stock_quantity FROM product WHERE product_id = ?";
    $stock_stmt = $conn->prepare($stock_query);
    $stock_stmt->bind_param("i", $product_id);
    $stock_stmt->execute();
    $stock_result = $stock_stmt->get_result();
    $product_stock = $stock_result->fetch_assoc()['stock_quantity'];
    $stock_stmt->close();
    
    if ($quantity > $product_stock) {
        return [
            'success' => false,
            'error' => 'stock',
            'available' => $product_stock
        ];
    }
    
    // 检查商品是否已在购物车
    $check_query = "SELECT * FROM cart WHERE user_id = ? AND product_id = ?";
    $check_stmt = $conn->prepare($check_query);
    $check_stmt->bind_param("ii", $user_id, $product_id);
    $check_stmt->execute();
    $result = $check_stmt->get_result();
    
    if ($result->num_rows >= 1) {
        $cart_item = $result->fetch_assoc();
        $new_quantity = $cart_item['quantity'] + $quantity;
        
        if ($new_quantity > $product_stock) {
            $stock_query = "SELECT stock_quantity FROM product WHERE product_id = ?";
            $stock_stmt = $conn->prepare($stock_query);
            $stock_stmt->bind_param("i", $product_id);
            $stock_stmt->execute();
            $stock_result = $stock_stmt->get_result();

            if ($stock_result->num_rows > 0) {
                $row = $stock_result->fetch_assoc();
                $quantity = $row['stock_quantity']; // 这才是获取库存值
            }
            $stock_stmt->close();
        }
        
        // 更新数量
        $update_query = "UPDATE cart SET quantity = ? WHERE user_id = ? AND product_id = ?";
        $update_stmt = $conn->prepare($update_query);
        $update_stmt->bind_param("iii", $quantity, $user_id, $product_id);
        $update_stmt->execute();
        $update_stmt->close();
    } else {
        // 添加新商品到购物车
        $insert_query = "INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?)";
        $insert_stmt = $conn->prepare($insert_query);
        $insert_stmt->bind_param("iii", $user_id, $product_id, $quantity);
        $insert_stmt->execute();
        $insert_stmt->close();
    }
    
    $check_stmt->close();
    return ['success' => true];
}


function getProductDetails($conn, $product_id) {
    $query = "SELECT * FROM product WHERE product_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

/**
 * 获取产品评分统计
 */
function getProductReviewStats($conn, $product_id) {
    $query = "SELECT COUNT(*) as total_reviews, AVG(rating) as avg_rating FROM review WHERE product_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $stats = $result->fetch_assoc();
    $stmt->close();
    
    return [
        'avg_rating' => $stats['avg_rating'] ?? 0,
        'rounded_rating' => round($stats['avg_rating'] ?? 0),
        'total_reviews' => $stats['total_reviews'] ?? 0
    ];
}

/**
 * 获取完整产品数据（包含基本信息和评分）
 */
function getCompleteProductData($conn, $product_id) {
    $product = getProductDetails($conn, $product_id);
    if (!$product) {
        return null;
    }
    
    $reviewStats = getProductReviewStats($conn, $product_id);
    
    return array_merge($product, $reviewStats);
}

function getProductRatingCounts($conn, $product_id) {
    $rating_counts = [];
    for ($i = 1; $i <= 5; $i++) {
        $query = "SELECT COUNT(*) FROM review WHERE product_id = ? AND rating = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ii", $product_id, $i);
        $stmt->execute();
        $rating_counts[$i] = $stmt->get_result()->fetch_row()[0];
        $stmt->close();
    }
    return $rating_counts;
}

/**
 * 获取产品的评价列表（可筛选和排序）
 */
function getProductReviews($conn, $product_id, $selected_rating = 0) {
    $query = "SELECT r.rating, r.review_description, r.review_date, 
                     u.first_name, u.last_name 
              FROM review r 
              JOIN `user` u ON r.user_id = u.user_id 
              WHERE r.product_id = ?";
    
    // 添加评价筛选条件
    if ($selected_rating > 0) {
        $query .= " AND r.rating = ?";
    }
    
    // 添加默认排序
    $query .= " ORDER BY r.review_date DESC";
    
    $stmt = $conn->prepare($query);
    
    if ($selected_rating > 0) {
        $stmt->bind_param("ii", $product_id, $selected_rating);
    } else {
        $stmt->bind_param("i", $product_id);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_all(MYSQLI_ASSOC);
}

/**
 * 获取完整的评价数据（包含统计和列表）
 */
function getCompleteReviewData($conn, $product_id, $selected_rating = 0) {
    return [
        'rating_counts' => getProductRatingCounts($conn, $product_id),
        'reviews' => getProductReviews($conn, $product_id, $selected_rating)
    ];
}


function VendorDetail($conn, $product_id) {
    $query = "SELECT v.vendor_id, v.store_name 
              FROM product p
              JOIN vendor v ON p.vendor_id = v.vendor_id
              WHERE p.product_id = ?";
              
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        return $row; // return vendor_id 和 store_name
    } else {
        return null;
    }
}

function VendorRating($conn, $vendor_id) {
    // 全局平均评分和评价数（需要提前查询或配置）
    $global_avg = 3.5; // 假设全局平均3.5星
    $min_reviews = 5;  // 最小评价数
    
    $query = "SELECT 
                 AVG(r.rating) AS avg_rating,
                 COUNT(r.review_id) AS review_count
              FROM review r
              JOIN product p ON r.product_id = p.product_id
              WHERE p.vendor_id = ?";
              
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $vendor_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        $avg_rating = $row['avg_rating'] ?? 0;
        $review_count = $row['review_count'] ?? 0;
        
        if ($review_count == 0) {
            return 0;
        }
        
        // 贝叶斯计算：加权全局平均和实际平均
        $bayesian_avg = ($min_reviews * $global_avg + $review_count * $avg_rating) 
                       / ($min_reviews + $review_count);
        
        return round($bayesian_avg, 2);
    }
    return 0;
}

?>