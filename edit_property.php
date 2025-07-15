<?php
require_once 'config/database.php';
require_once 'config/session.php';

if (!isLoggedIn() || (getUserRole() !== 'owner' && getUserRole() !== 'admin')) {
    header("Location: login.php");
    exit();
}

include 'includes/header.php';

$database = new Database();
$db = $database->getConnection();

$propertyId = $_GET['id'] ?? null;
$success = '';
$error = '';

if (!$propertyId || !is_numeric($propertyId)) {
    echo "<p>Invalid Property ID.</p>";
    exit();
}

// Fetch existing property details
$stmt = $db->prepare("SELECT * FROM properties WHERE id = ? LIMIT 1");
$stmt->execute([$propertyId]);
$property = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$property) {
    echo "<p>Property not found.</p>";
    exit();
}

// Process update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title        = trim($_POST['title']);
    $description  = trim($_POST['description']);
    $location     = trim($_POST['location']);
    $city         = trim($_POST['city']);
    $state        = $_POST['state'];
    $type         = $_POST['type'];
    $bhk          = (int)$_POST['bhk'];
    $price        = (float)$_POST['price'];
    $area         = (int)$_POST['area'];
    $amenities    = isset($_POST['amenities']) ? implode(',', $_POST['amenities']) : '';

    // ---- Handle image uploads ----
    $uploadedImages = [];
    if (!empty($_FILES['images']['name'][0])) {
        $uploadDir = __DIR__ . '/uploads/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

        foreach ($_FILES['images']['tmp_name'] as $i => $tmpName) {
            $origName = basename($_FILES['images']['name'][$i]);
            $cleanName = preg_replace('/[^a-zA-Z0-9.\-_]/', '', $origName);
            $targetFile = $uploadDir . uniqid() . '_' . $cleanName;

            $mime = mime_content_type($tmpName);
            if (!in_array($mime, ['image/jpeg', 'image/png', 'image/webp'])) continue;

            if (move_uploaded_file($tmpName, $targetFile)) {
                $uploadedImages[] = 'uploads/' . basename($targetFile);
            }
        }
    }

    $images = $uploadedImages ? implode(',', $uploadedImages) : $property['images'];

    // Validate fields
    if (empty($title) || empty($description) || empty($location) || empty($city) ||
        empty($state) || empty($type) || empty($price) || empty($area)) {
        $error = 'Please fill in all required fields';
    } else {
        // Update database
        $stmt = $db->prepare("
            UPDATE properties
            SET title = ?, description = ?, location = ?, city = ?, state = ?, type = ?, bhk = ?, price = ?, area = ?, amenities = ?, images = ?
            WHERE id = ? AND owner_id = ?
        ");
        $params = [$title, $description, $location, $city, $state, $type, $bhk, $price, $area, $amenities, $images, $propertyId, getUserId()];

        if ($stmt->execute($params)) {
            $success = 'Property updated successfully!';
        } else {
            $error = 'Failed to update property. Please try again.';
        }
    }
}

// Helpers
$states = ['Maharashtra', 'Karnataka', 'Delhi', 'Haryana', 'Tamil Nadu', 'Gujarat', 'Rajasthan', 'West Bengal', 'Uttar Pradesh', 'Kerala'];
$property_types = ['apartment'=>'Apartment','house'=>'House','villa'=>'Villa','studio'=>'Studio','room'=>'Room'];
$common_amenities = ['Parking','Gym','Swimming Pool','Security','Lift','Garden','Club House','Children Play Area','Power Backup','Internet','Air Conditioning','Furnished','Balcony','Terrace'];
$currentAmenities = explode(',', $property['amenities']);
?>

<main class="min-h-screen bg-gray-50 py-8">
  <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
    <header class="mb-8">
      <h1 class="text-3xl font-bold text-gray-900 mb-2">Edit Property</h1>
      <p class="text-lg text-gray-600">Update property details</p>
    </header>

    <?php if ($success): ?>
      <div class="mb-6 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded alert-auto-hide">
        <?= htmlspecialchars($success) ?>
      </div>
    <?php endif; ?>
    <?php if ($error): ?>
      <div class="mb-6 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded alert-auto-hide">
        <?= htmlspecialchars($error) ?>
      </div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data" class="bg-white rounded-xl shadow-md p-6 space-y-6">
      <!-- Reuse the exact same form fields as add_property.php -->
      <?php
        $_POST = array_merge($property, $_POST);
        $_POST['amenities'] = $currentAmenities;
        include 'property_form_fields.php'; // move the repeated form markup here if you want to reuse
      ?>

      <div class="flex space-x-4">
        <a href="properties.php" class="flex-1 px-6 py-3 border text-gray-700 rounded hover:bg-gray-50 text-center">
          Cancel
        </a>
        <button type="submit" class="flex-1 px-6 py-3 bg-blue-600 text-white rounded hover:bg-blue-700">
          Update Property
        </button>
      </div>
    </form>
  </div>
</main>

<?php include 'includes/footer.php'; ?>
