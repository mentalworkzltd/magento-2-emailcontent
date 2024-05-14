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

namespace Mentalworkz\EmailContent\Model\Config\Source;

use Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory as AttributeFactory;
use Mentalworkz\EmailContent\Helper\Data as MwzHelper;

class ProductAttributes extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{

    /**
     * @var AttributeFactory
     */
    protected $attributeFactory;

    public function __construct(
        AttributeFactory $attributeFactory
    ) {
        $this->attributeFactory = $attributeFactory;
    }

    /**
     * Get all product attributes
     *
     * @return array
     */
    public function getAllOptions(): array
    {
        $this->_options = [];
        $attributeCollection = $this->attributeFactory->create();
        foreach ($attributeCollection as $attribute) {
            // Some product attributes always included by default
            if(in_array($attribute->getAttributeCode(), MwzHelper::DEFAULT_PRODUCT_ATTRIBUTES)){
                continue;
            }
            $this->_options[] = [
                'value' => $attribute->getAttributeCode(),
                'label' => $attribute->getFrontendLabel()
            ];
        }
        return $this->_options;
    }

}