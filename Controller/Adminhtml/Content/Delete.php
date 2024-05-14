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

use Magento\Framework\App\Action\HttpPostActionInterface;

class Delete extends \Mentalworkz\EmailContent\Controller\Adminhtml\Content implements HttpPostActionInterface
{

    /**
     * Delete action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute(): \Magento\Backend\Model\View\Result\Redirect
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();

        $id = $this->getRequest()->getParam('id');
        if ($id) {
            try {

                $model = $this->contentModel->create();
                $this->contentResource->load($model, $id);
                $this->contentResource->delete($model);

                $this->messageManager->addSuccessMessage(__('You deleted the Email Content.'));
                return $resultRedirect->setPath('*/*/');

            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            }
        }else{
            $this->messageManager->addErrorMessage(__('We can\'t find the Email Content to delete.'));
        }

        return $resultRedirect->setPath('*/*/');
    }
}
