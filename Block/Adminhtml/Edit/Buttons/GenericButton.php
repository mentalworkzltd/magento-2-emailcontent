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

namespace Mentalworkz\EmailContent\Block\Adminhtml\Edit\Buttons;

use Magento\Backend\Block\Widget\Context;
use  Mentalworkz\EmailContent\Api\ContentRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class GenericButton
 */
class GenericButton
{
    /**
     * @var Context
     */
    protected $context;

    /**
     * @var ContentRepositoryInterface
     */
    protected $contentRepository;

    /**
     * GenericButton constructor.
     * @param Context $context
     * @param ContentRepositoryInterface $contentRepository
     */
    public function __construct(
        Context $context,
        ContentRepositoryInterface $contentRepository
    ) {
        $this->context = $context;
        $this->contentRepository = $contentRepository;
    }

    /**
     * Return Email Content ID
     *
     * @return int|null
     */
    public function getContentId(): ?int
    {
        try {
            $content = $this->contentRepository->getById(
                $this->context->getRequest()->getParam('id')
            );
            return (int)$content->getId();
        } catch (NoSuchEntityException $e) {
        }
        return null;
    }

    /**
     * Generate url by route and parameters
     *
     * @param   string $route
     * @param   array $params
     * @return  string
     */
    public function getUrl($route = '', $params = []): string
    {
        return $this->context->getUrlBuilder()->getUrl($route, $params);
    }

}
