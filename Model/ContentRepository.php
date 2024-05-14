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

use Mentalworkz\EmailContent\Api\ContentRepositoryInterface;
use Mentalworkz\EmailContent\Api\Data;
use Mentalworkz\EmailContent\Model\ResourceModel\Content as ResourceContent;
use Mentalworkz\EmailContent\Model\ResourceModel\Content\CollectionFactory as ContentCollectionFactory;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Reflection\DataObjectProcessor;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class ContentRepository
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ContentRepository implements ContentRepositoryInterface
{
    /**
     * @var ResourceContent
     */
    protected $resource;

    /**
     * @var ContentFactory
     */
    protected $contentFactory;

    /**
     * @var ContentCollectionFactory
     */
    protected $ContentCollectionFactory;

    /**
     * @var Data\ContentSearchResultsInterfaceFactory
     */
    protected $searchResultsFactory;

    /**
     * @var DataObjectHelper
     */
    protected $dataObjectHelper;

    /**
     * @var DataObjectProcessor
     */
    protected $dataObjectProcessor;

    /**
     * @var \Mentalworkz\EmailContent\Api\Data\ContentInterfaceFactory
     */
    protected $dataContentFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var CollectionProcessorInterface
     */
    private $collectionProcessor;

    /**
     * ContentRepository constructor.
     * @param ResourceContent $resource
     * @param ContentFactory $contentFactory
     * @param Data\ContentInterfaceFactory $dataContentFactory
     * @param ContentCollectionFactory $ContentCollectionFactory
     * @param Data\ContentSearchResultsInterfaceFactory $searchResultsFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param DataObjectProcessor $dataObjectProcessor
     * @param StoreManagerInterface $storeManager
     * @param CollectionProcessorInterface|null $collectionProcessor
     */
    public function __construct(
        ResourceContent $resource,
        ContentFactory $contentFactory,
        \Mentalworkz\EmailContent\Api\Data\ContentInterfaceFactory $dataContentFactory,
        ContentCollectionFactory $ContentCollectionFactory,
        Data\ContentSearchResultsInterfaceFactory $searchResultsFactory,
        DataObjectHelper $dataObjectHelper,
        DataObjectProcessor $dataObjectProcessor,
        StoreManagerInterface $storeManager,
        CollectionProcessorInterface $collectionProcessor = null
    ) {
        $this->resource = $resource;
        $this->contentFactory = $contentFactory;
        $this->ContentCollectionFactory = $ContentCollectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->dataContentFactory = $dataContentFactory;
        $this->dataObjectProcessor = $dataObjectProcessor;
        $this->storeManager = $storeManager;
        $this->collectionProcessor = $collectionProcessor ?: $this->getCollectionProcessor();
    }

    /**
     * Save Content data
     *
     * @param \Mentalworkz\EmailContent\Model\Content $content
     * @return \Mentalworkz\EmailContent\Model\Content
     * @throws CouldNotSaveException
     */
    public function save(\Mentalworkz\EmailContent\Model\Content $content): \Mentalworkz\EmailContent\Model\Content
    {
        try {
            $this->resource->save($content);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__($exception->getMessage()));
        }
        return $content;
    }

    /**
     * Load Content data by given Content Identity
     *
     * @param string $contentId
     * @return \Mentalworkz\EmailContent\Model\Content
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getById($contentId): \Mentalworkz\EmailContent\Model\Content
    {
        $content = $this->contentFactory->create();
        $this->resource->load($content, $contentId);
        if (!$content->getId()) {
            throw new NoSuchEntityException(__('The Email Content with ID "%1" does not exist.', $contentId));
        }
        return $content;
    }

    /**
     * Load Content data collection by given search criteria
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @param \Magento\Framework\Api\SearchCriteriaInterface $criteria
     * @return \Mentalworkz\EmailContent\Api\Data\ContentSearchResultsInterface
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $criteria): \Mentalworkz\EmailContent\Api\Data\ContentSearchResultsInterface
    {
        /** @var \Mentalworkz\EmailContent\Model\ResourceModel\Content\Collection $collection */
        $collection = $this->ContentCollectionFactory->create();

        $this->collectionProcessor->process($criteria, $collection);

        /** @var Data\ContentSearchResultsInterface $searchResults */
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($criteria);
        $searchResults->setItems($collection->getItems());
        $searchResults->setTotalCount($collection->getSize());
        return $searchResults;
    }

    /**
     * Delete Content
     *
     * @param \Mentalworkz\EmailContent\Api\Data\ContentInterface $content
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function delete(Data\ContentInterface $content): bool
    {
        try {
            $this->resource->delete($content);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__($exception->getMessage()));
        }
        return true;
    }

    /**
     * Delete Content by given Content Identity
     *
     * @param string $contentId
     * @return bool
     * @throws CouldNotDeleteException
     * @throws NoSuchEntityException
     */
    public function deleteById($contentId): bool
    {
        return $this->delete($this->getById($contentId));
    }

    /**
     * Retrieve collection processor
     *
     * @return CollectionProcessorInterface
     */
    private function getCollectionProcessor(): CollectionProcessorInterface
    {
        if (!$this->collectionProcessor) {
            $this->collectionProcessor = \Magento\Framework\App\ObjectManager::getInstance()->get(
                'Mentalworkz\EmailContent\Model\Api\SearchCriteria\ContentCollectionProcessor'
            );
        }
        return $this->collectionProcessor;
    }
}
