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

namespace Mentalworkz\EmailContent\Controller\Adminhtml\Ajax;

use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Catalog\Model\Product\Attribute\Repository;

class GetAttributeOptions extends \Magento\Backend\App\Action
{

    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Mentalworkz_EmailContent::emailcontent';

    /**
     * @var JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var Validator
     */
    protected $formKeyValidator;

    /**
     * @var Repository
     */
    protected $attributeRepository;

    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        Validator $formKeyValidator,
        Repository $attributeRepository
    ) {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->formKeyValidator = $formKeyValidator;
        $this->attributeRepository = $attributeRepository;
        parent::__construct($context);
    }

    public function execute()
    {
        $attributeCode = $this->getRequest()->getParam('attribute_code');
        $response = ['error'=> false, 'message' => '', 'attribute_code' => $attributeCode,'options' => ''];
        if($attributeCode && $this->formKeyValidator->validate($this->getRequest())){
            if(false !== strpos($attributeCode, '::')){
                $attributeCodeParts = explode('::', $attributeCode);
                $attributeCode = $attributeCodeParts[1];
            }
            $response['options'] = $this->getAttributeOptions($attributeCode);
        }else{
            $response['error'] = true;
            $response['message'] = __('Invalid request data');
        }
        return $this->resultJsonFactory->create()->setData($response);
    }

    /**
     * @param $attributeCode
     * @return array
     */
    private function getAttributeOptions($attributeCode): array
    {
        $options = [];
        $attrOptions = $this->attributeRepository->get($attributeCode)->getOptions();
        foreach($attrOptions as $attrOption){
            if($attrOption['value']) {
                $options[] = [
                    'label' => (string) $attrOption['label'],
                    'value' => $attrOption['value']
                ];
            }
        }
        return $options;
    }

}