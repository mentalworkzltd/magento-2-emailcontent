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

/**
 * Email content interface.
 * @api
 */
interface ContentInterface
{
    /**#@+
     * Constants for keys of data array. Identical to the name of the getter in snake case
     */
    const CONTENT_ID    = 'content_id';
    const IDENTIFIER    = 'identifier';
    const TITLE         = 'title';
    const DESCRIPTION   = 'description';
    const DISPLAY_CONDITIONS = 'display_conditions';
    const CONTENT_WRAPPER = 'content_wrapper';
    const CONTENT       = 'content';
    const CREATION_TIME = 'creation_time';
    const UPDATE_TIME   = 'update_time';
    const IS_ACTIVE     = 'is_active';
    const SORT_ORDER    = 'sort_order';
    const STORE_ID    = 'store_id';
    /**#@-*/

    /**
     * Get ID
     *
     * @return int|null
     */
    public function getId();

    /**
     * Get identifier
     *
     * @return string
     */
    public function getIdentifier();

    /**
     * Get title
     *
     * @return string|null
     */
    public function getTitle();

    /**
     * Get description
     *
     * @return string|null
     */
    public function getDescription();

    /**
     * Get display conditions
     *
     * @return string|null
     */
    public function getDisplayConditions();

    /**
     * Get custom content wrapper details
     *
     * @return string|null
     */
    public function getContentWrapper();

    /**
     * Get content
     *
     * @return string|null
     */
    public function getContent();

    /**
     * Get creation time
     *
     * @return string|null
     */
    public function getCreationTime();

    /**
     * Get update time
     *
     * @return string|null
     */
    public function getUpdateTime();

    /**
     * Is active
     *
     * @return bool|null
     */
    public function getIsActive();

    /**
     * Sort order
     *
     * @return int|null
     */
    public function getSortOrder();

    /**
     * Store ID
     *
     * @return string|null
     */
    public function getStoreId();

    /**
     * Set ID
     *
     * @param int $id
     * @return ContentInterface
     */
    public function setId($id);

    /**
     * Set identifier
     *
     * @param string $identifier
     * @return ContentInterface
     */
    public function setIdentifier($identifier);

    /**
     * Set title
     *
     * @param string $title
     * @return ContentInterface
     */
    public function setTitle($title);

    /**
     * Set description
     *
     * @param string $description
     * @return ContentInterface
     */
    public function setDescription($description);

    /**
     * Set display conditions
     *
     * @param string $displayConditions
     * @return ContentInterface
     */
    public function setDisplayConditions($displayConditions);

    /**
     * Set custom content wrapper details
     *
     * @return string|null
     */
    public function setContentWrapper($contentWrapper);

    /**
     * Set content
     *
     * @param string $content
     * @return ContentInterface
     */
    public function setContent($content);

    /**
     * Set creation time
     *
     * @param string $creationTime
     * @return ContentInterface
     */
    public function setCreationTime($creationTime);

    /**
     * Set update time
     *
     * @param string $updateTime
     * @return ContentInterface
     */
    public function setUpdateTime($updateTime);

    /**
     * Set is active
     *
     * @param bool|int $isActive
     * @return ContentInterface
     */
    public function setIsActive($isActive);

    /**
     * Set sort order
     *
     * @param int|null $sortOrder
     * @return ContentInterface
     */
    public function setSortOrder($sortOrder);

    /**
     * Set store id
     *
     * @param string|null $storeId
     * @return ContentInterface
     */
    public function setStoreId($storeId);
}
