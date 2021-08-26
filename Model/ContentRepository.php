<?php
/**
 * @package Goomento_PageBuilder
 * @link https://github.com/Goomento/PageBuilder
 */

declare(strict_types=1);

namespace Goomento\PageBuilder\Model;

use Goomento\PageBuilder\Api\Data;
use Goomento\PageBuilder\Api\ContentRepositoryInterface;
use Goomento\PageBuilder\Api\Data\ContentInterface;
use Goomento\PageBuilder\Helper\Authorization;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Goomento\PageBuilder\Model\ResourceModel\Content as ResourceContent;
use Goomento\PageBuilder\Model\ResourceModel\Content\CollectionFactory as ContentCollectionFactory;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class ContentRepository
 * @package Goomento\PageBuilder\Model
 */
class ContentRepository implements ContentRepositoryInterface
{
    /**
     * @var ResourceContent
     */
    private $resource;

    /**
     * @var ContentFactory
     */
    private $contentFactory;

    /**
     * @var ContentCollectionFactory
     */
    private $contentCollectionFactory;

    /**
     * @var Data\ContentSearchResultsInterfaceFactory
     */
    private $searchResultsFactory;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var CollectionProcessorInterface
     */
    private $collectionProcessor;
    /**
     * @var Authorization
     */
    private $authorizationHelper;

    /**
     * ContentRepository constructor.
     * @param ResourceContent $resource
     * @param ContentFactory $contentFactory
     * @param ContentCollectionFactory $contentCollectionFactory
     * @param Data\ContentSearchResultsInterfaceFactory $searchResultsFactory
     * @param StoreManagerInterface $storeManager
     * @param CollectionProcessorInterface|null $collectionProcessor
     * @param Authorization $authorizationHelper
     */
    public function __construct(
        ResourceContent $resource,
        ContentFactory $contentFactory,
        ContentCollectionFactory $contentCollectionFactory,
        Data\ContentSearchResultsInterfaceFactory $searchResultsFactory,
        StoreManagerInterface $storeManager,
        CollectionProcessorInterface $collectionProcessor,
        Authorization $authorizationHelper
    ) {
        $this->resource = $resource;
        $this->contentFactory = $contentFactory;
        $this->contentCollectionFactory = $contentCollectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->storeManager = $storeManager;
        $this->collectionProcessor = $collectionProcessor;
        $this->authorizationHelper = $authorizationHelper;
    }

    /**
     * @inheritDoc
     */
    public function save(ContentInterface $content)
    {
        try {
            $this->validateStatus($content);
            $this->validateContentType($content);
            $this->setStoreId($content);
            $this->resource->save($content);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(
                __('Could not save the page: %1', $exception->getMessage()),
                $exception
            );
        }
        return $content;
    }

    /**
     * @param ContentInterface $content
     * @throws LocalizedException
     */
    private function validateContentType(ContentInterface $content)
    {
        if (!isset(Content::getAvailableTypes()[$content->getType()])) {
            throw new LocalizedException(
                __('Invalid content type: %1', $content->getType())
            );
        }
    }

    /**
     * @param ContentInterface $content
     * @throws NoSuchEntityException
     */
    private function setStoreId(ContentInterface $content)
    {
        if ($content->getStoreId() === null) {
            $storeId = $this->storeManager->getStore()->getId();
            $storeId = [0, $storeId];
            $content->setStoreId($storeId);
        }
    }


    /**
     * @param ContentInterface $content
     * @throws LocalizedException
     */
    private function validateStatus(ContentInterface $content)
    {
        if (!isset(Content::getAvailableStatuses()[$content->getStatus()])) {
            throw new LocalizedException(
                __('Invalid content status: %1', $content->getStatus())
            );
        }

        if (
            $content->getStatus() === ContentInterface::STATUS_PUBLISHED &&
            $content->getOrigData('status') !== ContentInterface::STATUS_PUBLISHED &&
            !$this->authorizationHelper->isAllowed($content->getRoleName('publish'))
        ) {
            throw new LocalizedException(
                __('You can\'t publish this content')
            );
        }
    }

    /**
     * @inheritDoc
     */
    public function getById($contentId)
    {
        $content = $this->contentFactory->create();
        $this->resource->load($content, $contentId);
        if (!$content->getId()) {
            throw new NoSuchEntityException(__('The content with the "%1" ID doesn\'t exist.', $contentId));
        }
        $content->setOrigData();
        return $content;
    }

    /**
     * @inheritDoc
     */
    public function getList(SearchCriteriaInterface $searchCriteria)
    {
        $collection = $this->contentCollectionFactory->create();
        $this->collectionProcessor->process($searchCriteria, $collection);
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($searchCriteria);
        $searchResults->setItems($collection->getItems() ?: []);
        $searchResults->setTotalCount($collection->getSize());
        return $searchResults;
    }

    /**
     * @inheritDoc
     */
    public function delete(ContentInterface $page)
    {
        try {
            $this->resource->delete($page);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(
                __(
                    'Could not delete the content: %1',
                    $exception->getMessage()
                )
            );
        }
        return true;
    }

    /**
     * @inheritDoc
     */
    public function deleteById($pageId)
    {
        return $this->delete($this->getById($pageId));
    }
}