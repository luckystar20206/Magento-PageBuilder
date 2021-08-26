<?php
/**
 * @package Goomento_PageBuilder
 * @link https://github.com/Goomento/PageBuilder
 */

declare(strict_types=1);

namespace Goomento\PageBuilder\Controller\Adminhtml\Content;

use Magento\Backend\App\Action;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Result\PageFactory;
use Goomento\PageBuilder\Traits\TraitHttpPage;

/**
 * Class Grid
 * @package Goomento\PageBuilder\Controller\Adminhtml\Content
 */
class Grid extends Action implements HttpGetActionInterface
{
    use TraitHttpPage;
    use TraitContent;

    const DATA_KEY = 'pagebuilder_content';

    /**
     * @var PageFactory
     */
    protected $pageFactory;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory
    ) {
        parent::__construct($context);
        $this->pageFactory = $resultPageFactory;
    }

    /**
     * @inheritdoc
     */
    public function execute()
    {
        try {
            $dataPersistor = $this->_objectManager->get(DataPersistorInterface::class);
            $dataPersistor->clear(static::DATA_KEY);
            return $this->renderPage();
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage(
                $e->getMessage()
            );
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(
                __('Something went wrong when display contents.')
            );
        }

        return $this->resultRedirectFactory->create()->setRefererUrl();
    }

    /**
     * @inheritDoc
     */
    protected function _isAllowed()
    {
        try {
            return $this->_authorization->isAllowed(
                $this->getContentResourceName('grid')
            );
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage(
                $e->getMessage()
            );
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(
                __('Something went wrong when display content(s)')
            );
        }

        return false;
    }

    /**
     * @inheritDoc
     */
    protected function getPageConfig()
    {
        $type = $this->getContentType();
        $type = (string) __(ucfirst($type) . 's');
        return [
            'active_menu' => 'Goomento_PageBuilder::' . $this->getContentType(),
            'title' => $type,
            'breadcrumb' => [
                [__('Pages'), $type],
                [__('Manage %1', $type), __('Manage %1', $type)]
            ],
            'handler' => $this->getContentLayoutName('grid')
        ];
    }
}