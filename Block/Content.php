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


namespace Mentalworkz\EmailContent\Block;

use Magento\Framework\View\Element\Template\Context;
use Mentalworkz\EmailContent\ViewModel\Email\EmailContent as ViewModel;
use Mentalworkz\EmailContent\Helper\Data as MwzHelper;
use Magento\Cms\Model\Template\FilterProvider;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;

class Content extends \Magento\Framework\View\Element\Template
{

    // operators not suited for eval() operations
    const NON_EVAL_OPERATORS = ['()','!()'];

    /**
     * @var MwzHelper
     */
    protected $mwzhelper;

    /**
     * @var FilterProvider
     */
    protected $filterProvider;

    /**
     * @var integer
     */
    protected $storeId;

    /**
     * @var ViewModel
     */
    public $viewModel;

    /**
     * @var OrderInterface
     */
    protected $orderinterface;

    /**
     * @var CustomerRepositoryInterface
     */
    protected $customerRepositoryInterface;

    /**
     * @var array
     */
    private $defaultWrapperConfig;

    /**
     * @var OrderInterface
     */
    protected $order;

    /**
     * @var CustomerRepositoryInterface
     */
    protected $customer;

    /**
     * all|any, true|false
     * @var array
     */
    protected $conditionScope;

    /**
     * Content constructor.
     * @param Context $context
     * @param ViewModel $viewModel
     * @param FilterProvider $filterProvider
     * @param OrderInterface $orderinterface
     * @param CustomerRepositoryInterface $customerRepositoryInterface
     * @param MwzHelper $mwzhelper
     * @param array $data
     */
    public function __construct(
        Context $context,
        ViewModel $viewModel,
        FilterProvider $filterProvider,
        OrderInterface $orderinterface,
        CustomerRepositoryInterface $customerRepositoryInterface,
        MwzHelper $mwzhelper,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->viewModel = $viewModel;
        $this->filterProvider = $filterProvider;
        $this->mwzhelper = $mwzhelper;
        $this->orderinterface = $orderinterface;
        $this->customerRepositoryInterface = $customerRepositoryInterface;
    }

    /**
     * @return $this
     */
    protected function _prepareLayout()
    {
        if($this->mwzhelper->isEnabled()) {
            $this->setTemplate('Mentalworkz_EmailContent::email/template/content.phtml');
        }
        return $this;
    }

    /**
     * @return int
     */
    protected function getStoreId (): int
    {
        if(is_null($this->storeId)) {
            $this->storeId = (int) $this->mwzhelper->getCurrentStore()->getId();
        }
        return $this->storeId;
    }

    /**
     * @return ViewModel|null
     */
    public function getViewModel(): ?ViewModel
    {
        return $this->viewModel;
    }

    /**
     * @return array|null
     */
    protected function getDefaultWrapperConfig (): ?array
    {
        if(!$this->defaultWrapperConfig){
            $this->defaultWrapperConfig = $this->mwzhelper->getDefaultWrapperConfig();
        }
        return $this->defaultWrapperConfig;
    }

    /**
     * @param \Mentalworkz\EmailContent\Model\Content $emailContent
     * @return array|null
     */
    public function getContentTableWrapper (\Mentalworkz\EmailContent\Model\Content $emailContent): ?array
    {
        if($emailContent){
            if($emailContent->getContentWrapper()){
                return json_decode($emailContent->getContentWrapper(), true);
            }
            return $this->getDefaultWrapperConfig();
        }
        return null;
    }

    /**
     * @param \Mentalworkz\EmailContent\Model\Content $emailContent
     * @return bool
     */
    public function validateContentDisplay (\Mentalworkz\EmailContent\Model\Content $emailContent): bool
    {
        $storeValid = $this->checkStoreSetting($emailContent);
        $dcValid = $this->checkDisplayConditions($emailContent);

        return ($storeValid && $dcValid);
    }

    /**
     * @param \Mentalworkz\EmailContent\Model\Content $emailContent
     * @return bool
     */
    protected function checkStoreSetting(\Mentalworkz\EmailContent\Model\Content $emailContent): bool
    {
        $storeIds = $emailContent->getStoreId() ? explode(',', $emailContent->getStoreId()) : [0];
        return in_array($this->getStoreId(), $storeIds, false) || $storeIds[0] === 0;
    }

    /**
     * Check display conditions, render HTML content
     *
     * @param \Mentalworkz\EmailContent\Model\Content $emailContent
     * @return bool
     */
    protected function checkDisplayConditions(\Mentalworkz\EmailContent\Model\Content $emailContent): bool
    {
        $isValid = true;

        $this->mwzhelper->addLog('Targeted Email Content Debug Enabled - Title "%1", ID %2', [$emailContent->getTitle(), $emailContent->getId()]);

        if($displayConditions = $emailContent->getDisplayConditions()){

            $conditionResults = [];
            $displayConditions = json_decode($displayConditions, true);

            // Save conditions scope for use after process conditions (all|any, true|false)
            $scopeValueKey = array_search('scope',
                array_column($displayConditions, 'id'), false);
            $scopeValues = $displayConditions[$scopeValueKey]['values'];
            $this->conditionScope = array_column($scopeValues, 'value');
            $this->conditionScope[1] = ($this->conditionScope[1] === 'true');

            foreach($displayConditions as $condition){
                $isValid = true;
                $conditionIdParts = explode('_', $condition['id']);

                try {

                    list($conditionOperator, $conditionValues) = (!in_array($condition['id'], ['date_range', 'orderitem_attribute'])) ?
                        $this->getConditionData($condition['id'], $condition['values']) :
                        [null, null];

                    switch($conditionIdParts[0]) {
                        case 'date':
                            switch ($conditionIdParts[1]) {
                                case 'range':

                                    $todaysTs = strtotime(date('Y-m-d H:i:s'));

                                    $fromDateKey = array_search('from_date', array_column($condition['values'], 'name'),
                                        false);
                                    $fromDate = (!is_null($fromDateKey)) ? $condition['values'][$fromDateKey]['value'] : null;
                                    $fromTs = ($fromDate) ? strtotime($fromDate) : null;

                                    $toDateKey = array_search('to_date', array_column($condition['values'], 'name'),
                                        false);
                                    $toDate = (!is_null($toDateKey)) ? $condition['values'][$toDateKey]['value'] : null;
                                    $toTs = ($toDate) ? strtotime($toDate) : null;

                                    if (!(
                                        (!$fromTs && !$toTs) || (
                                            (!$fromTs || ($todaysTs >= $fromTs)) &&
                                            (!$toTs || ($todaysTs <= $toTs))
                                        )
                                    )
                                    ) {
                                        $this->mwzhelper->addLog('No match found for %1 [From: %2, To: %3]', [$condition['id'], $fromDate, $toDate]);
                                        $isValid = false;
                                    }else{
                                        $this->mwzhelper->addLog('Match found for %1 [From: %2, To: %3]', [$condition['id'], $fromDate, $toDate]);
                                    }

                                    break;
                                default:
                                    $this->mwzhelper->addLog('Invalid date condition. [Condition ID: %1]', [$condition['id']]);
                                    $isValid = false;
                            }
                            break;
                        case 'customer':
                            if ($this->loadOrder() || $this->loadCustomer()) {

                                switch ($conditionIdParts[1]) {
                                    case 'id':

                                        $customerId = ($this->customer) ? $this->customer->getId() : '-1';
                                        if(in_array($conditionOperator, self::NON_EVAL_OPERATORS)){
                                            $isValid = $this->evalMultiple($condition['id'], [$customerId], $conditionOperator, $conditionValues);
                                        }else{
                                            $isValid = $this->evalCondition($condition['id'], $customerId,$conditionOperator,$conditionValues[0]);
                                        }

                                        break;
                                    case 'group':

                                        $customerGroupId = $this->customer ? $this->customer->getGroupId() : '-1';
                                        if(in_array($conditionOperator, self::NON_EVAL_OPERATORS)){
                                            $isValid = $this->evalMultiple($condition['id'], [$customerGroupId], $conditionOperator, $conditionValues);
                                        }else{
                                            $isValid = $this->evalCondition($condition['id'], $customerGroupId,$conditionOperator,$conditionValues[0]);
                                        }

                                        break;
                                    default:
                                        $this->mwzhelper->addLog('Invalid customer condition. [Condition ID: %1]', [$condition['id']]);
                                        $isValid = false;
                                }

                            } else {
                                $this->mwzhelper->addLog('Customer condition, no customer or order object found. [Condition ID: %1]', [$condition['id']]);
                                $isValid = false;
                            }
                            break;
                        case 'order':
                            if ($this->loadOrder()) {

                                $orderValue = $this->loadOrderData($conditionIdParts[1]);

                                switch ($conditionIdParts[1]) {
                                    case 'total-items-qty':
                                    case 'total-items-weight':
                                    case 'subtotal-excl-tax':
                                    case 'subtotal':
                                    case 'total-excl-tax':
                                    case 'total':
                                    case 'shipping-method':

                                        if(in_array($conditionOperator, self::NON_EVAL_OPERATORS)){
                                            $isValid = $this->evalMultiple($condition['id'], $orderValue, $conditionOperator, $conditionValues);
                                        }else{
                                            $isValid = $this->evalCondition($condition['id'], (string) $orderValue,$conditionOperator,$conditionValues[0]);
                                        }

                                        break;
                                    default:
                                        $this->mwzhelper->addLog('Invalid order condition.  [Condition ID: %1]', [$condition['id']]);
                                        $isValid = false;
                                }

                            } else {
                                $this->mwzhelper->addLog('Order condition, no order object found. [Condition ID: %1]', [$condition['id']]);
                                $isValid = false;
                            }
                            break;
                        case 'orderitem':
                            if ($this->loadOrder()) {

                                $orderItemValues = $this->loadOrderItemData($conditionIdParts[1]);
                                switch ($conditionIdParts[1]) {
                                    case 'attribute':

                                        $isValid = false;
                                        foreach($orderItemValues as $orderItemValue){
                                            $this->mwzhelper->addLog('START orderitem condition matching...[SKU:' . $orderItemValue['sku'] . ']' , [$condition['id']]);
                                            $orderitemMatch = true;
                                            foreach($condition['values'] as $subCondition){
                                                list($attributeId, $attributeCode) = explode('::', $subCondition[0]['value'][0]);
                                                $operator = $subCondition[1]['value'];
                                                $attributeValues = $subCondition[2]['value'];

                                                switch ($attributeCode) {
                                                    case 'sku':
                                                    case 'name':
                                                    case 'price':

                                                        if(in_array($operator, self::NON_EVAL_OPERATORS)){
                                                            $orderitemMatch = $this->evalMultiple($condition['id'], $orderItemValue[$attributeCode], $operator, $attributeValues);
                                                        }else{
                                                            $orderitemMatch = $this->evalCondition($condition['id'], $orderItemValue[$attributeCode],$operator,$attributeValues);
                                                        }
                                                        break;
                                                    default:// options
                                                        $orderitemMatch = (array_key_exists($attributeId, $orderItemValue['options'])) ?
                                                            $this->evalMultiple($condition['id'], [$orderItemValue['options'][$attributeId]], $operator, $attributeValues)
                                                            : false;
                                                }

                                                if(!$orderitemMatch){
                                                    break;
                                                }
                                            }

                                            $this->mwzhelper->addLog('... END orderitem condition matching [SKU:' . $orderItemValue['sku'] . ']', [$condition['id']]);

                                            if($orderitemMatch){
                                                $isValid = true;
                                                break;
                                            }
                                        }

                                        $message = ($isValid) ? 'Match found for %1 condition combination' :
                                            'No match found for %1 condition combination';
                                        $this->mwzhelper->addLog($message, [$condition['id']]);

                                        break;
                                    case 'attribute-set':
                                    case 'category':

                                        if(in_array($conditionOperator, self::NON_EVAL_OPERATORS)){
                                            $isValid = $this->evalMultiple($condition['id'], $orderItemValues, $conditionOperator, $conditionValues);
                                        }else{
                                            $isValid = $this->evalCondition($condition['id'],$orderItemValues[0],$conditionOperator,$conditionValues[0]);
                                        }

                                        break;
                                    default:
                                        $this->mwzhelper->addLog('Invalid orderitem condition. [Condition ID: %1]', [$condition['id']]);
                                        $isValid = false;
                                }

                            } else {
                                $this->mwzhelper->addLog('Orderitem condition, no order object found. [Condition ID: %1]', [$condition['id']]);
                                $isValid = false;
                            }
                            break;
                        default:
                    }
                }catch(\Exception $e){
                    $this->mwzhelper->addLog($e->getMessage(),[], 'error');
                    $isValid = false;
                }

                $conditionResults[] = $isValid;

            }

            // Check conditions against scope - all|any true|false
            $isValid = $this->checkConditionsScope($conditionResults);

        }

        return $isValid;
    }

    /**
     * Extract condition data from condition values array
     *
     * @param string $conditionId
     * @param array $conditionValues
     * @return array
     */
    protected function getConditionData(string $conditionId, array $conditionValues): array
    {

        $_conditionOperator = null;
        $_conditionValues = null;

        $conditionOperatorKey = array_search('operator',
            array_column($conditionValues, 'name'), false);

        $_conditionOperator = $conditionValues[$conditionOperatorKey]['value'];

        $conditionValueKey = array_search($conditionId,
            array_column($conditionValues, 'name'), false);

        if($conditionId === 'orderitem_attribute') {
            // Required values in different part of $conditionValues array
            $attributeCode = $conditionValues[$conditionValueKey]['value'][0];
            $attributeValueKey = array_search($attributeCode,
                array_column($conditionValues, 'name'), false);
            $_conditionValues = [$attributeCode => $conditionValues[$attributeValueKey]['value']];
        }else{
            $_conditionValues = $conditionValues[$conditionValueKey]['value'];
        }

        if(!is_array($_conditionValues)) {
            $_conditionValues = explode(',', $_conditionValues);
        }

        return [$_conditionOperator, $_conditionValues];
    }

    /**
     * @param array $conditionResults
     * @return bool
     */
    protected function checkConditionsScope(array $conditionResults): bool
    {
        $isValid = false;
        try {
            switch ($this->conditionScope[0]) {
                case 'all':
                    $isValid = $this->conditionScope[1] ?
                        !in_array(false, $conditionResults, false) :
                        !in_array(true, $conditionResults, false);
                    break;
                case 'any':
                    $isValid = $this->conditionScope[1] ?
                        in_array(true, $conditionResults, false) :
                        in_array(false, $conditionResults, false);
                    break;
            }
        }catch(\Exception $e){
            $this->mwzhelper->addLog($e->getMessage(), [], 'error');
        }

        $this->mwzhelper->addLog('Condition scope result: %1 [Scope data: %2]', [($isValid) ? 'true' : 'false', implode('/', $this->conditionScope)]);
        return $isValid;
    }

    /**
     * @param $emailContent
     * @return string
     */
    public function getContentHtml(\Mentalworkz\EmailContent\Model\Content $emailContent): string
    {
        $contentHtml = '';
        try {
            $contentHtml = $this->filterProvider->getBlockFilter()->setStoreId($this->getStoreId())->filter($emailContent->getContent());
        } catch (\Exception $e) {
            $this->mwzhelper->addLog('Render content HTML Error: ' . $e->getMessage(), [], 'error');
        }
        return $contentHtml;
    }


    /**
     * @return CustomerRepositoryInterface|null
     */
    public function loadCustomer (int $customerId = null): ?\Magento\Customer\Model\Data\Customer
    {
        if($this->customer){
            return $this->customer;
        }

        $customerId = (!$customerId) ? $this->getData('customer_id') : $customerId;
        if($customerId){
            try {
                $this->customer = $this->customerRepositoryInterface->getById($customerId);
            }catch(\Magento\Framework\Exception\NoSuchEntityException $e){}
        }

        return $this->customer;
    }

    /**
     * @return null|OrderInterface
     */
    public function loadOrder (): ?OrderInterface
    {
        if($this->order){
            return $this->order;
        }

        if($incrementId = $this->getData('order_id')){
            try {
                $this->order = $this->orderinterface->loadByIncrementId($incrementId);
                if($customerId = $this->order->getCustomerId()){
                    $this->loadCustomer((int)$customerId);
                }
            }catch(\Magento\Framework\Exception\NoSuchEntityException $e){}
        }

        return $this->order;
    }

    /**
     * @param string $field
     * @return null|int|string
     * @throws \Exception
     */
    protected function loadOrderData (string $field)
    {
        $orderValue = null;

        switch($field){
            case 'total-items-qty':

                $orderItems = $this->order->getAllItems();
                $orderValue = 0;
                foreach ($orderItems as $item)
                {
                    $orderValue += $item->getQtyOrdered();
                }

                break;
            case 'total-items-weight':

                $orderItems = $this->order->getAllItems();
                $orderValue = 0;
                foreach ($orderItems as $item)
                {
                    $orderValue += $item->getWeight();
                }

                break;
            case 'subtotal-excl-tax':
                $orderValue = $this->order->getSubtotal();
                break;
            case 'subtotal':
                $orderValue = $this->order->getSubtotalInclTax();
                break;
            case 'total-excl-tax':
                $orderValue = ($this->order->getGrandTotal() - $this->order->getTaxAmount());
                break;
            case 'total':
                $orderValue = $this->order->getGrandTotal();
                break;
            case 'shipping-method':
                $orderValue = $this->order->getShippingMethod();
                break;
            default:
                throw new \Exception(__('Invalid order data requested [%1]', $field));
        }

        return $orderValue;
    }

    /**
     * @param string $field
     * @param array|null $attributeConditions
     * @return array
     * @throws \Exception
     */
    protected function loadOrderItemData (string $field)
    {
        $orderItemValues = [];
        $orderItems = $this->order->getAllItems();

        switch($field) {
            case 'attribute':

                foreach ($orderItems as $item) {
                    $_orderItemValue = [
                        'sku' => $item->getSku(),
                        'name' => $item->getName(),
                        'price' => $item->getPrice(),
                        'options' => []
                    ];

                    if($options = $item->getProductOptions()){
                        if (isset($options['attributes_info']) && !empty($options['attributes_info'])) {
                            foreach ($options['attributes_info'] as $option) {
                                $_orderItemValue['options'][$option['option_id']] = $option['option_value'];
                            }
                        }
                    }

                    $orderItemValues[] = $_orderItemValue;
                }

                break;
            case 'attribute-set':

                foreach ($orderItems as $item)
                {
                    $orderItemValues[] = $item->getProduct()->getAttributeSetId();
                }

                break;
            case 'category':

                $catArrays = [];
                foreach ($orderItems as $item)
                {
                    if(count($item->getProduct()->getCategoryIds())) {
                        $catArrays[] = $item->getProduct()->getCategoryIds();
                    }
                }
                if(!empty($catArrays)){
                    $orderItemValues = array_merge([], ...$catArrays);
                }

                break;
            default:
                throw new \Exception(__('Invalid orderitem data requested [%1]', $field));
        }

        return $orderItemValues;
    }

    /**
     * @param string $conditionId
     * @param array $comparevalue
     * @param string $operator
     * @param array $conditionValues
     * @return bool
     * @throws \Exception
     */
    private function evalMultiple (string $conditionId, array $compareArray, string $operator, array $conditionValues): bool
    {
        switch($operator){
            case '()': // Is one of
                $result = array_intersect($compareArray, $conditionValues);
                if(!$result){
                    $this->mwzhelper->addLog('No match for %1 "is one of" [%2 was not found in %3]', [$conditionId, implode(',',$compareArray), implode(',', $conditionValues)]);
                    return false;
                }
                $this->mwzhelper->addLog('Match found for %1 "is one of" [%2 was found in %3]', [$conditionId, implode(',',$compareArray), implode(',', $conditionValues)]);
                return true;
                break;
            case '!()': // Is not one of
                $result = array_intersect($compareArray, $conditionValues);
                if($result){
                    $this->mwzhelper->addLog('No match for %1 "is not one of" [%2 was found in %3]', [$conditionId, implode(',',$compareArray), implode(',', $conditionValues)]);
                    return false;
                }
                $this->mwzhelper->addLog('Match found for %1 "is not one of" [%2 was not found in %3]', [$conditionId, implode(',',$compareArray), implode(',', $conditionValues)]);
                return true;
                break;
            default:
                $this->mwzhelper->addLog('No match for %1 - invalid operator [%2]', [$conditionId, $operator]);
                return false;
        }

        return false;
    }


    /**
     * Use of eval() frowned upon, but given this is only accessed via the admin...
     *
     * @param string $conditionId
     * @param string $value
     * @param string $operator
     * @param string $conditionValue
     * @return bool
     */
    private function evalCondition (string $conditionId, string $value, string $operator, string $conditionValue): bool
    {
        switch($operator) {
            case '{}': // contains
                $evalStmt = 'return false !== stripos("' . $value . '", "' . $conditionValue . '");';
                $truncStmt = $value . ' contains ' . $conditionValue;
                break;
            case '!{}': // Does not contain
                $evalStmt = 'return false === stripos("' . $value . '", "' . $conditionValue . '");';
                $truncStmt = $value . ' does not contain ' . $conditionValue;
                break;
            default:// ==, !=, >, <, >=, <=
                $evalStmt = 'return ' . $value . ' ' . $operator . ' ' . $conditionValue . ';';
                $truncStmt = substr(str_replace('return ','', $evalStmt), 0,  strlen($evalStmt) -1);
        }

        if(!eval($evalStmt)){
            $this->mwzhelper->addLog('No match found for %1 [%2]', [$conditionId, $truncStmt]);
            return false;
        }
        $this->mwzhelper->addLog('Match found for %1 [%2]', [$conditionId, $truncStmt]);
        return true;
    }

}