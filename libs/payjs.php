<?php
/**
MIT License

Copyright (c) 2017 musnow

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.
 */

namespace Musnow\Payjs;

defined('ABSPATH') || exit;

class Pay {
	private $ssl = true;
	private $requestUrl = 'https://payjs.cn/api/';
	private $MerchantID;
	private $MerchantKey;
	private $NotifyURL = null;
	private $AutoSign = true;
	private $ToObject = true;
	/**
	 * Payjs constructor.
	 * @param $config
	 */
	public function __construct($config = null) {
		if (!is_array($config)) {
			return false;
		}
		foreach ($config as $key => $val) {
			if (property_exists($this, $key)) {
				$this->$key = $val;
			}
		}
	}
	/*
		     * 扫码支付
		     * @return json
	*/
	public function qrPay($data = array()) {
		return $this->merge('native', [
			'total_fee' => $data['TotalFee'],
			'body' => $data['Body'],
			'attach' => @$data['Attach'],
			'out_trade_no' => $data['outTradeNo'],
		]);
	}
	/*
		     * 收银台模式
		     * @return mixed
	*/
	public function Cashier($data = array()) {
		return $this->merge('cashier', [
			'total_fee' => $data['TotalFee'],
			'body' => $data['Body'],
			'attach' => @$data['Attach'],
			'out_trade_no' => $data['outTradeNo'],
			'callback_url' => @$data['callbackUrl'],
		]);
	}
	/*
		     * 订单查询
		     * @return mixed
	*/
	public function Query($data = array()) {
		return $this->merge('check', [
			'payjs_order_id' => $data['PayjsOrderId'],
		]);
	}
	/*
		     * 验证notify数据
		     * @return Boolean
	*/
	public function Checking($data = array()) {
		if (!is_array($data) || empty($this->MerchantKey) || !isset($data['sign']) || !is_scalar($data['sign'])) {
			return false;
		}

		$beSign = strtoupper((string) $data['sign']);
		unset($data['sign']);
		foreach ($data as $value) {
			if (!is_scalar($value)) {
				return false;
			}
		}

		return hash_equals($this->Sign($data), $beSign);
	}
	/*
		     * 关闭订单
		     * @return json
	*/
	public function Close($data = array()) {
		return $this->merge('close', [
			'payjs_order_id' => $data['PayjsOrderId'],
		]);
	}
	/*
		     * 数据签名
		     * @return string
	*/
	protected function Sign(array $data) {
		$data = array_filter(
			$data,
			static function ($value) {
				return '' !== (string) $value;
			}
		);
		ksort($data);
		return strtoupper(md5(urldecode(http_build_query($data)) . '&key=' . $this->MerchantKey));
	}
	/*
		     * 预处理数据
		     * @return mixed
	*/
	protected function merge($method, $data) {
		if ($this->AutoSign) {
			if (!array_key_exists('payjs_order_id', $data)) {
				$data['mchid'] = $this->MerchantID;
				if (!empty($this->NotifyURL)) {
					$data['notify_url'] = $this->NotifyURL;
				}
				if (is_null($data['attach'])) {
					unset($data['attach']);
				}
			}
			$data['sign'] = $this->Sign($data);
		}
		return $this->Curl($method, $data);
	}
	/*
		     * curl
		     * @return mixed
	*/
	protected function Curl($method, $data, $options = array()) {
		$url = $this->requestUrl . $method;
		$response = wp_remote_post($url, array(
			"body"       => $data,
			"timeout"    => 15,
			"redirection" => 0,
			"sslverify"  => true,
		));
		if (is_wp_error($response)) {
			return false;
		} else {
			$status_code = wp_remote_retrieve_response_code($response);
			if ($status_code < 200 || $status_code >= 300) {
				return false;
			}

			$body = wp_remote_retrieve_body($response);
			if ($this->ToObject) {
				return json_decode($body);
			} else {
				return $body;
			}
		}
	}
}
