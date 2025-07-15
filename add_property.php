<?php
// add_property.php

require_once 'config/database.php';
require_once 'config/session.php';

// Ensure user is logged in and has sufficient privileges
if (!isLoggedIn() || (getUserRole() !== 'owner' && getUserRole() !== 'admin')) {
    header("Location: login.php");
    exit();
}

include 'includes/header.php';

$database = new Database();
$db = $database->getConnection();

$success = '';
$error = '';

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
    $availabilities = isset($_POST['availabilities']) ? implode(',', $_POST['availabilities']) : '';
    

    // ---- Handle image uploads ----
    $uploadedImages = [];
    if (!empty($_FILES['images']['name'][0])) {
        $uploadDir = __DIR__ . '/uploads/';
        // Ensure upload dir exists
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

        foreach ($_FILES['images']['tmp_name'] as $i => $tmpName) {
            $origName = basename($_FILES['images']['name'][$i]);
            $cleanName = preg_replace('/[^a-zA-Z0-9.\-_]/', '', $origName);
            $targetFile = $uploadDir . uniqid() . '_' . $cleanName;

            $mime = mime_content_type($tmpName);
            if (!in_array($mime, ['image/jpeg', 'image/png', 'image/webp'])) continue;

            if (move_uploaded_file($tmpName, $targetFile)) {
                // Store relative path for database / display
                $uploadedImages[] = 'uploads/' . basename($targetFile);
            }
        }
    }

    // Default fallback image
    if (empty($uploadedImages)) {
        $uploadedImages[] = 'https://images.pexels.com/photos/1571460/pexels-photo-1571460.jpeg?auto=compress&cs=tinysrgb&w=800';
    }
    $images = implode(',', $uploadedImages);

    // Check required fields
    if (empty($title) || empty($description) || empty($location) || empty($city) ||
        empty($state) || empty($type) || empty($price) || empty($area)) {
        $error = 'Please fill in all required fields';
    } else {
        // Insert into DB
        $stmt = $db->prepare("
            INSERT INTO properties
              (title, description, location, city, state, type, bhk, price, area, amenities,only_for, images, owner_id)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        $params = [
            $title, $description, $location, $city, $state,
            $type, $bhk, $price, $area, $amenities, $availabilities, $images, getUserId()
        ];

        if ($stmt->execute($params)) {
            $propId = $db->lastInsertId();
            $log = $db->prepare("
                INSERT INTO activities (type, description, user_id, related_id)
                VALUES ('property', ?, ?, ?)
            ");
            $log->execute(["New property added: $title", getUserId(), $propId]);

            $success = 'Property added successfully!';
        } else {
            $error = 'Failed to add property. Please try again.';
        }
    }
}

// Data arrays for form
$states = [
    'Andhra Pradesh', 'Arunachal Pradesh', 'Assam', 'Bihar', 'Chhattisgarh',
    'Goa', 'Gujarat', 'Haryana', 'Himachal Pradesh', 'Jharkhand',
    'Karnataka', 'Kerala', 'Madhya Pradesh', 'Maharashtra', 'Manipur',
    'Meghalaya', 'Mizoram', 'Nagaland', 'Odisha', 'Punjab',
    'Rajasthan', 'Sikkim', 'Tamil Nadu', 'Telangana', 'Tripura',
    'Uttar Pradesh', 'Uttarakhand', 'West Bengal'
];

$property_types = ['apartment'=>'Apartment','house'=>'House','villa'=>'Villa','studio'=>'Studio','room'=>'Room'];
$common_amenities = ['Parking','Gym','Swimming Pool','Security','Lift','Garden',
                     'Club House','Children Play Area','Power Backup','Internet',
                     'Air Conditioning','Furnished','Balcony','Terrace'];

$availabilty_list = ['All','Boys','Girls', 'Family', 'Job Professionals', 'Bank Professional','Govt Job','Hindu Religion','Momadian Religion','All Religion','Jain Religion','Chirtian Religion'];

?>

<main class="min-h-screen bg-gray-50 py-8">
  <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
    <header class="mb-8">
      <h1 class="text-3xl font-bold text-gray-900 mb-2">Add New Property</h1>
      <p class="text-lg text-gray-600">List your property for rent</p>
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

    <form method="POST" enctype="multipart/form-data"
          class="bg-white rounded-xl shadow-md p-6 space-y-6">
      <!-- Basic Info -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Property Title *</label>
          <input name="title" type="text" required
                 value="<?= htmlspecialchars($_POST['title'] ?? '') ?>"
                 class="w-full px-3 py-2 border rounded focus:ring-blue-500" placeholder="Luxury 2BHK Apartment">
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Property Type *</label>
          <select name="type" required
                  class="w-full px-3 py-2 border rounded focus:ring-blue-500">
            <option value="">Select Type</option>
            <?php foreach ($property_types as $val => $lbl): ?>
              <option value="<?= $val ?>" <?= ($_POST['type'] ?? '') === $val ? 'selected' : '' ?>>
                <?= $lbl ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">BHK *</label>
          <select name="bhk" required class="w-full px-3 py-2 border rounded focus:ring-blue-500">
            <option value="">Select BHK</option>
            <?php for ($i=1;$i<=5;$i++): ?>
              <option value="<?= $i ?>" <?= ($_POST['bhk'] ?? '') == $i ? 'selected' : '' ?>>
                <?= $i ?> BHK
              </option>
            <?php endfor; ?>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Monthly Rent (₹) *</label>
          <input name="price" type="number" required min="1000"
                 value="<?= htmlspecialchars($_POST['price'] ?? '') ?>"
                 class="w-full px-3 py-2 border rounded focus:ring-blue-500" placeholder="25000">
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Area (sq ft) *</label>
          <input name="area" type="number" required min="100"
                 value="<?= htmlspecialchars($_POST['area'] ?? '') ?>"
                 class="w-full px-3 py-2 border rounded focus:ring-blue-500" placeholder="800">
        </div>
      </div>

      <!-- Location -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Location/Area *</label>
          <input name="location" type="text" required
                 value="<?= htmlspecialchars($_POST['location'] ?? '') ?>"
                 class="w-full px-3 py-2 border rounded focus:ring-blue-500" placeholder="Bandra West">
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">City *</label>
          <input name="city" type="text" required
                 value="<?= htmlspecialchars($_POST['city'] ?? '') ?>"
                 class="w-full px-3 py-2 border rounded focus:ring-blue-500" placeholder="Mumbai">
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">State *</label>
          <select name="state" required class="w-full px-3 py-2 border rounded focus:ring-blue-500">
            <option value="">Select State</option>
            <?php foreach ($states as $st): ?>
              <option value="<?= $st ?>" <?= ($_POST['state'] ?? '')=== $st ? 'selected' : '' ?>><?= $st ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>

      <!-- Description -->
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Description *</label>
        <textarea name="description" rows="4" required
                  class="w-full px-3 py-2 border rounded focus:ring-blue-500"
                  placeholder="Describe your property..."><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
      </div>

      <!-- Amenities -->
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">Amenities</label>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
          <?php foreach ($common_amenities as $amen): ?>
            <label class="flex items-center space-x-2">
              <input type="checkbox" name="amenities[]" value="<?= $amen ?>"
                     <?= in_array($amen, $_POST['amenities'] ?? []) ? 'checked' : '' ?>
                     class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
              <span class="text-sm text-gray-700"><?= $amen ?></span>
            </label>
          <?php endforeach; ?>
        </div>
      </div>


      <!-- Availability List For -->
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">Availabilities</label>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
          <?php foreach ($availabilty_list as $single_availability): ?>
            <label class="flex items-center space-x-2">
              <input type="checkbox" name="availabilities[]" value="<?= $single_availability ?>"
                     <?= in_array($single_availability, $_POST['availabilities'] ?? []) ? 'checked' : '' ?>
                     class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
              <span class="text-sm text-gray-700"><?= $single_availability ?></span>
            </label>
          <?php endforeach; ?>
        </div>
      </div>



      <!-- Images -->
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Upload Property Images</label>
        <input name="images[]" type="file" multiple accept="image/*"
               class="w-full px-3 py-2 border rounded focus:ring-blue-500">
        <p class="text-xs text-gray-500 mt-1">Upload multiple JPG/PNG/WebP images. Defaults to a sample image if none are uploaded.</p>
      </div>

      <!-- Submit -->
      <div class="flex space-x-4">
        <a href="properties.php" class="flex-1 px-6 py-3 border text-gray-700 rounded hover:bg-gray-50 text-center">
          Cancel
        </a>
        <button type="submit" class="flex-1 px-6 py-3 bg-blue-600 text-white rounded hover:bg-blue-700">
          Add Property
        </button>
      </div>
    </form>
  </div>
</main>

<?php include 'includes/footer.php'; ?>
