<?php
/**
 * @package Goomento_PageBuilder
 * @link https://github.com/Goomento/PageBuilder
 */

declare(strict_types=1);

namespace Goomento\PageBuilder\Controller\Adminhtml\Content;

use Exception;
use Goomento\PageBuilder\Api\Data\ContentInterface;
use Goomento\PageBuilder\Helper\StaticEncryptor;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class Save
 * @package Goomento\PageBuilder\Controller\Adminhtml\Content
 */
class Save extends AbstractContent implements HttpPostActionInterface
{
    use TraitContent;

    /**
     * @inheritdoc
     */
    public function execute()
    {
        $data = (array) $this->getRequest()->getPostValue();

        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        if (!empty($data)) {
            try {
                $contentType = $this->getContentType();
                $data['type'] = $contentType;

                $data = $this->dataProcessor->filter($data);
                $isNewObject = !((int) $data['content_id']);

                $content = $this->getContent(!$isNewObject);

                if (!$this->_authorization->isAllowed($this->getContentResourceName('save'))) {
                    throw new LocalizedException(
                        __('Sorry, you need permissions to save this content.')
                    );
                }

                if (!$content) {
                    $content = $this->contentFactory->create();
                } elseif (empty($contentType) || $content->getType() !== $contentType) {
                    throw new LocalizedException(
                        __('Invalid content type: %1', $contentType)
                    );
                }
            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
                return $resultRedirect->setRefererUrl();
            } catch (Exception $e) {
                $this->logger->error($e);
                $this->messageManager->addErrorMessage(__('Something went wrong when saving the content.'));
                return $resultRedirect->setRefererUrl();
            }

            try {
                if (!$this->dataProcessor->validate($data)) {
                    return $resultRedirect->setPath('*/*/edit', ['page_id' => $content->getId(), '_current' => true]);
                }

                $content
                    ->setStatus($data['status'])
                    ->setType($contentType);

                if (isset($data['store_id']) && $data['store_id']) {
                    $content->setStoreId($data['store_id']);
                }

                if ($content->isObjectNew()) {
                    $content->setAuthorId(
                        $this->userHelper->getCurrentAdminUser()->getId()
                    );
                }

                $content->setLastEditorId(
                    $this->userHelper->getCurrentAdminUser()->getId()
                );

                $content->setTitle($data['title']);

                if (!empty($data['content_data'])) {
                    $contentData = base64_decode($data['content_data']);
                    if ($contentData && self::isJson($contentData)) {
                        if ($contentData !== $content->getData(ContentInterface::ELEMENTS)) {
                            $content->setData(ContentInterface::ELEMENTS, $contentData);
                        }
                    }
                }

                $identifier = isset($data['identifier']) ? trim($data['identifier']) : '';
                if (empty($identifier)) {
                    $identifier = $content->getType() . '_' . StaticEncryptor::uniqueString();
                }

                if ($content->getIdentifier() !== $identifier) {
                    $content->setIdentifier($identifier);
                }

                $content = $this->contentRepository->save($content);
                $this->contentManagement->createRevision($content);
                $this->messageManager->addSuccessMessage(__('You saved the content.'));
                return $this->processResultRedirect($content, $resultRedirect, $data);
            } catch (LocalizedException $e) {
                $this->messageManager->addExceptionMessage($e->getPrevious() ?: $e);
            } catch (Exception $e) {
                $this->messageManager->addExceptionMessage($e, __('Something went wrong while saving the content.'));
            }

            $this->dataPersistor->set('pagebuilder_content', $data);
            return $resultRedirect->setPath('*/*/edit', [
                'content_id' => $this->getRequest()->getParam('content_id'),
                'type' => $contentType,
            ]);
        }

        return $resultRedirect->setRefererUrl();
    }

    /**
     * @param $model
     * @param $resultRedirect
     * @param $data
     * @return mixed
     * @throws LocalizedException
     */
    protected function processResultRedirect($model, $resultRedirect, $data)
    {
        if ($this->getRequest()->getParam('back', false) === 'duplicate') {
            $content = $this->contentFactory->create(['data' => $data]);
            $content->setId(null);
            $title = $content->getTitle();
            $title .= ' ' .  __('( Duplicated from #%1 )', $model->getId());
            $content->setTitle($title);
            $content->setStatus(ContentInterface::STATUS_PENDING);
            $identifier = $model->getIdentifier();
            $identifiers = explode('_', $identifier);
            array_pop($identifiers);
            $identifiers[] = StaticEncryptor::uniqueString();

            $content->setIdentifier(implode('_', $identifiers));
            $this->contentRepository->save($content);
            $this->messageManager->addSuccessMessage(__('You duplicated the content.'));
            return $resultRedirect->setPath(
                '*/*/edit',
                [
                    'content_id' => $content->getId(),
                    'type' => $content->getType(),
                    '_current' => true
                ]
            );
        }
        $this->dataPersistor->clear('pagebuilder_content');
        if ($this->getRequest()->getParam('back')) {
            return $resultRedirect->setPath('*/*/edit', [
                'content_id' => $model->getId(),
                'type' => $model->getType(),
                '_current' => true
            ]);
        }

        return $resultRedirect->setPath('*/*/grid', [
            'type' => $model->getType()
        ]);
    }

    /**
     * @param $string
     * @return bool
     */
    protected static function isJson($string)
    {
        return !(json_decode($string, true) == null);
    }
}
