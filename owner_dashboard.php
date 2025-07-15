<?php
require_once 'config/database.php';
require_once 'config/session.php';

requireRole('owner');
include 'includes/header.php';

$database = new Database();
$db = $database->getConnection();
$owner_id = getUserId();

$active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'overview';

// Get owner's properties
$properties_query = "SELECT * FROM properties WHERE owner_id = ? ORDER BY created_at DESC";
$properties_stmt = $db->prepare($properties_query);
$properties_stmt->execute([$owner_id]);
$properties = $properties_stmt->fetchAll(PDO::FETCH_ASSOC);

// Get owner's bookings
$bookings_query = "SELECT b.*, p.title as property_title, p.location, u.name as user_name 
                   FROM bookings b 
                   JOIN properties p ON b.property_id = p.id 
                   JOIN users u ON b.user_id = u.id 
                   WHERE b.owner_id = ? 
                   ORDER BY b.created_at DESC";
$bookings_stmt = $db->prepare($bookings_query);
$bookings_stmt->execute([$owner_id]);
$bookings = $bookings_stmt->fetchAll(PDO::FETCH_ASSOC);

// Get owner's payments
$payments_query = "SELECT p.*, b.property_id, pr.title as property_title 
                   FROM payments p 
                   JOIN bookings b ON p.booking_id = b.id 
                   JOIN properties pr ON b.property_id = pr.id 
                   WHERE b.owner_id = ? 
                   ORDER BY p.created_at DESC";
$payments_stmt = $db->prepare($payments_query);
$payments_stmt->execute([$owner_id]);
$payments = $payments_stmt->fetchAll(PDO::FETCH_ASSOC);

// Calculate statistics
$total_properties = count($properties);
$active_bookings = count(array_filter($bookings, function($b) { return $b['status'] == 'active'; }));
$total_rent_received = array_sum(array_map(function($p) { 
    return $p['status'] == 'paid' ? ($p['amount'] - $p['commission']) : 0; 
}, $payments));
$pending_rent = array_sum(array_map(function($p) { 
    return $p['status'] == 'pending' ? ($p['amount'] - $p['commission']) : 0; 
}, $payments));
?>

<main class="min-h-screen bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Owner Dashboard</h1>
                    <p class="text-lg text-gray-600 mt-1">Manage your properties and bookings</p>
                </div>
                <div class="text-right">
                    <p class="text-sm text-gray-500">Welcome back,</p>
                    <p class="text-lg font-semibold text-gray-900"><?php echo getUserName(); ?></p>
                </div>
            </div>
        </div>

        <!-- Tabs -->
        <div class="mb-6">
            <div class="border-b border-gray-200">
                <nav class="-mb-px flex space-x-8">
                    <a href="?tab=overview" 
                       class="<?php echo $active_tab == 'overview' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'; ?> flex items-center space-x-2 py-2 px-1 border-b-2 font-medium text-sm">
                        <i class="fas fa-eye"></i>
                        <span>Overview</span>
                    </a>
                    <a href="?tab=properties" 
                       class="<?php echo $active_tab == 'properties' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'; ?> flex items-center space-x-2 py-2 px-1 border-b-2 font-medium text-sm">
                        <i class="fas fa-building"></i>
                        <span>My Properties</span>
                    </a>
                    <a href="?tab=bookings" 
                       class="<?php echo $active_tab == 'bookings' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'; ?> flex items-center space-x-2 py-2 px-1 border-b-2 font-medium text-sm">
                        <i class="fas fa-calendar"></i>
                        <span>Bookings</span>
                    </a>
                    <a href="?tab=revenue" 
                       class="<?php echo $active_tab == 'revenue' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'; ?> flex items-center space-x-2 py-2 px-1 border-b-2 font-medium text-sm">
                        <i class="fas fa-dollar-sign"></i>
                        <span>Revenue</span>
                    </a>
                </nav>
            </div>
        </div>

        <!-- Tab Content -->
        <?php if ($active_tab == 'overview'): ?>
            <!-- Overview Tab -->
            <div class="space-y-6">
                <!-- Stats Cards -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    <div class="bg-white rounded-xl shadow-md p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600">My Properties</p>
                                <p class="text-3xl font-bold text-gray-900 mt-1"><?php echo $total_properties; ?></p>
                            </div>
                            <div class="p-3 rounded-full bg-blue-100">
                                <i class="fas fa-building text-2xl text-blue-600"></i>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-xl shadow-md p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600">Active Bookings</p>
                                <p class="text-3xl font-bold text-gray-900 mt-1"><?php echo $active_bookings; ?></p>
                            </div>
                            <div class="p-3 rounded-full bg-green-100">
                                <i class="fas fa-calendar text-2xl text-green-600"></i>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-xl shadow-md p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600">Rent Received</p>
                                <p class="text-3xl font-bold text-gray-900 mt-1">₹<?php echo number_format($total_rent_received); ?></p>
                            </div>
                            <div class="p-3 rounded-full bg-orange-100">
                                <i class="fas fa-dollar-sign text-2xl text-orange-600"></i>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-xl shadow-md p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600">Pending Rent</p>
                                <p class="text-3xl font-bold text-gray-900 mt-1">₹<?php echo number_format($pending_rent); ?></p>
                            </div>
                            <div class="p-3 rounded-full bg-red-100">
                                <i class="fas fa-clock text-2xl text-red-600"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="bg-white rounded-xl shadow-md p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Quick Actions</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <a href="add_property.php" 
                           class="flex items-center space-x-3 p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                            <i class="fas fa-plus text-2xl text-blue-600"></i>
                            <div>
                                <h4 class="font-medium text-gray-900">Add New Property</h4>
                                <p class="text-sm text-gray-500">List a new property for rent</p>
                            </div>
                        </a>
                        <a href="?tab=properties" 
                           class="flex items-center space-x-3 p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                            <i class="fas fa-cog text-2xl text-green-600"></i>
                            <div>
                                <h4 class="font-medium text-gray-900">Manage Properties</h4>
                                <p class="text-sm text-gray-500">Update property details</p>
                            </div>
                        </a>
                    </div>
                </div>

                <!-- Recent Payments -->
                <div class="bg-white rounded-xl shadow-md p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Recent Payments</h3>
                    <div class="space-y-3">
                        <?php foreach (array_slice($payments, 0, 5) as $payment): ?>
                            <div class="flex items-center justify-between py-2 border-b border-gray-100 last:border-b-0">
                                <div>
                                    <p class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($payment['month']); ?></p>
                                    <p class="text-xs text-gray-500">
                                        Amount: ₹<?php echo number_format($payment['amount']); ?> | Commission: ₹<?php echo number_format($payment['commission']); ?>
                                    </p>
                                </div>
                                <div class="text-right">
                                    <p class="text-sm font-medium text-gray-900">₹<?php echo number_format($payment['amount'] - $payment['commission']); ?></p>
                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full 
                                        <?php 
                                        switch($payment['status']) {
                                            case 'paid': echo 'bg-green-100 text-green-800'; break;
                                            case 'pending': echo 'bg-yellow-100 text-yellow-800'; break;
                                            default: echo 'bg-red-100 text-red-800'; break;
                                        }
                                        ?>">
                                        <?php echo ucfirst($payment['status']); ?>
                                    </span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

        <?php elseif ($active_tab == 'properties'): ?>
            <!-- Properties Tab -->
            <div class="space-y-6">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-900">My Properties</h3>
                    <a href="add_property.php" 
                       class="flex items-center space-x-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                        <i class="fas fa-plus"></i>
                        <span>Add Property</span>
                    </a>
                </div>

                <?php if (count($properties) > 0): ?>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <?php foreach ($properties as $property): ?>
                            <div class="bg-white rounded-xl shadow-md hover:shadow-xl transition-all duration-300 overflow-hidden">
                                <div class="relative overflow-hidden">
                                    <?php 
                                    $images = explode(',', $property['images']);
                                    $first_image = $images[0];
                                    ?>
                                    <img src="<?php echo htmlspecialchars($first_image); ?>" 
                                         alt="<?php echo htmlspecialchars($property['title']); ?>" 
                                         class="w-full h-100 object-cover">
                                    <div class="absolute bottom-3 left-3">
                                        <span class="px-2 py-1 rounded-full text-xs font-medium 
                                            <?php echo $property['available'] ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
                                            <?php echo $property['available'] ? 'Available' : 'Occupied'; ?>
                                        </span>
                                    </div>
                                </div>

                                <div class="p-5">
                                    <h3 class="text-lg font-semibold text-gray-900 mb-2"><?php echo htmlspecialchars($property['title']); ?></h3>
                                    <div class="flex items-center space-x-1 text-gray-500 mb-3">
                                        <i class="fas fa-map-marker-alt"></i>
                                        <span class="text-sm"><?php echo htmlspecialchars($property['location'] . ', ' . $property['city']); ?></span>
                                    </div>
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <p class="text-2xl font-bold text-gray-900">₹<?php echo number_format($property['price']); ?></p>
                                            <p class="text-sm text-gray-500">per month</p>
                                        </div>
                                        <a href="edit_property.php?id=<?php echo $property['id']; ?>" 
                                           class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors text-sm font-medium">
                                            Edit Details
                                        </a>
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
                    <div class="text-center py-12 bg-white rounded-xl shadow-md">
                        <i class="fas fa-building text-6xl text-gray-400 mb-4"></i>
                        <h3 class="text-lg font-medium text-gray-900 mb-2">No Properties Yet</h3>
                        <p class="text-gray-600 mb-4">Start by adding your first rental property</p>
                        <a href="add_property.php" 
                           class="inline-flex items-center space-x-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                            <i class="fas fa-plus"></i>
                            <span>Add Property</span>
                        </a>
                    </div>
                <?php endif; ?>
            </div>

        <?php elseif ($active_tab == 'bookings'): ?>
            <!-- Bookings Tab -->
            <div class="bg-white rounded-xl shadow-md">
                <div class="p-6 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Property Bookings</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Property</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tenant</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Duration</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Rent</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <?php foreach ($bookings as $booking): ?>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($booking['property_title']); ?></div>
                                        <div class="text-sm text-gray-500"><?php echo htmlspecialchars($booking['location']); ?></div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <?php echo htmlspecialchars($booking['user_name']); ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <?php echo date('M j, Y', strtotime($booking['start_date'])); ?> - <?php echo date('M j, Y', strtotime($booking['end_date'])); ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        ₹<?php echo number_format($booking['monthly_rent']); ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full 
                                            <?php 
                                            switch($booking['status']) {
                                                case 'active': echo 'bg-green-100 text-green-800'; break;
                                                case 'pending': echo 'bg-yellow-100 text-yellow-800'; break;
                                                default: echo 'bg-gray-100 text-gray-800'; break;
                                            }
                                            ?>">
                                            <?php echo ucfirst($booking['status']); ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        <?php elseif ($active_tab == 'revenue'): ?>
            <!-- Revenue Tab -->
            <div class="space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="bg-white rounded-xl shadow-md p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600">Total Received</p>
                                <p class="text-3xl font-bold text-gray-900 mt-1">₹<?php echo number_format($total_rent_received); ?></p>
                            </div>
                            <div class="p-3 rounded-full bg-green-100">
                                <i class="fas fa-dollar-sign text-2xl text-green-600"></i>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-xl shadow-md p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600">Pending Amount</p>
                                <p class="text-3xl font-bold text-gray-900 mt-1">₹<?php echo number_format($pending_rent); ?></p>
                            </div>
                            <div class="p-3 rounded-full bg-orange-100">
                                <i class="fas fa-clock text-2xl text-orange-600"></i>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-xl shadow-md p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600">Commission Paid</p>
                                <p class="text-3xl font-bold text-gray-900 mt-1">₹<?php echo number_format(array_sum(array_map(function($p) { return $p['status'] == 'paid' ? $p['commission'] : 0; }, $payments))); ?></p>
                            </div>
                            <div class="p-3 rounded-full bg-red-100">
                                <i class="fas fa-percentage text-2xl text-red-600"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-md">
                    <div class="p-6 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900">Payment History</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Month</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Property</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Gross Amount</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Commission</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Net Amount</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                <?php foreach ($payments as $payment): ?>
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                            <?php echo htmlspecialchars($payment['month']); ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            <?php echo htmlspecialchars($payment['property_title']); ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            ₹<?php echo number_format($payment['amount']); ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-red-600">
                                            -₹<?php echo number_format($payment['commission']); ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-green-600">
                                            ₹<?php echo number_format($payment['amount'] - $payment['commission']); ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full 
                                                <?php 
                                                switch($payment['status']) {
                                                    case 'paid': echo 'bg-green-100 text-green-800'; break;
                                                    case 'pending': echo 'bg-yellow-100 text-yellow-800'; break;
                                                    default: echo 'bg-red-100 text-red-800'; break;
                                                }
                                                ?>">
                                                <?php echo ucfirst($payment['status']); ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</main>

<?php include 'includes/footer.php'; ?>