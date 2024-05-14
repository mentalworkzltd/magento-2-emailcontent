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

namespace Mentalworkz\EmailContent\Plugin\Widget;

class ImageUrl
{
    /**
     * @var \Magento\Backend\Helper\Data
     */
    protected $backendData;

    /**
     * Widget constructor.
     *
     * @param \Magento\Backend\Helper\Data $backendData
     */
    public function __construct(
        \Magento\Backend\Helper\Data $backendData
    ) {
        $this->backendData = $backendData;
    }

    /**
     * @param \Magento\Widget\Model\Widget $subject
     * @param string $type
     * @param array $params
     * @param bool $asIs
     * @return array
     */
    public function beforeGetWidgetDeclaration(
        \Magento\Widget\Model\Widget $subject,
        string $type,
        array $params = [],
        bool $asIs = true
    ): array
    {
        foreach ($params as $name => $value) {
            if (preg_match('/(___directive\/)([a-zA-Z0-9,_-]+)/', $value, $matches)) {
                $directive = base64_decode(strtr($matches[2], '-_,', '+/='));
                $params[$name] = str_replace(['{{media url="', '"}}'], ['', ''], $directive);
            }
        }
        return [$type, $params, $asIs];
    }
}