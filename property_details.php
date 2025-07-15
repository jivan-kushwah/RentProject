<?php
require_once 'config/database.php';
require_once 'config/session.php';
include 'includes/header.php';

$property_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$property_id) {
    header("Location: properties.php");
    exit();
}

$database = new Database();
$db = $database->getConnection();

// Get property details
$query = "SELECT p.*, u.name as owner_name, u.phone as owner_phone, u.email as owner_email 
          FROM properties p 
          JOIN users u ON p.owner_id = u.id 
          WHERE p.id = ?";
$stmt = $db->prepare($query);
$stmt->execute([$property_id]);
$property = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$property) {
    header("Location: properties.php");
    exit();
}

$success = '';
$error = '';

// Handle booking request
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['book_property'])) {
    if (!isLoggedIn()) {
        $error = 'Please login to book a property';
    } else {
        $user_id = getUserId();
        $start_date = $_POST['start_date'];
        $end_date = $_POST['end_date'];
        $message = $_POST['message'];
        
        if (empty($start_date) || empty($end_date)) {
            $error = 'Please select start and end dates';
        } else {
            $security_deposit = $property['price'] * 2;
            
            $booking_query = "INSERT INTO bookings (property_id, user_id, owner_id, start_date, end_date, monthly_rent, security_deposit, message) 
                             VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            $booking_stmt = $db->prepare($booking_query);
            
            if ($booking_stmt->execute([$property_id, $user_id, $property['owner_id'], $start_date, $end_date, $property['price'], $security_deposit, $message])) {
                // Add activity
                $activity_query = "INSERT INTO activities (type, description, user_id, related_id) VALUES (?, ?, ?, ?)";
                $activity_stmt = $db->prepare($activity_query);
                $activity_stmt->execute(['booking', "New booking request for " . $property['title'], $user_id, $property_id]);
                
                $success = 'Booking request submitted successfully!';
            } else {
                $error = 'Failed to submit booking request. Please try again.';
            }
        }
    }
}

$images = explode(',', $property['images']);
$amenities = explode(',', $property['amenities']);
$only_for = explode(',', $property['only_for']);


?>

<main class="min-h-screen bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Back Button -->
        <a href="properties.php" class="mb-6 inline-flex items-center space-x-2 text-gray-600 hover:text-gray-900">
            <i class="fas fa-arrow-left"></i>
            <span>Back to Properties</span>
        </a>

        <?php if ($success): ?>
            <div class="mb-6 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded alert-auto-hide">
                <?php echo htmlspecialchars($success); ?>
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="mb-6 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded alert-auto-hide">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Main Content -->
            <div class="lg:col-span-2">
                <!-- Image Gallery -->
                <div class="bg-white rounded-xl shadow-md overflow-hidden mb-6">
                    <div class="aspect-w-16 aspect-h-9">
                        <img src="<?php echo htmlspecialchars($images[0]); ?>" 
                             alt="<?php echo htmlspecialchars($property['title']); ?>" 
                             class="w-full h-90 md:h-96 object-cover" id="main-image">
                    </div>
                    <?php if (count($images) > 1): ?>
                        <div class="flex space-x-2 p-4">
                            <?php foreach ($images as $index => $image): ?>
                                <button onclick="changeImage('<?php echo htmlspecialchars($image); ?>')" 
                                        class="w-20 h-16 rounded-lg overflow-hidden border-2 border-gray-200 hover:border-blue-500">
                                    <img src="<?php echo htmlspecialchars($image); ?>" 
                                         alt="View <?php echo $index + 1; ?>" 
                                         class="w-full h-full object-cover">
                                </button>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Property Details -->
                <div class="bg-white rounded-xl shadow-md p-6 mb-6">
                    <div class="flex items-start justify-between mb-4">
                        <div>
                            <h1 class="text-3xl font-bold text-gray-900 mb-2"><?php echo htmlspecialchars($property['title']); ?></h1>
                            <div class="flex items-center space-x-1 text-gray-500 mb-2">
                                <i class="fas fa-map-marker-alt"></i>
                                <span><?php echo htmlspecialchars($property['location'] . ', ' . $property['city'] . ', ' . $property['state']); ?></span>
                            </div>
                            <div class="flex items-center space-x-1">
                                <i class="fas fa-star text-yellow-400"></i>
                                <span class="text-lg font-medium"><?php echo $property['rating']; ?></span>
                                <span class="text-gray-500">(<?php echo $property['reviews_count']; ?> reviews)</span>
                            </div>
                        </div>
                        <span class="px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                            Available
                        </span>
                    </div>

                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 py-4 border-y border-gray-200">
                        <div class="text-center">
                            <i class="fas fa-bed text-2xl text-gray-400 mb-1"></i>
                            <p class="text-sm text-gray-500">Bedrooms</p>
                            <p class="font-semibold"><?php echo $property['bhk']; ?> BHK</p>
                        </div>
                        <div class="text-center">
                            <i class="fas fa-vector-square text-2xl text-gray-400 mb-1"></i>
                            <p class="text-sm text-gray-500">Area</p>
                            <p class="font-semibold"><?php echo $property['area']; ?> sq ft</p>
                        </div>
                        <div class="text-center">
                            <i class="fas fa-rupee-sign text-2xl text-gray-400 mb-1"></i>
                            <p class="text-sm text-gray-500">Rent</p>
                            <p class="font-semibold">₹<?php echo number_format($property['price']); ?></p>
                        </div>
                        <div class="text-center">
                            <i class="fas fa-shield-alt text-2xl text-gray-400 mb-1"></i>
                            <p class="text-sm text-gray-500">Deposit</p>
                            <p class="font-semibold">₹<?php echo number_format($property['price'] * 2); ?></p>
                        </div>
                    </div>

                    <div class="py-4">
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">Description</h3>
                        <p class="text-gray-600 leading-relaxed"><?php echo htmlspecialchars($property['description']); ?></p>
                    </div>
                    
                    <div class="py-4">
                        <h3 class="text-lg font-semibold text-gray-900 mb-3">Only For</h3>
                        <div class="grid grid-cols-2 md:grid-cols-3 gap-2">
                            <?php foreach ($only_for as $available): ?>
                                <div class="flex items-center space-x-2">
                                    <i class="fas fa-check text-green-500"></i>
                                    <span class="bg-green-200 text-gray-900 px-3 py-2 rounded-full text-sm"><?php echo htmlspecialchars(trim($available)); ?></span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div class="py-4">
                        <h3 class="text-lg font-semibold text-gray-900 mb-3">Amenities</h3>
                        <div class="grid grid-cols-2 md:grid-cols-3 gap-2">
                            <?php foreach ($amenities as $amenity): ?>
                                <div class="flex items-center space-x-2">
                                    <i class="fas fa-check text-green-500"></i>
                                    <span class="text-sm text-gray-600"><?php echo htmlspecialchars(trim($amenity)); ?></span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                </div>
            </div>

            <!-- Sidebar -->
            <div class="lg:col-span-1">
                <!-- Pricing Card -->
                <div class="bg-white rounded-xl shadow-md p-6 mb-6 sticky top-6">
                    <div class="text-center mb-6">
                        <p class="text-3xl font-bold text-gray-900">₹<?php echo number_format($property['price']); ?></p>
                        <p class="text-gray-500">per month</p>
                    </div>

                    <?php if (isLoggedIn()): ?>
                        <button onclick="openBookingModal()" 
                                class="w-full py-3 bg-blue-600 text-white rounded-lg font-medium hover:bg-blue-700 transition-colors mb-4">
                            Book Now
                        </button>
                    <?php else: ?>
                        <a href="login.php" 
                           class="block w-full py-3 bg-blue-600 text-white rounded-lg font-medium hover:bg-blue-700 transition-colors mb-4 text-center">
                            Login to Book
                        </a>
                    <?php endif; ?>

                    <div class="space-y-3 pt-4 border-t border-gray-200">
                        <div class="flex items-center justify-between">
                            <span class="text-gray-600">Monthly Rent</span>
                            <span class="font-medium">₹<?php echo number_format($property['price']); ?></span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-gray-600">Security Deposit</span>
                            <span class="font-medium">₹<?php echo number_format($property['price'] * 2); ?></span>
                        </div>
                        <div class="flex items-center justify-between pt-2 border-t border-gray-200">
                            <span class="font-semibold">Total Move-in Cost</span>
                            <span class="font-bold text-lg">₹<?php echo number_format($property['price'] * 3); ?></span>
                        </div>
                    </div>
                </div>

                <!-- Owner Info -->
                <div class="bg-white rounded-xl shadow-md p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Property Owner</h3>
                    <div class="flex items-center space-x-3 mb-4">
                        <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                            <span class="text-blue-600 font-medium"><?php echo strtoupper(substr($property['owner_name'], 0, 1)); ?></span>
                        </div>
                        <div>
                            <p class="font-medium text-gray-900"><?php echo htmlspecialchars($property['owner_name']); ?></p>
                            <p class="text-sm text-gray-500">Property Owner</p>
                        </div>
                    </div>
                    <div class="space-y-2">
                        <a href="tel:<?php echo htmlspecialchars($property['owner_phone']); ?>" 
                           class="flex items-center space-x-2 w-full px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                            <i class="fas fa-phone text-gray-400"></i>
                            <span class="text-sm"><?php echo htmlspecialchars($property['owner_phone']); ?></span>
                        </a>
                        <a href="mailto:<?php echo htmlspecialchars($property['owner_email']); ?>" 
                           class="flex items-center space-x-2 w-full px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                            <i class="fas fa-envelope text-gray-400"></i>
                            <span class="text-sm">Send Message</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Booking Modal -->
    <div id="bookingModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50 hidden">
        <div class="bg-white rounded-xl max-w-md w-full p-6">
            <h3 class="text-xl font-bold text-gray-900 mb-4">Book Property</h3>
            
            <form method="POST">
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Move-in Date</label>
                        <input type="date" name="start_date" required 
                               min="<?php echo date('Y-m-d'); ?>"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Lease End Date</label>
                        <input type="date" name="end_date" required 
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Message (Optional)</label>
                        <textarea name="message" rows="3" 
                                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500" 
                                  placeholder="Any special requests or questions..."></textarea>
                    </div>
                </div>

                <div class="flex space-x-3 mt-6">
                    <button type="button" onclick="closeBookingModal()" 
                            class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">
                        Cancel
                    </button>
                    <button type="submit" name="book_property" 
                            class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                        Submit Booking
                    </button>
                </div>
            </form>
        </div>
    </div>
</main>

<script>
function changeImage(src) {
    document.getElementById('main-image').src = src;
}

function openBookingModal() {
    document.getElementById('bookingModal').classList.remove('hidden');
}

function closeBookingModal() {
    document.getElementById('bookingModal').classList.add('hidden');
}
</script>

<?php include 'includes/footer.php'; ?>