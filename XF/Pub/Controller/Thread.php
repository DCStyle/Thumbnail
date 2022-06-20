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
                $thumbnail = $this->em()->create('DC\Thumbnail:Thumbnail');
            }

            $thumbnail->thread_id = $thread->thread_id;

            if ($this->filter('image_url', 'str') != 'custom')
            {
                $thumbnail->thumbnail_url = $this->filter('image_url', 'str');
            }
            
            $thumbnail->save();

            if ($this->filter('image_url', 'str') == 'custom')
            {
                if ($upload = $this->request->getFile('upload', false, false))
                {
                    $thumbnailRepo->setThumbnailFromUpload($upload, $thumbnail);
                }
            }

            if ($this->filter('delete_custom_image', 'bool'))
            {
                $thumbnailRepo->deleteThumbnailImage($thumbnail);

                $thumbnail->thumbnail_url = $noImageLink;
                $thumbnail->upload_url = '';
                $thumbnail->save();
            }

            return $this->redirect($this->getDynamicRedirect($this->buildLink('threads', $thread)));
        }

        $viewParams = [
            'thread'    => $thread,
            'images'    => $images,
            'thumbnail' => $thumbnail
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