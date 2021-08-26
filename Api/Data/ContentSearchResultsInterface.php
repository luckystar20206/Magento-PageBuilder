<?php
/**
 * @package Goomento_PageBuilder
 * @link https://github.com/Goomento/PageBuilder
 */

declare(strict_types=1);

namespace Goomento\PageBuilder\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

/**
 * Interface ContentSearchResultsInterface
 * @package Goomento\PageBuilder\Api\Data
 */
interface ContentSearchResultsInterface extends SearchResultsInterface
{
    /**
     * Get contents list.
     *
     * @return ContentInterface[]
     */
    public function getItems();

    /**
     * Set contents list.
     *
     * @param ContentInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}