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
 * Secureebs Standard Front Controller
 *
 * @category   Mage
 * @package    Mage_Secureebs
 * @name       Mage_Secureebs_StandardController
 * @author     Magento Core Team <core@magentocommerce.com>
*/

namespace Ebs\Payment\Controller\Standard;


use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Sales\Model\Order;
use Magento\Checkout\Model\Session;
use Magento\Framework\Controller\ResultFactory;
use Magento\Sales\Model\Order\Email\Sender\OrderSender;
use Magento\Sales\Model\Order\Email\Sender\InvoiceSender;
use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Framework\App\RequestInterface;
		
class Success extends \Magento\Framework\App\Action\Action implements CsrfAwareActionInterface
{
	protected $_order;
	protected $order;
	protected $pageFactory;
	protected $salesOrder;
	protected $checkoutSession;
	
    public function __construct(Context $context, PageFactory $pageFactory, Order $salesOrder, Session $checkoutSession)
    {
        $this->pageFactory = $pageFactory;
        $this->order = $salesOrder;
        $this->checkoutSession = $checkoutSession;
		//echo "<pre>"; print_r($_POST); echo "</pre>"; exit;
       return parent::__construct($context);
	}
	
	
	/**
     * When a customer chooses Secureebs on Checkout/Payment page
     *
    */
	
	
	public function createCsrfValidationException(
	    RequestInterface $request
	): ?InvalidRequestException {
	    return null;
	}

	public function validateForCsrf(RequestInterface $request): ?bool
	{
	    return true;
	}
	
	public function execute()
    {		
		
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$config = $objectManager->create('\Ebs\Payment\Model\Config');		
		$secret_key = $config->getSecretKey();
    	$hashType = $config->getHashType();
    	$params = $secret_key;
    	$response = $_POST;
		/*echo '<pre>';
		print_r($_POST);
		echo '</pre>';
		die();
		*/
    	$secureHash = $response['SecureHash'];	
		
        $this->checkoutSession->setErrorCode($response['ResponseCode']);
        $this->checkoutSession->setErrorMessage($response['ResponseMessage']);
    	
		unset($response['SecureHash']);
		ksort($response);
		foreach ($response as $key => $value){
			if (strlen($value) > 0) {
				$params .= '|'.$value;
			}
		}	
			
		if (strlen($params) > 0) {
			if($hashType == "SHA512")
				$hashValue = strtoupper(hash('SHA512',$params));	
			if($hashType == "SHA1")
				$hashValue = strtoupper(sha1($params));	
			if($hashType == "MD5")
				$hashValue = strtoupper(md5($params));	
			
		}

		$hashValid = ($hashValue == $secureHash) ? true : false;
		$resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
		$baseUrl = $objectManager->create('\Magento\Store\Model\StoreManagerInterface')->getStore()->getBaseUrl();

		$orderSender = $objectManager->create('\Magento\Sales\Model\Order\Email\Sender\OrderSender');
		$invoiceSender = $objectManager->create('\Magento\Sales\Model\Order\Email\Sender\InvoiceSender');
		
    	if($response['ResponseCode'] == 0 && $hashValid) {
    		$this->checkoutSession->setQuoteId($this->checkoutSession->getSecureebsStandardQuoteId());
    		$this->checkoutSession->unsSecureebsStandardQuoteId();

    		$order = $this->getOrder();
    		$order->setStatus($order::STATE_PROCESSING);

    		if (!$order->getId()) {
    			$this->norouteAction();
    			return;
    		}

    		$order->addStatusToHistory(
	    		$order->getStatus(),
	    		__('Customer successfully returned from Secureebs')
    		);
			
			$order->addStatusToHistory(
	    		$order->getStatus(),
	    		__('Response Message - '. $response['ResponseMessage'])
    		);
			
			$order->addStatusToHistory(
	    		$order->getStatus(),
	    		__('Payment ID - '. $response['PaymentID'])
    		);
			
			$order->addStatusToHistory(
	    		$order->getStatus(),
	    		__('Payment Method - '. $response['PaymentMethod'])
    		);
			
			$order->addStatusToHistory(
	    		$order->getStatus(),
	    		__('Transaction ID - '. $response['TransactionID'])
    		);

    		$order->save();
			$orderSender->send($order);
		
			$resultRedirect->setUrl($baseUrl.'checkout/onepage/success');
    	} else {
			
			
			
			$resultRedirect->setUrl($baseUrl.'ebs/standard/failure');
    	}

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
		
		return $resultRedirect;
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