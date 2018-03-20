<?php
/**
 * Copyright © 2018 Cointopay. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Cointopay\PaymentGateway\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;

class SalesOrderPlaceAfterObserver implements ObserverInterface
{

    protected $_context;
    protected $_pageFactory;
    protected $_jsonEncoder;

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
        \Magento\Framework\HTTP\Client\Curl $curl,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\View\Result\PageFactory $pageFactory,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Sales\Model\Order\Status\HistoryFactory $historyFactory,
        \Magento\Sales\Model\OrderFactory $orderFactory
    )
    {
        $this->logger = $logger;
        $this->_cookieManager = $cookieManager;
        $this->_jsonEncoder = $encoder;
        $this->_curl = $curl;
        $this->scopeConfig = $scopeConfig;
        $this->_storeManager = $storeManager;
        $this->_pageFactory = $pageFactory;
        $this->_request = $request;
        $this->_historyFactory = $historyFactory;
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
        $order = $observer->getOrder();
        $lastOrderId = $observer->getOrder()->getIncrementId();
        $this->orderTotal = $observer->getOrder()->getGrandTotal();
        $payment_method_code = $order->getPayment()->getMethodInstance()->getCode();
        // getting data from file
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $fileSystem = $objectManager->create('\Magento\Framework\Filesystem');
        $mediaPath=$fileSystem->getDirectoryRead(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA)->getAbsolutePath();
        $this->coinId = $_SESSION['coin_id'];
        if ($payment_method_code == 'cointopay_gateway') {
            $response = $this->sendCoins($lastOrderId);
            $_SESSION['cointopay_response'] = $response;
            $orderresponse = @json_decode($response);
            $order->setExtOrderId($orderresponse->TransactionID);
            $order->save();
        }
        exit;
    }

    /**
    * @return json response
    **/
    private function sendCoins ($orderId = 0) {
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        $this->merchantId = trim($this->scopeConfig->getValue(self::XML_PATH_MERCHANT_ID, $storeScope));
        $this->merchantKey = trim($this->scopeConfig->getValue(self::XML_PATH_MERCHANT_KEY, $storeScope));
        $this->securityKey = trim($this->scopeConfig->getValue(self::XML_PATH_MERCHANT_SECURITY, $storeScope));
        $this->currencyCode = $this->_storeManager->getStore()->getCurrentCurrency()->getCode();
        
        $this->_curlUrl = 'https://cointopay.com/MerchantAPI?Checkout=true&MerchantID='.$this->merchantId.'&Amount='.$this->orderTotal.'&AltCoinID='.$this->coinId.'&CustomerReferenceNr='.$orderId.'&SecurityCode='.$this->securityKey.'&output=json&inputCurrency='.$this->currencyCode;
        $this->_curl->get($this->_curlUrl);
        $response = $this->_curl->getBody();
        $orderresponse = @json_decode($response);
        $this->logger->info('$this->orderTotal respinse from cointopay');
        $this->logger->info(print_r($orderresponse, true));
        $this->logger->info($orderresponse->TransactionID);
        $this->logger->info('$payment_method_code ****');
        return $response;
    }
}