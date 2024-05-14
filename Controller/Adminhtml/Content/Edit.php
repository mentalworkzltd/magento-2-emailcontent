<?php
/**
 * Mentalworkz
 *
 * @category    Mentalworkz
 * @package     Mentalworkz_EmailContent
 * @copyright   Copyright (c) Mentalworkz (https://www.mentalworkz.co.uk/)
 * @author      Shaun Clifford
 */
declare(strict_types=1);

namespace Mentalworkz\EmailContent\Controller\Adminhtml\Content;

use Magento\Framework\App\Action\HttpGetActionInterface;
use Mentalworkz\EmailContent\Model\ContentFactory;
use Mentalworkz\EmailContent\Model\ResourceModel\Content as ContentResource;

class Edit extends \Mentalworkz\EmailContent\Controller\Adminhtml\Content implements HttpGetActionInterface
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * Edit constructor.
     * @param \Magento\Backend\App\Action\Context $context
     * @param ContentFactory $contentModel
     * @param ContentResource $contentResource
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        ContentFactory $contentModel,
        ContentResource $contentResource,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory
    ) {
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct($context, $contentModel, $contentResource,$coreRegistry);
    }

    /**
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute(): \Magento\Framework\View\Result\Page
    {
        $model = $this->contentModel->create();
        $id = $this->getRequest()->getParam('id');
        if ($id) {

            $this->contentResource->load($model, $id);

            if (!$model->getContentId()) {
                $this->messageManager->addErrorMessage(__('This Email Content no longer exists.'));
                /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
                $resultRedirect = $this->resultRedirectFactory->create();
                return $resultRedirect->setPath('*/*/');
            }
        }

        $this->coreRegistry->register('emailcontent', $model);

        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $this->initPage($resultPage)->addBreadcrumb(
            $id ? __('Edit Email Content') : __('New Email Content'),
            $id ? __('Edit Email Content') : __('New Email Content')
        );
        $resultPage->getConfig()->getTitle()->prepend(__('Email Content'));
        $resultPage->getConfig()->getTitle()->prepend($id ? __('Edit Email Content') : __('New Email Content'));
        return $resultPage;
    }
}
