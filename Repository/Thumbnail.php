<?php

namespace DC\Thumbnail\Repository;

use XF\Mvc\Entity\Repository;
use Exception;

class Thumbnail extends Repository
{
    public function getThreadImages(\XF\Entity\Thread $thread)
    {
        $attachments = $this->getThreadAttachments($thread);

        $externalImages = $this->getThreadExternalImages($thread);

        $youtubeImages = $this->getThreadYoutubeImages($thread);

        $vimeoImages = $this->getThreadVimeoImages($thread);

        $dailymotionImages = $this->getThreadDailymotionImages($thread);

        return array_merge($attachments, $externalImages, $youtubeImages, $vimeoImages, $dailymotionImages);
    }

    public function getThreadAttachments(\XF\Entity\Thread $thread)
    {
        $images = [];

        $firstPost = $thread->FirstPost;

        if (!$firstPost)
        {
            return $images;
        }
        
        foreach($firstPost->Attachments AS $attachment)
        {
            if ($attachment->has_thumbnail)
            {
                $images[] = $attachment->getDirectUrl(true);
            }
        }

        return $images;
    }

    public function getThreadExternalImages(\XF\Entity\Thread $thread)
    {
        $images = [];

        if (!$this->options()->dcThumbnail_enable_external_images)
        {
            return $images;
        }

        $firstPost = $thread->FirstPost;

        if (!$firstPost)
        {
            return $images;
        }
        
        preg_match_all('/\[IMG(.*?)\](.+?)\[\/IMG\]/i', $firstPost->message, $matches);
        if ($matches)
        {
            $urls = $matches[2];

            $strFormatter = $this->app()->stringFormatter();

            foreach($urls AS $url)
            {
                $linkInfo = $strFormatter->getLinkClassTarget($url);
                if ($linkInfo['local'])
                {
                    $images[] = $url;
                }
                else
                {
                    $urlProxied = $this->app()->stringFormatter()->getProxiedUrlIfActive('image', $url);
                    if ($urlProxied)
                    {
                        $images[] = $urlProxied;
                    }
                    else
                    {
                        $images[] = $url;
                    }
                }
            }
        }

        return $images;
    }

    public function getThreadYoutubeImages(\XF\Entity\Thread $thread)
    {
        $images = [];

        if (!$this->options()->dcThumbnail_enable_media_images)
        {
            return $images;
        }

        $firstPost = $thread->FirstPost;

        if (!$firstPost)
        {
            return $images;
        }
        
        preg_match_all('#\[media=youtube\](.+?)\[/media\]#i', $firstPost->message, $matchesYtb);

        if ($matchesYtb)
        {
            $urls = $matchesYtb[0];

            foreach ($urls AS $url)
            {
                preg_match('#\[media=youtube\](.+?)\[/media\]#i', $url, $matchUrlYtb);

                if (@file_get_contents('https://img.youtube.com/vi/'.$matchUrlYtb[1].'/mqdefault.jpg'))
                {
                    $url = 'https://img.youtube.com/vi/'.$matchUrlYtb[1].'/maxresdefault.jpg';

                    if (!file_exists($url))
                    {
                        $url = 'https://img.youtube.com/vi/'.$matchUrlYtb[1].'/mqdefault.jpg';
                    }

                    $images[] = $url;
                }
            }
        }

        return $images;
    }

    public function getThreadVimeoImages(\XF\Entity\Thread $thread)
    {
        $images = [];

        if (!$this->options()->dcThumbnail_enable_media_images)
        {
            return $images;
        }

        $firstPost = $thread->FirstPost;

        if (!$firstPost)
        {
            return $images;
        }
        
        preg_match_all('#\[media=vimeo\](.+?)\[/media\]#i', $firstPost->message, $matchesVimeo);

        if ($matchesVimeo)
        {
            $urls = $matchesVimeo[0];

            foreach ($urls AS $url)
            {
                preg_match('#\[media=vimeo\](.+?)\[/media\]#i', $url, $matchUrlVimeo);

                if (@file_get_contents('https://vimeo.com/api/v2/video/'.$matchUrlVimeo[1].'.json'))
                {
                    $url = file_get_contents('https://vimeo.com/api/v2/video/'.$matchUrlVimeo[1].'.json');

                    $url = json_decode($url);

                    $images[] = $url[0]->thumbnail_medium;
                }
            }
        }

        return $images;
    }

    public function getThreadDailymotionImages(\XF\Entity\Thread $thread)
    {
        $images = [];

        if (!$this->options()->dcThumbnail_enable_media_images)
        {
            return $images;
        }

        $firstPost = $thread->FirstPost;

        if (!$firstPost)
        {
            return $images;
        }
        
        preg_match_all('#\[media=dailymotion\](.+?)\[/media\]#i', $firstPost->message, $matchesDaily);

        if ($matchesDaily)
        {
            $urls = $matchesDaily[0];

            foreach ($urls AS $url)
            {
                preg_match('#\[media=dailymotion\](.+?)\[/media\]#i', $url, $matchUrlDaily);

                if (@file_get_contents('https://www.dailymotion.com/thumbnail/video/'.$matchUrlDaily[1]))
                {
                    $url = 'https://www.dailymotion.com/thumbnail/video/'.$matchUrlDaily[1];

                    $images[] = $url;
                }
            }
        }

        return $images;
    }

    public function setThumbnailFromUpload($upload, \DC\Thumbnail\Entity\Thumbnail $thumbnail)
    {
        $upload->requireImage();

        if (!$upload->isValid())
		{
			throw new \XF\PrintableException(\XF::phrase('unexpected_error_occurred'));
        }
        
        $target = 'data://dc_thumbnails/'.$thumbnail->thread_id.'.jpg';

        try
		{
			$image = \XF::app()->imageManager->imageFromFile($upload->getTempFile());

            $newTempFile = \XF\Util\File::getTempFile();

			if ($newTempFile && $image->save($newTempFile))
			{
				$output = $newTempFile;
			}
			unset($image);
			
			\XF\Util\File::copyFileToAbstractedPath($output, $target);

            $thumbnailImage = 'data://dc_thumbnails/'.$thumbnail->thread_id.'.jpg';

            if (\XF\Util\File::abstractedPathExists($thumbnailImage))
            {
                $thumbnail->upload_url = $this->app()->applyExternalDataUrl('dc_thumbnails/'.$thumbnail->thread_id.'.jpg?'.$thumbnail->thumbnail_date, true);
                $thumbnail->save();
            }
            else
            {
                throw new \XF\PrintableException(\XF::phrase('file_not_found'));
            }
		}
		catch (Exception $e)
		{
			throw new \XF\PrintableException(\XF::phrase('unexpected_error_occurred'));
		}

        return true;
    }

    public function deleteThumbnailImage(\DC\Thumbnail\Entity\Thumbnail $thumbnail)
    {
        \XF\Util\File::deleteFromAbstractedPath('data://dc_thumbnails/'.$thumbnail->thread_id.'.jpg');

        return true;
    }
}