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

namespace Mentalworkz\EmailContent\Model\ResourceModel\Content;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{

    /**
     * Required for massactions to work correctly
     * @var string
     */
    protected $_idFieldName = 'content_id';

    protected function _construct()
    {
        $this->_init('Mentalworkz\EmailContent\Model\Content','Mentalworkz\EmailContent\Model\ResourceModel\Content');
    }

    public function addStoreFilter($storeId){
        $this->addFieldToFilter('main_table.store_id',
            array(
                array('finset'=> array($storeId))
            )
        );
    }

}