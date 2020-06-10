<?php
/**
* Copyright © 2018 Cointopay. All rights reserved.
* See COPYING.txt for license details.
*/

namespace Cointopay\Paymentgateway\Controller\Order;

class Index extends \Magento\Framework\App\Action\Action
{
    protected $_context;
    protected $_pageFactory;
    protected $_jsonEncoder;
    protected $orderManagement;
    protected $resultJsonFactory;

    /**
   * @var \Magento\Framework\App\Config\ScopeConfigInterface
   */
   protected $scopeConfig;

    /**
    * @var \Magento\Framework\HTTP\Client\Curl
    */
    protected $_curl;

    /**
    * @var $merchantId
    **/
    protected $merchantId;

    /**
    * @var $merchantKey
    **/
    protected $merchantKey;

    /**
    * @var $coinId
    **/
    protected $coinId;

    /**
    * @var $type
    **/
    protected $type;

    /**
    * @var $orderTotal
    **/
    protected $orderTotal;

    /**
    * @var $_curlUrl
    **/
    protected $_curlUrl;

    /**
    * @var currencyCode
    **/
    protected $currencyCode;

    /**
    * @var $_storeManager
    **/
    protected $_storeManager;
    
    /**
    * @var $securityKey
    **/
    protected $securityKey;

    /**
    * Merchant ID
    */
    const XML_PATH_MERCHANT_ID = 'payment/cointopay_gateway/merchant_gateway_id';

    /**
    * Merchant COINTOPAY API Key
    */
    const XML_PATH_MERCHANT_KEY = 'payment/cointopay_gateway/merchant_gateway_key';

    /**
    * Merchant COINTOPAY SECURITY Key
    */
    const XML_PATH_MERCHANT_SECURITY = 'payment/cointopay_gateway/merchant_gateway_security';

    /**
    * API URL
    **/
    const COIN_TO_PAY_API = 'https://cointopay.com/MerchantAPI';

    /**
    * @var $response
    **/
    protected $response = [] ;

    /*
    * @param \Magento\Framework\App\Action\Context $context
    * @param \Magento\Framework\Json\EncoderInterface $encoder
    * @param \Magento\Framework\HTTP\Client\Curl $curl
    * @param \Magento\Framework\App\Config\ScopeConfigInterface    $scopeConfig
    * @param \Magento\Store\Model\StoreManagerInterface $storeManager
    * @param \Magento\Framework\View\Result\PageFactory $pageFactory
    * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
    */
    public function __construct (
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Json\EncoderInterface $encoder,
        \Magento\Framework\HTTP\Client\Curl $curl,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\View\Result\PageFactory $pageFactory,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Sales\Api\OrderManagementInterface $orderManagement
    ) {
        $this->_context = $context;
        $this->_jsonEncoder = $encoder;
        $this->_curl = $curl;
        $this->scopeConfig = $scopeConfig;
        $this->_storeManager = $storeManager;
        $this->_pageFactory = $pageFactory;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->orderManagement = $orderManagement;
        parent::__construct($context);
    }

    public function execute()
    {
        try {
            $customerReferenceNr = $this->getRequest()->getParam('CustomerReferenceNr');
            $status = $this->getRequest()->getParam('status');
            $ConfirmCode = $this->getRequest()->getParam('ConfirmCode');
            $SecurityCode = $this->getRequest()->getParam('SecurityCode');
            $notenough = $this->getRequest()->getParam('notenough');
            $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
            $this->securityKey = $this->scopeConfig->getValue(self::XML_PATH_MERCHANT_SECURITY, $storeScope);
            if ($this->securityKey == $SecurityCode) {
				$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
				$order = $objectManager->create('\Magento\Sales\Model\Order')
					->loadByIncrementId($customerReferenceNr);
				if (count($order->getData()) > 0) {
					if ($status == 'paid' && $notenough == 1) {
						$order->setState('pending_payment')->setStatus('pending_payment');
						$order->save();
					} else if ($status == 'paid') {
						$order->setState('complete')->setStatus('complete');
						$order->save();
					} else if ($status == 'failed') {
						if ($order->getStatus() == 'complete') {
							/** @var \Magento\Framework\Controller\Result\Json $result */
							$result = $this->resultJsonFactory->create();
							return $result->setData([
								'CustomerReferenceNr' => $customerReferenceNr,
								'status' => 'error',
								'message' => 'Order cannot be cancel now, because it is completed now.'
							]);
						} else {
							$this->orderManagement->cancel($order->getId());
						}
					} else {
						/** @var \Magento\Framework\Controller\Result\Json $result */
						$result = $this->resultJsonFactory->create();
						return $result->setData([
							'CustomerReferenceNr' => $customerReferenceNr,
							'status' => 'error',
							'message' => 'Order status should have valid value.'
						]);
					}
					/** @var \Magento\Framework\Controller\Result\Json $result */
					$result = $this->resultJsonFactory->create();
					return $result->setData([
						'CustomerReferenceNr' => $customerReferenceNr,
						'status' => 'success',
						'message' => 'Order status successfully updated.'
					]);
				} else {
					/** @var \Magento\Framework\Controller\Result\Json $result */
					$result = $this->resultJsonFactory->create();
					return $result->setData([
						'CustomerReferenceNr' => $customerReferenceNr,
						'status' => 'error',
						'message' => 'No order found.'
					]);
				}
			} else {
				/** @var \Magento\Framework\Controller\Result\Json $result */
				$result = $this->resultJsonFactory->create();
				return $result->setData([
					'CustomerReferenceNr' => $customerReferenceNr,
					'status' => 'error',
					'message' => 'Security key is not valid.'
				]);
			}
        } catch (\Exception $e) {
            /** @var \Magento\Framework\Controller\Result\Json $result */
            $result = $this->resultJsonFactory->create();
            return $result->setData([
                'CustomerReferenceNr' => $customerReferenceNr,
                'status' => 'error',
                'message' => 'General error'
            ]);
        }
        /** @var \Magento\Framework\Controller\Result\Json $result */
        $result = $this->resultJsonFactory->create();
        return $result->setData([
            'status' => 'error'
        ]);
    }
}
