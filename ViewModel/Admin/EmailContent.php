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

namespace Mentalworkz\EmailContent\ViewModel\Admin;

use Magento\Framework\App\Request\Http;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\Registry;
use Magento\Store\Api\StoreRepositoryInterface;
use Magento\Customer\Model\ResourceModel\Group\Collection as CustomerGroup;
use Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory as AttributeFactory;
use Magento\Catalog\Model\Product\AttributeSet\Options as AttributeSets;
use Magento\Shipping\Model\Config as ShippingMethods;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Catalog\Model\CategoryFactory;
use Magento\Catalog\Model\ResourceModel\Category as ResourceCategory;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory;
use Mentalworkz\EmailContent\Helper\Data as MwzHelper;

class EmailContent implements \Magento\Framework\View\Element\Block\ArgumentInterface
{

    /**
     * @var
     */
    protected $registry;

    /**
     * @var DataPersistorInterface
     */
    protected $dataPersistor;

    /**
     * @var StoreRepositoryInterface
     */
    protected $storeRepository;

    /**
     * @var CustomerGroup
     */
    protected $customerGroup;

    /**
     * @var AttributeSets
     */
    protected $attributeSets;

    /**
     * @var AttributeFactory
     */
    protected $attributeFactory;

    /**
     * @var ShippingMethods
     */
    protected $shippingMethods;

    /**
     * @var CategoryManagement
     */
    protected $categoryManagement;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var CategoryFactory
     */
    protected $categoryFactory;

    /**
     * @var ResourceCategory
     */
    protected $categoryResource;

    /**
     * @var CollectionFactory
     */
    protected $categoryCollectionFactory;

    /**
     * @var MwzHelper
     */
    protected $mwzHelper;

    /**
     * @var \Mentalworkz\EmailContent\Model\Content
     */
    protected $emailContent;

    /**
     * EmailContent constructor.
     * @param Registry $coreRegistry
     * @param DataPersistorInterface $dataPersistor
     * @param Http $request
     * @param StoreRepositoryInterface $storeRepository
     * @param CustomerGroup $customerGroup
     * @param AttributeSets $attributeSets
     * @param AttributeFactory $attributeFactory
     * @param ShippingMethods $shippingMethods
     * @param CategoryFactory $categoryFactory
     * @param ResourceCategory $categoryResource
     * @param CollectionFactory $categoryCollectionFactory
     * @param ScopeConfigInterface $scopeConfig
     * @param MwzHelper $mwzHelper
     */
    public function __construct(
        Registry $coreRegistry,
        DataPersistorInterface $dataPersistor,
        Http $request,
        StoreRepositoryInterface $storeRepository,
        CustomerGroup $customerGroup,
        AttributeSets $attributeSets,
        AttributeFactory $attributeFactory,
        ShippingMethods $shippingMethods,
        CategoryFactory $categoryFactory,
        ResourceCategory $categoryResource,
        CollectionFactory $categoryCollectionFactory,
        ScopeConfigInterface $scopeConfig,
        MwzHelper $mwzHelper
    ) {
        $this->registry = $coreRegistry;
        $this->dataPersistor = $dataPersistor;
        $this->request = $request;
        $this->storeRepository = $storeRepository;
        $this->customerGroup = $customerGroup;
        $this->attributeSets = $attributeSets;
        $this->attributeFactory = $attributeFactory;
        $this->shippingMethods = $shippingMethods;
        $this->categoryFactory = $categoryFactory;
        $this->categoryResource = $categoryResource;
        $this->categoryCollectionFactory = $categoryCollectionFactory;
        $this->scopeConfig = $scopeConfig;
        $this->mwzHelper = $mwzHelper;
    }

    /**
     * @return mixed|null
     */
    protected function getEmailContent ()
    {
        if(!$this->emailContent){
            if($emailContent = $this->dataPersistor->get('emailcontent')){
                $this->emailContent = $emailContent;
            }elseif($emailContent = $this->registry->registry('emailcontent')) {
                $this->emailContent = $emailContent;
            }
        }

        return $this->emailContent;
    }

    /**
     * @return array
     */
    public function getConditionsConfig (): array
    {
        $displayConditions = $this->getDisplayConditions();

        $conditionConfig = [
            'condition_config' => [
                'active_display_conditions' => $displayConditions,
                'select_options' => [
                    'boolean' => $this->getBooleanValues(),
                    'customer_group' => $this->getCustomerGroups(),
                    'product_category' => $this->getCategories(),
                    'product_attribute' => $this->getProductAttributes(),
                    'product_attribute_set' => $this->getAttributeSets(),
                    'shipping_method' => $this->getShippingMethods(),
                ]
            ]
        ];

        return $conditionConfig;
    }

    /**
     * @return array
     */
    public function getDefaultContentWrapperConfig (): array
    {
        return [
            'wrapper' => $this->mwzHelper->useContentTableWrapper(),
            'padding' => $this->mwzHelper->getContentTablePadding(),
            'maxwidth' => $this->mwzHelper->getContentTableMaxwidth()
        ];
    }

    /**
     * @return null|string
     */
    public function getContentWrapper(): ?string
    {
        $emailContent = $this->getEmailContent();
        return $emailContent ? $emailContent['content_wrapper'] : '';
    }

    /**
     * @return string
     */
    protected function getDisplayConditions (): string
    {
        $emailContent = $this->getEmailContent();
        return !empty($emailContent['display_conditions']) ? $emailContent['display_conditions'] : '';
    }

    /**
     * @return array
     */
    protected function getBooleanValues(): array
    {
        return [
            [
                'label' => 'True',
                'value' => 1
            ],
            [
                'label' => 'False',
                'value' => 0
            ]
        ];
    }

    /**
     * @return array
     */
    protected function getCustomerGroups(): array
    {
        $customerGroups = $this->customerGroup->toOptionArray();
        return $customerGroups;
    }

    /**
     * @return array
     */
    protected function getCategories(): array
    {
        $categories = $this->getCategoryCollection(true, 0, '', 0);
        $categoryList = [];
        foreach ($categories as $category)
        {
            $prefix = str_repeat('- ', $category->getLevel() - 1);
            $categoryList[] = [
                'label' => $prefix . ' ' . $category->getName(),
                'value' => ($prefix) ? $category->getEntityId() : 'optgroup'
                ];
        }
        return $categoryList;
    }

    /**
     * @param bool $isActive
     * @param int $level
     * @param string $sortBy
     * @param int $pageSize
     * @return ResourceCategory\Collection
     */
    protected function getCategoryCollection(bool $isActive = true, int $level = 0, string $sortBy = '', int $pageSize = 0): \Magento\Catalog\Model\ResourceModel\Category\Collection
    {
        $collection = $this->categoryCollectionFactory->create();
        $collection->addAttributeToSelect('*');

        // select only active categories
        if ($isActive) {
            $collection->addIsActiveFilter();
        }

        // select categories of certain level
        if ($level) {
            $collection->addLevelFilter($level);
        }

        // sort categories by some value
        if ($sortBy) {
            $collection->addOrderField($sortBy);
        }

        // select certain number of categories
        if ($pageSize) {
            $collection->setPageSize($pageSize);
        }

        return $collection;
    }

    /**
     * @return array
     */
    protected function getProductAttributes(): array
    {
        $enabledProductAttributes = $this->mwzHelper->getEnabledProductAttributes();
        if(empty($enabledProductAttributes)){
            return [];
        }

        $attributeCollection = $this->attributeFactory->create();
        $attributeData = [];
        foreach ($attributeCollection as $attribute) {
            if(!in_array($attribute->getAttributeCode(), $enabledProductAttributes)){
                continue;
            }
            $attributeData[] = [
                'value' => $attribute->getId() . '::' . $attribute->getAttributeCode(),
                'label' => $attribute->getFrontendLabel(),
                'input' => $attribute->getData('frontend_input'),
                'type' => $attribute->getData('backend_type')
            ];
        }
        return $attributeData;
    }

    /**
     * @return array
     */
    protected function getAttributeSets(): array
    {
        $attributeSets = $this->attributeSets->toOptionArray();
        return $attributeSets;
    }

    /**
     * @return array
     */
    protected function getShippingMethods(): array
    {
        $_shippingMethods = $this->shippingMethods->getActiveCarriers();
        $shippingMethods = [];
        foreach($_shippingMethods as $shippingCode => $shippingModel){
            if($carrierMethods = $shippingModel->getAllowedMethods()) {
                foreach ($carrierMethods as $methodCode => $method) {
                    $code = $shippingCode.'_'.$methodCode;
                    $carrierTitle = $this->scopeConfig->getValue('carriers/'. $shippingCode.'/title');
                    $shippingMethods[] = ['value'=>$code,'label'=>$carrierTitle];
                }
            }
        }
        return $shippingMethods;
    }

}
