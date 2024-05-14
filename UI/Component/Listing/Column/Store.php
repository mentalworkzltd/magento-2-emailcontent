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

namespace Mentalworkz\EmailContent\Ui\Component\Listing\Column;

/**
 * Class Store
 */
class Store extends \Magento\Store\Ui\Component\Listing\Column\Store
{
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {

                // Store ID string to array
                $item['store_id'] = (!is_array($item['store_id'])) ? explode(',', $item['store_id']) : $item['store_id'];

                $item['orig_' . $this->getData('name')] = $item[$this->getData('name')];
                $item[$this->getData('name')] = $this->prepareItem($item);
            }
        }

        return $dataSource;
    }
}
