<?php

namespace DC\Thumbnail\XF\Entity;

use XF\Mvc\Entity\Structure;

class Thread extends XFCP_Thread
{
    public function getThumbnail()
    {
        $nodeIds = \XF::options()->dcThumbnail_forums_limit;

        if (!in_array($this->node_id, $nodeIds))
        {
            return;
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
        $thumbnail = $this->em()->find('DC\Thumbnail:Thumbnail', $this->thread_id);

        if ($thumbnail)
        {
            return $thumbnail;
        }

        return;
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

        return;
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
    
    public static function getStructure(Structure $structure)
    {
        $parent = parent::getStructure($structure);

        return $parent;
    }
}