<?php

namespace DC\ThumbnailRebuild\Cli\Command\Rebuild;

use XF\Cli\Command\Rebuild\AbstractRebuildCommand;
use Symfony\Component\Console\Input\InputOption;

class ThumbnailRebuild extends AbstractRebuildCommand
{
    protected function getRebuildName()
    {
        return 'dcThumbnail-thumbnail';
    }

    protected function getRebuildDescription()
    {
        return 'Rebuilds thread thumbnails.';
    }

    protected function getRebuildClass()
    {
        return 'DC\Thumbnail:ThumbnailRebuild';
    }

    protected function configureOptions()
	{
		$this
			->addOption(
				'replace_current',
				null,
				InputOption::VALUE_NONE,
				'This will slow the process down and also rebuild threads which already have thumbnail. Default: false'
			);
	}
}