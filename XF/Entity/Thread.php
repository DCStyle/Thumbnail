<?php

namespace DC\Thumbnail\XF\Entity;

class Thread extends XFCP_Thread
{
    public function isVideoThumbnail()
    {
	    if (!$this->FirstPost)
	    {
			return false;
	    }

		if ($this->getThumbnailEntity())
	    {
			$thumbnail = $this->getThumbnailEntity();
			if ($thumbnail->is_video !== -1)
			{
				return $thumbnail->is_video == 1;
			}
	    }

	    /** @var \DC\Thumbnail\Repository\Thumbnail $thumbnailRepo */
	    $thumbnailRepo = $this->repository('DC\Thumbnail:Thumbnail');

	    return $thumbnailRepo->isContainingMediaBbCode($this->FirstPost->message);
    }

	public function getDefaultThumbnail()
    {
		$baseUrl = \XF::app()->request()->getFullBasePath();
		$baseUrl = trim($baseUrl, '/');
		$baseUrl .= '/' . $this->app()->options()->dcThumbnail_default_thumbnail;
		
		return $baseUrl;
    }
	
	public function getThumbnail()
    {
        $nodeIds = \XF::options()->dcThumbnail_forums_limit;

        if (!in_array($this->node_id, $nodeIds))
        {
            return null;
        }

        $thumbnail = $this->getThumbnailEntity();

        if ($thumbnail)
        {
            if ($thumbnail->upload_url)
            {
                return $thumbnail->upload_url;
            }

            if ($thumbnail->thumbnail_url)
            {
                return $thumbnail->thumbnail_url;
            }
        }

        return $this->app()->router('public')->buildLink('canonical:'.\XF::options()->dcThumbnail_default_thumbnail);
    }

    public function getThumbnailEntity()
    {
        /** @var \DC\Thumbnail\Entity\Thumbnail $thumbnail */
		$thumbnail = $this->em()->find('DC\Thumbnail:Thumbnail', $this->thread_id);

        if ($thumbnail)
        {
            return $thumbnail;
        }

        return null;
    }
    
    public function canEditThumbnail()
    {
        $visitor = \XF::visitor();

        if ($visitor->is_admin)
        {
            return true;
        }

        if ($visitor->hasPermission('dcThumbnail', 'canEditAny'))
        {
            return true;
        }

        if ($visitor->hasPermission('dcThumbnail', 'canEdit')
            && $visitor->user_id == $this->user_id)
        {
            return true;
        }

        return false;
    }

    protected function _postDelete()
    {
        parent::_postDelete();
        
        $thumbnail = $this->getThumbnailEntity();

        if ($thumbnail)
        {
            $thumbnail->delete();
        }
    }
}