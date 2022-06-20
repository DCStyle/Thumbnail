<?php

namespace DC\Thumbnail\XF\Pub\Controller;

use XF\Service\Thread\Creator;

class Forum extends XFCP_Forum
{
    protected function finalizeThreadCreate(Creator $creator)
    {
        parent::finalizeThreadCreate($creator);

        $thread = $creator->getThread();

        $thumbnail = $this->em()->create('DC\Thumbnail:Thumbnail');
        
        $thumbnailRepo = $this->getThumbnailRepo();

        $images = $thumbnailRepo->getThreadImages($thread);

        if (!empty($images))
        {
            $thumbnailUrl = $images[0];
        }
        else
        {
            $thumbnailUrl = \XF::app()->router('public')->buildLink('canonical:'.\XF::options()->dcThumbnail_default_thumbnail);
        }

        $thumbnail->thread_id = $thread->thread_id;
        
        $thumbnail->thumbnail_url = $thumbnailUrl;

        $thumbnail->save();
    }

    /**
     * @return \DC\Thumbnail\Repository\Thumbnail
     */
    protected function getThumbnailRepo()
    {
        return $this->repository('DC\Thumbnail:Thumbnail');
    }
}