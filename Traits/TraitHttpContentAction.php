<?php
/**
 * @package Goomento_Core
 * @link https://github.com/Goomento/Core
 */

declare(strict_types=1);

namespace Goomento\PageBuilder\Traits;

use Goomento\Core\Traits\TraitHttpAction;
use Goomento\PageBuilder\Helper\StaticContent;
use Goomento\PageBuilder\Api\Data\ContentInterface;
use Magento\Framework\Exception\LocalizedException;

/**
 * Trait TraitHttpContentAction
 * @package Goomento\PageBuilder\Traits
 * @property ContentInterface $content
 */
trait TraitHttpContentAction
{
    use TraitHttpAction;

    /**
     * @var ContentInterface|null
     */
    protected $content;

    /**
     * @return ContentInterface|null
     * @throws LocalizedException
     */
    public function getContent(bool $force = false)
    {
        if (null === $this->content) {
            $this->content = false;
            $contentId = $this->getRequest()->getParam('content_id');
            $contentId = (int) $contentId;
            if ($contentId !== 0) {
                $this->content = StaticContent::get($contentId);
            }
        }

        if (true === $force && !$this->content) {
            throw new LocalizedException(
                __('Page content not found')
            );
        }

        return $this->content;
    }
}