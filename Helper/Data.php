<?php

namespace ModestoSolar\GoogleShopping\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\ScopeInterface;

class Data extends AbstractHelper
{
    const GS_CONFIG_PATH = 'modestogoogleshopping/settings/';

    public function getConfig($field)
    {
        return $this->scopeConfig->getValue(self::GS_CONFIG_PATH.$field, ScopeInterface::SCOPE_STORE);
    }

    public function removeHtml($data)
    {
        $description = strip_tags($data, ['ul', 'li', 'p']);
        $description = preg_replace(['/{[^>]*}/', '#\[.*\]#'], '', $description);
        return str_replace('#html-body', '', $description);
    }
}
