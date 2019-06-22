<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * @category   Mage
 * @package    Mage_Secureebs
 * @copyright  Copyright (c) 2008 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Redirect to Secureebs
 *
 * @category    Mage
 * @package     Mage_Secureebs
 * @name        Mage_Secureebs_Block_Standard_Redirect
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Ebs\Payment\Block\Standard;

use \Magento\Framework\View\Element\Template\Context;
use \Magento\Customer\Model\Session;
use \Magento\Framework\ObjectManagerInterface;

class Redirect extends \Magento\Framework\View\Element\Template
{
	protected $checkoutSession;
	
	public function __construct(Context $context, Session $checkoutSession, ObjectManagerInterface $objectManager,array $data = []) {
		parent::__construct($context, $data);
		$this->checkoutSession = $checkoutSession;
	}
  
	protected function _prepareLayout()
    {
		
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$config = $objectManager->create('\Ebs\Payment\Model\Config');
		
        $secret_key = $config->getSecretKey();
		$transaction_key = $config->getTransactionMode();
		$hashType = $config->getHashType();
		$pageId = $config->getPageId();
		
		$this->setSecretKey($secret_key);
		$this->setTransactionKey($transaction_key);
		$this->setHashType($hashType);
		$this->setPageId($pageId);
        $standard = $objectManager->create('\Ebs\Payment\Model\Standard');
		
		//echo "<br>SECURE EBS URL   ".$standard->getSecureebsUrl();		

		if($transaction_key == 1) {
			$mode = "TEST";
			$actionUrl = "https://sandbox.secure.ebs.in/pg/ma/payment/request/";
		} else {
			$mode = "LIVE";
			$actionUrl = "https://secure.ebs.in/pg/ma/payment/request/";
		}
        
		$params = $standard->getStandardCheckoutFormFields();
		
		$params['mode'] =  $mode;
		$params['page_id'] =  $pageId;	
		ksort($params);
		$hashData = $secret_key;
		
		
		
    	foreach ($params as $key => $value){
			if (($key == 'address') || ($key == 'ship_address')) {
				if(isset($value[1]) && !empty($value[1])) {
					$hashData .= '|'.$value[0] .','.$value[1];
				} else {
					$hashData .= '|'.$value[0];
				}
			} else {
				if(strlen($value) > 0) {
					$hashData .= '|'.$value;
				}
			}
		}
		if (strlen($hashData) > 0) {
			if($hashType == "SHA512")
				$hashValue = strtoupper(hash('SHA512',$hashData));	
			if($hashType == "SHA1")
				$hashValue = strtoupper(sha1($hashData));	
			if($hashType == "MD5")
				$hashValue = strtoupper(md5($hashData));	
		}

        $this->setActionUrl($actionUrl);
		$this->setParams($params);
		$this->setHashValue($hashValue);
		
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
       $_cacheTypeList = $objectManager->create('\Magento\Framework\App\Cache\TypeListInterface');
       $_cacheFrontendPool = $objectManager->create('\Magento\Framework\App\Cache\Frontend\Pool');

       $types = array('full_page');
       foreach ($types as $type) {
         $_cacheTypeList->cleanType($type);
       }

      foreach ($_cacheFrontendPool as $cacheFrontend) {
        $cacheFrontend->getBackend()->clean();
       }
    }
	
	/**
	* Retrieve current order
	*
	* @return \Magento\Sales\Model\Order
	*/
	public function getOrder()
	{
	   $orderId = $this->checkoutSession->getLastOrderId();
	   return $this->order->load($orderId);
	}
}
