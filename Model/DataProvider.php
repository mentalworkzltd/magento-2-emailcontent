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

use Mentalworkz\EmailContent\Model\ResourceModel\Content\CollectionFactory;
use Magento\Framework\App\Request\DataPersistorInterface;
use Mentalworkz\EmailContent\Helper\Data as MwzHelper;

class DataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{

    /**
     * @var array
     */
    protected $loadedData = [];

    /**
     * @var DataPersistorInterface
     */
    protected $dataPersistor;

    /**
     * @var MwzHelper
     */
    protected $mwzHelper;

    /**
     * DataProvider constructor.
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param CollectionFactory $CollectionFactory
     * @param DataPersistorInterface $dataPersistor
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        string $name,
        string $primaryFieldName,
        string $requestFieldName,
        CollectionFactory $CollectionFactory,
        DataPersistorInterface $dataPersistor,
        MwzHelper $mwzHelper,
        array $meta = [],
        array $data = []
    ) {
        $this->dataPersistor = $dataPersistor;
        $this->mwzHelper = $mwzHelper;
        $this->collection = $CollectionFactory->create();
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }

    /**
     * @return array
     */
    public function getData(): array
    {

        if (!empty($this->loadedData)) {
            return $this->loadedData;
        }

        $items = $this->collection->getItems();
        foreach ($items as $item) {
            $item->setDirective($this->mwzHelper->getDirective((int)$item->getContentId(), $item->getIdentifier()));
            $this->loadedData[$item->getContentId()] = $item->getData();
        }

        $data = $this->dataPersistor->get('emailcontent');
        if (!empty($data)) {
            $model = $this->collection->getNewEmptyItem();
            $model->setData($data);
            $this->loadedData[$model->getId()] = $model->getData();
            $this->dataPersistor->clear('emailcontent');
        }

        return $this->loadedData;
    }

}