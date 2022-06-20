<?php

namespace DC\Thumbnail;

class Listener
{
    public static function appSetup(\XF\App $app)
    {
        $options = \XF::options();

        $app->thumbWidth = $options->dcThumbnail_thumbnail_width;
        $app->thumbHeight = $options->dcThumbnail_thumbnail_height;
        $app->thumbnailRadius = $options->dcThumbnail_thumbnail_borderRadius;
        $app->base64 = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8Xw8AAoMBgDTD2qgAAAAASUVORK5CYII=';
    }
}