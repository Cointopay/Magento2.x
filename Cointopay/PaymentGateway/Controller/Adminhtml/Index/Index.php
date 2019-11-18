<?php

namespace Cointopay\Paymentgateway\Controller\Adminhtml\Index;

class Index extends \Magento\Backend\App\Action
{
    protected $_context;
    protected $_pageFactory;
    protected $_jsonEncoder;
    protected $_jsonDecoder;

    /**
    * @var \Magento\Framework\HTTP\Client\Curl
    */
    protected $_curl;

    /**
    * @var $merchantId
    **/
    protected $merchantId;

    /**
    * @var $_curlUrl
    **/
    protected $_curlUrl;

    /**
    * @var $response
    **/
    protected $response = [] ;

    /*
    * @param \Magento\Backend\App\Action\Context $context
    * @param \Magento\Framework\Json\EncoderInterface $encoder
    * @param \Magento\Framework\Json\DecoderInterface $decoder
    * @param \Magento\Framework\HTTP\Client\Curl $curl
    * @param \Magento\Framework\View\Result\PageFactory $pageFactory
    */
    public function __construct (
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Json\EncoderInterface $encoder,
        \Magento\Framework\Json\DecoderInterface $decoder,
        \Magento\Framework\HTTP\Client\Curl $curl,
        \Magento\Framework\View\Result\PageFactory $pageFactory
    ) {
        $this->_context = $context;
        $this->_jsonEncoder = $encoder;
        $this->_jsonDecoder = $decoder;
        $this->_curl = $curl;
        $this->_pageFactory = $pageFactory;
        parent::__construct($context);
    }

    public function execute()
    {
        if ($this->getRequest()->isXmlHttpRequest()) {
            $this->merchantId = $this->getRequest()->getPost('merchant');
            $type = $this->getRequest()->getPost('type');
            if ($type == 'merchant') {
                if (isset($this->merchantId))
                {
                    $this->response = $this->getSupportedCoins();
                }
                $this->getResponse()->representJson($this->_jsonEncoder->encode($this->response));
            } else {
                $response = $this->verifyCode();
                if ($response) {
                    $this->getResponse()->representJson($this->_jsonEncoder->encode(array ('status' => 'success')));
                } else {
                    $this->getResponse()->representJson($this->_jsonEncoder->encode(array ('status' => 'error')));
                }
            }
        }
        return;
    }

    /**
    * @return available coins for merchant
    **/
    private function getSupportedCoins ()
    {
        $this->_curlUrl = 'https://cointopay.com/CloneMasterTransaction?MerchantID='.$this->merchantId.'&output=json';
        $this->_curl->get($this->_curlUrl);
        $response = $this->_curl->getBody();
        $supportedCoins = $this->_jsonDecoder->decode($response);
        $coins = [];
        if (count($supportedCoins) > 0)
        {
            foreach ($supportedCoins as $k => $title)
            {
                if ($k % 2 == 0)
                {
                    $coins[] = array (
                        'value' => $supportedCoins[$k+1],
                        'title' => $title,
                    );
                }
            } 
        }
        return $coins;
    }

    // verify security code
    private function verifyCode () {
        $this->_curlUrl = 'https://cointopay.com/MerchantAPI?Checkout=true&MerchantID=123&Amount=1000&AltCoinID=1&CustomerReferenceNr=buy%20something%20from%20me&SecurityCode='.$this->merchantId.'&inputCurrency=EUR&output=json&testmerchant';
        $this->_curl->get($this->_curlUrl);
        $response = $this->_curl->getBody();
        if ($response == '"SecurityCode should be type Integer, please correct."') {
            return false;
        } else {
            return true;
        }
    }
}