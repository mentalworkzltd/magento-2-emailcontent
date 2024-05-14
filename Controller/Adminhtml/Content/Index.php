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
use Magento\Framework\App\Request\DataPersistorInterface;

class Index extends \Mentalworkz\EmailContent\Controller\Adminhtml\Content implements HttpGetActionInterface
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var DataPersistorInterface
     */
    protected $dataPersistor;

    /**
     * Index constructor.
     * @param \Magento\Backend\App\Action\Context $context
     * @param ContentFactory $contentModel
     * @param ContentResource $contentResource
     * @param \Magento\Framework\Registry $coreRegistry
     * @param DataPersistorInterface $dataPersistor
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        ContentFactory $contentModel,
        ContentResource $contentResource,
        \Magento\Framework\Registry $coreRegistry,
        DataPersistorInterface $dataPersistor,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->dataPersistor = $dataPersistor;
        parent::__construct($context, $contentModel, $contentResource,$coreRegistry);
    }

    /**
     * Index action
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute(): \Magento\Framework\View\Result\Page
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $this->initPage($resultPage)->getConfig()->getTitle()->prepend(__('Email Content'));

        $this->dataPersistor->clear('emailcontent');

        return $resultPage;
    }
}
