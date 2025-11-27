// Tab switching functionality
function switchTab(tabName) {
  // Hide all tab contents
  const tabs = document.querySelectorAll('.tab-content');
  tabs.forEach(tab => tab.classList.add('hidden'));
  
  // Remove active class from all buttons
  const buttons = document.querySelectorAll('.tab-btn');
  buttons.forEach(btn => btn.classList.remove('active', 'bg-yellow-600', 'text-white'));
  buttons.forEach(btn => btn.classList.add('bg-gray-200', 'text-gray-800'));
  
  // Show selected tab
  document.getElementById(tabName + '-tab').classList.remove('hidden');
  
  // Activate selected button
  document.getElementById('tab-' + tabName).classList.remove('bg-gray-200', 'text-gray-800');
  document.getElementById('tab-' + tabName).classList.add('active', 'bg-yellow-600', 'text-white');
}

// Initialize M-Pesa payment
async function initiateMpesaPayment() {
  const donorName = document.getElementById('donor-name').value;
  const donorEmail = document.getElementById('donor-email').value;
  const phoneNumber = document.getElementById('phone-number').value;
  const amount = document.getElementById('amount').value;
  const purpose = document.getElementById('purpose').value;
  
  // Validation
  if (!donorName || !donorEmail || !phoneNumber || !amount || !purpose) {
    showError('Please fill in all fields');
    return;
  }
  
  if (amount < 100) {
    showError('Minimum amount is KES 100');
    return;
  }
  
  // Validate phone number format
  if (!/^\d{12}$/.test(phoneNumber)) {
    showError('Phone number must be 12 digits (e.g., 254712345678)');
    return;
  }
  
  const button = document.getElementById('mpesa-btn');
  button.disabled = true;
  button.textContent = '⏳ Processing...';
  showLoading(true);
  clearMessages();
  
  try {
    const response = await fetch('mpesa-stk-push.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({
        phone: phoneNumber,
        amount: amount,
        donor_name: donorName,
        donor_email: donorEmail,
        purpose: purpose
      })
    });
    
    const data = await response.json();
    
    if (data.success) {
      showSuccess('Payment initiated! Check your phone for the M-Pesa prompt. You have 2 minutes to enter your PIN.');
      // Reset form after successful initiation
      setTimeout(() => {
        document.getElementById('mpesa-form').reset();
        showLoading(false);
      }, 2000);
    } else {
      showError(data.message || 'Failed to initiate payment. Please try again.');
      showLoading(false);
    }
  } catch (error) {
    console.error('Error:', error);
    showError('Network error. Please check your connection and try again.');
    showLoading(false);
  } finally {
    button.disabled = false;
    button.textContent = '📱 Initiate M-Pesa Payment';
  }
}

// Show/hide loading message
function showLoading(show) {
  document.getElementById('loading-message').classList.toggle('hidden', !show);
}

// Show error message
function showError(message) {
  const errorDiv = document.getElementById('error-message');
  errorDiv.textContent = '❌ ' + message;
  errorDiv.classList.remove('hidden');
}

// Show success message
function showSuccess(message) {
  const successDiv = document.getElementById('success-message');
  successDiv.textContent = '✅ ' + message;
  successDiv.classList.remove('hidden');
}

// Clear all messages
function clearMessages() {
  document.getElementById('error-message').classList.add('hidden');
  document.getElementById('success-message').classList.add('hidden');
}

// Initialize with active tab styling
window.addEventListener('DOMContentLoaded', function() {
  document.getElementById('tab-message').classList.add('bg-yellow-600', 'text-white');
  document.getElementById('tab-message').classList.remove('bg-gray-200', 'text-gray-800');
});
