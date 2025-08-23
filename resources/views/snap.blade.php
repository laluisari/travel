<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Midtrans Snap Test</title>
    <!-- Panggil Midtrans Snap.js -->
    <script type="text/javascript"
    src="https://app.sandbox.midtrans.com/snap/snap.js" 
    data-client-key="SB-Mid-client-wUhbHxdkDknY3cq1"></script>
</head>
<body>
    <h1>Midtrans Snap Test</h1>
    @if($snapToken)
        <button id="pay-button">Pay Now</button>
        <script>
            document.getElementById('pay-button').onclick = function() {
                snap.pay("{{ $snapToken }}", {
                    onSuccess: function(result) {
                        console.log('Payment Success:', result);
                        alert('Payment Success! Transaction ID: ' + result.transaction_id);
                    },
                    onPending: function(result) {
                        console.log('Payment Pending:', result);
                        alert('Payment Pending! Transaction ID: ' + result.transaction_id);
                    },
                    onError: function(result) {
                        console.log('Payment Error:', result);
                        alert('Payment Error! ' + result.status_message);
                    }
                });
            }
        </script>
    @else
        <p>Failed to create snap token.</p>
    @endif
</body>
</html>