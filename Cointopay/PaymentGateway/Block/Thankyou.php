<?php
/**
 * Copyright © 2018 Cointopay. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Cointopay\PaymentGateway\Block;

class Thankyou extends \Magento\Sales\Block\Order\Totals
{
    protected $checkoutSession;
    protected $customerSession;
    protected $_orderFactory;
    protected $_coreSession;
    
    public function __construct(
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        parent::__construct($context, $registry, $data);
        $this->checkoutSession = $checkoutSession;
        $this->customerSession = $customerSession;
        $this->_orderFactory = $orderFactory;
    }

    public function getOrder()
    {
        return  $this->_order = $this->_orderFactory->create()->loadByIncrementId(
            $this->checkoutSession->getLastRealOrderId());
    }

    public function getCustomerId()
    {
        return $this->customerSession->getCustomer()->getId();
    }

    public function getCointopayHtml ()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance(); 
        $customerSession = $objectManager->get('Magento\Customer\Model\Session');
        $cointopay_response = $customerSession->getCoinresponse();
        if (isset($cointopay_response)) {
            $customerSession->unsCoinresponse();
			$customerSession->unsCointopayresponseGateway();
			$customerSession->unsCointopayresponseOrderId();
			$customerSession->unsCointopayresponselastOrderId();
			$customerSession->unsCointopayresponselastBaseUrl();
            return json_decode($cointopay_response);
        }
        return false;
    }
    
    /**
     * Returns value view
     *
     * @return string | URL
     */
    public function getCoinsPaymentUrl()
    {
        return $this->getUrl("paymentcointopay");
    }
}