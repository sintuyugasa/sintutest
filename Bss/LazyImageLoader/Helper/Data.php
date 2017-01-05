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
namespace Bss\LazyImageLoader\Helper;

/**
 * Visitor Observer
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
	protected $_scopeConfig;
	protected $_request;
	public $_storeManager;

	public function __construct(
		\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig , \Magento\Framework\App\Request\Http $request, \Magento\Store\Model\StoreManagerInterface $storeManager) {
		$this->_scopeConfig = $scopeConfig;
		$this->_request = $request;
		$this->_storeManager=$storeManager;
	}

	public function isEnabled() {
		$active =  $this->_scopeConfig->getValue('lazyimageloader/general/active', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
		if($active != 1) {
			return false;
		}

		//check home page
		$active =  $this->_scopeConfig->getValue('lazyimageloader/general/home_page', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
		if($active == 1 && $this->_request->getFullActionName() == 'cms_index_index') {
			return false;
		}
		//end

		$module = $this->_request->getModuleName();
		$controller = $this->_request->getControllerName();
		$action = $this->_request->getActionName();
		//check controller
		if($this->regexMatchSimple($this->_scopeConfig->getValue('lazyimageloader/general/controller', \Magento\Store\Model\ScopeInterface::SCOPE_STORE),"{$module}_{$controller}_{$action}",1))
			return false;
		
		//check path
		if($this->regexMatchSimple($this->_scopeConfig->getValue('lazyimageloader/general/path', \Magento\Store\Model\ScopeInterface::SCOPE_STORE),$this->_request->getRequestUri(),2))
			return false;

		return true;
	}

	public function getThreshold(){
		return $this->_scopeConfig->getValue('lazyimageloader/general/threshold', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
	}

	public function getLoadingWidth(){
		return $this->_scopeConfig->getValue('lazyimageloader/general/loading_width', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
	}

	public function lazyLoad($html) {
		$regex = '#<img class="product-image-photo([^>]*) src="([^"/]*/?[^".]*\.[^"]*)"(?!.*?notlazy)([^>]*)>#';
		if(preg_match('/MSIE/i',$_SERVER['HTTP_USER_AGENT'])) {
			$replace = '<noscript><img$1 src="$2" $3></noscript>';
			$replace .= '<img class="product-image-photo lazy$1 src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7" data-src="$2"$3>';
		}else {
			$replace = '<img class="product-image-photo lazy$1 src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7" srcset="" data-src="$2"$3>';
		}
		$html = preg_replace($regex, $replace, $html);
		return $html;
	}

	public function getLazyImage(){
		$img =  $this->_scopeConfig->getValue('lazyimageloader/general/loading', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

		if(!$img || $img == '') {
			return $this->getLazyImg();
		}

		return $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA).'lazyimage'.DIRECTORY_SEPARATOR.$img;
	}

	public function regexMatchSimple($regex, $matchTerm,$type) {

		if (!$regex)
			return false;

		$rules = @unserialize($regex);

		if (empty($rules))
			return false;

		foreach ($rules as $rule) {
			$regex = trim($rule['lazyimage'], '#');
			if($type == 1) {
				$regexs = explode('_', $regex);
				switch(count($regexs)) {
					case 1:
					$regex = $regex.'_index_index';
					break;
					case 2:
					$regex = $regex.'_index';
					break;
					default:
					break;
				}
			}

			$regexp = '#' . $regex . '#';
			if (@preg_match($regexp, $matchTerm))
				return true;

		}
		return false;
	}

	// public function lazyLoad2($html) {
	// 	$conditionalJsPattern = '/src\=\"([^\s]+(?=\.(bmp|gif|jpeg|jpg|png))\.\2)\"/';
	// 	preg_match_all($conditionalJsPattern,$html,$_matches);
	// 	foreach ($_matches[0] as $key => $match) {
	// 		$html = str_replace($match, 'src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7" data-'.$match,$html);
	// 	}
	// 	return $html;
	// }

	protected function getLazyImg() {
		return 'data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7';
	}
}