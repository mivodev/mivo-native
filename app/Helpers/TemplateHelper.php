<?php

namespace App\Helpers;

class TemplateHelper
{
    public static function getDefaultTemplate()
    {
        return '
<style>
    .voucher { width: 250px; background: #fff; padding: 10px; border: 1px solid #ccc; font-family: "Courier New", Courier, monospace; color: #000; }
    .header { text-align: center; font-weight: bold; margin-bottom: 5px; font-size: 14px; }
    .row { display: flex; justify-content: space-between; margin-bottom: 2px; font-size: 12px; }
    .code { font-size: 16px; font-weight: bold; text-align: center; margin: 10px 0; border: 1px dashed #000; padding: 5px; }
    .qr { text-align: center; margin-top: 5px; }
</style>
<div class="voucher">
    <div class="header">{{server_name}}</div>
    <div class="row"><span>Profile:</span> <span>{{profile}}</span></div>
    <div class="row"><span>Valid:</span> <span>{{validity}}</span></div>
    <div class="row"><span>Price:</span> <span>{{price}}</span></div>
    <div class="code">
        User: {{username}}<br>
        Pass: {{password}}
    </div>
    <div class="qr">{{qrcode}}</div>
    <div style="text-align:center; font-size: 10px; margin-top:5px;">
        Login: http://{{dns_name}}/login
    </div>
</div>';
    }

    public static function getMockContent($content)
    {
        if (empty($content)) {
            return '';
        }

        // Dummy Data
        $dummyData = [
            '{{server_name}}' => 'Hotspot',
            '{{dns_name}}' => 'hotspot.lan',
            '{{username}}' => 'u-5829',
            '{{password}}' => '5912',
            '{{price}}' => '5.000',
            '{{validity}}' => '12 Hours',
            '{{profile}}' => 'Small-Packet',
            '{{time_limit}}' => '12h',
            '{{data_limit}}' => '1 GB',
            '{{ip_address}}' => '192.168.88.254',
            '{{mac_address}}' => 'AA:BB:CC:DD:EE:FF',
            '{{comment}}' => 'Thank You',
            '{{copyright}}' => 'Mivo',
        ];

        $content = str_replace(array_keys($dummyData), array_values($dummyData), $content);

        // QR Code replacement - Using canvas for client-side rendering with QRious
        $content = preg_replace('/\{\{\s*qrcode\s*(.*?)\s*\}\}/i', '<canvas class="qrcode-placeholder" data-options=\'$1\' style="width:80px;height:80px;display:inline-block;"></canvas>', $content);

        return $content;
    }

    public static function getPreviewPage($content)
    {
        $mockContent = self::getMockContent($content);

        return '
        <!DOCTYPE html>
        <html>
        <head>
            <style>
                html, body { margin: 0; padding: 0; width: 100%; height: 100%; overflow: hidden; }
                body { display: flex; align-items: center; justify-content: center; background-color: transparent; }
                #wrapper { display: inline-block; transform-origin: center center; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
            </style>
            <script src="/assets/js/qrious.min.js"></script>
        </head>
        <body>
            <div id="wrapper">'.$mockContent.'</div>
            <script>
                window.addEventListener("load", () => {
                    const wrap = document.getElementById("wrapper");
                    if(!wrap) return;

                    // Render QR Codes
                    document.querySelectorAll(".qrcode-placeholder").forEach(canvas => {
                        const optionsStr = canvas.dataset.options || "";
                        const options = {};
                        
                        // Robust parser for "fg=red bg=#fff size=100" format
                        const regex = /([a-z]+)=([^ \t\r\n\f\v"]+|"[^"]*"|\'[^\']*\')/gi;
                        let match;
                        while ((match = regex.exec(optionsStr)) !== null) {
                            let key = match[1].toLowerCase();
                            let val = match[2].replace(/["\']/g, "");
                            options[key] = val;
                        }

                        new QRious({
                            element: canvas,
                            value: "http://hotspot.lan/login?username=u-5829&password=5912",
                            size: parseInt(options.size) || 100,
                            foreground: options.fg || "#000000",
                            backgroundAlpha: 0,
                            level: "M"
                        });
                        
                        // Handle styles via CSS for better compatibility with rounding and padding
                        if (options.size) {
                            canvas.style.width = options.size + "px";
                            canvas.style.height = options.size + "px";
                        }
                        
                        if (options.bg) {
                            canvas.style.backgroundColor = options.bg;
                        }
                        
                        if (options.padding) {
                            canvas.style.padding = options.padding + "px";
                        }
                        
                        if (options.rounded) {
                            canvas.style.borderRadius = options.rounded + "px";
                        }
                    });
                    
                    const updateScale = () => {
                        const w = wrap.offsetWidth;
                        const h = wrap.offsetHeight;
                        const winW = window.innerWidth - 24; 
                        const winH = window.innerHeight - 24;
                        
                        let scale = 1;
                        if (w > winW || h > winH) {
                             scale = Math.min(winW / w, winH / h);
                        } else {
                             scale = Math.min(winW / w, winH / h);
                        }
                        wrap.style.transform = `scale(${scale})`;
                    };
                    
                    updateScale();
                    window.addEventListener("resize", updateScale);
                });
            </script>
        </body>
        </html>';
    }
}
