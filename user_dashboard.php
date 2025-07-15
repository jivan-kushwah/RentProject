<?php
require_once 'config/database.php';
require_once 'config/session.php';

requireRole('user');
include 'includes/header.php';

$database = new Database();
$db = $database->getConnection();
$user_id = getUserId();

$active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'overview';

// Get user's bookings
$bookings_query = "SELECT b.*, p.title as property_title, p.location, p.city, p.images, p.bhk, p.area 
                   FROM bookings b 
                   JOIN properties p ON b.property_id = p.id 
                   WHERE b.user_id = ? 
                   ORDER BY b.created_at DESC";
$bookings_stmt = $db->prepare($bookings_query);
$bookings_stmt->execute([$user_id]);
$bookings = $bookings_stmt->fetchAll(PDO::FETCH_ASSOC);

// Get user's payments
$payments_query = "SELECT p.*, b.property_id, pr.title as property_title 
                   FROM payments p 
                   JOIN bookings b ON p.booking_id = b.id 
                   JOIN properties pr ON b.property_id = pr.id 
                   WHERE b.user_id = ? 
                   ORDER BY p.due_date DESC";
$payments_stmt = $db->prepare($payments_query);
$payments_stmt->execute([$user_id]);
$payments = $payments_stmt->fetchAll(PDO::FETCH_ASSOC);

// Calculate statistics
$active_booking = array_filter($bookings, function($b) { return $b['status'] == 'active'; });
$active_booking = count($active_booking) > 0 ? $active_booking[0] : null;
$total_paid = array_sum(array_map(function($p) { return $p['status'] == 'paid' ? $p['amount'] : 0; }, $payments));
$pending_payments = count(array_filter($payments, function($p) { return $p['status'] == 'pending'; }));

// Handle rent bill generation
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['generate_bill'])) {
    $payment_id = $_POST['payment_id'];
    // In a real application, you would generate a PDF bill here
    $success = "Rent bill generated and sent to your email!";
}
?>

<main class="min-h-screen bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">User Dashboard</h1>
                    <p class="text-lg text-gray-600 mt-1">Manage your rentals and payments</p>
                </div>
                <div class="text-right">
                    <p class="text-sm text-gray-500">Welcome back,</p>
                    <p class="text-lg font-semibold text-gray-900"><?php echo getUserName(); ?></p>
                </div>
            </div>
        </div>

        <?php if (isset($success)): ?>
            <div class="mb-6 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded alert-auto-hide">
                <?php echo htmlspecialchars($success); ?>
            </div>
        <?php endif; ?>

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
                        <span>My Bookings</span>
                    </a>
                    <a href="?tab=payments" 
                       class="<?php echo $active_tab == 'payments' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'; ?> flex items-center space-x-2 py-2 px-1 border-b-2 font-medium text-sm">
                        <i class="fas fa-credit-card"></i>
                        <span>Payments</span>
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
                                <p class="text-sm font-medium text-gray-600">Active Bookings</p>
                                <p class="text-3xl font-bold text-gray-900 mt-1"><?php echo count(array_filter($bookings, function($b) { return $b['status'] == 'active'; })); ?></p>
                            </div>
                            <div class="p-3 rounded-full bg-blue-100">
                                <i class="fas fa-home text-2xl text-blue-600"></i>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-xl shadow-md p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600">Total Bookings</p>
                                <p class="text-3xl font-bold text-gray-900 mt-1"><?php echo count($bookings); ?></p>
                            </div>
                            <div class="p-3 rounded-full bg-green-100">
                                <i class="fas fa-calendar text-2xl text-green-600"></i>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-xl shadow-md p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600">Total Paid</p>
                                <p class="text-3xl font-bold text-gray-900 mt-1">₹<?php echo number_format($total_paid); ?></p>
                            </div>
                            <div class="p-3 rounded-full bg-orange-100">
                                <i class="fas fa-credit-card text-2xl text-orange-600"></i>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-xl shadow-md p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600">Pending Payments</p>
                                <p class="text-3xl font-bold text-gray-900 mt-1"><?php echo $pending_payments; ?></p>
                            </div>
                            <div class="p-3 rounded-full bg-red-100">
                                <i class="fas fa-clock text-2xl text-red-600"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Current Booking -->
                <?php if ($active_booking): ?>
                    <div class="bg-white rounded-xl shadow-md p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Current Rental</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <div class="flex space-x-4">
                                    <?php 
                                    $images = explode(',', $active_booking['images']);
                                    $first_image = $images[0];
                                    ?>
                                    <img src="<?php echo htmlspecialchars($first_image); ?>" 
                                         alt="<?php echo htmlspecialchars($active_booking['property_title']); ?>" 
                                         class="w-20 h-20 object-cover rounded-lg">
                                    <div>
                                        <h4 class="font-medium text-gray-900"><?php echo htmlspecialchars($active_booking['property_title']); ?></h4>
                                        <p class="text-sm text-gray-500"><?php echo htmlspecialchars($active_booking['location'] . ', ' . $active_booking['city']); ?></p>
                                        <p class="text-sm text-gray-500"><?php echo $active_booking['bhk']; ?> BHK • <?php echo $active_booking['area']; ?> sq ft</p>
                                    </div>
                                </div>
                            </div>
                            <div class="space-y-2">
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Monthly Rent:</span>
                                    <span class="font-medium">₹<?php echo number_format($active_booking['monthly_rent']); ?></span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Lease Period:</span>
                                    <span class="font-medium">
                                        <?php echo date('M j, Y', strtotime($active_booking['start_date'])); ?> - <?php echo date('M j, Y', strtotime($active_booking['end_date'])); ?>
                                    </span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Status:</span>
                                    <span class="font-medium text-green-600">Active</span>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Upcoming Payments -->
                <div class="bg-white rounded-xl shadow-md p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Upcoming Payments</h3>
                    <div class="space-y-3">
                        <?php 
                        $pending_payments_list = array_filter($payments, function($p) { return $p['status'] == 'pending'; });
                        $pending_payments_list = array_slice($pending_payments_list, 0, 3);
                        ?>
                        <?php foreach ($pending_payments_list as $payment): ?>
                            <div class="flex items-center justify-between p-3 border border-gray-200 rounded-lg">
                                <div>
                                    <p class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($payment['month']); ?></p>
                                    <p class="text-xs text-gray-500">Due: <?php echo date('M j, Y', strtotime($payment['due_date'])); ?></p>
                                </div>
                                <div class="text-right">
                                    <p class="text-sm font-medium text-gray-900">₹<?php echo number_format($payment['amount']); ?></p>
                                    <form method="POST" class="inline">
                                        <input type="hidden" name="payment_id" value="<?php echo $payment['id']; ?>">
                                        <button type="submit" name="generate_bill" 
                                                class="text-xs text-blue-600 hover:text-blue-700">
                                            Generate Bill
                                        </button>
                                    </form>
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
                    <h3 class="text-lg font-semibold text-gray-900">My Bookings</h3>
                </div>
                <div class="divide-y divide-gray-200">
                    <?php foreach ($bookings as $booking): ?>
                        <div class="p-6">
                            <div class="flex items-start space-x-4">
                                <?php 
                                $images = explode(',', $booking['images']);
                                $first_image = $images[0];
                                ?>
                                <img src="<?php echo htmlspecialchars($first_image); ?>" 
                                     alt="<?php echo htmlspecialchars($booking['property_title']); ?>" 
                                     class="w-24 h-24 object-cover rounded-lg">
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-start justify-between">
                                        <div>
                                            <h4 class="text-lg font-medium text-gray-900"><?php echo htmlspecialchars($booking['property_title']); ?></h4>
                                            <p class="text-sm text-gray-500"><?php echo htmlspecialchars($booking['location'] . ', ' . $booking['city']); ?></p>
                                            <p class="text-sm text-gray-500"><?php echo $booking['bhk']; ?> BHK • <?php echo $booking['area']; ?> sq ft</p>
                                        </div>
                                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full 
                                            <?php 
                                            switch($booking['status']) {
                                                case 'active': echo 'bg-green-100 text-green-800'; break;
                                                case 'pending': echo 'bg-yellow-100 text-yellow-800'; break;
                                                case 'confirmed': echo 'bg-blue-100 text-blue-800'; break;
                                                default: echo 'bg-gray-100 text-gray-800'; break;
                                            }
                                            ?>">
                                            <?php echo ucfirst($booking['status']); ?>
                                        </span>
                                    </div>
                                    <div class="mt-4 grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                                        <div>
                                            <span class="text-gray-500">Monthly Rent:</span>
                                            <p class="font-medium">₹<?php echo number_format($booking['monthly_rent']); ?></p>
                                        </div>
                                        <div>
                                            <span class="text-gray-500">Security Deposit:</span>
                                            <p class="font-medium">₹<?php echo number_format($booking['security_deposit']); ?></p>
                                        </div>
                                        <div>
                                            <span class="text-gray-500">Lease Period:</span>
                                            <p class="font-medium">
                                                <?php echo date('M j, Y', strtotime($booking['start_date'])); ?> - <?php echo date('M j, Y', strtotime($booking['end_date'])); ?>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

        <?php elseif ($active_tab == 'payments'): ?>
            <!-- Payments Tab -->
            <div class="space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="bg-white rounded-xl shadow-md p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600">Total Paid</p>
                                <p class="text-3xl font-bold text-gray-900 mt-1">₹<?php echo number_format($total_paid); ?></p>
                            </div>
                            <div class="p-3 rounded-full bg-green-100">
                                <i class="fas fa-check-circle text-2xl text-green-600"></i>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-xl shadow-md p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600">Pending Payments</p>
                                <p class="text-3xl font-bold text-gray-900 mt-1"><?php echo $pending_payments; ?></p>
                            </div>
                            <div class="p-3 rounded-full bg-orange-100">
                                <i class="fas fa-clock text-2xl text-orange-600"></i>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-xl shadow-md p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600">Next Payment</p>
                                <?php 
                                $next_payment = array_filter($payments, function($p) { return $p['status'] == 'pending'; });
                                $next_payment = count($next_payment) > 0 ? array_values($next_payment)[0] : null;
                                ?>
                                <p class="text-3xl font-bold text-gray-900 mt-1">
                                    <?php echo $next_payment ? '₹' . number_format($next_payment['amount']) : 'None'; ?>
                                </p>
                            </div>
                            <div class="p-3 rounded-full bg-blue-100">
                                <i class="fas fa-credit-card text-2xl text-blue-600"></i>
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
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Due Date</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
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
                                            <?php echo date('M j, Y', strtotime($payment['due_date'])); ?>
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
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                                            <form method="POST" class="inline">
                                                <input type="hidden" name="payment_id" value="<?php echo $payment['id']; ?>">
                                                <button type="submit" name="generate_bill" 
                                                        class="text-blue-600 hover:text-blue-700 font-medium">
                                                    Generate Bill
                                                </button>
                                            </form>
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