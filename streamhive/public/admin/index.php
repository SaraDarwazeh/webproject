<?php
$page_title = 'Admin Dashboard';
include '../../app/includes/header.php';
include '../../app/includes/navbar.php';
?>

<main class="container-fluid py-5">
    <div class="container">
        <!-- Admin Header -->
        <div class="d-flex justify-content-between align-items-center mb-5">
            <div>
                <h1>📊 Admin Dashboard</h1>
                <p class="text-muted">System overview and statistics</p>
            </div>
            <a href="../index.php" class="btn btn-outline-primary">← Main Site</a>
        </div>

        <!-- KPI Cards -->
        <div class="row mb-5">
            <div class="col-md-3 mb-3">
                <div class="card border-primary">
                    <div class="card-body">
                        <p class="text-muted small fw-600 mb-2">TOTAL MOVIES</p>
                        <h2 class="text-primary mb-2">12</h2>
                        <small class="text-muted">In database</small>
                    </div>
                </div>
            </div>

            <div class="col-md-3 mb-3">
                <div class="card border-primary">
                    <div class="card-body">
                        <p class="text-muted small fw-600 mb-2">TOTAL USERS</p>
                        <h2 class="text-primary mb-2">142</h2>
                        <small class="text-muted">Registered accounts</small>
                    </div>
                </div>
            </div>

            <div class="col-md-3 mb-3">
                <div class="card border-primary">
                    <div class="card-body">
                        <p class="text-muted small fw-600 mb-2">TOTAL RATINGS</p>
                        <h2 class="text-primary mb-2">543</h2>
                        <small class="text-muted">This month</small>
                    </div>
                </div>
            </div>

            <div class="col-md-3 mb-3">
                <div class="card border-primary">
                    <div class="card-body">
                        <p class="text-muted small fw-600 mb-2">ACTIVE SESSIONS</p>
                        <h2 class="text-primary mb-2">28</h2>
                        <small class="text-muted">Right now</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="row mb-5">
            <div class="col-md-6 mb-3">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title mb-3">📽️ Movie Management</h5>
                        <p class="card-text text-muted small">Manage the movie catalog</p>
                        <a href="../admin/movies.php" class="btn btn-primary">Go to Movies</a>
                    </div>
                </div>
            </div>

            <div class="col-md-6 mb-3">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title mb-3">👥 User Management</h5>
                        <p class="card-text text-muted small">Manage user accounts</p>
                        <a href="../admin/users.php" class="btn btn-primary">Go to Users</a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Activity -->
        <h3 class="mb-3">📋 Recent Activity</h3>
        <div class="card">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="bg-darker">
                        <tr>
                            <th>Time</th>
                            <th>User</th>
                            <th>Action</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><small class="text-muted">2024-02-24 14:32</small></td>
                            <td>john@example.com</td>
                            <td>Added movie to list</td>
                            <td><span class="badge bg-success">Success</span></td>
                        </tr>
                        <tr>
                            <td><small class="text-muted">2024-02-24 14:15</small></td>
                            <td>jane@example.com</td>
                            <td>Rated "Cyber Dawn"</td>
                            <td><span class="badge bg-success">Success</span></td>
                        </tr>
                        <tr>
                            <td><small class="text-muted">2024-02-24 13:48</small></td>
                            <td>bob@example.com</td>
                            <td>User registration</td>
                            <td><span class="badge bg-success">Success</span></td>
                        </tr>
                        <tr>
                            <td><small class="text-muted">2024-02-24 13:20</small></td>
                            <td>admin@example.com</td>
                            <td>Database backup</td>
                            <td><span class="badge bg-info">Pending</span></td>
                        </tr>
                        <tr>
                            <td><small class="text-muted">2024-02-24 12:45</small></td>
                            <td>alice@example.com</td>
                            <td>Search query</td>
                            <td><span class="badge bg-success">Success</span></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</main>

<?php include '../../app/includes/footer.php'; ?>
