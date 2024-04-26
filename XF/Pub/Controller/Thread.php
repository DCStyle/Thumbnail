<?php

namespace DC\Thumbnail\XF\Pub\Controller;

use XF\Mvc\ParameterBag;

class Thread extends XFCP_Thread
{
    public function actionEditThumbnail(ParameterBag $params)
    {
        /** @var \DC\Thumbnail\XF\Entity\Thread $thread */
        $thread = $this->assertViewableThread($params->thread_id);

        if (!$thread->canEditThumbnail())
        {
            return $this->noPermission();
        }

        $thumbnail = $this->assertViewableThumbnail($thread->thread_id);

        $thumbnailRepo = $this->getThumbnailRepo();

        $images = $thumbnailRepo->getThreadImages($thread);

        $noImageLink = $this->buildLink('canonical:'.$this->options()->dcThumbnail_default_thumbnail);

        if ($this->isPost())
        {
            if (!$thumbnail)
            {
                /** @var \DC\Thumbnail\Entity\Thumbnail $thumbnail */
				$thumbnail = $this->em()->create('DC\Thumbnail:Thumbnail');
            }

            $thumbnail->thread_id = $thread->thread_id;
			
			$imageUrl = $this->filter('image_url', 'str');
			switch ($imageUrl)
			{
				case 'default':
					$thumbnailRepo->deleteThumbnailImage($thumbnail);
					
					$thumbnail->thumbnail_url = $thread->getDefaultThumbnail();
					$thumbnail->upload_url = '';
					break;
				case 'custom':
					if ($upload = $this->request->getFile('upload', false, false))
					{
						try {
							$customThumbnailUrl = $thumbnailRepo->getThumbnailUrlFromUpload($upload, $thumbnail);
							
							$thumbnail->thumbnail_url = '';
							$thumbnail->upload_url = $customThumbnailUrl;
						} catch (\Exception $e)
						{
							throw $this->exception($this->error($e->getMessage()));
						}
					}
					
					break;
				default: // An actual image url in first post
					$thumbnailRepo->deleteThumbnailImage($thumbnail);
					
					$thumbnail->thumbnail_url = $this->filter('image_url', 'str');
					$thumbnail->upload_url = '';
					break;
			}
			
            if ($this->filter('delete_custom_image', 'bool'))
            {
                $thumbnailRepo->deleteThumbnailImage($thumbnail);
            }

			$thumbnail->is_video = $this->filter('is_video', 'int');

	        $thumbnail->save();

            return $this->redirect($this->getDynamicRedirect($this->buildLink('threads', $thread)));
        }

        $viewParams = [
            'thread'    => $thread,
            'images'    => $images,
            'thumbnail' => $thumbnail,
	        'noImageLink' => $noImageLink
        ];

        return $this->view('XF:Thread\EditThumbnail', 'dcThumbnail_thread_edit_thumbnail', $viewParams);
    }

    protected function assertViewableThumbnail($threadId)
    {
        $thumbnail = $this->em()->find('DC\Thumbnail:Thumbnail', $threadId);

        return $thumbnail;
    }

    /**
     * @return \DC\Thumbnail\Repository\Thumbnail
     */
    protected function getThumbnailRepo()
    {
        return $this->repository('DC\Thumbnail:Thumbnail');
    }
}