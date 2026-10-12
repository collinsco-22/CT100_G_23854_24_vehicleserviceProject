<?php
// Server-side processing: validates input and recalculates the cost independently of JavaScript.

// Service prices (must match the amounts shown on service.html).
$servicePrices = [
    'Engine Oil Change'  => 3000,
    'Brake Inspection'   => 2000,
    'Wheel Alignment'    => 2500,
    'Computer Diagnosis' => 3500,
    'Full Service'       => 8000,
    'Tyre Balancing'     => 2000,
];
$allowedVehicleTypes = ['Saloon Car', 'SUV', 'Pickup', 'Van'];

// Only accept requests sent from the form.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: service.html');
    exit;
}

// Read and trim a POST value safely.
function postValue($key) {
    return isset($_POST[$key]) ? trim($_POST[$key]) : '';
}

$customerName = postValue('customer_name');
$phone        = postValue('phone');
$email        = postValue('email');
$regNumber    = postValue('reg_number');
$vehicleType  = postValue('vehicle_type');
$vehicleModel = postValue('vehicle_model');
$year         = (int) postValue('year');
$serviceDate  = postValue('service_date');
$selected     = isset($_POST['services']) && is_array($_POST['services']) ? $_POST['services'] : [];

// Keep only services that exist in our price list.
$validServices = array_values(array_filter($selected, function ($s) use ($servicePrices) {
    return array_key_exists($s, $servicePrices);
}));

// Server-side validation.
$errors = [];
if ($customerName === '') $errors[] = 'Customer name is required.';
if (!preg_match('/^\d{10}$/', $phone)) $errors[] = 'Phone number must be exactly 10 digits.';
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'A valid email address is required.';
if ($regNumber === '') $errors[] = 'Vehicle registration number is required.';
if (!in_array($vehicleType, $allowedVehicleTypes, true)) $errors[] = 'Invalid vehicle type.';
if ($vehicleModel === '') $errors[] = 'Vehicle model is required.';
if ($year < 1980 || $year > (int) date('Y')) $errors[] = 'Year of manufacture is not reasonable.';
if ($serviceDate === '') $errors[] = 'Preferred service date is required.';
if (count($validServices) === 0) $errors[] = 'At least one service must be selected.';

// Escape output to prevent HTML injection.
function e($text) {
    return htmlspecialchars((string) $text, ENT_QUOTES, 'UTF-8');
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>AutoCare Garage - Service Request</title>
    <link rel="stylesheet" href="css/style.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body>
    <header>
        <div class="brand"><i class="fa-solid fa-car-side"></i> AutoCare<span>.</span> Garage</div>
        <nav>
            <a href="index.html"><i class="fa-solid fa-house"></i> Home</a>
            <a href="service.html"><i class="fa-solid fa-calendar-plus"></i> Book Service</a>
        </nav>
    </header>
    <main>
<?php if (count($errors) > 0): ?>
        <div class="errors-box">
            <h2>Request not accepted</h2>
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?= e($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <p><a class="btn" href="service.html">Go back and correct the form</a></p>
<?php else:
    // Recalculate the total on the server from the validated services.
    $total = 0;
    foreach ($validServices as $service) {
        $total += $servicePrices[$service];
    }
    $formattedDate = date('j F Y', strtotime($serviceDate));
?>
        <div class="card confirm">
            <h2>Service request received</h2>
            <div class="detail-grid">
                <div class="detail"><small>Customer</small><strong><?= e($customerName) ?></strong></div>
                <div class="detail"><small>Phone</small><strong><?= e($phone) ?></strong></div>
                <div class="detail"><small>Email</small><strong><?= e($email) ?></strong></div>
                <div class="detail"><small>Service date</small><strong><?= e($formattedDate) ?></strong></div>
                <div class="detail"><small>Vehicle</small><strong><?= e($regNumber) ?> · <?= e($vehicleModel) ?> (<?= e($year) ?>)</strong></div>
                <div class="detail"><small>Vehicle type</small><strong><?= e($vehicleType) ?></strong></div>
            </div>
            <h3 style="margin-bottom:8px;">Selected services</h3>
            <ul>
                <?php foreach ($validServices as $service): ?>
                    <li><?= e($service) ?> (KSh <?= number_format($servicePrices[$service]) ?>)</li>
                <?php endforeach; ?>
            </ul>
            <div class="total-box">
                <div class="total-label">Estimated service cost</div>
                <div class="total">KSh <?= number_format($total) ?></div>
            </div>
            <p style="margin-top:20px;">Thank you for choosing AutoCare Garage.</p>
            <p><a class="btn" href="service.html">Book another service</a></p>
        </div>
<?php endif; ?>
    </main>
    <footer>&copy; 2026 AutoCare Garage. All rights reserved.</footer>
</body>
</html>
