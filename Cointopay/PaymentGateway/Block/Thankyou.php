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
	
	protected $_pageFactory;
    protected $_jsonEncoder;
    protected $resultJsonFactory;
    protected $_objectManager;
	
	/**
	* @var \Magento\Sales\Model\Order\Email\Sender\InvoiceSender
	*/
    protected $invoiceSender;

    /**
   * @var \Magento\Framework\App\Config\ScopeConfigInterface
   */
   protected $scopeConfig;

    /**
    * @var \Magento\Framework\HTTP\Client\Curl
    */
    protected $_curl;

    /**
    * @var $merchantKey
    **/
    protected $merchantKey;
	
	/**
    * @var $merchantId
    **/
    protected $merchantId;

    /**
    * @var $_curlUrl
    **/
    protected $_curlUrl;
	
	/**
    * @var $transactionId
    **/
    protected $transactionId;

    /**
    * Merchant COINTOPAY API Key
    */
    const XML_PATH_MERCHANT_KEY = 'payment/cointopay_gateway/merchant_gateway_api_key';
	
	/**
    * Merchant ID
    */
    const XML_PATH_MERCHANT_ID = 'payment/cointopay_gateway/merchant_gateway_id';

    /**
    * @var $response
    **/
    protected $response = [] ;
    
    public function __construct(
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Registry $registry,
		\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
		\Magento\Framework\Session\SessionManagerInterface $coreSession,
        \Magento\Framework\HTTP\Client\Curl $curl,
		\Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        array $data = []
    ) {
        parent::__construct($context, $registry, $data);
        $this->checkoutSession = $checkoutSession;
        $this->customerSession = $customerSession;
        $this->_orderFactory = $orderFactory;
		$this->scopeConfig = $scopeConfig;
		$this->_storeManager = $storeManager;
		$this->_coreSession = $coreSession;
		$this->_curl = $curl;
		$this->resultJsonFactory = $resultJsonFactory;
    }

    public function getOrder()
    {
        $this->_order = $this->_orderFactory->create()->loadByIncrementId(
            $this->checkoutSession->getLastRealOrderId());
		if(empty($this->_order)){
			$objectManager =  \Magento\Framework\App\ObjectManager::getInstance();
			$orderDatamodel = $objectManager->get('Magento\Sales\Model\Order')->getCollection()->getLastItem();
			$orderId   =   $orderDatamodel->getId();
			$this->_order = $objectManager->create('\Magento\Sales\Model\Order')->load($orderId);
		}
		return  $this->_order;
    }

    public function getCustomerId()
    {
        return $this->customerSession->getCustomer()->getId();
    }

    public function getCointopayHtml ()
    {
        /*$objectManager = \Magento\Framework\App\ObjectManager::getInstance(); 
        $customerSession = $objectManager->get('Magento\Customer\Model\Session');
        $cointopay_response = $customerSession->getCoinresponse();
        if (isset($cointopay_response)) {
            $customerSession->unsCoinresponse();
            return json_decode($cointopay_response);
        }
        return false;*/
		$objectManager =  \Magento\Framework\App\ObjectManager::getInstance(); 
        /*$storeManager = $objectManager->get('\Magento\Store\Model\StoreManagerInterface');
        $store = $storeManager->getStore();
        $baseUrl = $store->getBaseUrl();
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
		$orderObj = $this->getOrder();
		if(null !== $orderObj->getExtOrderId()){
		$this->transactionId = $orderObj->getExtOrderId();
		$this->merchantId = $this->scopeConfig->getValue(self::XML_PATH_MERCHANT_ID, $storeScope);
        $this->merchantKey = $this->scopeConfig->getValue(self::XML_PATH_MERCHANT_KEY, $storeScope);
        $this->_curlUrl = 'https://cointopay.com/v2REAPI?Call=Transactiondetail&MerchantID='.$this->merchantId.'&APIKey=_&ConfirmCode='.$this->transactionId.'&output=json';
        $this->_curl->get($this->_curlUrl);
        $response = $this->_curl->getBody();
        return json_decode($response);
		}*/
		
		$customerSession = $objectManager->get('Magento\Customer\Model\Session');
        $cointopay_response = $customerSession->getCoinresponse();
        if (isset($cointopay_response)) {
            $customerSession->unsCoinresponse();
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
	
	/**
	 * {@inheritdoc}
	 */
	public function getTransactions()
	{
		
		$objectManager =  \Magento\Framework\App\ObjectManager::getInstance(); 
        /*$storeManager = $objectManager->get('\Magento\Store\Model\StoreManagerInterface');
        $store = $storeManager->getStore();
        $baseUrl = $store->getBaseUrl();
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
		$orderObj = $this->getOrder();
		if(null !== $orderObj->getExtOrderId()){
		$this->transactionId = $orderObj->getExtOrderId();
		$this->merchantId = $this->scopeConfig->getValue(self::XML_PATH_MERCHANT_ID, $storeScope);
        $this->merchantKey = $this->scopeConfig->getValue(self::XML_PATH_MERCHANT_KEY, $storeScope);
        $this->_curlUrl = 'https://app.cointopay.com/v2REAPI?Call=Transactiondetail&MerchantID='.$this->merchantId.'&APIKey='.$this->merchantKey.'&TransactionID='.$this->transactionId.'&output=json';
        $this->_curl->get($this->_curlUrl);
        $response = $this->_curl->getBody();
        return json_decode($response);
		}*/
		
		$customerSession = $objectManager->get('Magento\Customer\Model\Session');
        $cointopay_response = $customerSession->getCoinresponse();
        if (isset($cointopay_response)) {
            $customerSession->unsCoinresponse();
            return json_decode($cointopay_response);
        }
		return false;
	}
}
