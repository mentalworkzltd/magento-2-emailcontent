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

namespace Mentalworkz\EmailContent\Api;

/**
 * Command to load the content data by specified identifier
 * @api
 */
interface GetContentByIdentifierInterface
{
    /**
     * Load email content data by given content identifier.
     *
     * @param string $identifier
     * @param int $storeId
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @return \Mentalworkz\EmailContent\Api\Data\ContentInterface
     */
    public function execute(string $identifier, int $storeId) : \Mentalworkz\EmailContent\Api\Data\ContentInterface;
}
