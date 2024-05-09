<?php

namespace DC\Thumbnail\XF\Entity;

class Thread extends XFCP_Thread
{
    public function isInThumbnailForum()
    {
	    $nodeIds = $this->app()->options()->dcThumbnail_forums_limit;
	    if (!in_array($this->node_id, $nodeIds) && !in_array(-1, $nodeIds))
	    {
		    return false;
	    }

		return true;
    }

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
		$defaultThumbnailAvatar = $this->app()->options()->dcThumbnail_default_thumbnail_avatar;
		if (!empty($defaultThumbnailAvatar) && $this->User)
		{
			if ($defaultThumbnailAvatar['custom_avatar']
				&& $this->User->getAvatarType() == 'custom')
			{
				return $this->User->getAvatarUrl('o', null, true);
			} elseif ($defaultThumbnailAvatar['gravatar']
				&& $this->User->getAvatarType() == 'gravatar'
				&& $this->app()->options()->gravatarEnable
			) {
				return $this->User->getAvatarUrl('o', null, true);
			}
		}

	    $noThumbnail = \XF::options()->dcThumbnail_default_thumbnail;
	    if (filter_var($noThumbnail, FILTER_VALIDATE_URL) !== false)
	    {
		    $noThumbnailUrl = $noThumbnail;
	    }
	    else
	    {
		    $noThumbnailUrl = \XF::app()->router('public')->buildLink('canonical:'. $noThumbnail);
	    }

		return $noThumbnailUrl;
    }
	
	public function getThumbnail()
    {
        if (!$this->isInThumbnailForum())
        {
			return null;
        }

        $thumbnail = $this->getThumbnailEntity();
        if ($thumbnail && !$thumbnail->is_no_thumbnail)
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

        return $this->getDefaultThumbnail();
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
	    $thumbnail?->delete();
    }
}