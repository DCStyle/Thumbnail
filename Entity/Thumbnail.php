<?php

namespace DC\Thumbnail\Entity;

use XF\Mvc\Entity\Entity;
use XF\Mvc\Entity\Structure;

/**
 * COLUMNS
 * @property int $thread_id
 * @property string $thumbnail_url
 * @property string $upload_url
 * @property int $is_video Define if a thread thumbnail is video: 1 is true, 0 is false, -1 is unset (auto-detect)
 * @property int $thumbnail_date
 * @property bool $is_no_thumbnail
 *
 * RELATIONS
 * @property \XF\Entity\Thread $Thread
 */

class Thumbnail extends Entity
{
	public static function getStructure(Structure $structure)
    {
        $structure->table = 'xf_dcThumbnail_thumbnail';
        $structure->primaryKey = 'thread_id';
        $structure->shortName = 'DC\Thumbnail:Thumbnail';
        $structure->columns = [
            'thread_id'             => ['type' => self::UINT, 'required' => true],
            'thumbnail_url'         => ['type' => self::STR, 'default' => ''],
            'upload_url'            => ['type' => self::STR, 'default' => ''],
	        'is_video'              => ['type' => self::INT, 'default' => -1],
            'thumbnail_date'        => ['type' => self::UINT, 'default' => \XF::$time],
	        'is_no_thumbnail'       => ['type' => self::BOOL, 'default' => false]
        ];
        $structure->relations = [
            'Thread' => [
                'entity' => 'XF:Thread',
                'type' => self::TO_ONE,
                'conditions' => 'thread_id',
                'primary' => true
            ]
        ];

        return $structure;
    }

	protected function _postDelete()
	{
		/** @var \DC\Thumbnail\Repository\Thumbnail $thumbnail */
		$thumbnailRepo = $this->repository('DC\Thumbnail:Thumbnail');

		$thumbnailRepo->deleteThumbnailImage($this);
	}
}