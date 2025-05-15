<?php
require_once 'db.php';

// Handle review submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $dish_name = $conn->real_escape_string($_POST['dish_name']);
    $review_text = $conn->real_escape_string($_POST['review_text']);
    $rating = (int)$_POST['rating'];
    
    $sql = "INSERT INTO reviews (dish_name, review_text, rating) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssi", $dish_name, $review_text, $rating);
    
    if ($stmt->execute()) {
        $success_message = "Review submitted successfully!";
    } else {
        $error_message = "Error submitting review: " . $conn->error;
    }
}

// Fetch existing reviews
$reviews = [];
$sql = "SELECT dish_name, review_text, rating, created_at FROM reviews ORDER BY created_at DESC";
$result = $conn->query($sql);
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $reviews[] = $row;
    }
}

// Fetch menu items from the database
$menu_items = [];
$sql = "SELECT name, description, price, picture FROM menu_items ORDER BY name ASC";
$result = $conn->query($sql);
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $menu_items[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Matthews Rooftop Restaurant</title>
    <style>
        body {
            margin: 0;
            font-family: 'Segoe UI', sans-serif;
            background-color: white;
            color: black;
        }

        header {
            background-color: #003366;
            color: white;
            padding: 20px;
            text-align: center;
        }

        nav {
            background-color: #FFD700;
            display: flex;
            justify-content: center;
            padding: 10px 0;
        }

        nav a {
            color: black;
            text-decoration: none;
            margin: 0 20px;
            font-weight: bold;
            font-size: 1.1em;
        }

        nav a:hover {
            text-decoration: underline;
        }

        section {
            padding: 30px;
            text-align: center;
        }

        .highlight {
            color: #003366;
        }

        footer {
            background-color: black;
            color: white;
            text-align: center;
            padding: 15px;
        }

        .review-display {
            margin-top: 20px;
            padding: 10px;
            background-color: #f9f9f9;
            border-radius: 5px;
            max-height: 300px;
            overflow-y: auto;
        }
        
        .review-stars {
            color: gold;
            font-size: 18px;
        }
        
        .review-date {
            color: #666;
            font-size: 0.8em;
        }
        
        .success-message {
            color: green;
            text-align: center;
            margin: 10px 0;
        }
        
        .error-message {
            color: red;
            text-align: center;
            margin: 10px 0;
        }

        .star-rating {
            display: flex;
            direction: row;
        }

        .star {
            font-size: 24px;
            color: #ddd;
            cursor: pointer;
            transition: color 0.3s;
        }

        .star:hover,
        .star.selected {
            color: gold;
        }

        textarea {
            width: 100%;
            padding: 8px;
            margin-top: 10px;
        }

        button {
            padding: 10px 20px;
            margin-top: 10px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        .dish-card {
            width: 300px;
            border: 1px solid #ccc;
            border-radius: 8px;
            overflow: hidden;
            margin: 15px;
        }

        .dish-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }

        .dish-content {
            padding: 15px;
        }

        .menu-container {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 30px;
            margin-top: 30px;
        }

        .review-box {
            border: 1px solid #ddd;
            padding: 10px;
            margin-bottom: 10px;
            border-radius: 5px;
            background-color: white;
        }

        .review-display::-webkit-scrollbar {
            width: 8px;
        }

        .review-display::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 4px;
        }

        .review-display::-webkit-scrollbar-thumb {
            background: #888;
            border-radius: 4px;
        }

        .review-display::-webkit-scrollbar-thumb:hover {
            background: #555;
        }
    </style>
</head>
<body>
    <header>
        <img src="https://static.wixstatic.com/media/6eb5e0_ab8bcfe7842140529e1ce8a989dc292c~mv2_d_2429_2429_s_4_2.jpeg/v1/crop/x_0,y_187,w_2429,h_2055/fill/w_532,h_450,al_c,q_80,usm_0.66_1.00_0.01,enc_avif,quality_auto/6eb5e0_ab8bcfe7842140529e1ce8a989dc292c~mv2_d_2429_2429_s_4_2.jpeg" alt="Matthews Rooftop Restaurant">
        <h1>Matthews Rooftop Restaurant</h1>
    </header>

    <nav>
        <a href="index.html">Home</a>
        <a href="about.html">About Us</a>
        <a href="menu.php">Menu</a>
        <a href="reservations.php">Reservation</a>
        <a href="contact.html">Contact</a>
    </nav>

    <section id="menu">
        <h2>Our Featured Dishes</h2>
        
        <?php if (isset($success_message)): ?>
            <div class="success-message"><?php echo htmlspecialchars($success_message); ?></div>
        <?php endif; ?>
        
        <?php if (isset($error_message)): ?>
            <div class="error-message"><?php echo htmlspecialchars($error_message); ?></div>
        <?php endif; ?>

        <div class="menu-container">
            <?php foreach ($menu_items as $item): ?>
                <div class="dish-card">
                    <?php if (!empty($item['picture'])): ?>
                        <img src="<?php echo htmlspecialchars($item['picture']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" class="dish-image">
                    <?php endif; ?>
                    <div class="dish-content">
                        <h3><?php echo htmlspecialchars($item['name']); ?></h3>
                        <p><b>$<?php echo number_format($item['price'], 2); ?></b></p>
                        <?php if (!empty($item['description'])): ?>
                            <p><?php echo nl2br(htmlspecialchars($item['description'])); ?></p>
                        <?php endif; ?>
                        
                        <!-- Review Form -->
                        <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                            <div style="margin-top: 15px;">
                                <span class="star-rating" data-rating="0">
                                    <i class="star" data-value="1">&#9733;</i>
                                    <i class="star" data-value="2">&#9733;</i>
                                    <i class="star" data-value="3">&#9733;</i>
                                    <i class="star" data-value="4">&#9733;</i>
                                    <i class="star" data-value="5">&#9733;</i>
                                </span>
                                <textarea name="review_text" rows="3" placeholder="Leave a review..." required></textarea>
                                
                                <input type="hidden" name="rating" class="rating-input" value="">
                                <input type="hidden" name="dish_name" value="<?php echo htmlspecialchars($item['name']); ?>">

                                <button type="submit">Submit Review</button>
                            </div>
                        </form>

                        <!-- Display Reviews for this dish -->
                        <div class="review-display">
                            <?php
                            $dish_reviews = array_filter($reviews, function($review) use ($item) {
                                return $review['dish_name'] === $item['name'];
                            });
                            
                            if (empty($dish_reviews)) {
                                echo '<p style="text-align: center; color: #666;">No reviews yet. Be the first to review!</p>';
                            } else {
                                foreach ($dish_reviews as $review) {
                                    echo '<div class="review-box">';
                                    echo '<div class="review-stars">';
                                    for ($i = 0; $i < $review['rating']; $i++) {
                                        echo '&#9733;';
                                    }
                                    echo '</div>';
                                    echo '<p>' . htmlspecialchars($review['review_text']) . '</p>';
                                    echo '<div class="review-date">' . date('F j, Y', strtotime($review['created_at'])) . '</div>';
                                    echo '</div>';
                                }
                            }
                            ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
            <?php if (empty($menu_items)): ?>
                <p style="text-align:center; color:#666;">No menu items available.</p>
            <?php endif; ?>
        </div>
    </section>

    <footer>
        &copy; 2025 Matthews Rooftop Restaurant. All rights reserved.
    </footer>

    <script>
    document.querySelectorAll('.star-rating').forEach(rating => {
        rating.addEventListener('click', function(event) {
            if (event.target.classList.contains('star')) {
                let selectedValue = event.target.getAttribute('data-value');
                rating.setAttribute('data-rating', selectedValue);
                
                let ratingInput = rating.closest('form').querySelector('.rating-input');
                ratingInput.value = selectedValue;

                rating.querySelectorAll('.star').forEach(star => {
                    if (star.getAttribute('data-value') <= selectedValue) {
                        star.classList.add('selected');
                    } else {
                        star.classList.remove('selected');
                    }
                });
            }
        });
    });

    document.querySelectorAll('form').forEach(form => {
        form.addEventListener('submit', function(event) {
            const ratingInput = form.querySelector('.rating-input');
            const reviewText = form.querySelector('textarea[name="review_text"]');
            
            if (!ratingInput.value) {
                event.preventDefault();
                alert("Please provide a rating.");
            } else if (!reviewText.value.trim()) {
                event.preventDefault();
                alert("Please provide a review.");
            }
        });
    });
    </script>
</body>
</html> 