<?php
/**
 * Plugin Name: Room Listings with Detailed View
 * Description: Displays a list of rooms with detailed view for each room.
 * Version: 1.1.0
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Room Listing and Search Shortcode
function room_listing_search_shortcode() {
    ob_start();
    global $wpdb;

    // Fetch room listings
    $rooms = $wpdb->get_results("SELECT * FROM wp_rooms", ARRAY_A);
    ?>
    <div>
        <!-- Search Form -->
        <form method="GET" action="">
            <label>Check-in Date:</label>
            <input type="date" name="check_in" required>
            <label>Check-out Date:</label>
            <input type="date" name="check_out" required>
            <label>Room Type:</label>
            <select name="room_type">
                <option value="">All</option>
                <option value="single">Single</option>
                <option value="double">Double</option>
                <option value="suite">Suite</option>
            </select>
            <label>Price Range:</label>
            <select name="price_range">
                <option value="">All</option>
                <option value="0-100">$0 - $100</option>
                <option value="100-200">$100 - $200</option>
                <option value="200-300">$200 - $300</option>
            </select>
            <button type="submit">Search</button>
        </form>

        <!-- Room Listings -->
        <div class="room-listings">
            <?php 
            // Filter rooms based on search parameters
            foreach ($rooms as $room) {
                if (isset($_GET['room_type']) && $_GET['room_type'] && $_GET['room_type'] !== $room['type']) continue;
                if (isset($_GET['price_range']) && $_GET['price_range']) {
                    list($min, $max) = explode('-', $_GET['price_range']);
                    if ($room['price'] < $min || $room['price'] > $max) continue;
                }

                // Display room details in a container
                echo "<div class='room' onclick='window.location=\"http://hoteltmt.local/room-details/?room_id=" . $room['id'] . "\"'>";
                echo "<h3>" . ucfirst($room['type']) . " Room</h3>";
                echo "<p>Price: $" . $room['price'] . " per night</p>";
                echo "<p>Beds: " . $room['beds'] . "</p>";
                echo "<p>Amenities: " . $room['amenities'] . "</p>";
                echo "<p>Max Occupancy: " . $room['occupancy'] . "</p>";
                echo "</div>";
            }
            ?>
        </div>
    </div>
    <?php
    return ob_get_clean();
}

// Room Detailed View Shortcode
function room_detailed_view_shortcode() {
    if (isset($_GET['room_id'])) {
        global $wpdb;
        $room_id = intval($_GET['room_id']);
        
        // Fetch room details
        $room = $wpdb->get_row($wpdb->prepare("SELECT * FROM wp_rooms WHERE id = %d", $room_id), ARRAY_A);
        if ($room) {
            ob_start();
            ?>
            <div class="room-details">
                <h2><?php echo ucfirst($room['type']); ?> Room Details</h2>
                <p>Price: $<?php echo $room['price']; ?> per night</p>
                <p>Description: <?php echo $room['description']; ?></p>
                
                <!-- Room Images -->
                <div class="room-images">
                    <h3>Images</h3>
                    <?php
                    $images = explode(',', $room['images']);
                    foreach ($images as $image) {
                        echo "<img src='$image' alt='Room Image' />";
                    }
                    ?>
                </div>
                
                <!-- Room Reviews -->
                <div class="room-reviews">
                    <h3>Reviews</h3>
                    <p><?php echo $room['reviews']; ?></p>
                </div>
            </div>
            <?php
            return ob_get_clean();
        } else {
            return "<p>Room not found.</p>";
        }
    }
}

// Register shortcodes
add_shortcode('room_listing_search', 'room_listing_search_shortcode');
add_shortcode('room_detailed_view', 'room_detailed_view_shortcode');


function enqueue_room_styles() {
    wp_enqueue_style('room-style', plugins_url('css/room.style.css', __FILE__));
}
add_action('wp_enqueue_scripts', 'enqueue_room_styles');
wp_enqueue_style('room-style', plugins_url('css/room.style.css', __FILE__), array(), '1.1');