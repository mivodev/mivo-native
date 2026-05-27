<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print Voucher</title>
    <style>
        @media print {
            .no-print { display: none !important; }
            body { margin: 0; padding: 0; }
        }
        body { font-family: 'Courier New', Courier, monospace; background: #eee; }
        .voucher-container {
             display: flex;
             flex-wrap: wrap;
             justify-content: center;
             gap: 10px;
        }
        .voucher {
            width: 300px;
            background: #fff;
            padding: 10px;
            margin: 5px;
            border: 1px solid #ccc;
            page-break-inside: avoid;
            display: inline-block;
        }
        .header { text-align: center; font-weight: bold; margin-bottom: 5px; }
        .row { display: flex; justify-content: space-between; margin-bottom: 2px; font-size: 12px; }
        .code { font-size: 16px; font-weight: bold; text-align: center; margin: 10px 0; border: 1px dashed #000; padding: 5px; }
        .qr-wrapper { text-align: center; margin-top: 5px; }
    </style>
    <script src="/assets/js/qrious.min.js"></script>
</head>
<body>
    <?php include __DIR__.'/toolbar.php'; ?>

    <div class="voucher-container">
        <?php foreach ($users as $index => $u) { ?>
        <div class="voucher">
            <div class="header"><?= htmlspecialchars($u['dns_name']) ?></div>
            <div class="row"><span>Profile:</span> <span><?= htmlspecialchars($u['profile']) ?></span></div>
            <div class="row"><span>Valid:</span> <span><?= htmlspecialchars($u['validity']) ?></span></div>
            <div class="row"><span>Price:</span> <span><?= htmlspecialchars($u['price']) ?></span></div>
            
            <div class="code">
                User: <?= htmlspecialchars($u['username']) ?><br>
                Pass: <?= htmlspecialchars($u['password']) ?>
            </div>

            <div class="qr-wrapper">
                 <canvas id="qr-<?= $index ?>"></canvas>
            </div>
            
            <div style="text-align:center; font-size: 10px; margin-top:5px;">
                Login: <?= htmlspecialchars($u['login_url']) ?>
            </div>
        </div>
        <script>
            (function() {
                new QRious({
                    element: document.getElementById('qr-<?= $index ?>'),
                    value: '<?= htmlspecialchars($u['login_url']) ?>?user=<?= htmlspecialchars($u['username']) ?>&password=<?= htmlspecialchars($u['password']) ?>',
                    size: 100
                });
            })();
        </script>
        <?php } ?>
    </div>

    <script>
        // Optional: Auto print if directed
        // window.print();
    </script>
</body>
</html>
