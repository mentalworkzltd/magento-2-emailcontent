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

use Magento\Framework\Model\AbstractModel;
use Mentalworkz\EmailContent\Api\Data\ContentInterface;

class Content extends AbstractModel implements ContentInterface
{

    /**
     * email content cache tag
     */
    const CACHE_TAG = 'email_content';

    /**
     * @var string
     */
    protected $_cacheTag = 'email_content';

    /**
     * Prefix of model events names.
     *
     * @var string
     */
    protected $_eventPrefix = 'email_content';

    protected function _construct(){
        $this->_init('Mentalworkz\EmailContent\Model\ResourceModel\Content');
    }

    /**
     * Get identities
     *
     * @return array
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }


    /**
     * Get ID
     *
     * @return int|null
     */
    public function getId(){
        return $this->getData(self::CONTENT_ID);
    }

    /**
     * Get identifier
     *
     * @return string
     */
    public function getIdentifier(){
        return $this->getData(self::IDENTIFIER);
    }

    /**
     * Get title
     *
     * @return string|null
     */
    public function getTitle(){
        return $this->getData(self::TITLE);
    }

    /**
     * Get description
     *
     * @return string|null
     */
    public function getDescription(){
        return $this->getData(self::DESCRIPTION);
    }

    /**
     * Get display conditions
     *
     * @return string|null
     */
    public function getDisplayConditions(){
        return $this->getData(self::DISPLAY_CONDITIONS);
    }

    /**
     * Get content
     *
     * @return string|null
     */
    public function getContent(){
        return $this->getData(self::CONTENT);
    }

    /**
     * Get custom content wrapper details
     *
     * @return string|null
     */
    public function getContentWrapper(){
        return $this->getData(self::CONTENT_WRAPPER);
    }

    /**
     * Get creation time
     *
     * @return string|null
     */
    public function getCreationTime(){
        return $this->getData(self::CREATION_TIME);
    }

    /**
     * Get update time
     *
     * @return string|null
     */
    public function getUpdateTime(){
        return $this->getData(self::UPDATE_TIME);
    }

    /**
     * Is active
     *
     * @return bool|null
     */
    public function getIsActive(){
        return $this->getData(self::IS_ACTIVE);
    }

    /**
     * Sort order
     *
     * @return int|null
     */
    public function getSortOrder(){
        return $this->getData(self::SORT_ORDER);
    }

    /**
     * Store ID
     *
     * @return string|null
     */
    public function getStoreId(){
        return $this->getData(self::STORE_ID);
    }

    /**
     * Set ID
     *
     * @param int $id
     * @return ContentInterface
     */
    public function setId($id){
        return $this->setData(self::CONTENT_ID, $id);
    }

    /**
     * Set identifier
     *
     * @param string $identifier
     * @return ContentInterface
     */
    public function setIdentifier($identifier){
        return $this->setData(self::IDENTIFIER, $identifier);
    }

    /**
     * Set title
     *
     * @param string $title
     * @return ContentInterface
     */
    public function setTitle($title){
        return $this->setData(self::TITLE, $title);
    }

    /**
     * Set description
     *
     * @param string $description
     * @return ContentInterface
     */
    public function setDescription($description){
        return $this->setData(self::DESCRIPTION, $description);
    }

    /**
     * Set display conditions
     *
     * @param string $displayConditions
     * @return ContentInterface
     */
    public function setDisplayConditions($displayConditions){
        return $this->setData(self::DISPLAY_CONDITIONS, $displayConditions);
    }

    /**
     * Set custom content wrapper details
     *
     * @return string|null
     */
    public function setContentWrapper($contentWrapper){
        return $this->getData(self::CONTENT_WRAPPER, $contentWrapper);
    }

    /**
     * Set content
     *
     * @param string $content
     * @return ContentInterface
     */
    public function setContent($content){
        return $this->setData(self::CONTENT, $content);
    }

    /**
     * Set creation time
     *
     * @param string $creationTime
     * @return ContentInterface
     */
    public function setCreationTime($creationTime){
        return $this->setData(self::CREATION_TIME, $creationTime);
    }

    /**
     * Set update time
     *
     * @param string $updateTime
     * @return ContentInterface
     */
    public function setUpdateTime($updateTime){
        return $this->setData(self::UPDATE_TIME, $updateTime);
    }

    /**
     * Set is active
     *
     * @param bool|int $isActive
     * @return ContentInterface
     */
    public function setIsActive($isActive){
        return $this->setData(self::IS_ACTIVE, $isActive);
    }

    /**
     * Set sort order
     *
     * @param int|null $sortOrder
     * @return ContentInterface
     */
    public function setSortOrder($sortOrder){
        return $this->setData(self::SORT_ORDER, $sortOrder);
    }

    /**
     * Set store id
     *
     * @param string|null $storeId
     * @return ContentInterface
     */
    public function setStoreId($storeId){
        return $this->setData(self::STORE_ID, $storeId);
    }
}