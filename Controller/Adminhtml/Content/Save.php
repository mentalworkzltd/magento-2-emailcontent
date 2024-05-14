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

use Braintree\Exception;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Registry;
use Mentalworkz\EmailContent\Model\ContentFactory;
use Mentalworkz\EmailContent\Model\ResourceModel\Content as ContentResource;

class Save extends \Mentalworkz\EmailContent\Controller\Adminhtml\Content implements HttpPostActionInterface
{
    /**
     * @var DataPersistorInterface
     */
    protected $dataPersistor;

    /**
     * Save constructor.
     * @param Context $context
     * @param ContentFactory $contentModel
     * @param ContentResource $contentResource
     * @param Registry $coreRegistry
     * @param DataPersistorInterface $dataPersistor
     */
    public function __construct(
        Context $context,
        ContentFactory $contentModel,
        ContentResource $contentResource,
        Registry $coreRegistry,
        DataPersistorInterface $dataPersistor
    ) {
        $this->dataPersistor = $dataPersistor;
        parent::__construct($context, $contentModel, $contentResource, $coreRegistry);
    }

    /**
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute(): \Magento\Backend\Model\View\Result\Redirect
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        $data = $this->getRequest()->getPostValue();

        if ($data) {

            /** @var \Magento\Cms\Model\Block $model */
            $model = $this->contentModel->create();

            if (empty($data['content_id'])) {
                $data['content_id'] = null;
            }else {
                try {
                    $this->contentResource->load($model, $data['content_id']);
                    if(!$model->getContentId()){
                        throw new LocalizedException(__('This email content no longer exists.'));
                    }
                } catch (LocalizedException $e) {
                    $this->messageManager->addErrorMessage($e->getMessage());
                    return $resultRedirect->setPath('*/*/');
                }
            }

            // Remove any incorrectly formatted display conditions
            $data['display_conditions'] = $this->validateDisplayConditions($data['display_conditions']);

            try {
                $data['store_id'] = $data['store_id'] ? implode(',', $data['store_id']) : '0';
                $data['sort_order'] = $data['sort_order'] ?? '0';
                $data['identifier'] = trim($data['identifier']);
                $model->setData($data);
                $this->contentResource->save($model);
                $this->messageManager->addSuccessMessage(__('You saved the email content.'));

                if($data['back'] ==='continue' || $data['back'] === 'duplicate'){
                    if($data['back'] === 'duplicate'){
                        $data['content_id'] = null;
                        $this->messageManager->addSuccessMessage(__('You duplicated the email content.'));
                    }
                    $this->dataPersistor->set('emailcontent', $data);
                }else{
                    $this->dataPersistor->clear('emailcontent');
                }

                return $this->processReturn($model->getContentId(), $data, $resultRedirect);
            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addExceptionMessage($e, __('Something went wrong while saving the email content.'));
            }

            $this->dataPersistor->set('emailcontent', $data);
            return $resultRedirect->setPath('*/*/edit', ['id' => $data['content_id']]);
        }
        return $resultRedirect->setPath('*/*/');
    }


    /**
     * Every condition should have at least one value
     *
     * @param string $jsonDisplayConditions
     * @return string
     */
    private function validateDisplayConditions (string $jsonDisplayConditions): ?string
    {
        if(!empty($jsonDisplayConditions)){
            $displayConditions = json_decode($jsonDisplayConditions, true);
            foreach($displayConditions as $key => $condition){
                if(empty($condition['values'])){
                    unset($displayConditions[$key]);
                }
            }
            return json_encode($displayConditions);
        }
        return '';
    }

    /**
     * @param $id
     * @param $data
     * @param $resultRedirect
     * @return \Magento\Framework\Controller\ResultInterface
     */
    private function processReturn($id, $data, $resultRedirect): \Magento\Framework\Controller\ResultInterface
    {
        $redirect = $data['back'] ?? 'close';

        if ($redirect ==='continue') {
            $resultRedirect->setPath('*/*/edit', ['id' => $id]);
        } else if ($redirect === 'duplicate') {
            $resultRedirect->setPath('*/*/newaction');
        } else if ($redirect === 'close') {
            $resultRedirect->setPath('*/*/');
        }
        return $resultRedirect;
    }

}
