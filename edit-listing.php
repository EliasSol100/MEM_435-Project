<?php
require_once __DIR__ . '/includes/header.php';
require_login();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$listing = $id > 0 ? fetch_listing_by_id($id) : null;

if (!$listing || (int)$listing['user_id'] !== (int)$_SESSION['user_id']) {
    set_flash('error', 'You are not allowed to edit this listing.');
    redirect('profile.php');
}

$errors = [];
$categories = get_categories();
$title = $listing['title'];
$description = $listing['description'];
$price = $listing['price'];
$categoryId = (int)$listing['category_id'];
$itemType = $listing['item_type'];
$condition = $listing['condition_label'];
$universityTarget = $listing['university_target'];
$imageUrl = $listing['image_url'];
$status = $listing['status'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $price = trim($_POST['price'] ?? '');
    $categoryId = (int)($_POST['category_id'] ?? 0);
    $itemType = trim($_POST['item_type'] ?? 'Item');
    $condition = trim($_POST['condition_label'] ?? 'Good');
    $universityTarget = trim($_POST['university_target'] ?? '');
    $imageUrl = trim($_POST['image_url'] ?? '');
    $status = trim($_POST['status'] ?? 'Active');

    if (!verify_csrf()) {
        $errors[] = 'Invalid request token. Please try again.';
    }

    if ($title === '' || $description === '') {
        $errors[] = 'Title and description are required.';
    }

    if (!is_numeric($price) || (float)$price < 0) {
        $errors[] = 'Please enter a valid price.';
    }

    if ($categoryId <= 0) {
        $errors[] = 'Please choose a category.';
    }

    if (!in_array($itemType, ['Item', 'Notes', 'Service'], true)) {
        $errors[] = 'Invalid listing type selected.';
    }

    if (!in_array($condition, ['New', 'Like New', 'Good', 'Fair'], true)) {
        $errors[] = 'Invalid condition selected.';
    }

    if (!in_array($status, ['Active', 'Sold'], true)) {
        $errors[] = 'Invalid status selected.';
    }

    $imagePath = $listing['image_path'];

    if (!$errors && !empty($_FILES['image']['name'])) {
        list($ok, $uploadResult) = upload_listing_image($_FILES['image']);
        if (!$ok) {
            $errors[] = $uploadResult;
        } else {
            $imagePath = $uploadResult;
        }
    }

    if (!$errors) {
        $stmt = $mysqli->prepare("
            UPDATE listings
            SET category_id = ?, title = ?, description = ?, price = ?, condition_label = ?, item_type = ?, image_path = ?, image_url = ?, university_target = ?, status = ?
            WHERE id = ? AND user_id = ?
        ");
        $priceValue = (float)$price;
        $userId = (int)$_SESSION['user_id'];
        $stmt->bind_param(
            'issdssssssii',
            $categoryId,
            $title,
            $description,
            $priceValue,
            $condition,
            $itemType,
            $imagePath,
            $imageUrl,
            $universityTarget,
            $status,
            $id,
            $userId
        );

        if ($stmt->execute()) {
            $stmt->close();
            set_flash('success', 'Listing updated successfully.');
            redirect('listing.php?id=' . $id);
        }

        $stmt->close();
        $errors[] = 'Could not update listing. Please try again.';
    }
}
?>

<section class="page-header">
    <div>
        <span class="eyebrow">Manage listing</span>
        <h1>Edit Listing</h1>
        <p>Update your listing information and availability.</p>
    </div>
</section>

<?php if ($errors): ?>
    <div class="alert alert-error">
        <?php foreach ($errors as $error): ?>
            <div><?= e($error); ?></div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<form method="POST" enctype="multipart/form-data" class="form-card wide-card">
    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()); ?>">

    <div class="form-grid two-cols">
        <div class="form-group">
            <label for="title">Title</label>
            <input id="title" type="text" name="title" value="<?= e($title); ?>" required>
        </div>

        <div class="form-group">
            <label for="price">Price (€)</label>
            <input id="price" type="number" step="0.01" name="price" value="<?= e($price); ?>" required>
        </div>
    </div>

    <div class="form-group">
        <label for="description">Description</label>
        <textarea id="description" name="description" rows="6" required><?= e($description); ?></textarea>
    </div>

    <div class="form-grid three-cols">
        <div class="form-group">
            <label for="category_id">Category</label>
            <select id="category_id" name="category_id" required>
                <option value="0">Select category</option>
                <?php foreach ($categories as $category): ?>
                    <option value="<?= (int)$category['id']; ?>" <?= $categoryId === (int)$category['id'] ? 'selected' : ''; ?>>
                        <?= e($category['name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label for="item_type">Type</label>
            <select id="item_type" name="item_type">
                <option value="Item" <?= $itemType === 'Item' ? 'selected' : ''; ?>>Item</option>
                <option value="Notes" <?= $itemType === 'Notes' ? 'selected' : ''; ?>>Notes</option>
                <option value="Service" <?= $itemType === 'Service' ? 'selected' : ''; ?>>Service</option>
            </select>
        </div>

        <div class="form-group">
            <label for="condition_label">Condition</label>
            <select id="condition_label" name="condition_label">
                <option value="New" <?= $condition === 'New' ? 'selected' : ''; ?>>New</option>
                <option value="Like New" <?= $condition === 'Like New' ? 'selected' : ''; ?>>Like New</option>
                <option value="Good" <?= $condition === 'Good' ? 'selected' : ''; ?>>Good</option>
                <option value="Fair" <?= $condition === 'Fair' ? 'selected' : ''; ?>>Fair</option>
            </select>
        </div>
    </div>

    <div class="form-grid two-cols">
        <div class="form-group">
            <label for="university_target">Target University (optional)</label>
            <input id="university_target" type="text" name="university_target" value="<?= e($universityTarget); ?>">
        </div>

        <div class="form-group">
            <label for="image_url">External Image URL (optional)</label>
            <input id="image_url" type="url" name="image_url" value="<?= e($imageUrl); ?>">
        </div>
    </div>

    <div class="form-grid two-cols">
        <div class="form-group">
            <label for="status">Listing Status</label>
            <select id="status" name="status">
                <option value="Active" <?= $status === 'Active' ? 'selected' : ''; ?>>Active</option>
                <option value="Sold" <?= $status === 'Sold' ? 'selected' : ''; ?>>Sold</option>
            </select>
        </div>

        <div class="form-group">
            <label for="image">Replace Image (optional)</label>
            <input id="image" type="file" name="image" accept=".jpg,.jpeg,.png,.webp">
        </div>
    </div>

    <button type="submit" class="btn btn-primary">Save Changes</button>
</form>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
