<?php
include_once 'database.php';
$db = new Database();
$conn = $db->conn;

function addToCart($conn, $product_id, $quantity, $user_id) {
    // quantity at least 1
    $quantity = max(1, intval($quantity));
    
    // check stock_quantity
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
    
    // check item in cart already or not
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
                $quantity = $row['stock_quantity'];
            }
            $stock_stmt->close();
        }
        
        // update quantity
        $update_query = "UPDATE cart SET quantity = ? WHERE user_id = ? AND product_id = ?";
        $update_stmt = $conn->prepare($update_query);
        $update_stmt->bind_param("iii", $quantity, $user_id, $product_id);
        $update_stmt->execute();
        $update_stmt->close();
    } else {
        // add new item to cart
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

//get each product rating
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

//get complete product data (including basic information and ratings)
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

//get the product review list (filterable and sortable)
function getProductReviews($conn, $product_id, $selected_rating = 0) {
    $query = "SELECT r.rating, r.review_description, r.review_date, 
                     u.first_name, u.last_name 
              FROM review r 
              JOIN `user` u ON r.user_id = u.user_id 
              WHERE r.product_id = ?";
    
    // Add review filter
    if ($selected_rating > 0) {
        $query .= " AND r.rating = ?";
    }
    
    // Add default sort
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

//get complete review data (including statistics and lists)
function getCompleteReviewData($conn, $product_id, $selected_rating = 0) {
    return [
        'rating_counts' => getProductRatingCounts($conn, $product_id),
        'reviews' => getProductReviews($conn, $product_id, $selected_rating)
    ];
}


function VendorDetail($conn, $product_id) {
    $query = "SELECT v.vendor_id, v.store_name, u.user_id 
              FROM product p
              JOIN vendor v ON p.vendor_id = v.vendor_id
              JOIN user u ON v.user_id = u.user_id
              WHERE p.product_id = ?";
              
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        return $row; // return vendor_id, store_name, and user_id
    } else {
        return null;
    }
}
function VendorRating($conn, $vendor_id) {
    // global average rating and number of reviews (need to be queried or configured in advance)
    $global_avg = 3.5; // assuming the global average is 3.5 stars
    $min_reviews = 5;  
    
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
        
        // Bayesian Computation: Weighted Global Average and Actual Average
        $bayesian_avg = ($min_reviews * $global_avg + $review_count * $avg_rating) 
                       / ($min_reviews + $review_count);
        
        return round($bayesian_avg, 2);
    }
    return 0;
}
?>