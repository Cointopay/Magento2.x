<?php
/**
 * Copyright © 2018 Cointopay. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Cointopay\PaymentGateway\Observer;

use Magento\Framework\Event\ObserverInterface;

class SalesOrderPlaceAfterObserver implements ObserverInterface
{

    protected $_context;
    protected $_pageFactory;
    protected $_jsonEncoder;
    protected $_coreSession;
    protected $_jsonDecoder;

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

    protected $_request;
    protected $_historyFactory;
    protected $_orderFactory;

    protected $logger;

    /**
    * @var \Magento\Framework\Stdlib\CookieManagerInterface
    * @param \Magento\Framework\Json\EncoderInterface $encoder
    * @param \Magento\Framework\Json\DecoderInterface $decoder,
    * @param \Magento\Framework\HTTP\Client\Curl $curl
    * @param \Magento\Framework\App\Config\ScopeConfigInterface    $scopeConfig
    * @param \Magento\Store\Model\StoreManagerInterface $storeManager
    * @param \Magento\Framework\View\Result\PageFactory $pageFactory
    */
    protected $_cookieManager;

    public function __construct (
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Stdlib\CookieManagerInterface $cookieManager,
        \Magento\Framework\Json\EncoderInterface $encoder,
        \Magento\Framework\Json\DecoderInterface $decoder,
        \Magento\Framework\HTTP\Client\Curl $curl,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\View\Result\PageFactory $pageFactory,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Sales\Model\Order\Status\HistoryFactory $historyFactory,
        \Magento\Framework\Session\SessionManagerInterface $coreSession,
        \Magento\Sales\Model\OrderFactory $orderFactory
    )
    {
        $this->logger = $logger;
        $this->_cookieManager = $cookieManager;
        $this->_jsonEncoder = $encoder;
        $this->_jsonDecoder = $decoder;
        $this->_curl = $curl;
        $this->scopeConfig = $scopeConfig;
        $this->_storeManager = $storeManager;
        $this->_pageFactory = $pageFactory;
        $this->_request = $request;
        $this->_historyFactory = $historyFactory;
        $this->_coreSession = $coreSession;
        $this->_orderFactory = $orderFactory;
    }

    /**
     * Sales Order Place After event handler.
     *
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
		/** @var $orderInstance Order */
		$order = $observer->getEvent()->getOrder();
		$orderId = $order->getId();
		$this->_coreSession->start();
		$this->coinId =  $this->_coreSession->getCoinid(); //$_SESSION['coin_id'];
		//$this->coinId = $_SESSION['coin_id'];
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();				
		//$orderObject = $objectManager->create('Magento\Sales\Model\Order')->load($orderId);
		$lastOrderId = $order->getIncrementId();
		$this->orderTotal = $order->getGrandTotal();
		$payment_method_code = $order->getPayment()->getMethodInstance()->getCode();
		$storeManager = $objectManager->get('\Magento\Store\Model\StoreManagerInterface');
		$store = $storeManager->getStore();
		$baseUrl = $store->getBaseUrl();
		// // getting data from file
		// $fileSystem = $objectManager->create('\Magento\Framework\Filesystem');
		// $mediaPath=$fileSystem->getDirectoryRead(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA)->getAbsolutePath();
		if ($payment_method_code == 'cointopay_gateway') {
			$response = $this->sendCoins($lastOrderId);
			$orderresponse = $this->_jsonDecoder->decode($response);
			if(!isset($orderresponse['TransactionID'])){
				throw new \Magento\Framework\Exception\LocalizedException(__($response));
			}
			// $_SESSION['cointopay_response'] = $response;
			$this->_coreSession->setCointopayresponse($response);
			$objectManager = \Magento\Framework\App\ObjectManager::getInstance(); 
			$customerSession = $objectManager->get('Magento\Customer\Model\Session');
			$customerSession->setCoinresponse($response); //set value in customer session
			$customerSession->setCointopayresponseGateway($payment_method_code);
			$customerSession->setCointopayresponseOrderId($orderId);
			$customerSession->setCointopayresponselastOrderId($lastOrderId);
			$customerSession->setCointopayresponselastBaseUrl($baseUrl);
			$order->setExtOrderId($orderresponse['TransactionID']);
			$order->save();
		}
    }

    /**
    * @return json response
    **/
    private function sendCoins ($orderId = 0) {
        $objectManager =  \Magento\Framework\App\ObjectManager::getInstance(); 
        $storeManager = $objectManager->get('\Magento\Store\Model\StoreManagerInterface');
        $store = $storeManager->getStore();
        $baseUrl = $store->getBaseUrl();
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        $this->merchantId = trim($this->scopeConfig->getValue(self::XML_PATH_MERCHANT_ID, $storeScope));
        $this->merchantKey = trim($this->scopeConfig->getValue(self::XML_PATH_MERCHANT_KEY, $storeScope));
        $this->securityKey = trim($this->scopeConfig->getValue(self::XML_PATH_MERCHANT_SECURITY, $storeScope));
        $this->currencyCode = $this->_storeManager->getStore()->getCurrentCurrency()->getCode();
        $this->_curlUrl = 'https://cointopay.com/MerchantAPI?Checkout=true&MerchantID='.$this->merchantId.'&Amount='.$this->orderTotal.'&AltCoinID='.$this->coinId.'&CustomerReferenceNr='.$orderId.'&SecurityCode='.$this->securityKey.'&output=json&inputCurrency='.$this->currencyCode.'&transactionconfirmurl='.$baseUrl.'paymentcointopay/order/&transactionfailurl='.$baseUrl.'paymentcointopay/order/';
        $this->_curl->get($this->_curlUrl);
        $response = $this->_curl->getBody();
        return $response;
    }
}