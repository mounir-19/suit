<?php
// Get all users
$users_query = "SELECT * FROM users WHERE is_admin = 0 ORDER BY created_at DESC";
$users_result = $mysqli->query($users_query);
$users = $users_result->fetch_all(MYSQLI_ASSOC);
?>

<div class="row mb-4">
    <div class="col-12">
        <h2><i class="fas fa-users"></i> Users Management</h2>
        <p class="text-muted">Manage all registered users in your store.</p>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5><i class="fas fa-list"></i> All Users</h5>
                <span class="badge bg-primary"><?= count($users) ?> Total Users</span>
            </div>
            <div class="card-body">
                <?php if (empty($users)): ?>
                    <p class="text-muted">No users found.</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Registration Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($users as $user): ?>
                                    <tr>
                                        <td><?= $user['id'] ?></td>
                                        <td><?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?></td>
                                        <td><?= htmlspecialchars($user['email']) ?></td>
                                        <td><?= date('M j, Y', strtotime($user['created_at'])) ?></td>
                                        <td>
                                            <button class="btn btn-danger btn-sm btn-action" onclick="deleteItem('user', <?= $user['id'] ?>)">
                                                <i class="fas fa-trash"></i> Delete
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>