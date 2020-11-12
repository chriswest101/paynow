<?php

namespace Chriswest101\Paynow\Helpers;

use SimpleSoftwareIO\QrCode\Facades\QrCode;

class QRCodeService
{
    /**
     * Generate a QR code
     *
     * @param string $string
     * @return string
     */
    public static function generate(string $string, string $pathToImage, int $imageSize): string
    {
        return base64_encode(QrCode::format('png')
                ->size($imageSize)
                ->style('square', 0.8)
                ->margin(5)
                ->errorCorrection('H')
                ->mergeString($pathToImage, .2)
                ->color(124, 26, 120)
                ->generate($string));
    }
}
