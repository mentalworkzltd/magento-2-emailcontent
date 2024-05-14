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


namespace Mentalworkz\EmailContent\Helper;

use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\ProductMetadataInterface;
use Magento\Framework\Module\Manager as ModuleManager;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Backend\Model\Auth\Session;
use Mentalworkz\EmailContent\Logger\Logger as MwzEcLogger;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{

    private const CONFIG_PATH_EMAILCONTENT_ENABLED        = 'mwz_emailcontent/email_content/general/isenabled';
    private const CONFIG_PATH_EMAILCONTENT_DEBUG_ENABLED  = 'mwz_emailcontent/email_content/general/isdebugenabled';
    private const CONFIG_PATH_EMAILCONTENT_TABLE          = 'mwz_emailcontent/email_content/content/wrapper/tablewrapper';
    private const CONFIG_PATH_EMAILCONTENT_TABLE_PADDING  = 'mwz_emailcontent/email_content/content/wrapper/tablepadding';
    private const CONFIG_PATH_EMAILCONTENT_TABLE_MAXWIDTH = 'mwz_emailcontent/email_content/content/wrapper/tablemaxwidth';
    private const CONFIG_PATH_EMAILCONTENT_PRODUCT_ATTRIBUTES = 'mwz_emailcontent/email_content/content/display_conditions/product_attributes';
    public const DEFAULT_PRODUCT_ATTRIBUTES = ['name','price','sku'];

    /**
     * @var ProductMetadataInterface
     */
    protected $productMetadata;

    /**
     * @var ModuleManager
     */
    protected $moduleManager;

    /**
     * @var Session
     */
    protected $session;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var MwzEcLogger
     */
    protected $mwzEcLogger;

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        ProductMetadataInterface $productMetadata,
        ModuleManager $moduleManager,
        Session $session,
        StoreManagerInterface $storeManager,
        MwzEcLogger $mwzEcLogger
    )
    {
        parent::__construct($context);
        $this->moduleManager = $moduleManager;
        $this->productMetadata = $productMetadata;
        $this->storeManager = $storeManager;
        $this->session = $session;
        $this->mwzEcLogger = $mwzEcLogger;
    }

    /**
     * @return null|string
     */
    public function getMagentoVersion(): ?string
    {
        return $this->productMetadata->getVersion();
    }

    /**
     * Returns if module exists or not
     *
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function isModuleEnabled($moduleName): bool
    {
        return $this->moduleManager->isEnabled($moduleName);
    }

    /**
     * @return \Magento\Store\Api\Data\StoreInterface
     */
    public function getCurrentStore(): \Magento\Store\Api\Data\StoreInterface
    {
        return $this->storeManager->getStore();
    }

    /**
     * @return bool
     */
    public function isAdminLoggedin(): bool
    {
        return $this->session->getUser() && $this->session->getUser()->getId();
    }

    /**
     * @return int|null
     */
    public function isEnabled($storeId = null): ?int
    {
        return (int)$this->scopeConfig->getValue(self::CONFIG_PATH_EMAILCONTENT_ENABLED, ScopeInterface::SCOPE_STORE, $storeId);
    }

    /**
     * @return int|null
     */
    public function isDebugEnabled($storeId = null): ?int
    {
        return (int)$this->scopeConfig->getValue(self::CONFIG_PATH_EMAILCONTENT_DEBUG_ENABLED, ScopeInterface::SCOPE_STORE, $storeId);
    }

    /**
     * @return int|null
     */
    public function useContentTableWrapper($storeId = null): ?int
    {
        return (int)$this->scopeConfig->getValue(self::CONFIG_PATH_EMAILCONTENT_TABLE, ScopeInterface::SCOPE_STORE, $storeId);
    }

    /**
     * @return int|null
     */
    public function getContentTablePadding($storeId = null): ?int
    {
        return (int)$this->scopeConfig->getValue(self::CONFIG_PATH_EMAILCONTENT_TABLE_PADDING, ScopeInterface::SCOPE_STORE, $storeId);
    }

    /**
     * @return string|null
     */
    public function getContentTableMaxwidth($storeId = null): ?string
    {
        return $this->scopeConfig->getValue(self::CONFIG_PATH_EMAILCONTENT_TABLE_MAXWIDTH, ScopeInterface::SCOPE_STORE, $storeId);
    }

    /**
     * @param null $storeId
     * @return array
     */
    public function getEnabledProductAttributes($storeId = null): array
    {
        $enabledAttributesStr = $this->scopeConfig->getValue(self::CONFIG_PATH_EMAILCONTENT_PRODUCT_ATTRIBUTES, ScopeInterface::SCOPE_STORE, $storeId);
        $enabledAttributes = $enabledAttributesStr ? explode(',', $enabledAttributesStr) : [];
        return array_merge(self::DEFAULT_PRODUCT_ATTRIBUTES, $enabledAttributes);
    }

    /**
     * @return array|null
     */
    public function getDefaultWrapperConfig (): ?array
    {
        if($useWrapper = $this->useContentTableWrapper()){
            return [
                'wrapper' => 1,
                'padding' => $this->getContentTablePadding(),
                'maxwidth' => $this->getContentTableMaxwidth()
            ];
        }
        return null;
    }

    /**
     * @param int $id
     * @param string $identifier
     * @return string
     */
    public function getDirective( ?int $id = null, ?string $identifier = null): string
    {
        if($id && $identifier){
            return '{{block class="Mentalworkz\\EmailContent\\Block\\Content" identifier="' . $identifier . '" area="frontend" order_id=$order.increment_id customer_id=$customer.entity_id }}';
        }else{
            return 'Save the content to see the directive...';
        }
    }

    /**
     * @param string $message
     * @param array $params
     * @param string $type
     */
    public function addLog(string $message, array $params = [], string $type = '')
    {
        if(empty($type) && !$this->isDebugEnabled()){
            return;
        }

        switch($type){
            case 'error':
                $this->mwzEcLogger->addCritical((string)__('ERROR: ' . $message, $params));
                break;
            default:
                $this->mwzEcLogger->addDebug((string)__($message, $params));
        }
    }

}