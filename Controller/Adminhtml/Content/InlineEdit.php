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

namespace Mentalworkz\EmailContent\Controller\Adminhtml\Content;

use Magento\Backend\App\Action\Context;
use Mentalworkz\EmailContent\Api\ContentRepositoryInterface as ContentRepository;
use Magento\Framework\Controller\Result\JsonFactory;
use Mentalworkz\EmailContent\Api\Data\ContentInterface;

class InlineEdit extends \Magento\Backend\App\Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Mentalworkz_EmailContent::emailcontent';

    /**
     * @var ContentRepository
     */
    protected $contentRepository;

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $jsonFactory;

    /**
     * @param Context $context
     * @param ContentRepository $contentRepository
     * @param JsonFactory $jsonFactory
     */
    public function __construct(
        Context $context,
        ContentRepository $contentRepository,
        JsonFactory $jsonFactory
    ) {
        parent::__construct($context);
        $this->contentRepository = $contentRepository;
        $this->jsonFactory = $jsonFactory;
    }

    /**
     * @return \Magento\Framework\Controller\Result\Json
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute(): \Magento\Framework\Controller\Result\Json
    {
        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->jsonFactory->create();
        $error = false;
        $messages = [];

        if ($this->getRequest()->getParam('isAjax')) {
            $postItems = $this->getRequest()->getParam('items', []);
            if (!count($postItems)) {
                $messages[] = __('Please correct the data sent.');
                $error = true;
            } else {
                foreach (array_keys($postItems) as $contentId) {
                    /** @var \Mentalworkz\EmailContent\Model\Content $content */
                    $content = $this->contentRepository->getById($contentId);
                    try {
                        $content->setData(array_merge($content->getData(), $postItems[$contentId]));
                        $this->contentRepository->save($content);
                    } catch (\Exception $e) {
                        $messages[] = $this->getErrorWithContentId(
                            $content,
                            __($e->getMessage())
                        );
                        $error = true;
                    }
                }
            }
        }

        return $resultJson->setData([
            'messages' => $messages,
            'error' => $error
        ]);
    }

    /**
     * Add content ID to error message
     *
     * @param ContentInterface $content
     * @param string $errorText
     * @return string
     */
    protected function getErrorWithContentId(ContentInterface $content, $errorText): string
    {
        return '[Content ID: ' . $content->getContentId() . '] ' . $errorText;
    }
}
