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

namespace Mentalworkz\EmailContent\Model\Directives;

use Magento\Framework\Filter\SimpleDirective\ProcessorInterface;
use Magento\Framework\View\Result\Layout;
use Mentalworkz\EmailContent\Helper\Data as MwzHelper;

/**
 * Adds targeted email content based on email content identifier
 */
class EmailContentDirective implements ProcessorInterface
{

    const DIRECTIVE_NAME = 'emailcontent';

    /**
     * @var Layout
     */
    protected $layout;

    /**
     * @var MwzHelper
     */
    protected $mwzhelper;

    /**
     * EmailContentDirective constructor.
     * @param Layout $layout
     */
    public function __construct(
        Layout $layout,
        MwzHelper $mwzhelper
    ) {
        $this->layout = $layout;
        $this->mwzhelper = $mwzhelper;
    }

    /**
     * Custom email directive name
     * @return string
     */
    public function getName(): string
    {
        return self::DIRECTIVE_NAME;
    }

    /**
     * @return array|null
     */
    public function getDefaultFilters(): ?array
    {
        // Make sure newlines are converted to <br /> tags by default
        return ['nl2br'];
    }

    /**
     * Return email content HTML based on identifier and email content display conditions
     *
     * @param mixed $value
     * @param array $parameters
     * @param null|string $html
     * @return string
     */
    public function process($value, array $parameters, ?string $html): string
    {

        $contentHtml = '';
        if($this->mwzhelper->isEnabled()) {

            // First char from first parameter missing issue, fixed in 2.4
            $identifier = array_key_exists('identifier', $parameters) ? $parameters['identifier'] :
                (array_key_exists('dentifier',$parameters) ? $parameters['dentifier'] : '');

            if ($identifier) {
                $emailContentBlock = $this->layout->getLayout()->createBlock('\Mentalworkz\EmailContent\Block\Content')
                    ->setData('identifier', $identifier);

                array_shift($parameters);
                foreach($parameters as $key => $param){
                    $emailContentBlock->setData($key, $param);
                }

                $contentHtml = $emailContentBlock->toHtml();
            }
        }

        return $contentHtml;
    }

}
