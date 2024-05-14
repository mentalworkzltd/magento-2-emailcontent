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

namespace Mentalworkz\EmailContent\ViewModel\Email;

use Mentalworkz\EmailContent\Model\ResourceModel\Content\CollectionFactory;

class EmailContent implements \Magento\Framework\View\Element\Block\ArgumentInterface
{

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * EmailContent constructor.
     */
    public function __construct(
        CollectionFactory $collectionFactory
    ) {
        $this->collectionFactory = $collectionFactory;
    }


    /**
     * @param $identifier
     * @return \Mentalworkz\EmailContent\Model\ResourceModel\Content\Collection|null
     */
    public function getEmailContent ($identifier): ?\Mentalworkz\EmailContent\Model\ResourceModel\Content\Collection
    {
        $emailContent = null;
        if(!empty($identifier)){
            $emailContent = $this->collectionFactory->create()
                ->addFieldToFilter('identifier', ['eq' => $identifier])
                ->addFieldToFilter('is_active', ['eq' => 1])
                ->setOrder('sort_order', 'ASC');
        }

        return $emailContent;
    }


}
