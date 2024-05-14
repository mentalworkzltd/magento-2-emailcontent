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

namespace Mentalworkz\EmailContent\Block\Adminhtml\Edit;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Asset\Repository;

class Conditions extends Template
{

    /**
     * @var Repository
     */
    public $assetRepo;

    /**
     * Conditions constructor.
     * @param Template\Context $context
     * @param Repository $assetRepo
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        Repository $assetRepo,
        array $data = []
    ) {
        $this->assetRepo = $assetRepo;
        parent::__construct($context, $data);
    }

    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('Mentalworkz_EmailContent::conditions/fieldset.phtml');
    }

}
