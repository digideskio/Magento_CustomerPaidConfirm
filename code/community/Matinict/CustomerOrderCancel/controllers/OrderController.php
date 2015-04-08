<?php
/**
 * @category    Matinict
 * @package     Matinict_CustomerOrderCashPaid
 * @copyright   Copyright (c) 2015 Abdul Matin (http://www.matinict.wordpress.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Matinict_CustomerOrderCancel_OrderController extends Mage_Core_Controller_Front_Action {

    /**
     * Processing order on customer request
     */
    public function cancelAction()
    {

        // Retrieve order_id passed by clicking on "Processing Order" in customer account
        $orderId = $this->getRequest()->getParam('order_id');

        // Load Mage_Sales_Model_Order object
        $order = Mage::getModel('sales/order')->load($orderId);

        // Retrieve catalog session.
        // We must use catalog session as customer session messages are not initiated for sales order view
        // and this is where we want to redirect at the end of this action
        // @see Mage_Sales_Controller_Abstract::_viewAction()
        $session = Mage::getSingleton('catalog/session');

        try {

            // Make sure that the order can still be Processing since customer clicked on "Processing Order"
            if(!Mage::helper('customerordercancel')->canCancel($order)) {
                throw new Exception('Order cannot be Cash Paid Processing anymore.');
            }

            // Cancel and save the order
			$order->setState(Mage_Sales_Model_Order::STATE_PROCESSING, true)->save();
           // $order->cancel();
           // $order->save();
		   

            // If sending transactionnal email is enabled in system configuration, we send the email
            if(Mage::getStoreConfigFlag('sales/cancel/send_email')) {
                $order->sendOrderUpdateEmail();
            }

            $session->addSuccess($this->__('The order has been Cash Paid Processing & Admin Get Notification.'));
        }
        catch (Exception $e) {
            Mage::logException($e);
            $session->addError($this->__('The order cannot be Cash Paid Processing.'));
        }

        // Redirect to current sale order view
        $this->_redirect('sales/order/view', array('order_id' => $orderId));
    }
}