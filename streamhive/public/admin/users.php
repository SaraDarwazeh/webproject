<?php
$page_title = 'Manage Users';
include '../../app/includes/header.php';
include '../../app/includes/navbar.php';
?>

<main class="container-fluid py-5">
    <div class="container-fluid">
        <!-- Page Header -->
        <div class="d-flex justify-content-between align-items-center mb-5">
            <div>
                <h1>👥 User Management</h1>
                <p class="text-muted">View and manage user accounts</p>
            </div>
            <a href="../admin/index.php" class="btn btn-outline-secondary">← Dashboard</a>
        </div>

        <!-- Users Table -->
        <div class="card">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="bg-darker">
                        <tr>
                            <th>ID</th>
                            <th>Username</th>
                            <th>Email</th>
                            <th>Joined</th>
                            <th>Status</th>
                            <th>Watchlist</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>#001</td>
                            <td><strong>JohnDoe</strong></td>
                            <td>john@example.com</td>
                            <td><small class="text-muted">2024-01-15</small></td>
                            <td><span class="badge bg-success">Active</span></td>
                            <td>3 movies</td>
                            <td>
                                <button class="btn btn-sm btn-outline-primary me-1">View</button>
                                <button class="btn btn-sm btn-outline-danger">Suspend</button>
                            </td>
                        </tr>
                        <tr>
                            <td>#002</td>
                            <td><strong>JaneSmith</strong></td>
                            <td>jane@example.com</td>
                            <td><small class="text-muted">2024-01-20</small></td>
                            <td><span class="badge bg-success">Active</span></td>
                            <td>5 movies</td>
                            <td>
                                <button class="btn btn-sm btn-outline-primary me-1">View</button>
                                <button class="btn btn-sm btn-outline-danger">Suspend</button>
                            </td>
                        </tr>
                        <tr>
                            <td>#003</td>
                            <td><strong>BobJones</strong></td>
                            <td>bob@example.com</td>
                            <td><small class="text-muted">2024-02-01</small></td>
                            <td><span class="badge bg-success">Active</span></td>
                            <td>2 movies</td>
                            <td>
                                <button class="btn btn-sm btn-outline-primary me-1">View</button>
                                <button class="btn btn-sm btn-outline-danger">Suspend</button>
                            </td>
                        </tr>
                        <tr>
                            <td>#004</td>
                            <td><strong>AliceWonder</strong></td>
                            <td>alice@example.com</td>
                            <td><small class="text-muted">2024-02-05</small></td>
                            <td><span class="badge bg-success">Active</span></td>
                            <td>8 movies</td>
                            <td>
                                <button class="btn btn-sm btn-outline-primary me-1">View</button>
                                <button class="btn btn-sm btn-outline-danger">Suspend</button>
                            </td>
                        </tr>
                        <tr>
                            <td>#005</td>
                            <td><strong>CharlesB</strong></td>
                            <td>charles@example.com</td>
                            <td><small class="text-muted">2024-02-10</small></td>
                            <td><span class="badge bg-secondary">Inactive</span></td>
                            <td>1 movie</td>
                            <td>
                                <button class="btn btn-sm btn-outline-primary me-1">View</button>
                                <button class="btn btn-sm btn-outline-success">Activate</button>
                            </td>
                        </tr>
                        <tr>
                            <td>#006</td>
                            <td><strong>DianaLee</strong></td>
                            <td>diana@example.com</td>
                            <td><small class="text-muted">2024-02-12</small></td>
                            <td><span class="badge bg-danger">Suspended</span></td>
                            <td>0 movies</td>
                            <td>
                                <button class="btn btn-sm btn-outline-primary me-1">View</button>
                                <button class="btn btn-sm btn-outline-success">Restore</button>
                            </td>
                        </tr>
                        <tr>
                            <td>#007</td>
                            <td><strong>EthanWest</strong></td>
                            <td>ethan@example.com</td>
                            <td><small class="text-muted">2024-02-14</small></td>
                            <td><span class="badge bg-success">Active</span></td>
                            <td>4 movies</td>
                            <td>
                                <button class="btn btn-sm btn-outline-primary me-1">View</button>
                                <button class="btn btn-sm btn-outline-danger">Suspend</button>
                            </td>
                        </tr>
                        <tr>
                            <td>#008</td>
                            <td><strong>FionaGreen</strong></td>
                            <td>fiona@example.com</td>
                            <td><small class="text-muted">2024-02-18</small></td>
                            <td><span class="badge bg-success">Active</span></td>
                            <td>6 movies</td>
                            <td>
                                <button class="btn btn-sm btn-outline-primary me-1">View</button>
                                <button class="btn btn-sm btn-outline-danger">Suspend</button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Pagination -->
        <nav aria-label="User pagination" class="mt-4">
            <ul class="pagination justify-content-center">
                <li class="page-item disabled">
                    <a class="page-link" href="#" tabindex="-1">Previous</a>
                </li>
                <li class="page-item active"><a class="page-link" href="#">1</a></li>
                <li class="page-item"><a class="page-link" href="#">2</a></li>
                <li class="page-item"><a class="page-link" href="#">3</a></li>
                <li class="page-item">
                    <a class="page-link" href="#">Next</a>
                </li>
            </ul>
        </nav>
    </div>
</main>

<?php include '../../app/includes/footer.php'; ?>
