<?php

namespace DC\Thumbnail\Job;

use XF\Job\AbstractRebuildJob;

class ThumbnailRebuild extends AbstractRebuildJob
{
    protected $defaultData = [
		'replace_current' => false
	];
    
    /**
     * @param mixed $start
     * @param mixed $batch
     * @return array
     */
    protected function getNextIds($start, $batch)
    {
        $db = $this->app->db();

        $nodeIds = \XF::options()->dcThumbnail_forums_limit;
		if (in_array(-1, $nodeIds)) // All forums
		{
			return $db->fetchAllColumn($db->limit('SELECT thread_id FROM xf_thread WHERE thread_id > ? ORDER BY thread_id ', $batch), $start);
		} else { // Specific forums
			$nodeIdsList = '(' . implode(',', $nodeIds) .')';
			return $db->fetchAllColumn($db->limit('SELECT thread_id FROM xf_thread WHERE thread_id > ? AND node_id IN ' . $nodeIdsList . ' ORDER BY thread_id ', $batch), $start);
		}
    }

    /**
     * @return \XF\Phrase
     */
    protected function getStatusType()
    {
        return \XF::phrase('threads');
    }

    /**
     * @param mixed $id
     * @throws \XF\PrintableException
     * @return void
     */
    protected function rebuildById($id)
    {
        $em = $this->app->em();
        
        $thread = $em->find('XF:Thread', $id);
        if ($thread === null) {
            return;
        }

		/** @var \DC\Thumbnail\Entity\Thumbnail $thumbnail */
        $thumbnail = $em->find('DC\Thumbnail:Thumbnail', $id);

        if ($thumbnail && !$this->data['replace_current'])
        {
            return;
        }
        
        if ($thumbnail === null)
        {
            $thumbnail = $em->create('DC\Thumbnail:Thumbnail');
        }

        $thumbnailRepo = self::getThumbnailRepo();

        $images = $thumbnailRepo->getThreadImages($thread);

        if (!empty($images))
        {
            $thumbnailUrl = $images[0];
	        $thumbnail->is_no_thumbnail = false;
        }
        else // Has no thumbnail
        {
            $thumbnailUrl = '';
			$thumbnail->is_no_thumbnail = true;
        }

        $thumbnail->thread_id = $thread->thread_id;
        $thumbnail->thumbnail_url = $thumbnailUrl;

        $thumbnail->save();
    }

    /**
     * @return \DC\Thumbnail\Repository\Thumbnail
     */
    protected static function getThumbnailRepo()
    {
        return \XF::repository('DC\Thumbnail:Thumbnail');
    }
}