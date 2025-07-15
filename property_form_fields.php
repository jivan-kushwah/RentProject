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

      <!-- Images -->
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Upload Property Images</label>
        <input name="images[]" type="file" multiple accept="image/*"
               class="w-full px-3 py-2 border rounded focus:ring-blue-500">
        <p class="text-xs text-gray-500 mt-1">Upload multiple JPG/PNG/WebP images. Defaults to a sample image if none are uploaded.</p>
      </div>