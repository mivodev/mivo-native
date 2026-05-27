<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print Voucher</title>
    <style>
        @media print {
            .no-print { display: none !important; }
            body { 
                margin: 0; 
                padding: 0; 
                -webkit-print-color-adjust: exact !important; 
                print-color-adjust: exact !important; 
            }
            * {
                -webkit-print-color-adjust: exact !important; 
                print-color-adjust: exact !important; 
            }
        }
        body { margin: 0; padding: 0; background: #eee; font-family: sans-serif; }
        .voucher-wrapper {
             /* Wrapper for page break control if needed */
             display: inline-block;
             margin: 5px;
             page-break-inside: avoid;
        }
    </style>
    <script src="/assets/js/qrious.min.js"></script>
</head>
<body>
    <?php include __DIR__.'/toolbar.php'; ?>

    <div style="padding: 20px; text-align: center;">
        <?php foreach ($users as $index => $u) { ?>
            <div class="voucher-wrapper">
                <?php
                    $html = $templateContent;
            // Replace Variables
            // Standard variables
            $replacements = [
                '{{username}}' => $u['username'],
                '{{password}}' => $u['password'],
                '{{price}}' => $u['price'],
                '{{validity}}' => $u['validity'],
                '{{timelimit}}' => $u['timelimit'] ?? $u['validity'], // Fallback if missing
                '{{datalimit}}' => $u['datalimit'] ?? '',
                '{{profile}}' => $u['profile'],
                '{{comment}}' => $u['comment'],
                '{{hotspotname}}' => $u['hotspotname'],
                '{{dns_name}}' => $u['dns_name'],
                '{{login_url}}' => $u['login_url'],
                '{{num}}' => ($index + 1),
                '{{logo}}' => '<img src="/assets/img/logo.png" style="height:30px;border:0;">', // Default Logo placeholder
            ];

            // 1. Handle {{logo id=...}}
            $html = preg_replace_callback('/\{\{logo\s+id=[\'"]?([^\'"\s]+)[\'"]?\}\}/i', function ($matches) use ($logoMap) {
                $id = $matches[1];
                if (isset($logoMap[$id])) {
                    return '<img src="'.$logoMap[$id].'" style="height:50px; width:auto;">'; // Default style, user can wrap in div
                }

                return ''; // Return empty if not found
            }, $html);

            foreach ($replacements as $key => $val) {
                $html = str_replace($key, $val, $html);
            }

            // 2. Handle QR Code with Logo support
            $html = preg_replace_callback('/\{\{qrcode(?:\s+(.*?))?\}\}/i', function ($matches) use ($index, $u, $logoMap) {
                $qrId = 'qr-custom-'.$index.'-'.uniqid();
                $qrCodeValue = $u['login_url'].'?user='.$u['username'].'&password='.$u['password'];

                // Default Options
                $opts = [
                    'element' => 'document.getElementById("'.$qrId.'")',
                    'value' => $qrCodeValue,
                    'size' => 100,
                    'foreground' => 'black',
                    'background' => 'white',
                    'padding' => null,
                    'logo' => null, // Logo ID
                ];

                $rounded = '';

                // Parse Attributes
                if (! empty($matches[1])) {
                    $attrs = $matches[1];
                    if (preg_match('/fg\s*=\s*[\'"]?([^\'"\s]+)[\'"]?/i', $attrs, $m)) {
                        $opts['foreground'] = $m[1];
                    }
                    if (preg_match('/bg\s*=\s*[\'"]?([^\'"\s]+)[\'"]?/i', $attrs, $m)) {
                        $opts['background'] = $m[1];
                    }
                    if (preg_match('/size\s*=\s*[\'"]?(\d+)[\'"]?/i', $attrs, $m)) {
                        $opts['size'] = $m[1];
                    }
                    if (preg_match('/padding\s*=\s*[\'"]?(\d+)[\'"]?/i', $attrs, $m)) {
                        $opts['padding'] = $m[1];
                    }
                    if (preg_match('/rounded\s*=\s*[\'"]?(\d+)[\'"]?/i', $attrs, $m)) {
                        $rounded = 'border-radius: '.$m[1].'px;';
                    }
                    if (preg_match('/logo\s*=\s*[\'"]?([^\'"\s]+)[\'"]?/i', $attrs, $m)) {
                        $opts['logo'] = $m[1];
                    }
                }

                // CSS Styles
                $cssPadding = $opts['padding'] ? ('padding: '.$opts['padding'].'px; ') : '';
                $cssBg = 'background-color: '.$opts['background'].'; ';
                $baseStyle = 'display: inline-block; vertical-align: middle; '.$cssBg.$cssPadding.$rounded;

                // JS Generation
                $qrJs = "
                            (function() {
                                var qr = new QRious({
                                    element: document.getElementById('$qrId'),
                                    value: \"{$opts['value']}\",
                                    size: {$opts['size']},
                                    foreground: \"{$opts['foreground']}\",
                                    backgroundAlpha: 0
                                });
                        ";

                // If Logo is requested and found
                if ($opts['logo'] && isset($logoMap[$opts['logo']])) {
                    $logoPath = $logoMap[$opts['logo']];
                    $qrJs .= "
                                var img = new Image();
                                img.src = '$logoPath';
                                img.onload = function() {
                                    var canvas = document.getElementById('$qrId');
                                    var ctx = canvas.getContext('2d');
                                    var size = {$opts['size']};
                                    var logoSize = size * 0.2; // Logo is 20% of QR size
                                    var logoPos = (size - logoSize) / 2;
                                    ctx.drawImage(img, logoPos, logoPos, logoSize, logoSize);
                                };
                            ";
                }

                $qrJs .= '})();';

                return '<canvas id="'.$qrId.'" style="'.$baseStyle.'"></canvas><script>'.$qrJs.'</script>';
            }, $html);

            echo $html;
            ?>
            </div>
        <?php } ?>
    </div>
</body>
</html>
