<?php
$page_title = 'Subscribe';
include '../app/includes/header.php';
include '../app/includes/navbar.php';

if (!isset($_SESSION['user_id'])) {
    echo '<main class="container py-5">
        <div class="page-empty-state mt-5">
            <i class="fas fa-lock text-primary"></i>
            <h3>Login Required</h3>
            <p>You need to be logged in to subscribe.</p>
            <a href="login.php" class="btn btn-primary mt-3"><i class="fas fa-sign-in-alt me-2"></i>Sign In</a>
        </div>
    </main>';
    include '../app/includes/footer.php';
    exit;
}

require_once '../app/controllers/purchase_controller.php';
$purchaseCtrl = new PurchaseController();
$activeSub = $purchaseCtrl->getActiveSubscription($_SESSION['user_id']);
?>

<main class="subscribe-page">
    <div class="container">
        <div class="text-center mb-5">
            <h1><i class="fas fa-crown me-2" style="color: #f1c40f;"></i>StreamHive Premium</h1>
            <p class="text-muted lead">Unlimited access to all movies, episodes, ratings, and comments</p>
        </div>

        <?php if ($activeSub): ?>
        <div class="active-sub-banner">
            <i class="fas fa-check-circle"></i>
            <div>
                <strong>You have an active subscription!</strong>
                <p class="mb-0">Plan: <strong><?php echo ucfirst($activeSub['plan_type']); ?></strong> · Expires: <strong><?php echo date('M j, Y g:ia', strtotime($activeSub['expires_at'])); ?></strong>
                <?php if ($activeSub['auto_renew']): ?> · <span class="text-success"><i class="fas fa-sync-alt me-1"></i>Auto-renew ON</span><?php endif; ?>
                </p>
            </div>
        </div>
        <?php endif; ?>

        <!-- Plans Grid -->
        <div class="subscription-plans-grid">
            <!-- Day -->
            <div class="sub-plan-card">
                <div class="plan-icon"><i class="fas fa-clock"></i></div>
                <h3 class="plan-name">Day Pass</h3>
                <div class="plan-price"><span class="price-dollar">$</span>1</div>
                <p class="plan-duration">24 hours of access</p>
                <ul class="plan-features">
                    <li><i class="fas fa-check"></i> All movies & episodes</li>
                    <li><i class="fas fa-check"></i> Rate content</li>
                    <li><i class="fas fa-check"></i> Write comments</li>
                    <li><i class="fas fa-check"></i> Auto-renewal</li>
                </ul>
                <button class="btn btn-outline-primary btn-lg w-100" onclick="selectPlan('day', 1)">Choose Day Pass</button>
            </div>

            <!-- Week -->
            <div class="sub-plan-card">
                <div class="plan-icon"><i class="fas fa-calendar-week"></i></div>
                <h3 class="plan-name">Weekly</h3>
                <div class="plan-price"><span class="price-dollar">$</span>5</div>
                <p class="plan-duration">7 days of access</p>
                <ul class="plan-features">
                    <li><i class="fas fa-check"></i> All movies & episodes</li>
                    <li><i class="fas fa-check"></i> Rate content</li>
                    <li><i class="fas fa-check"></i> Write comments</li>
                    <li><i class="fas fa-check"></i> Auto-renewal</li>
                </ul>
                <button class="btn btn-outline-primary btn-lg w-100" onclick="selectPlan('week', 5)">Choose Weekly</button>
            </div>

            <!-- Month (Popular) -->
            <div class="sub-plan-card plan-popular">
                <div class="plan-popular-tag">Best Value</div>
                <div class="plan-icon"><i class="fas fa-calendar-alt"></i></div>
                <h3 class="plan-name">Monthly</h3>
                <div class="plan-price"><span class="price-dollar">$</span>10</div>
                <p class="plan-duration">30 days of access</p>
                <ul class="plan-features">
                    <li><i class="fas fa-check"></i> All movies & episodes</li>
                    <li><i class="fas fa-check"></i> Rate content</li>
                    <li><i class="fas fa-check"></i> Write comments</li>
                    <li><i class="fas fa-check"></i> Auto-renewal</li>
                    <li><i class="fas fa-star text-warning"></i> Best value</li>
                </ul>
                <button class="btn btn-primary btn-lg w-100" onclick="selectPlan('month', 10)">Choose Monthly</button>
            </div>

            <!-- Year -->
            <div class="sub-plan-card">
                <div class="plan-icon"><i class="fas fa-calendar"></i></div>
                <h3 class="plan-name">Annual</h3>
                <div class="plan-price"><span class="price-dollar">$</span>70</div>
                <p class="plan-duration">365 days of access</p>
                <ul class="plan-features">
                    <li><i class="fas fa-check"></i> All movies & episodes</li>
                    <li><i class="fas fa-check"></i> Rate content</li>
                    <li><i class="fas fa-check"></i> Write comments</li>
                    <li><i class="fas fa-check"></i> Auto-renewal</li>
                    <li><i class="fas fa-gift text-success"></i> Save $50/year</li>
                </ul>
                <button class="btn btn-outline-primary btn-lg w-100" onclick="selectPlan('year', 70)">Choose Annual</button>
            </div>
        </div>

        <!-- Payment Form (hidden until plan selected) -->
        <div class="payment-form-card mt-5" id="sub-payment-section" style="display:none;">
            <h3><i class="fas fa-credit-card me-2"></i>Payment Details</h3>
            <p class="text-muted mb-4">Selected plan: <strong id="selected-plan-label">—</strong></p>

            <form id="subscribe-form" onsubmit="return processSubscription(event)">
                <input type="hidden" id="plan-type" value="">
                <input type="hidden" id="plan-price" value="">

                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label">Cardholder Name</label>
                        <input type="text" class="form-control form-control-lg" id="sub-card-name" placeholder="John Doe" required>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Card Number</label>
                        <input type="text" class="form-control form-control-lg" id="sub-card-number" placeholder="1234 5678 9012 3456" maxlength="19" required oninput="formatCardNumber(this)">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Expiry Date</label>
                        <input type="text" class="form-control form-control-lg" id="sub-card-expiry" placeholder="MM/YY" maxlength="5" required oninput="formatExpiry(this)">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">CVV</label>
                        <input type="text" class="form-control form-control-lg" id="sub-card-cvv" placeholder="123" maxlength="4" required>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary btn-lg w-100 mt-4" id="sub-pay-btn">
                    <i class="fas fa-crown me-2"></i>Subscribe for <span id="sub-pay-amount">$0</span>
                </button>
                <p class="text-muted text-center mt-2 small"><i class="fas fa-shield-alt me-1"></i>Simulated payment — no real charges. Auto-renews at end of period.</p>
            </form>
        </div>

        <!-- Or buy points -->
        <div class="text-center mt-5 mb-5">
            <p class="text-muted">Prefer to pay per movie?</p>
            <a href="buy_points.php" class="btn btn-outline-primary btn-lg"><i class="fas fa-gem me-2"></i>Buy Points Instead</a>
        </div>
    </div>
</main>

<script>
function selectPlan(type, price) {
    document.getElementById('sub-payment-section').style.display = 'block';
    document.getElementById('plan-type').value = type;
    document.getElementById('plan-price').value = price;
    document.getElementById('selected-plan-label').textContent = type.charAt(0).toUpperCase() + type.slice(1) + ' — $' + price;
    document.getElementById('sub-pay-amount').textContent = '$' + price;

    // Highlight selected card
    document.querySelectorAll('.sub-plan-card').forEach(c => c.classList.remove('selected'));
    event.currentTarget.closest('.sub-plan-card').classList.add('selected');

    document.getElementById('sub-payment-section').scrollIntoView({ behavior: 'smooth', block: 'center' });
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

async function processSubscription(e) {
    e.preventDefault();
    const btn = document.getElementById('sub-pay-btn');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Processing...';

    try {
        const response = await fetch('/streamhive/app/api/purchase.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                action: 'subscribe',
                plan_type: document.getElementById('plan-type').value,
                card_number: document.getElementById('sub-card-number').value,
                card_name: document.getElementById('sub-card-name').value,
                card_expiry: document.getElementById('sub-card-expiry').value,
                card_cvv: document.getElementById('sub-card-cvv').value
            })
        });

        const data = await response.json();

        if (data.success) {
            showToast(data.message, 'success');
            // Reload page to show active subscription
            setTimeout(() => window.location.reload(), 1500);
        } else {
            showToast(data.message, 'danger');
        }
    } catch (err) {
        showToast('Subscription failed. Please try again.', 'danger');
    }

    btn.disabled = false;
    const price = document.getElementById('plan-price').value;
    btn.innerHTML = '<i class="fas fa-crown me-2"></i>Subscribe for <span id="sub-pay-amount">$' + price + '</span>';
}
</script>

<?php include '../app/includes/footer.php'; ?>
