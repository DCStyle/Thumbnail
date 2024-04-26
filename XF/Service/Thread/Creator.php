<?php

namespace DC\Thumbnail\XF\Service\Thread;

class Creator extends XFCP_Creator
{
	protected function _save()
	{
		$thread = parent::_save();

		$thumbnailRepo = $this->getThumbnailRepo();
		$thumbnailRepo->createThumbnailForThread($thread);

		return $thread;
	}

	/**
	 * @return \XF\Mvc\Entity\Repository|\DC\Thumbnail\Repository\Thumbnail
	 */
	protected function getThumbnailRepo()
	{
		return $this->repository('DC\Thumbnail:Thumbnail');
	}
}