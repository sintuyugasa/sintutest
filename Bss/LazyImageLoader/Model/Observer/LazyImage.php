<?php
/**
* BSS Commerce Co.
*
* NOTICE OF LICENSE
*
* This source file is subject to the EULA
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://bsscommerce.com/Bss-Commerce-License.txt
*
* =================================================================
*                 MAGENTO EDITION USAGE NOTICE
* =================================================================
* This package designed for Magento COMMUNITY edition
* BSS Commerce does not guarantee correct work of this extension
* on any other Magento edition except Magento COMMUNITY edition.
* BSS Commerce does not provide extension support in case of
* incorrect edition usage.
* =================================================================
*
* @category   BSS
* @package    Bss_LazyImageLoader
* @author     Extension Team
* @copyright  Copyright (c) 2015-2016 BSS Commerce Co. ( http://bsscommerce.com )
* @license    http://bsscommerce.com/Bss-Commerce-License.txt
*/
namespace Bss\LazyImageLoader\Model\Observer;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;

class LazyImage implements ObserverInterface
{
    protected $_helper;

    public function __construct(
        \Bss\LazyImageLoader\Helper\Data $helper
        ) {
        $this->_helper = $helper;
    }


    public function execute(EventObserver $observer)
    {
        if(!$this->_helper->isEnabled()) return;
        
        $request = $observer->getEvent()->getRequest();
        $response = $observer->getEvent()->getResponse();
        if(!$response) return;

        $html = $response->getBody();
        $html = $this->_helper->lazyLoad($html);

        $response->setBody($html);
    }
}
