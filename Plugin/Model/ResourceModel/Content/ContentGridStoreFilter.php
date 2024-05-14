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

namespace Mentalworkz\EmailContent\Plugin\Model\ResourceModel\Content;

use Closure;
use Magento\Framework\DB\Select;
use Magento\Framework\View\Element\UiComponent\DataProvider\SearchResult;
use Magento\Cms\Ui\Component\Listing\Column\Cms\Options as StoreOptions;

class ContentGridStoreFilter
{

    const FILTER_FIELD = 'store_id';

    /**
     * @param StoreOptions $storeOptions
     */
    public function __construct(
        StoreOptions $storeOptions
    ) {
    }

    /**
     * Stomre finset style filter
     *
     * @param SearchResult $subject
     * @param Closure $proceed
     * @param string $field
     * @param array|null $condition
     * @return SearchResult|mixed
     */
    public function aroundAddFieldToFilter(
        SearchResult $subject,
        Closure      $proceed,
        $field,
        $condition = null
    ): SearchResult {
        if ($field === self::FILTER_FIELD) {
            $conditionValue = array_values($condition)[0];
            $subject->getSelect()->where('FIND_IN_SET(' . $conditionValue . ', main_table.store_id)');
            return $subject;
        }
        return $proceed($field, $condition);
    }
}