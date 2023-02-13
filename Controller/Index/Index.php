<?php

namespace ModestoSolar\GoogleShopping\Controller\Index;

use ModestoSolar\GoogleShopping\Helper\Data;
use ModestoSolar\GoogleShopping\Model\Xmlfeed;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\ForwardFactory;
use ModestoSolar\GoogleShopping\Model\SaveFeed;

class Index extends Action
{
    protected Xmlfeed $xmlFeed;
    private Data $helper;
    private ForwardFactory $resultForwardFactory;
    private SaveFeed $saveFeed;

    public function __construct(
        Context $context,
        Xmlfeed $xmlFeed,
        Data $helper,
        ForwardFactory $resultForwardFactory,
        SaveFeed $saveFeed
    ) {
        $this->xmlFeed = $xmlFeed;
        $this->helper = $helper;
        $this->resultForwardFactory = $resultForwardFactory;
        $this->saveFeed = $saveFeed;
        parent::__construct($context);
    }

    public function execute()
    {
        $resultForward = $this->resultForwardFactory->create();
        if (!empty($this->helper->getConfig('enabled'))) {
            $this->saveFeed->saveOnFilesystem();
            header("Content-Type: text/xml; charset=utf-8");
            header("Cache-Control: no-cache, no-store, must-revalidate");
            echo $this->xmlFeed->getFeed();
        } else {
            $resultForward->forward('noroute');
        }
    }
}
