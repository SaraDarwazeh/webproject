<?php
$page_title = 'Manage Movies';
include '../../app/includes/header.php';
include '../../app/includes/navbar.php';
?>

<main class="container-fluid py-5">
    <div class="container-fluid">
        <!-- Page Header -->
        <div class="d-flex justify-content-between align-items-center mb-5">
            <div>
                <h1>🎬 Movie Management</h1>
                <p class="text-muted">Edit and manage movie catalog</p>
            </div>
            <div>
                <a href="../admin/index.php" class="btn btn-outline-secondary me-2">Dashboard</a>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addMovieModal">
                    <span class="me-2">+</span> Add Movie
                </button>
            </div>
        </div>

        <!-- Movies Table -->
        <div class="card">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="bg-darker">
                        <tr>
                            <th>ID</th>
                            <th>Title</th>
                            <th>Genre</th>
                            <th>Year</th>
                            <th>Duration</th>
                            <th>Rating</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>1</td>
                            <td><strong>Cyber Dawn</strong></td>
                            <td><span class="badge badge-primary">Sci-Fi</span></td>
                            <td>2024</td>
                            <td>125 min</td>
                            <td><span class="text-primary">★ 8.5</span></td>
                            <td>
                                <button class="btn btn-sm btn-outline-primary me-1" data-bs-toggle="modal" data-bs-target="#editMovieModal">Edit</button>
                                <button class="btn btn-sm btn-outline-danger">Delete</button>
                            </td>
                        </tr>
                        <tr>
                            <td>2</td>
                            <td><strong>Neon City</strong></td>
                            <td><span class="badge badge-primary">Sci-Fi</span></td>
                            <td>2024</td>
                            <td>110 min</td>
                            <td><span class="text-primary">★ 7.5</span></td>
                            <td>
                                <button class="btn btn-sm btn-outline-primary me-1" data-bs-toggle="modal" data-bs-target="#editMovieModal">Edit</button>
                                <button class="btn btn-sm btn-outline-danger">Delete</button>
                            </td>
                        </tr>
                        <tr>
                            <td>3</td>
                            <td><strong>Silent Shadows</strong></td>
                            <td><span class="badge badge-primary">Thriller</span></td>
                            <td>2024</td>
                            <td>115 min</td>
                            <td><span class="text-primary">★ 8.2</span></td>
                            <td>
                                <button class="btn btn-sm btn-outline-primary me-1" data-bs-toggle="modal" data-bs-target="#editMovieModal">Edit</button>
                                <button class="btn btn-sm btn-outline-danger">Delete</button>
                            </td>
                        </tr>
                        <tr>
                            <td>4</td>
                            <td><strong>Aurora Rising</strong></td>
                            <td><span class="badge badge-primary">Drama</span></td>
                            <td>2023</td>
                            <td>145 min</td>
                            <td><span class="text-primary">★ 9.1</span></td>
                            <td>
                                <button class="btn btn-sm btn-outline-primary me-1" data-bs-toggle="modal" data-bs-target="#editMovieModal">Edit</button>
                                <button class="btn btn-sm btn-outline-danger">Delete</button>
                            </td>
                        </tr>
                        <tr>
                            <td>5</td>
                            <td><strong>Forgotten Kingdom</strong></td>
                            <td><span class="badge badge-primary">Fantasy</span></td>
                            <td>2023</td>
                            <td>150 min</td>
                            <td><span class="text-primary">★ 8.7</span></td>
                            <td>
                                <button class="btn btn-sm btn-outline-primary me-1" data-bs-toggle="modal" data-bs-target="#editMovieModal">Edit</button>
                                <button class="btn btn-sm btn-outline-danger">Delete</button>
                            </td>
                        </tr>
                        <tr>
                            <td>6</td>
                            <td><strong>Ocean's Echo</strong></td>
                            <td><span class="badge badge-primary">Adventure</span></td>
                            <td>2023</td>
                            <td>138 min</td>
                            <td><span class="text-primary">★ 8.3</span></td>
                            <td>
                                <button class="btn btn-sm btn-outline-primary me-1" data-bs-toggle="modal" data-bs-target="#editMovieModal">Edit</button>
                                <button class="btn btn-sm btn-outline-danger">Delete</button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</main>

<!-- Add Movie Modal -->
<div class="modal fade" id="addMovieModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Movie</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Title</label>
                        <input type="text" class="form-control" placeholder="Movie title" required>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Genre</label>
                            <select class="form-select" required>
                                <option>Sci-Fi</option>
                                <option>Drama</option>
                                <option>Thriller</option>
                                <option>Adventure</option>
                                <option>Fantasy</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Year</label>
                            <input type="number" class="form-control" placeholder="2024" min="1900" max="2099" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Duration (minutes)</label>
                            <input type="number" class="form-control" placeholder="120" min="1" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Rating (1-10)</label>
                            <input type="number" class="form-control" placeholder="8.5" min="1" max="10" step="0.1" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" rows="3" placeholder="Movie description..."></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Poster URL</label>
                        <input type="text" class="form-control" placeholder="../assets/img/posters/p1.jpg">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Movie</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Movie Modal -->
<div class="modal fade" id="editMovieModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Movie</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Title</label>
                        <input type="text" class="form-control" placeholder="Movie title" required>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Genre</label>
                            <select class="form-select" required>
                                <option selected>Sci-Fi</option>
                                <option>Drama</option>
                                <option>Thriller</option>
                                <option>Adventure</option>
                                <option>Fantasy</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Year</label>
                            <input type="number" class="form-control" placeholder="2024" min="1900" max="2099" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" rows="3" placeholder="Movie description..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include '../../app/includes/footer.php'; ?>
