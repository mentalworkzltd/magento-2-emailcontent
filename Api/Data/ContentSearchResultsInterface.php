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

namespace Mentalworkz\EmailContent\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

/**
 * Interface for email content search results.
 * @api
 */
interface ContentSearchResultsInterface extends SearchResultsInterface
{
    /**
     * Get contents list.
     *
     * @return \Mentalworkz\EmailContent\Api\Data\ContentInterface[]
     */
    public function getItems();

    /**
     * Set contents list.
     *
     * @param \Mentalworkz\EmailContent\Api\Data\ContentInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
