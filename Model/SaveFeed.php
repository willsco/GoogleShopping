<?php

namespace ModestoSolar\GoogleShopping\Model;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem\Io\File;
use ModestoSolar\GoogleShopping\Helper\Data;

class SaveFeed
{
    const XML_NAMEFILE = 'feed-gs.xml';
    const GSHOPPING_FOLDER = '/'.'googleshopping'.'/';
    private Xmlfeed $xmlFeed;
    private File $file;
    private DirectoryList $directoryList;
    protected Data $dataHelper;

    public function __construct(
        Data $dataHelper,
        Xmlfeed $xmlFeed,
        File $file,
        DirectoryList $directoryList
    ) {
        $this->dataHelper = $dataHelper;
        $this->xmlFeed = $xmlFeed;
        $this->file = $file;
        $this->directoryList = $directoryList;
    }

    public function saveOnFilesystem()
    {
        $xmlContent = $this->xmlFeed->getFeed();
        $fileName = self::XML_NAMEFILE;
        $folder = self::GSHOPPING_FOLDER;
        $ioAdapter = $this->file;
        $xmlPath = $this->directoryList->getPath('media') . $folder;
        if (!is_dir($xmlPath)) {
            $ioAdapter->mkdir($xmlPath, 0775);
        }
        if (is_dir($xmlPath) && !empty($xmlContent)) {
            $ioAdapter->write($xmlPath.$fileName, $xmlContent, 0666);
        }
    }
}
