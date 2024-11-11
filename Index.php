<?php
// Start the output buffering
ob_start();

// Include the database connection
include 'db.php';

// Define pagination variables
$limit = 10;  // Rows per page
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Add item to the database
if (isset($_POST['add'])) {
    $name = $_POST['name'];
    $description = $_POST['description'];

    $stmt = $pdo->prepare("INSERT INTO items (name, description) VALUES (:name, :description)");
    $stmt->execute(['name' => $name, 'description' => $description]);

    // Redirect to avoid form resubmission
    header("Location: index.php");
    exit(); // Always use exit after a header redirect
}

// Edit item in the database
if (isset($_POST['edit'])) {
    $id = $_POST['id'];
    $name = $_POST['name'];
    $description = $_POST['description'];

    $stmt = $pdo->prepare("UPDATE items SET name = :name, description = :description WHERE id = :id");
    $stmt->execute(['name' => $name, 'description' => $description, 'id' => $id]);

    // Redirect to avoid form resubmission
    header("Location: index.php?page=$page");
    exit();
}

// Delete item from the database
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM items WHERE id = :id");
    $stmt->execute(['id' => $id]);

    // Redirect to the current page without delete parameter
    header("Location: index.php?page=$page");
    exit();
}

// Fetch items from the database
$stmt = $pdo->prepare("SELECT * FROM items ORDER BY created_at DESC LIMIT :limit OFFSET :offset");
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get total count for pagination
$totalStmt = $pdo->query("SELECT COUNT(*) FROM items");
$totalRows = $totalStmt->fetchColumn();
$totalPages = ceil($totalRows / $limit);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CRUD App</title>
    <style>
        /* Basic styles for layout */
        body { font-family: Arial, sans-serif; }
        .container { max-width: 800px; margin: auto; padding: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        table, th, td { border: 1px solid #ddd; padding: 8px; }
        th { background-color: #353336; }
        .pagination { margin-top: 10px; }
        .pagination button { padding: 5px 10px; margin-right: 5px; }
        .add-button { float: right; margin-bottom: 10px; }
    </style>
</head>
<body style="color: white; background-color: darkslategrey;"></body>
    <div class="container">
        <h1><strong>CRUD Application</strong></h1>
        <button class="add-button" onclick="showAddForm()">Add New</button>

        <!-- Add Form -->
        <form id="addForm" style="display:none;" method="post">
            <input type="text" name="name" placeholder="Name" required>
            <input type="text" name="description" placeholder="Description">
            <button type="submit" name="add">Add</button>
        </form>

        <!-- Edit Form -->
        <form id="editForm" style="display:none;" method="post">
            <input type="hidden" name="id" id="editId">
            <input type="text" name="name" id="editName" placeholder="Name" required>
            <input type="text" name="description" id="editDescription" placeholder="Description">
            <button type="submit" name="edit">Save Changes</button>
        </form>

        <!-- Table of items -->
        <table>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Description</th>
                <th>Actions</th>
            </tr>
            <?php foreach ($items as $item): ?>
            <tr>
                <td><?php echo htmlspecialchars($item['id']); ?></td>
                <td><?php echo htmlspecialchars($item['name']); ?></td>
                <td><?php echo htmlspecialchars($item['description']); ?></td>
                <td>
                    <button onclick="editItem(<?php echo $item['id']; ?>, '<?php echo htmlspecialchars($item['name']); ?>', '<?php echo htmlspecialchars($item['description']); ?>')">Edit</button>
                    <button onclick="deleteItem(<?php echo $item['id']; ?>)">Delete</button>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>

        <!-- Pagination -->
        <div class="pagination">
            <?php if ($page > 1): ?>
                <a href="?page=<?php echo $page - 1; ?>"><button>Previous</button></a>
            <?php endif; ?>
            <?php if ($page < $totalPages): ?>
                <a href="?page=<?php echo $page + 1; ?>"><button>Next</button></a>
            <?php endif; ?>
        </div>
    </div>

    <script>
        // Show the add form
        function showAddForm() {
            document.getElementById('addForm').style.display = 'block';
            document.getElementById('editForm').style.display = 'none';
        }

        // Populate and show the edit form with existing data
        function editItem(id, name, description) {
            document.getElementById('editId').value = id;
            document.getElementById('editName').value = name;
            document.getElementById('editDescription').value = description;
            document.getElementById('editForm').style.display = 'block';
            document.getElementById('addForm').style.display = 'none';
        }

        // Confirm deletion
        function deleteItem(id) {
            if (confirm('Are you sure you want to delete this item?')) {
                window.location.href = `?delete=${id}`;
            }
        }
    </script>
</body>
</html>
