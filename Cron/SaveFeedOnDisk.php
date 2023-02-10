<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace ModestoSolar\GoogleShopping\Cron;

use ModestoSolar\GoogleShopping\Helper\Data;
use ModestoSolar\GoogleShopping\Helper\Products as ProductsHelper;
use ModestoSolar\GoogleShopping\Model\Xmlfeed;
use ModestoSolar\GoogleShopping\Model\SaveFeed;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem\Io\File;
use Psr\Log\LoggerInterface;

class SaveFeedOnDisk
{

    protected LoggerInterface $logger;
    private Xmlfeed $xmlFeed;
    private Data $helper;
    private ProductsHelper $productsHelper;
    private DirectoryList $directoryList;
    private File $file;
    private SaveFeed $saveFeed;


    /**
     * @param LoggerInterface $logger
     * @param Xmlfeed $xmlFeed
     * @param Data $helper
     * @param ProductsHelper $productsHelper
     * @param DirectoryList $directoryList
     * @param File $file
     */
    public function __construct(
        LoggerInterface $logger,
        Xmlfeed $xmlFeed,
        Data $helper,
        ProductsHelper $productsHelper,
        DirectoryList $directoryList,
        File $file,
        SaveFeed $saveFeed
    ) {
        $this->logger = $logger;
        $this->xmlFeed = $xmlFeed;
        $this->helper = $helper;
        $this->productsHelper = $productsHelper;
        $this->directoryList = $directoryList;
        $this->file = $file;
        $this->saveFeed = $saveFeed;
    }

    /**
     * Execute the cron
     *
     * @return void
     * @throws NoSuchEntityException
     * @throws FileSystemException
     */
    public function execute()
    {
        if (!empty($this->helper->getConfig('enabled'))) {
            $this->saveFeed->saveOnFilesystem();
        }
    }
}
