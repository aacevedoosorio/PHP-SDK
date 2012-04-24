<?php

class OndangoResponse
{
	public $data			= null;
	public $curl_info		= null;
	public $curl_errorNo	= null;
	public $curl_errorMsg	= null;


	/**
	 * Check if $this->curl_errorNo contains any error code (number)
	 * 
	 * @return boolean
	 */
	public function has_error ()
	{
		return !$this->curl_errorNo ? false : true;
	}
	
	/**
	 * Returns a string with the cURL error message
	 * 
	 * @return string 
	 */
	public function error ()
	{
		return sprintf ('CURL Error: %d: %s', $this->curl_errorNo, $this->curl_errorMsg);
	}
}
?>
