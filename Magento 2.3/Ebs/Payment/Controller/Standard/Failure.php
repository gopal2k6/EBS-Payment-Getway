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

use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\App\Action\Context;
use Magento\Sales\Model\Order;
use Magento\Checkout\Model\Session;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Framework\App\RequestInterface;
		
class Failure extends \Magento\Framework\App\Action\Action  implements CsrfAwareActionInterface
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
        $errorMsg = __('There was an error occurred during paying process. Payment Cancel By Customer');

        $order = $this->getOrder();
		//$order->setStatus('pending');

        if (!$order->getId()) {
            $this->norouteAction();
            return;
        }

        if ($order->getId()) {
			
            $order->addStatusToHistory($order->getStatus(), $errorMsg);
            $order->cancel();
            $order->save();
        }

		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$baseUrl = $objectManager->create('\Magento\Store\Model\StoreManagerInterface')->getStore()->getBaseUrl();
		$resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $this->pageFactory->create();
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
