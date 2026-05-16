<?php
$page_title = 'Buy Points';
include '../app/includes/header.php';
include '../app/includes/navbar.php';

if (!isset($_SESSION['user_id'])) {
    echo '<main class="container py-5">
        <div class="page-empty-state mt-5">
            <i class="fas fa-lock text-primary"></i>
            <h3>Login Required</h3>
            <p>You need to be logged in to buy points.</p>
            <a href="login.php" class="btn btn-primary mt-3"><i class="fas fa-sign-in-alt me-2"></i>Sign In</a>
        </div>
    </main>';
    include '../app/includes/footer.php';
    exit;
}

require_once '../app/controllers/purchase_controller.php';
$purchaseCtrl = new PurchaseController();
$balance = $purchaseCtrl->getPointsBalance($_SESSION['user_id']);
?>

<main class="buy-points-page">
    <div class="container">
        <div class="page-header-row">
            <div>
                <h1><i class="fas fa-gem me-2"></i>Buy Points</h1>
                <p class="text-muted">Purchase points to unlock movies, episodes, ratings, and comments</p>
            </div>
            <div class="balance-display-lg">
                <span class="balance-label">Your Balance</span>
                <span class="balance-amount" id="current-balance"><?php echo $balance; ?></span>
                <span class="balance-unit">points</span>
            </div>
        </div>

        <!-- Pricing Info -->
        <div class="pricing-info-banner">
            <div class="pricing-info-item">
                <i class="fas fa-film"></i>
                <span>Movies cost <strong>20 points</strong></span>
            </div>
            <div class="pricing-info-item">
                <i class="fas fa-tv"></i>
                <span>Episodes cost <strong>5 points</strong></span>
            </div>
            <div class="pricing-info-item">
                <i class="fas fa-exchange-alt"></i>
                <span><strong>$1 = 10 points</strong></span>
            </div>
        </div>

        <!-- Point Packages -->
        <h2 class="section-title mt-5"><i class="fas fa-box-open me-2"></i>Choose a Package</h2>
        <div class="points-packages-grid">
            <div class="points-package-card" onclick="selectPackage(10, 100)">
                <div class="package-points">100</div>
                <div class="package-label">points</div>
                <div class="package-price">$10</div>
                <div class="package-note">Great for 5 movies</div>
            </div>
            <div class="points-package-card popular" onclick="selectPackage(25, 250)">
                <div class="package-popular-tag">Most Popular</div>
                <div class="package-points">250</div>
                <div class="package-label">points</div>
                <div class="package-price">$25</div>
                <div class="package-note">Great for 12 movies</div>
            </div>
            <div class="points-package-card" onclick="selectPackage(50, 500)">
                <div class="package-points">500</div>
                <div class="package-label">points</div>
                <div class="package-price">$50</div>
                <div class="package-note">Great for 25 movies</div>
            </div>
            <div class="points-package-card" onclick="selectPackage(100, 1000)">
                <div class="package-points">1000</div>
                <div class="package-label">points</div>
                <div class="package-price">$100</div>
                <div class="package-note">Best value</div>
            </div>
        </div>

        <!-- Payment Form -->
        <div class="payment-form-card mt-5" id="payment-section" style="display:none;">
            <h3><i class="fas fa-credit-card me-2"></i>Payment Details</h3>
            <p class="text-muted mb-4">Selected: <strong id="selected-package-label">—</strong></p>

            <form id="buy-points-form" onsubmit="return processPurchase(event)">
                <input type="hidden" id="dollar-amount" value="">

                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label">Cardholder Name</label>
                        <input type="text" class="form-control form-control-lg" id="card-name" placeholder="John Doe" required>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Card Number</label>
                        <input type="text" class="form-control form-control-lg" id="card-number" placeholder="1234 5678 9012 3456" maxlength="19" required oninput="formatCardNumber(this)">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Expiry Date</label>
                        <input type="text" class="form-control form-control-lg" id="card-expiry" placeholder="MM/YY" maxlength="5" required oninput="formatExpiry(this)">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">CVV</label>
                        <input type="text" class="form-control form-control-lg" id="card-cvv" placeholder="123" maxlength="4" required>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary btn-lg w-100 mt-4" id="pay-btn">
                    <i class="fas fa-lock me-2"></i>Pay <span id="pay-amount">$0</span>
                </button>
                <p class="text-muted text-center mt-2 small"><i class="fas fa-shield-alt me-1"></i>This is a simulated payment — no real charges</p>
            </form>
        </div>

        <!-- Or subscribe -->
        <div class="text-center mt-5 mb-5">
            <p class="text-muted">Want unlimited access to everything?</p>
            <a href="subscribe.php" class="btn btn-outline-primary btn-lg"><i class="fas fa-crown me-2"></i>View Subscription Plans</a>
        </div>
    </div>
</main>

<script>
function selectPackage(dollars, points) {
    // Highlight selected card
    document.querySelectorAll('.points-package-card').forEach(c => c.classList.remove('selected'));
    event.currentTarget.classList.add('selected');

    // Show payment section
    document.getElementById('payment-section').style.display = 'block';
    document.getElementById('dollar-amount').value = dollars;
    document.getElementById('selected-package-label').textContent = `${points} points for $${dollars}`;
    document.getElementById('pay-amount').textContent = `$${dollars}`;

    document.getElementById('payment-section').scrollIntoView({ behavior: 'smooth', block: 'center' });
}

function formatCardNumber(input) {
    let v = input.value.replace(/\D/g, '').substring(0, 16);
    input.value = v.replace(/(\d{4})(?=\d)/g, '$1 ');
}

function formatExpiry(input) {
    let v = input.value.replace(/\D/g, '').substring(0, 4);
    if (v.length >= 2) v = v.substring(0, 2) + '/' + v.substring(2);
    input.value = v;
}

async function processPurchase(e) {
    e.preventDefault();
    const btn = document.getElementById('pay-btn');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Processing...';

    try {
        const response = await fetch('/streamhive/app/api/purchase.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                action: 'buy_points',
                dollar_amount: parseFloat(document.getElementById('dollar-amount').value),
                card_number: document.getElementById('card-number').value,
                card_name: document.getElementById('card-name').value,
                card_expiry: document.getElementById('card-expiry').value,
                card_cvv: document.getElementById('card-cvv').value
            })
        });

        const data = await response.json();

        if (data.success) {
            showToast(data.message, 'success');
            document.getElementById('current-balance').textContent = data.balance;
            // Update navbar balance
            const navBal = document.getElementById('nav-points-balance');
            if (navBal) navBal.textContent = data.balance;

            // Reset form
            document.getElementById('buy-points-form').reset();
            document.getElementById('payment-section').style.display = 'none';
            document.querySelectorAll('.points-package-card').forEach(c => c.classList.remove('selected'));
        } else {
            showToast(data.message, 'danger');
        }
    } catch (err) {
        showToast('Payment failed. Please try again.', 'danger');
    }

    btn.disabled = false;
    btn.innerHTML = '<i class="fas fa-lock me-2"></i>Pay <span id="pay-amount">$' + document.getElementById('dollar-amount').value + '</span>';
}
</script>

<?php include '../app/includes/footer.php'; ?>
