<?php
require_once 'config/database.php';
require_once 'config/session.php';

requireRole('admin');
include 'includes/header.php';

$database = new Database();
$db = $database->getConnection();

$active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'overview';

// Get statistics
$stats_query = "SELECT 
    (SELECT COUNT(*) FROM properties) as total_properties,
    (SELECT COUNT(*) FROM bookings WHERE status = 'active') as active_bookings,
    (SELECT COALESCE(SUM(amount), 0) FROM payments WHERE status = 'paid') as total_revenue,
    (SELECT COALESCE(SUM(commission), 0) FROM payments WHERE status = 'paid') as total_commission,
    (SELECT COUNT(*) FROM users) as total_users";
$stats_stmt = $db->prepare($stats_query);
$stats_stmt->execute();
$stats = $stats_stmt->fetch(PDO::FETCH_ASSOC);

// Get recent activities
$activities_query = "SELECT a.*, u.name as user_name FROM activities a 
                    JOIN users u ON a.user_id = u.id 
                    ORDER BY a.created_at DESC LIMIT 10";
$activities_stmt = $db->prepare($activities_query);
$activities_stmt->execute();
$activities = $activities_stmt->fetchAll(PDO::FETCH_ASSOC);

// Get all bookings with property and user details
$bookings_query = "SELECT b.*, p.title as property_title, p.location, u.name as user_name 
                   FROM bookings b 
                   JOIN properties p ON b.property_id = p.id 
                   JOIN users u ON b.user_id = u.id 
                   ORDER BY b.created_at DESC";
$bookings_stmt = $db->prepare($bookings_query);
$bookings_stmt->execute();
$bookings = $bookings_stmt->fetchAll(PDO::FETCH_ASSOC);

// Get all payments
$payments_query = "SELECT p.*, b.property_id, pr.title as property_title 
                   FROM payments p 
                   JOIN bookings b ON p.booking_id = b.id 
                   JOIN properties pr ON b.property_id = pr.id 
                   ORDER BY p.created_at DESC";
$payments_stmt = $db->prepare($payments_query);
$payments_stmt->execute();
$payments = $payments_stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<main class="min-h-screen bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Admin Dashboard</h1>
                    <p class="text-lg text-gray-600 mt-1">Manage your rental platform</p>
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
                    <a href="?tab=activity" 
                       class="<?php echo $active_tab == 'activity' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'; ?> flex items-center space-x-2 py-2 px-1 border-b-2 font-medium text-sm">
                        <i class="fas fa-activity"></i>
                        <span>Activity</span>
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
                                <p class="text-sm font-medium text-gray-600">Total Properties</p>
                                <p class="text-3xl font-bold text-gray-900 mt-1"><?php echo $stats['total_properties']; ?></p>
                                <p class="text-sm text-green-600 mt-1">+12% from last month</p>
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
                                <p class="text-3xl font-bold text-gray-900 mt-1"><?php echo $stats['active_bookings']; ?></p>
                                <p class="text-sm text-green-600 mt-1">+5% from last month</p>
                            </div>
                            <div class="p-3 rounded-full bg-green-100">
                                <i class="fas fa-calendar text-2xl text-green-600"></i>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-xl shadow-md p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600">Total Revenue</p>
                                <p class="text-3xl font-bold text-gray-900 mt-1">₹<?php echo number_format($stats['total_revenue']); ?></p>
                                <p class="text-sm text-green-600 mt-1">+18% from last month</p>
                            </div>
                            <div class="p-3 rounded-full bg-orange-100">
                                <i class="fas fa-chart-line text-2xl text-orange-600"></i>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-xl shadow-md p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600">Commission Earned</p>
                                <p class="text-3xl font-bold text-gray-900 mt-1">₹<?php echo number_format($stats['total_commission']); ?></p>
                                <p class="text-sm text-green-600 mt-1">+15% from last month</p>
                            </div>
                            <div class="p-3 rounded-full bg-purple-100">
                                <i class="fas fa-dollar-sign text-2xl text-purple-600"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Activity -->
                <div class="bg-white rounded-xl shadow-md p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Recent Activity</h3>
                    <div class="space-y-3">
                        <?php foreach (array_slice($activities, 0, 5) as $activity): ?>
                            <div class="flex items-center space-x-3 py-2">
                                <div class="w-2 h-2 bg-blue-500 rounded-full"></div>
                                <div class="flex-1">
                                    <p class="text-sm text-gray-900"><?php echo htmlspecialchars($activity['description']); ?></p>
                                    <p class="text-xs text-gray-500">
                                        <?php echo date('M j, Y', strtotime($activity['created_at'])); ?> - <?php echo htmlspecialchars($activity['user_name']); ?>
                                    </p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

        <?php elseif ($active_tab == 'bookings'): ?>
            <!-- Bookings Tab -->
            <div class="bg-white rounded-xl shadow-md">
                <div class="p-6 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">All Bookings</h3>
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
                                <p class="text-sm font-medium text-gray-600">Monthly Revenue</p>
                                <p class="text-3xl font-bold text-gray-900 mt-1">₹<?php echo number_format($stats['total_revenue']); ?></p>
                            </div>
                            <div class="p-3 rounded-full bg-green-100">
                                <i class="fas fa-chart-line text-2xl text-green-600"></i>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-xl shadow-md p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600">Commission Earned</p>
                                <p class="text-3xl font-bold text-gray-900 mt-1">₹<?php echo number_format($stats['total_commission']); ?></p>
                            </div>
                            <div class="p-3 rounded-full bg-blue-100">
                                <i class="fas fa-dollar-sign text-2xl text-blue-600"></i>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-xl shadow-md p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600">Pending Payments</p>
                                <p class="text-3xl font-bold text-gray-900 mt-1"><?php echo count(array_filter($payments, function($p) { return $p['status'] == 'pending'; })); ?></p>
                            </div>
                            <div class="p-3 rounded-full bg-orange-100">
                                <i class="fas fa-credit-card text-2xl text-orange-600"></i>
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
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Amount</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Commission</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
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
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            ₹<?php echo number_format($payment['commission']); ?>
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
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <?php echo $payment['paid_date'] ? date('M j, Y', strtotime($payment['paid_date'])) : '-'; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        <?php elseif ($active_tab == 'activity'): ?>
            <!-- Activity Tab -->
            <div class="bg-white rounded-xl shadow-md p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-6">Recent Activity</h3>
                <div class="space-y-4">
                    <?php foreach ($activities as $activity): ?>
                        <div class="flex items-start space-x-3 p-3 hover:bg-gray-50 rounded-lg">
                            <div class="w-3 h-3 rounded-full mt-1 
                                <?php 
                                switch($activity['type']) {
                                    case 'booking': echo 'bg-blue-500'; break;
                                    case 'payment': echo 'bg-green-500'; break;
                                    case 'property': echo 'bg-orange-500'; break;
                                    default: echo 'bg-purple-500'; break;
                                }
                                ?>"></div>
                            <div class="flex-1">
                                <p class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($activity['description']); ?></p>
                                <div class="flex items-center space-x-4 text-xs text-gray-500 mt-1">
                                    <span><?php echo htmlspecialchars($activity['user_name']); ?></span>
                                    <span><?php echo date('M j, Y', strtotime($activity['created_at'])); ?></span>
                                    <span><?php echo date('g:i A', strtotime($activity['created_at'])); ?></span>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</main>

<?php include 'includes/footer.php'; ?>