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

namespace Mentalworkz\EmailContent\Model;

use Mentalworkz\EmailContent\Api\GetContentByIdentifierInterface;
use Mentalworkz\EmailContent\Api\Data\ContentInterface;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class GetContentByIdentifier
 */
class GetContentByIdentifier implements GetContentByIdentifierInterface
{
    /**
     * @var \Mentalworkz\EmailContent\Model\ContentFactory
     */
    private $contentFactory;

    /**
     * @var ResourceModel\Content
     */
    private $contentResource;

    /**
     * GetContentByIdentifier constructor.
     * @param ContentFactory $contentFactory
     * @param ResourceModel\Content $contentResource
     */
    public function __construct(
        \Mentalworkz\EmailContent\Model\ContentFactory $contentFactory,
        \Mentalworkz\EmailContent\Model\ResourceModel\Content $contentResource
    ) {
        $this->contentFactory = $contentFactory;
        $this->contentResource = $contentResource;
    }

    /**
     * @param string $identifier
     * @param int $storeId
     * @return ContentInterface
     * @throws NoSuchEntityException
     */
    public function execute(string $identifier, int $storeId) : ContentInterface
    {
        $content = $this->contentFactory->create();
        $content->setStoreId($storeId);
        $this->contentResource->load($content, $identifier, ContentInterface::IDENTIFIER);

        if (!$content->getId()) {
            throw new NoSuchEntityException(__('The Email Content with identifier "%1" does not exist.', $identifier));
        }

        return $content;
    }
}
