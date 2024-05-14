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

class NewAction extends \Mentalworkz\EmailContent\Controller\Adminhtml\Content implements HttpGetActionInterface
{
    /**
     * @var \Magento\Backend\Model\View\Result\ForwardFactory
     */
    protected $resultForwardFactory;

    /**
     * NewAction constructor.
     * @param \Magento\Backend\App\Action\Context $context
     * @param ContentFactory $contentModel
     * @param ContentResource $contentResource
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        ContentFactory $contentModel,
        ContentResource $contentResource,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory
    ) {
        $this->resultForwardFactory = $resultForwardFactory;
        parent::__construct($context, $contentModel, $contentResource, $coreRegistry);
    }

    /**
     * @return \Magento\Backend\Model\View\Result\Forward
     */
    public function execute(): \Magento\Backend\Model\View\Result\Forward
    {
        /** @var \Magento\Framework\Controller\Result\Forward $resultForward */
        $resultForward = $this->resultForwardFactory->create();
        return $resultForward->forward('edit');
    }
}
