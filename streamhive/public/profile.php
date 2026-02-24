<?php
$page_title = 'My Profile';
include '../app/includes/header.php';
include '../app/includes/navbar.php';
?>

<main class="container-fluid" style="background-color: #0f0f0f; min-height: 100vh; padding: 40px 20px;">
    <div class="container">
        <!-- USER CARD AT TOP -->
        <div style="background: linear-gradient(135deg, rgba(142, 68, 173, 0.15), rgba(32, 201, 151, 0.08)); border: 1px solid #333; border-radius: 12px; padding: 40px; margin-bottom: 50px; text-align: center; max-width: 600px; margin-left: auto; margin-right: auto;">
            <div style="width: 100px; height: 100px; background: #20c997; border-radius: 50%; margin: 0 auto 25px; display: flex; align-items: center; justify-content: center; font-size: 2.5rem; color: #000; font-weight: 700;">J</div>
            <h2 style="margin-bottom: 5px; font-size: 1.8rem;">JohnDoe</h2>
            <p style="color: #999; margin-bottom: 5px; font-size: 1rem;">john@example.com</p>
            <p style="color: #666; font-size: 0.9rem; margin-bottom: 25px;">Member since January 2024</p>
            <div style="display: flex; gap: 12px; justify-content: center;">
                <button style="padding: 10px 24px; background: #20c997; color: #000; border: none; border-radius: 6px; font-weight: 600; cursor: pointer;">Edit Profile</button>
                <button style="padding: 10px 24px; background: #e74c3c; color: white; border: none; border-radius: 6px; font-weight: 600; cursor: pointer;">Logout</button>
            </div>
        </div>

        <!-- STATISTICS SECTION -->
        <h2 style="font-size: 1.8rem; margin-bottom: 30px; text-align: center;">Your Activity</h2>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 20px; margin-bottom: 50px;">
            <!-- STAT CARD 1 -->
            <div style="background: linear-gradient(135deg, rgba(32, 201, 151, 0.15), transparent); border: 1px solid rgba(32, 201, 151, 0.3); border-radius: 10px; padding: 30px; text-align: center;">
                <p style="color: #999; font-size: 0.9rem; margin-bottom: 15px; font-weight: 600; text-transform: uppercase;">Watchlist Items</p>
                <div id="stat-watchlist" style="font-size: 3rem; font-weight: 700; color: #20c997; margin-bottom: 10px;">0</div>
                <p style="color: #666; font-size: 0.9rem; margin: 0;">Movies saved for later</p>
            </div>

            <!-- STAT CARD 2 -->
            <div style="background: linear-gradient(135deg, rgba(142, 68, 173, 0.15), transparent); border: 1px solid rgba(142, 68, 173, 0.3); border-radius: 10px; padding: 30px; text-align: center;">
                <p style="color: #999; font-size: 0.9rem; margin-bottom: 15px; font-weight: 600; text-transform: uppercase;">Movies Rated</p>
                <div id="stat-rated" style="font-size: 3rem; font-weight: 700; color: #8e44ad; margin-bottom: 10px;">0</div>
                <p style="color: #666; font-size: 0.9rem; margin: 0;">Total ratings submitted</p>
            </div>

            <!-- STAT CARD 3 -->
            <div style="background: linear-gradient(135deg, rgba(52, 152, 219, 0.15), transparent); border: 1px solid rgba(52, 152, 219, 0.3); border-radius: 10px; padding: 30px; text-align: center;">
                <p style="color: #999; font-size: 0.9rem; margin-bottom: 15px; font-weight: 600; text-transform: uppercase;">Average Rating</p>
                <div id="stat-avg-rating" style="font-size: 3rem; font-weight: 700; color: #3498db; margin-bottom: 10px;">4.2</div>
                <p style="color: #666; font-size: 0.9rem; margin: 0;">Out of 5 stars</p>
            </div>

            <!-- STAT CARD 4 -->
            <div style="background: linear-gradient(135deg, rgba(46, 204, 113, 0.15), transparent); border: 1px solid rgba(46, 204, 113, 0.3); border-radius: 10px; padding: 30px; text-align: center;">
                <p style="color: #999; font-size: 0.9rem; margin-bottom: 15px; font-weight: 600; text-transform: uppercase;">Account Type</p>
                <div style="font-size: 3rem; font-weight: 700; color: #2ecc71; margin-bottom: 10px;">Premium</div>
                <p style="color: #666; font-size: 0.9rem; margin: 0;">Full library access</p>
            </div>
        </div>

        <!-- PREFERENCES SECTION -->
        <h2 style="font-size: 1.8rem; margin-bottom: 25px;">Preferences</h2>
        <div style="background: #1a1a1a; border: 1px solid #333; border-radius: 10px; padding: 30px; max-width: 600px;">
            <div style="margin-bottom: 25px;">
                <label style="display: block; color: #ccc; font-weight: 600; margin-bottom: 10px; font-size: 0.95rem;">Favorite Genre</label>
                <select style="width: 100%; padding: 10px 15px; background-color: #0f0f0f; border: 1px solid #333; border-radius: 6px; color: white;">
                    <option>Sci-Fi</option>
                    <option>Drama</option>
                    <option>Adventure</option>
                    <option>Thriller</option>
                    <option>Fantasy</option>
                </select>
            </div>
            <div style="margin-bottom: 25px;">
                <label style="display: block; color: #ccc; font-weight: 600; margin-bottom: 15px; font-size: 0.95rem;">Notifications</label>
                <div style="display: flex; align-items: center; margin-bottom: 12px;">
                    <input type="checkbox" id="notify-new" checked style="margin-right: 10px; width: 18px; height: 18px; cursor: pointer;">
                    <label for="notify-new" style="color: #bbb; cursor: pointer; font-size: 0.95rem;">New releases this week</label>
                </div>
                <div style="display: flex; align-items: center;">
                    <input type="checkbox" id="notify-recommendations" checked style="margin-right: 10px; width: 18px; height: 18px; cursor: pointer;">
                    <label for="notify-recommendations" style="color: #bbb; cursor: pointer; font-size: 0.95rem;">Personalized recommendations</label>
                </div>
            </div>
            <button style="padding: 12px 24px; background: #20c997; color: #000; border: none; border-radius: 6px; font-weight: 600; cursor: pointer; width: 100%;">Save Preferences</button>
        </div>
    </div>
</main>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        document.getElementById('stat-watchlist').textContent = myList.length;
        document.getElementById('stat-rated').textContent = Object.keys(ratings).length;

        const ratingValues = Object.values(ratings);
        if (ratingValues.length > 0) {
            const avgRating = (ratingValues.reduce((a, b) => a + b, 0) / ratingValues.length).toFixed(1);
            document.getElementById('stat-avg-rating').textContent = avgRating;
        } else {
            document.getElementById('stat-avg-rating').textContent = '—';
        }
    });
</script>
<?php include '../app/includes/footer.php'; ?>
