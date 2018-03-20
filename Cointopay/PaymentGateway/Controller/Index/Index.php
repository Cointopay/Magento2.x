<?php
/**
* Copyright © 2018 Cointopay. All rights reserved.
* See COPYING.txt for license details.
*/

namespace Cointopay\Paymentgateway\Controller\Index;

class Index extends \Magento\Framework\App\Action\Action
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

    /**
    * @var \Magento\Framework\Registry
    */
    protected $_registry;

    /*
    * @param \Magento\Framework\App\Action\Context $context
    * @param \Magento\Framework\Json\EncoderInterface $encoder
    * @param \Magento\Framework\HTTP\Client\Curl $curl
    * @param \Magento\Framework\App\Config\ScopeConfigInterface    $scopeConfig
    * @param \Magento\Store\Model\StoreManagerInterface $storeManager
    * @param \Magento\Framework\View\Result\PageFactory $pageFactory
    * @param \Magento\Framework\Registry $registry
    */
    public function __construct (
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Json\EncoderInterface $encoder,
        \Magento\Framework\HTTP\Client\Curl $curl,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\View\Result\PageFactory $pageFactory,
        \Magento\Framework\Registry $registry
    ) {
        $this->_context = $context;
        $this->_jsonEncoder = $encoder;
        $this->_curl = $curl;
        $this->scopeConfig = $scopeConfig;
        $this->_storeManager = $storeManager;
        $this->_pageFactory = $pageFactory;
        $this->_registry = $registry;
        parent::__construct($context);
    }

    public function execute()
    {
        if ($this->getRequest()->isXmlHttpRequest()) {
            $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
            $this->coinId = $this->getRequest()->getPost('paymentaction');
            $type = $this->getRequest()->getPost('type');
            if ($type == 'status') {
                $response = $this->getStatus($this->coinId);
            } else {
                $_SESSION['coin_id'] = $this->coinId;
                print json_encode( 
                    array (
                        'status' => 'success',
                        'coindid' => $this->coinId,
                    )
                );
                die;
            }
        }
        return;
    }

    /**
    * @return json response
    **/
    private function payOrder() {
        $this->orderTotal = $this->getCartAmount();
        $this->_curlUrl = 'https://cointopay.com/MerchantAPI?Checkout=true&MerchantID='.$this->merchantId.'&Amount='.$this->orderTotal.'&AltCoinID='.$this->coinId.'&CustomerReferenceNr=buy%20something%20from%20me&SecurityCode='.$this->securityKey.'&output=json&inputCurrency='.$this->currencyCode;
        $this->_curl->get($this->_curlUrl);
        $response = $this->_curl->getBody();
        $orderresponse = @json_decode($response);
        print_r($response);
        die;
    }

    /**
    * @return Total order amount from cart
    **/
    private function getCartAmount () {
        
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $cart = $objectManager->get('\Magento\Checkout\Model\Cart');   
        return $cart->getQuote()->getGrandTotal();
    }

    /**
    * @return string payment status
    **/
    private function getStatus ($TransactionID) {
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        $this->merchantId = trim($this->scopeConfig->getValue(self::XML_PATH_MERCHANT_ID, $storeScope));
        $this->_curlUrl = 'https://cointopay.com/CloneMasterTransaction?MerchantID='.$this->merchantId.'&TransactionID='.$TransactionID.'&output=json';
        $this->_curl->get($this->_curlUrl);
        $response = $this->_curl->getBody();
        print_r($response);
        die;
    }
}