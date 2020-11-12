<?php

namespace Chriswest101\Paynow;

use DateTime;
use File;
use InvalidArgumentException;
use Chriswest101\Paynow\Helpers\CRC16;
use Chriswest101\Paynow\Helpers\QRCodeService;

class Paynow
{
    	const PAY_VIA_UEN = '2';
	const PAY_VIA_MOBILE = '0';

	/**
	 * @var $uen
	 */
	protected ?string $uen;

	/**
	 * @var $mobile
	 */
	protected ?string $mobile;

	/**
	 * @var $paymentType
	 */
	protected string $paymentType;

	/**
	 * @var $amount
	 */
	protected float $amount;

	/**
	 * @var $editable
	 */
	protected bool $editable;

	/**
	 * @var $expiry
	 */
	protected DateTime $expiry;

	/**
	 * @var $company
	 */
	protected string $company;

	/**
	 * @var $merchantCountry
	 */
	protected string $merchantCountry;

	/**
	 * @var $merchantCity
	 */
	protected string $merchantCity;

	/**
	 * @var $uniqueOrderCode
	 */
	protected string $uniqueOrderCode;

	/**
	 * @var $createAsBase64Image
	 */
	protected bool $createAsBase64Image;

	/**
	 * Generate a PayNow QR Code
	 * 
	 * @param float $amount - Amount of transaction
	 * @param boolean $editable - If amount is editable
	 * @param string $uniqueOrderCode - Unique reference for QR Code
	 * @param DateTime $expiry - Expiry date of QR Code
	 * @param string $company - Company Name
	 * @param string $merchantCountry - ISO 3166-1 alpha-2 country code of merchant company
	 * @param string $merchantCity - City of merchant company 
	 * @param null|string $uen - Company UEN to pay (Mandatory unless mobile is provided)
	 * @param null|string $mobile - Mobile number to pay (Mandatory unless UEN is provided)
	 * @param bool $createAsBase64Image - Return QR Code as Base64 image
	 */
	public function generate(
		float $amount,
		bool $editable,
		string $uniqueOrderCode,
		DateTime $expiry,
		string $company,
		string $merchantCountry = 'SG',
		string $merchantCity = "Singapore",
		string $uen = null,
		string $mobile = null,
		bool $createAsBase64Image = false)
	{
		$this->uen = $uen;
		$this->mobile = $mobile;
		$this->amount = $amount;
		$this->editable = $editable;
		$this->expiry = $expiry;
		$this->uniqueOrderCode = $uniqueOrderCode;
		$this->company = $company;
		$this->merchantCountry = $merchantCountry;
		$this->merchantCity = $merchantCity;
		$this->createAsBase64Image = $createAsBase64Image;

		$this->validateParams();
		return $this->build();
	}

	/**
	 * Validate the provided params
	 *
	 * @throws InvalidArgumentException
	 * @return void
	 */
	private function validateParams(): void
	{
		if ($this->amount < 0) {
			throw new InvalidArgumentException("Amount cannot be < 0.");
		}

		if ($this->company == "") {
			throw new InvalidArgumentException("Company name must be provided.");
		}
		
		if ($this->merchantCountry == "") {
			throw new InvalidArgumentException("Country of merchant must be provided.");
		}
		
		if ($this->merchantCity == "") {
			throw new InvalidArgumentException("City of merchant must be provided.");
		}
		
		if ($this->expiry < new DateTime()) {
			throw new InvalidArgumentException("Expiry date of QR Code must be in the future.");
		}

		if ($this->uen === null && $this->mobile === null) {
			throw new InvalidArgumentException("Mobile OR company UEN must be provided.");
		}

		if ($this->uen !== null) {
			$this->paymentType = PayNowService::PAY_VIA_UEN;
		} else {
			$this->paymentType = PayNowService::PAY_VIA_MOBILE;
		}
	}

	/**
	 * Build the PayNow QR code
	 *
	 * @return string
	 */
	private function build(): string
	{
		$mainData = 
		[
			[ "id" => '00', "value" => '01' ],                    											// ID 00: Payload Format Indicator (Fixed to '01')
			[ "id" => '01', "value" => '12' ],                    											// ID 01: Point of Initiation Method 11: static, 12: dynamic
			[
				"id" => '26', "value" =>                            										// ID 26: Merchant Account Info Template
					[
						[ "id" => '00', "value" => 'SG.PAYNOW' ],
						[ "id" => '01', "value" => $this->paymentType ],                 					// 0 for mobile, 2 for UEN. 1 is not used.
						[ "id" => '02', "value" => $this->paymentType == '2' ? $this->uen : $this->mobile ],// PayNow UEN (Company Unique Entity Number)
						[ "id" => '03', "value" => $this->editable ? 1 : 0 ],       						// 1 = Payment amount is editable, 0 = Not Editable
						[ "id" => '04', "value" => $this->expiry->format('Ymd') ]							// Expiry date (YYYYMMDD)
					]         
			],
			[ "id" => '52', "value" => '0000' ],                  											// ID 52: Merchant Category Code (not used)
			[ "id" => '53', "value" => '702' ],                   											// ID 53: Currency. SGD is 702
			[ "id" => '54', "value" => number_format($this->amount, 2, '.', '') ],  						// ID 54: Transaction Amount
			[ "id" => '58', "value" => $this->merchantCountry ],                    						// ID 58: 2-letter Country Code (SG)
			[ "id" => '59', "value" => $this->company ],          											// ID 59: Company Name
			[ "id" => '60', "value" => $this->merchantCity ]             									// ID 60: Merchant City
			
		];
		
		$addtionalData = 
		[
			"id" => '62', "value" => 
			[
				[                         													// ID 62: Additional data fields
					"id" => '01', "value" => $this->uniqueOrderCode           					// ID 01: Bill Number
				]
			]
		];
  
		if($this->uniqueOrderCode !== ""){
			array_push($mainData, $addtionalData);
		}
		
		$output = "";
		foreach ($mainData as $mainDataValue) {
			if (is_array($mainDataValue['value'])) {
				$tempValue = "";
				foreach ($mainDataValue['value'] as $nestedValue) {
					$tempValue .= $nestedValue['id'] . $this->padLeft(strlen($nestedValue['value']), 2) . $nestedValue['value'];
				}
				$mainDataValue['value'] = $tempValue;
			}
			$output .= $mainDataValue['id'] . $this->padLeft(strlen($mainDataValue['value']), 2) . $mainDataValue['value'];
		}
		
		// Here we add "6304" to the previous string
		// ID 63 (Checksum) 04 (4 characters)
		// Do a CRC16 of the whole string including the "6304"
		// then append it to the end.
		$output .= '6304' . CRC16::calculate($output . '6304');
		if ($this->createAsBase64Image) {
			$output = $this->createAsBase64Image($output);
		}

		return $output;
	}

	/**
	 * Generate Base64 image
	 *
	 * @param string $str
	 * @return string
	 */
	private function createAsBase64Image(string $str): string
	{
		$filename = dirname(__FILE__).'/assets/images/PayNow.png';
		$imgBinary = fread(fopen($filename, "r"), filesize($filename));
		return "data:image/png;base64," . QRCodeService::generate($str, $imgBinary);
	}

	/**
	 * @param integer $stringLength
	 * @param integer $padNumberCharacters
	 * @return string
	 */
	private function padLeft(string $s, int $n) 
	{
		if ($n <= strlen($s)) {
		  	return $s;
		} else {
		  	return '0' . $s;
		}
	}
}
