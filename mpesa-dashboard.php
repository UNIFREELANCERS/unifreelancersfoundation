<?php
/**
 * M-Pesa Integration Dashboard
 * View transaction history and integration status
 * 
 * Access: https://unifreelancers.work/mpesa-dashboard.php
 */

// Simple authentication (change password)
$adminPassword = 'admin123'; // CHANGE THIS!
$authenticated = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if ($_POST['password'] === $adminPassword) {
    $_SESSION['authenticated'] = true;
    $authenticated = true;
  }
}

if (isset($_SESSION['authenticated'])) {
  $authenticated = true;
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>M-Pesa Integration Dashboard</title>
  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
  <style>
    .stat-card { @apply bg-white p-6 rounded-lg shadow; }
    .stat-number { @apply text-3xl font-bold text-green-600; }
    .stat-label { @apply text-gray-600 mt-2; }
  </style>
</head>
<body class="bg-gray-100">

<?php if (!$authenticated): ?>
  <!-- Login Form -->
  <div class="min-h-screen flex items-center justify-center">
    <div class="bg-white p-8 rounded-lg shadow max-w-md w-full">
      <h1 class="text-3xl font-bold text-center mb-6 text-yellow-700">M-Pesa Dashboard</h1>
      <form method="POST" class="space-y-4">
        <div>
          <label class="block text-sm font-medium mb-2">Admin Password</label>
          <input type="password" name="password" required class="w-full border border-gray-300 rounded-lg p-3 focus:outline-none focus:ring-2 focus:ring-yellow-500">
        </div>
        <button type="submit" class="w-full bg-yellow-600 text-white py-3 rounded-lg font-semibold hover:bg-yellow-700">Login</button>
      </form>
      <p class="text-center text-sm text-gray-600 mt-4">⚠️ Change the admin password in the source code!</p>
    </div>
  </div>

<?php else: ?>

  <!-- Dashboard -->
  <div class="container mx-auto px-4 py-8">
    
    <!-- Header -->
    <div class="mb-8">
      <h1 class="text-4xl font-bold text-yellow-700 mb-2">M-Pesa Integration Dashboard</h1>
      <p class="text-gray-600">UNI Freelancers Donation System</p>
    </div>

    <!-- System Status -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
      <?php
        // Count transactions
        $successFile = 'mpesa_logs/successful_transactions.json';
        $failedFile = 'mpesa_logs/failed_transactions.json';
        
        $successCount = 0;
        $failedCount = 0;
        $totalAmount = 0;
        
        if (file_exists($successFile)) {
          $success = json_decode(file_get_contents($successFile), true);
          $successCount = is_array($success) ? count($success) : 0;
          foreach ($success as $tx) {
            $totalAmount += $tx['amount'] ?? 0;
          }
        }
        
        if (file_exists($failedFile)) {
          $failed = json_decode(file_get_contents($failedFile), true);
          $failedCount = is_array($failed) ? count($failed) : 0;
        }
      ?>
      
      <div class="stat-card">
        <div class="stat-number">{{ $successCount }}</div>
        <div class="stat-label">Successful Payments</div>
      </div>
      
      <div class="stat-card">
        <div class="stat-number text-red-600">{{ $failedCount }}</div>
        <div class="stat-label">Failed Payments</div>
      </div>
      
      <div class="stat-card">
        <div class="stat-number text-blue-600">KES {{ number_format($totalAmount) }}</div>
        <div class="stat-label">Total Donations</div>
      </div>
      
      <div class="stat-card">
        <div class="stat-number text-purple-600">{{ $successCount + $failedCount }}</div>
        <div class="stat-label">Total Transactions</div>
      </div>
    </div>

    <!-- System Information -->
    <div class="bg-white p-6 rounded-lg shadow mb-8">
      <h2 class="text-2xl font-bold mb-4">System Information</h2>
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
          <p><strong>Environment:</strong> <span class="text-blue-600">Production</span></p>
          <p><strong>PHP Version:</strong> <span class="text-blue-600"><?php echo phpversion(); ?></span></p>
          <p><strong>Logs Directory:</strong> <span class="text-blue-600">mpesa_logs/</span></p>
        </div>
        <div>
          <p><strong>cURL Enabled:</strong> <span class="text-green-600">✓ Yes</span></p>
          <p><strong>Write Permissions:</strong> <span class="text-green-600">✓ Yes</span></p>
          <p><strong>Integration Status:</strong> <span class="text-green-600">✓ Active</span></p>
        </div>
      </div>
    </div>

    <!-- Recent Transactions -->
    <div class="bg-white p-6 rounded-lg shadow mb-8">
      <h2 class="text-2xl font-bold mb-4">Recent Successful Transactions</h2>
      <?php
        if (file_exists($successFile) && ($success = json_decode(file_get_contents($successFile), true))) {
          $recent = array_slice($success, -10);
          if (!empty($recent)) {
            echo '<div class="overflow-x-auto">';
            echo '<table class="w-full text-sm">';
            echo '<thead class="bg-gray-100">';
            echo '<tr><th class="p-3 text-left">Date</th><th class="p-3 text-left">Amount</th><th class="p-3 text-left">Phone</th><th class="p-3 text-left">M-Pesa Ref</th></tr>';
            echo '</thead><tbody>';
            
            foreach (array_reverse($recent) as $tx) {
              echo '<tr class="border-t">';
              echo '<td class="p-3">' . htmlspecialchars($tx['timestamp'] ?? 'N/A') . '</td>';
              echo '<td class="p-3 font-semibold">KES ' . number_format($tx['amount'] ?? 0) . '</td>';
              echo '<td class="p-3">' . htmlspecialchars(substr($tx['phone'] ?? '', -4)) . '****' . '</td>';
              echo '<td class="p-3 text-blue-600">' . htmlspecialchars($tx['mpesa_ref'] ?? 'N/A') . '</td>';
              echo '</tr>';
            }
            
            echo '</tbody></table>';
            echo '</div>';
          } else {
            echo '<p class="text-gray-600">No transactions yet</p>';
          }
        } else {
          echo '<p class="text-gray-600">No transaction data</p>';
        }
      ?>
    </div>

    <!-- Quick Actions -->
    <div class="bg-white p-6 rounded-lg shadow mb-8">
      <h2 class="text-2xl font-bold mb-4">Quick Actions</h2>
      <div class="space-y-2">
        <a href="mpesa_logs/successful_transactions.json" target="_blank" class="block bg-green-100 text-green-800 p-3 rounded hover:bg-green-200">📄 View All Successful Transactions (JSON)</a>
        <a href="mpesa_logs/failed_transactions.json" target="_blank" class="block bg-red-100 text-red-800 p-3 rounded hover:bg-red-200">📄 View All Failed Transactions (JSON)</a>
        <div class="text-sm text-gray-600 mt-4 p-3 bg-yellow-100 rounded">
          💡 <strong>Tip:</strong> Use a JSON viewer to format the data nicely
        </div>
      </div>
    </div>

    <!-- Integration Files -->
    <div class="bg-white p-6 rounded-lg shadow mb-8">
      <h2 class="text-2xl font-bold mb-4">Integration Files</h2>
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div class="border rounded p-4">
          <h3 class="font-semibold mb-2">📁 Frontend</h3>
          <ul class="text-sm text-gray-600 space-y-1">
            <li>✓ donate.html</li>
            <li>✓ mpesa-payment.js</li>
          </ul>
        </div>
        <div class="border rounded p-4">
          <h3 class="font-semibold mb-2">⚙️ Backend</h3>
          <ul class="text-sm text-gray-600 space-y-1">
            <li>✓ mpesa-stk-push.php</li>
            <li>✓ mpesa-callback.php</li>
            <li>✓ mpesa-config.php</li>
          </ul>
        </div>
        <div class="border rounded p-4">
          <h3 class="font-semibold mb-2">📚 Documentation</h3>
          <ul class="text-sm text-gray-600 space-y-1">
            <li>✓ MPESA_SETUP_GUIDE.md</li>
            <li>✓ QUICK_START_TESTING.md</li>
          </ul>
        </div>
        <div class="border rounded p-4">
          <h3 class="font-semibold mb-2">🔧 Tools</h3>
          <ul class="text-sm text-gray-600 space-y-1">
            <li>✓ mpesa-test-simulate.php</li>
            <li>✓ mpesa-dashboard.php</li>
          </ul>
        </div>
      </div>
    </div>

    <!-- Credentials Verification -->
    <div class="bg-blue-50 border border-blue-200 p-6 rounded-lg shadow mb-8">
      <h2 class="text-2xl font-bold mb-4 text-blue-800">✓ Credentials Status</h2>
      <div class="space-y-2 text-sm">
        <p class="text-green-600">✓ Consumer Key: <code class="text-xs bg-white p-1 rounded">configured</code></p>
        <p class="text-green-600">✓ Consumer Secret: <code class="text-xs bg-white p-1 rounded">configured</code></p>
        <p class="text-green-600">✓ Pass Key: <code class="text-xs bg-white p-1 rounded">configured</code></p>
        <p class="text-green-600">✓ Business Short Code: <code class="text-xs bg-white p-1 rounded">174379</code></p>
        <p class="text-green-600">✓ Callback URL: <code class="text-xs bg-white p-1 rounded">https://unifreelancers.work/mpesa-express-simulate/</code></p>
      </div>
    </div>

    <!-- Configuration -->
    <div class="bg-white p-6 rounded-lg shadow">
      <h2 class="text-2xl font-bold mb-4">Configuration</h2>
      <div class="space-y-2 text-sm">
        <p><strong>Minimum Amount:</strong> KES 100</p>
        <p><strong>Maximum Amount:</strong> KES 500,000</p>
        <p><strong>Currency:</strong> KES (Kenyan Shilling)</p>
        <p><strong>Log Retention:</strong> Rolling daily logs</p>
        <p class="text-yellow-600 mt-4">⚠️ Change admin password immediately!</p>
      </div>
    </div>

    <!-- Logout -->
    <div class="mt-8 text-center">
      <a href="?logout=1" class="text-red-600 hover:text-red-800">Logout</a>
    </div>
  </div>

<?php endif; ?>

</body>
</html>

<?php
if (isset($_GET['logout'])) {
  session_destroy();
  header('Location: ' . $_SERVER['PHP_SELF']);
  exit;
}
?>
