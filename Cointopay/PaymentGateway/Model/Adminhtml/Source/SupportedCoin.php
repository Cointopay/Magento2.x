<?php
/**
 * Copyright © 2018 Cointopay. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Cointopay\PaymentGateway\Model\Adminhtml\Source;
use Magento\Payment\Model\Method\AbstractMethod;
/**
 * Class SupportedCoin
 */
class SupportedCoin implements \Magento\Framework\Option\ArrayInterface
{
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
    * @var $_curlUrl
    **/
    protected $_curlUrl;
    /**
    * @param \Magento\Framework\App\Config\ScopeConfigInterface    $scopeConfig
    * @param \Magento\Framework\Json\DecoderInterface $decoder
    * @param \Magento\Framework\HTTP\Client\Curl                   $curl
    */
    public function __construct (
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Json\DecoderInterface $decoder,
        \Magento\Framework\HTTP\Client\Curl $curl
    )
    {
        $this->scopeConfig = $scopeConfig;
        $this->_jsonDecoder = $decoder;
        $this->_curl = $curl;
    }
    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        $this->merchantId = $this->scopeConfig->getValue('payment/cointopay_gateway/merchant_gateway_id', $storeScope);
        if (isset($this->merchantId))
        {
            return $this->getSupportedCoins();
        } else
        {
            return [];
        }
    }
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
                        'label' => __($title)
                    );
                }
            } 
        }
        return $coins;
    }
}