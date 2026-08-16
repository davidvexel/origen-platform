<?php

namespace App\Domain\Loyalty\Support;

use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;

class LoyaltyQrCode
{
    public function svg(string $value, int $size = 280): string
    {
        $renderer = new ImageRenderer(new RendererStyle($size, 2), new SvgImageBackEnd);

        return (new Writer($renderer))->writeString($value);
    }
}
