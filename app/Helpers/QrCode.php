<?php

declare(strict_types=1);

namespace App\Helpers;

use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;

final class QrCode
{
    public static function svg(string $value, int $size = 240): string
    {
        $size = max(120, min(600, $size));
        $renderer = new ImageRenderer(
            new RendererStyle($size, 4),
            new SvgImageBackEnd()
        );

        return (new Writer($renderer))->writeString($value);
    }
}
