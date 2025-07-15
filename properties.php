<?php
require_once 'config/database.php';
include 'includes/header.php';

$database = new Database();
$db = $database->getConnection();

// Filter parameters
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$city = isset($_GET['city']) ? $_GET['city'] : '';
$type = isset($_GET['type']) ? $_GET['type'] : '';
$bhk = isset($_GET['bhk']) ? $_GET['bhk'] : '';
$min_price = isset($_GET['min_price']) ? $_GET['min_price'] : '';
$max_price = isset($_GET['max_price']) ? $_GET['max_price'] : '';
$sort_by = isset($_GET['sort_by']) ? $_GET['sort_by'] : 'newest';

// Build query
$where_conditions = ["p.available = 1"];
$params = [];

if (!empty($search)) {
    $where_conditions[] = "(p.title LIKE ? OR p.location LIKE ? OR p.city LIKE ?)";
    $search_param = "%$search%";
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
}

if (!empty($city)) {
    $where_conditions[] = "p.city = ?";
    $params[] = $city;
}

if (!empty($type)) {
    $where_conditions[] = "p.type = ?";
    $params[] = $type;
}

if (!empty($bhk)) {
    $where_conditions[] = "p.bhk = ?";
    $params[] = $bhk;
}

if (!empty($min_price)) {
    $where_conditions[] = "p.price >= ?";
    $params[] = $min_price;
}

if (!empty($max_price)) {
    $where_conditions[] = "p.price <= ?";
    $params[] = $max_price;
}

$where_clause = implode(" AND ", $where_conditions);

// Sorting
$order_by = "p.created_at DESC";
switch ($sort_by) {
    case 'price-low':
        $order_by = "p.price ASC";
        break;
    case 'price-high':
        $order_by = "p.price DESC";
        break;
    case 'rating':
        $order_by = "p.rating DESC";
        break;
}

$query = "SELECT p.*, u.name as owner_name FROM properties p 
          JOIN users u ON p.owner_id = u.id 
          WHERE $where_clause 
          ORDER BY $order_by";

$stmt = $db->prepare($query);
$stmt->execute($params);
$properties = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get unique cities for filter
$city_query = "SELECT DISTINCT city FROM properties ORDER BY city";
$city_stmt = $db->prepare($city_query);
$city_stmt->execute();
$cities = $city_stmt->fetchAll(PDO::FETCH_COLUMN);
?>

<main class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Filter Panel -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <form method="GET" class="space-y-4">
                <!-- Search Bar -->
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fas fa-search text-gray-400"></i>
                    </div>
                    <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" 
                           placeholder="Search properties, locations..." 
                           class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                </div>

                <!-- Filters -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-6 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">City</label>
                        <select name="city" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500">
                            <option value="">All Cities</option>
                            <?php foreach ($cities as $city_option): ?>
                                <option value="<?php echo htmlspecialchars($city_option); ?>" 
                                        <?php echo $city == $city_option ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($city_option); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Type</label>
                        <select name="type" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500">
                            <option value="">All Types</option>
                            <option value="apartment" <?php echo $type == 'apartment' ? 'selected' : ''; ?>>Apartment</option>
                            <option value="house" <?php echo $type == 'house' ? 'selected' : ''; ?>>House</option>
                            <option value="villa" <?php echo $type == 'villa' ? 'selected' : ''; ?>>Villa</option>
                            <option value="studio" <?php echo $type == 'studio' ? 'selected' : ''; ?>>Studio</option>
                            <option value="room" <?php echo $type == 'room' ? 'selected' : ''; ?>>Room</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">BHK</label>
                        <select name="bhk" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500">
                            <option value="">Any BHK</option>
                            <option value="0">1 RK</option>
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <option value="<?php echo $i; ?>" <?php echo $bhk == $i ? 'selected' : ''; ?>><?php echo $i; ?> BHK</option>
                            <?php endfor; ?>

                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Min Price</label>
                        <input type="number" name="min_price" value="<?php echo htmlspecialchars($min_price); ?>" 
                               placeholder="₹10,000" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Max Price</label>
                        <input type="number" name="max_price" value="<?php echo htmlspecialchars($max_price); ?>" 
                               placeholder="₹1,00,000" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Sort By</label>
                        <select name="sort_by" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500">
                            <option value="newest" <?php echo $sort_by == 'newest' ? 'selected' : ''; ?>>Newest First</option>
                            <option value="price-low" <?php echo $sort_by == 'price-low' ? 'selected' : ''; ?>>Price: Low to High</option>
                            <option value="price-high" <?php echo $sort_by == 'price-high' ? 'selected' : ''; ?>>Price: High to Low</option>
                            <option value="rating" <?php echo $sort_by == 'rating' ? 'selected' : ''; ?>>Highest Rated</option>
                        </select>
                    </div>
                </div>

                <div class="flex space-x-4">
                    <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                        <i class="fas fa-search mr-2"></i>Search
                    </button>
                    <a href="properties.php" class="px-6 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400">
                        <i class="fas fa-times mr-2"></i>Clear
                    </a>
                </div>
            </form>
        </div>

        <!-- Results -->
        <div class="flex items-center justify-between mb-6">
            <p class="text-gray-600">Showing <?php echo count($properties); ?> properties</p>
        </div>

        <?php if (count($properties) > 0): ?>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php foreach ($properties as $property): ?>
                    
                    


                    <div class="bg-white rounded-xl shadow-md hover:shadow-xl transition-all duration-300 overflow-hidden card-hover">
                        <div class="relative overflow-hidden">
                            <?php 
                            $images = explode(',', $property['images']);
                            $first_image = $images[0];
                            ?>
                            <img src="<?php echo htmlspecialchars($first_image); ?>" 
                                 alt="<?php echo htmlspecialchars($property['title']); ?>" 
                                 class="w-full h-100 object-cover">
                            <div class="absolute bottom-3 left-3">
                                <span class="px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    Available
                                </span>
                            </div>
                        </div>

                        <div class="p-5">
                            <div class="flex items-start justify-between mb-2">
                                <h3 class="text-lg font-semibold text-gray-900"><?php echo htmlspecialchars($property['title']); ?></h3>
                                <div class="flex items-center space-x-1">
                                    <i class="fas fa-star text-yellow-400"></i>
                                    <span class="text-sm text-gray-600"><?php echo $property['rating']; ?></span>
                                </div>
                            </div>

                            <div class="flex items-center space-x-1 text-gray-500 mb-3">
                                <i class="fas fa-map-marker-alt"></i>
                                <span class="text-sm"><?php echo htmlspecialchars($property['location'] . ', ' . $property['city']); ?></span>
                            </div>

                            <div class="flex items-center space-x-4 mb-4">
                                <div class="flex items-center space-x-1">
                                    <i class="fas fa-bed text-gray-400"></i>
                                    <span class="text-sm text-gray-600"><?php echo $property['bhk']; ?> BHK</span>
                                </div>
                                <div class="flex items-center space-x-1">
                                    <i class="fas fa-vector-square text-gray-400"></i>
                                    <span class="text-sm text-gray-600"><?php echo $property['area']; ?> sq ft</span>
                                </div>
                            </div>

                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-2xl font-bold text-green-600">₹<?php echo number_format($property['price']); ?></p>
                                    <p class="text-sm text-gray-500">per month</p>
                                </div>
                                <a href="property_details.php?id=<?php echo $property['id']; ?>" 
                                   class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors text-sm font-medium">
                                    View Details
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="text-center py-12">
                <i class="fas fa-home text-6xl text-gray-400 mb-4"></i>
                <h3 class="text-lg font-medium text-gray-900 mb-2">No properties found</h3>
                <p class="text-gray-600">Try adjusting your filters to see more results</p>
            </div>
        <?php endif; ?>
    </div>
</main>

<?php include 'includes/footer.php'; ?>