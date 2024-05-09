<?php

namespace DC\Thumbnail\XF\Service\Post;

class Editor extends XFCP_Editor
{
	protected function _save()
	{
		$post = parent::_save();

		/** @var \DC\Thumbnail\XF\Entity\Thread $thread */
		$thread = $this->post->Thread;

		// Rebuild thread thumbnail cache
		if ($post->isFirstPost()
			&& $this->app->options()->dcThumbnail_auto_rebuild_after_post_changes
			&& $thread->getThumbnailEntity()
		)
		{
			$thumbnail = $thread->getThumbnailEntity();

			/** @var \DC\Thumbnail\Repository\Thumbnail $thumbnailRepo */
			$thumbnailRepo = $this->app->repository('DC\Thumbnail:Thumbnail');

			$images = $thumbnailRepo->getThreadImages($thread);
			$thumbnailUrl = !empty($images) ? $images[0] : '';

			$thumbnail->thumbnail_url = $thumbnailUrl;
			$thumbnail->is_no_thumbnail = empty($images);
			$thumbnail->save();
		}

		return $post;
	}
}