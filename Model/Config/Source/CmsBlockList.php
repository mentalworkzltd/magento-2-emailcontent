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

use Magento\Cms\Model\BlockFactory;

class CmsBlockList extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{

    /**
     * @var BlockFactory
     */
    protected $blockFactory;

    public function __construct(
        BlockFactory $blockFactory
    ) {
        $this->blockFactory = $blockFactory;
    }

    /**
     * Get all CMS blocks
     *
     * @return array
     */
    public function getAllOptions(): array
    {
        $this->_options = [];
        $this->_options[] = ['value' => '', 'label' => '--Please Select--'];

        $collection = $this->blockFactory->create()->getCollection();
        foreach($collection as $block){
            $this->_options[] = ['value' => $block->getIdentifier(), 'label' => $block->getTitle()];
        }

        return $this->_options;
    }

}