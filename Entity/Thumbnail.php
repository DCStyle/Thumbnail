<?php

namespace DC\Thumbnail\Entity;

use XF\Mvc\Entity\Entity;
use XF\Mvc\Entity\Structure;

class Thumbnail extends Entity
{
    protected function _postSave()
    {
        $this->fastUpdate('thumbnail_date', \XF::$time);
    }

    protected function _postDelete()
    {
        /** @var \DC\Thumbnail\Repository\Thumbnail $thumbnail */
        $thumbnailRepo = $this->repository('DC\Thumbnail:Thumbnail');

        $thumbnailRepo->deleteThumbnailImage($this);
    }
    
    public static function getStructure(Structure $structure)
    {
        $structure->table = 'xf_dcThumbnail_thumbail';
        $structure->primaryKey = 'thread_id';
        $structure->shortName = 'DC\Thumbnail:Thumbnail';
        $structure->columns = [
            'thread_id'             => ['type' => self::UINT, 'required' => true],
            'thumbnail_url'         => ['type' => self::STR, 'default' => ''],
            'upload_url'            => ['type' => self::STR, 'default' => ''],
            'thumbnail_date'        => ['type' => self::UINT, 'default' => \XF::$time]
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
}