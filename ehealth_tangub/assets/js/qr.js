<?php
/* PHP QR Code Library (minimal embed) */
class QRcode {
    public static function png($text, $outfile, $level, $size) {
        $url = "https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=" . urlencode($text);
        file_put_contents($outfile, file_get_contents($url));
    }
}
