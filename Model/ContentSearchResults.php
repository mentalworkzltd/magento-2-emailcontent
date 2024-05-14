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

use Mentalworkz\EmailContent\Api\Data\ContentSearchResultsInterface;
use Magento\Framework\Api\SearchResults;

/**
 * Service Data Object with Email Content search results.
 */
class ContentSearchResults extends SearchResults implements ContentSearchResultsInterface
{
}
