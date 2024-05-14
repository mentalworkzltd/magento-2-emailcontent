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
 * EmailContent CRUD interface.
 * @api
 */
interface ContentRepositoryInterface
{
    /**
     * Save EmailContent
     *
     * @param \Mentalworkz\EmailContent\Model\Content $content
     * @return \Mentalworkz\EmailContent\Model\Content
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(\Mentalworkz\EmailContent\Model\Content $content);

    /**
     * Retrieve content.
     *
     * @param int $contentId
     * @return \Mentalworkz\EmailContent\Api\Data\ContentInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getById($contentId);

    /**
     * Retrieve content matching the specified criteria.
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Mentalworkz\EmailContent\Api\Data\ContentSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria);

    /**
     * Delete content.
     *
     * @param \Mentalworkz\EmailContent\Api\Data\ContentInterface $content
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(Data\ContentInterface $content);

    /**
     * Delete content by ID.
     *
     * @param int $contentId
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($contentId);
}
