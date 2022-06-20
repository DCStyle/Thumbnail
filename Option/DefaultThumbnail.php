<?php

namespace DC\Thumbnail\Option;

use XF\Option\AbstractOption;

class DefaultThumbnail extends AbstractOption
{
    public static function renderOption(\XF\Entity\Option $option, array $htmlParams)
	{
		return self::getTemplate('admin:dcThumbnail_option_default_thumbnail', $option, $htmlParams, [
			
		]);
	}
}