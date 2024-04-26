<?php

namespace DC\Thumbnail\XF\Pub\Controller;

use XF\Service\Thread\Creator;

class Forum extends XFCP_Forum
{
	/**
     * @return \DC\Thumbnail\Repository\Thumbnail
     */
    protected function getThumbnailRepo()
    {
        return $this->repository('DC\Thumbnail:Thumbnail');
    }
}