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

namespace Mentalworkz\EmailContent\Controller\Adminhtml;

use Mentalworkz\EmailContent\Model\ContentFactory;
use Mentalworkz\EmailContent\Model\ResourceModel\Content as ContentResource;

abstract class Content extends \Magento\Backend\App\Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Mentalworkz_EmailContent::emailcontent';

    /**
     * @var \Mentalworkz\EmailContent\Model\Content
     */
    protected $contentModel;

    /**
     * @var \Mentalworkz\EmailContent\Model\ResourceModel\Content
     */
    protected $contentResource;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry;

    /**
     * Content constructor.
     * @param \Magento\Backend\App\Action\Context $context
     * @param ContentFactory $contentModel
     * @param ContentResource $contentResource
     * @param \Magento\Framework\Registry $coreRegistry
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        ContentFactory $contentModel,
        ContentResource $contentResource,
        \Magento\Framework\Registry $coreRegistry
    ) {
        $this->contentModel = $contentModel;
        $this->contentResource = $contentResource;
        $this->coreRegistry = $coreRegistry;
        parent::__construct($context);
    }

    /**
     * Init page
     *
     * @param \Magento\Backend\Model\View\Result\Page $resultPage
     * @return \Magento\Backend\Model\View\Result\Page
     */
    protected function initPage(\Magento\Backend\Model\View\Result\Page $resultPage): \Magento\Backend\Model\View\Result\Page
    {
        $resultPage->setActiveMenu('Mentalworkz_EmailContent::emailcontent')
            ->addBreadcrumb(__('Mentalworkz'), __('Mentalworkz'))
            ->addBreadcrumb(__('Email Content'), __('Email Content'));
        return $resultPage;
    }
}
