<?php
require_once __DIR__ . '/includes/functions.php';
require_login();

$errors = [];
$categories = get_categories();
$title = '';
$description = '';
$price = '';
$categoryId = 0;
$itemType = 'Item';
$condition = 'Good';
$universityTarget = '';
$imageUrl = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $price = trim($_POST['price'] ?? '');
    $categoryId = (int)($_POST['category_id'] ?? 0);
    $itemType = trim($_POST['item_type'] ?? 'Item');
    $condition = trim($_POST['condition_label'] ?? 'Good');
    $universityTarget = trim($_POST['university_target'] ?? '');
    $imageUrl = trim($_POST['image_url'] ?? '');

    if (!verify_csrf()) {
        $errors[] = 'Invalid request token. Please try again.';
    }

    if ($title === '') {
        $errors[] = 'Title is required.';
    }

    if ($description === '') {
        $errors[] = 'Description is required.';
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

    $uploadedImagePath = null;

    if (!$errors) {
        list($ok, $uploadResult) = upload_listing_image($_FILES['image'] ?? []);
        if (!$ok) {
            $errors[] = $uploadResult;
        } else {
            $uploadedImagePath = $uploadResult;
        }
    }

    if (!$errors) {
        $stmt = $mysqli->prepare("
            INSERT INTO listings
            (user_id, category_id, title, description, price, condition_label, item_type, image_path, image_url, university_target)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $userId = (int)$_SESSION['user_id'];
        $priceValue = (float)$price;
        $stmt->bind_param(
            'iissdsssss',
            $userId,
            $categoryId,
            $title,
            $description,
            $priceValue,
            $condition,
            $itemType,
            $uploadedImagePath,
            $imageUrl,
            $universityTarget
        );

        if ($stmt->execute()) {
            $listingId = $stmt->insert_id;
            $stmt->close();
            set_flash('success', 'Listing created successfully.');
            redirect('listing.php?id=' . $listingId);
        }

        $stmt->close();
        $errors[] = 'Could not create listing. Please try again.';
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<section class="page-header">
    <div>
        <span class="eyebrow">Post a listing</span>
        <h1>Create a New Listing</h1>
        <p>Share books, notes, gadgets, or student services with the UniTrade CY community.</p>
    </div>
</section>

<div class="form-layout">
    <div>
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
                    <input id="title" type="text" name="title" value="<?= e($title); ?>" placeholder="e.g. Data Structures textbook bundle" required>
                </div>

                <div class="form-group">
                    <label for="price">Price (€)</label>
                    <input id="price" type="number" step="0.01" name="price" value="<?= e($price); ?>" placeholder="18.00" required>
                </div>
            </div>

            <div class="form-group">
                <label for="description">Description</label>
                <textarea id="description" name="description" rows="6" placeholder="Explain condition, what is included, and how students should use it." required><?= e($description); ?></textarea>
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
                    <input id="university_target" type="text" name="university_target" value="<?= e($universityTarget); ?>" placeholder="e.g. University of Cyprus">
                </div>

                <div class="form-group">
                    <label for="image_url">External Image URL (optional)</label>
                    <input id="image_url" type="url" name="image_url" value="<?= e($imageUrl); ?>" placeholder="https://...">
                </div>
            </div>

            <div class="form-group">
                <label for="image">Upload Image (optional)</label>
                <input id="image" type="file" name="image" accept=".jpg,.jpeg,.png,.webp">
                <small class="input-help">JPG, PNG, or WEBP • max 2MB</small>
            </div>

            <button type="submit" class="btn btn-primary">Publish Listing</button>
        </form>
    </div>

    <aside class="support-card">
        <div>
            <span class="eyebrow">Listing support</span>
            <h3>What makes a strong post?</h3>
        </div>
        <div class="support-list">
            <div class="support-item">
                <strong>Lead with clarity</strong>
                <p>Use a title that tells students exactly what they are opening.</p>
            </div>
            <div class="support-item">
                <strong>Add honest details</strong>
                <p>Mention condition, included materials, and whether pickup is flexible.</p>
            </div>
            <div class="support-item">
                <strong>Use a clean image</strong>
                <p>Well-lit photos help your listing look more trustworthy and easier to compare.</p>
            </div>
        </div>
        <div class="support-tip">
            Students usually respond faster to listings that explain condition, delivery area, and what problem the item solves.
        </div>
    </aside>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
