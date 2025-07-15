<?php
require_once 'config/database.php';
include 'includes/header.php';

$database = new Database();
$db = $database->getConnection();

// Get featured properties
$query = "SELECT p.*, u.name as owner_name FROM properties p 
          JOIN users u ON p.owner_id = u.id 
          WHERE p.available = 1 
          ORDER BY p.created_at DESC LIMIT 3";
$stmt = $db->prepare($query);
$stmt->execute();
$featured_properties = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<main>
    <!-- Hero Section -->
    <section class="relative bg-gradient-to-r from-blue-600 to-blue-800 text-white">
        <div class="absolute inset-0 bg-black opacity-20"></div>
        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-24">
            <div class="text-center">
                <h1 class="text-4xl md:text-6xl font-bold mb-6">
                    Find Your Perfect
                    <span class="block text-blue-200">Rental Home</span>
                </h1>
                <p class="text-xl md:text-2xl mb-8 text-blue-100">
                    Discover amazing properties, connect with trusted owners, and make renting effortless
                </p>
                <a href="properties.php" class="inline-flex items-center space-x-2 px-8 py-3 bg-white text-blue-600 rounded-lg font-medium hover:bg-blue-50 transition-colors">
                    <i class="fas fa-search"></i>
                    <span>Explore Properties</span>
                </a>
            </div>
        </div>
    </section>
    
<!-- Featured Properties -->
    <section class="py-16 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between mb-12">
                <div>
                    <h2 class="text-3xl font-bold text-gray-900 mb-2">Featured Properties</h2>
                    <p class="text-lg text-gray-600">Discover our most popular rental homes</p>
                </div>
                <a href="properties.php" class="flex items-center space-x-1 text-blue-600 hover:text-blue-700 font-medium">
                    <span>View All</span>
                    <i class="fas fa-arrow-right"></i>
                </a>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php foreach ($featured_properties as $property): ?>
                    <div class="bg-white rounded-xl shadow-md hover:shadow-xl transition-all duration-300 overflow-hidden card-hover">
                        <div class="relative overflow-hidden">
                            <?php 
                            $images = explode(',', $property['images']);
                            $only_for = explode(',', $property['only_for']);
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

                            <div class="flex items-center space-x-4 mb-4">
                                Only For : 
                                <?php foreach ($only_for as $available): ?>
                                    <div class="flex items-center space-x-2">
                                        
                                        
                                        <span> </span>
                                        <span class="bg-green-100 text-gray-900 px-3 py-2 rounded-full text-sm"><?php echo htmlspecialchars(trim($available)); ?></span>
                                    </div>
                                <?php endforeach; ?>
                                
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
        </div>
    </section>

    <!-- Features Section -->
    <section class="py-16 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <h2 class="text-3xl font-bold text-gray-900 mb-4">Why Choose RentHub?</h2>
                <p class="text-lg text-gray-600">Everything you need for a seamless rental experience</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="text-center p-6">
                    <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-home text-2xl text-blue-600"></i>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-2">Wide Selection</h3>
                    <p class="text-gray-600">Browse thousands of verified properties across multiple cities</p>
                </div>

                <div class="text-center p-6">
                    <div class="w-16 h-16 bg-emerald-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-shield-alt text-2xl text-emerald-600"></i>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-2">Secure & Trusted</h3>
                    <p class="text-gray-600">All properties and owners are verified for your safety</p>
                </div>

                <div class="text-center p-6">
                    <div class="w-16 h-16 bg-orange-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-star text-2xl text-orange-600"></i>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-2">Easy Management</h3>
                    <p class="text-gray-600">Automated rent collection and payment tracking</p>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="py-16 bg-blue-600">
        <div class="max-w-4xl mx-auto text-center px-4 sm:px-6 lg:px-8">
            <h2 class="text-3xl font-bold text-white mb-4">Ready to Get Started?</h2>
            <p class="text-xl text-blue-100 mb-8">
                Join thousands of happy renters and property owners on RentHub
            </p>
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <a href="properties.php" class="px-8 py-3 bg-white text-blue-600 rounded-lg font-medium hover:bg-blue-50 transition-colors">
                    Find a Property
                </a>
                <?php if (isLoggedIn() && (getUserRole() == 'owner' || getUserRole() == 'admin')): ?>
                    <a href="add_property.php" class="px-8 py-3 bg-blue-700 text-white rounded-lg font-medium hover:bg-blue-800 transition-colors border border-blue-400">
                        List Your Property
                    </a>
                <?php else: ?>
                    <a href="register.php" class="px-8 py-3 bg-blue-700 text-white rounded-lg font-medium hover:bg-blue-800 transition-colors border border-blue-400">
                        Become an Owner
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </section>
</main>
