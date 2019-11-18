<?php
/**
* Copyright © 2018 Cointopay. All rights reserved.
* See COPYING.txt for license details.
*/

namespace Cointopay\Paymentgateway\Controller\Coin;

class Index extends \Magento\Framework\App\Action\Action
{
    protected $_context;
    protected $_pageFactory;
    protected $_jsonEncoder;
    protected $_coreSession;
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
    * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
    * @param \Magento\Framework\Registry $registry
    */
    public function __construct (
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Json\EncoderInterface $encoder,
        \Magento\Framework\HTTP\Client\Curl $curl,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\View\Result\PageFactory $pageFactory,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Framework\Session\SessionManagerInterface $coreSession,
        \Magento\Framework\Registry $registry
    ) {
        $this->_context = $context;
        $this->_jsonEncoder = $encoder;
        $this->_curl = $curl;
        $this->scopeConfig = $scopeConfig;
        $this->_storeManager = $storeManager;
        $this->_pageFactory = $pageFactory;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->_coreSession = $coreSession;
        $this->_registry = $registry;
        parent::__construct($context);
    }

    public function execute()
    {
        $cCoinId = $this->getRequest()->getParam('coinId');
        $this->_coreSession->start();
        $this->_coreSession->setCoinId($cCoinId);
        return $this->getResponse()->setBody($cCoinId);
    }
}