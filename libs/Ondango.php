<?php
/**
 *	Ondango PHP-SDK for Ondango API
 *	written by Claudio Bredfeldt & Antonio López Muzas
 *	
 *	http://github.com/Ondango/PHP-SDK
 *	http://apidocs.ondango.com
 *
 *	Copyright (c) 2012 Ondango GmbH (http://ondango.com)
 *	Dual licensed under the MIT and GPL licenses.
 */
 
require_once dirname (__FILE__)."/OndangoRequest.php";

class Ondango
{
	private $api_key		= null;
	private $api_secret		= null;


	public function __construct ($api_key, $api_secret = null)
	{
		$this->api_key = $api_key;
		$this->api_secret = $api_secret;
	}


	/**
	 * Magic method for GET, PUT, POST or DELETE
	 * 
	 * @param string $method GET, PUT, POST or DELETE
	 * @param array $args
	 * @return mixed 
	 */
	public function __call ($method, $args)
	{
		$allowed_methods = array ("get", "put", "post", "delete");
        // $extended_api_url is an array with the api_url mapped to the function that will solve the api_url request
        $extended_api_url = array("products/best-sellers-ids"=>"get_products_best_sellers");

		if (!in_array (strtolower ($method), $allowed_methods)) {
			die ("Fatal error: Call to undefined method Ondango::{$method}(). Only following magic methods are allowed: ".implode (", ", $allowed_methods));
		}
		else if (empty ($args[0])) {
			die ("Fatal error: Missing argument 1 for Ondango::{$method}(). You have to provide a api url (see: http://apidocs.ondango.com");
		}

        // Check if the api url is a api core one or a extended one
        // If it's an extended one, call the function to solve the request
        if(array_key_exists($args[0],$extended_api_url)){
            $function_name = $extended_api_url[$args[0]];
            return call_user_func(array($this,$function_name),$args[1]);
        }

		return $this->request ($method, $args[0], $args[1]);
	}
	
	/**
	 * Compose and execute a specific api url (i.e: /shops?api_key=...&id=n)
	 * 
	 * @param string $method GET, PUT, POST or DELETE
	 * @param string $url
	 * @param array $params [optional]	i.e: array ("shop_id" => 5)
	 * @return object
	 */
	public function request ($method, $url, $params = array ())
	{
		$request = new OndangoRequest ($method, $url, $this->init_params ($params));

		return json_decode ($request->execute ());
	}
	
	/**
	 * Add additional information to the parameters of the api url 
	 * i.e: the API key and Secret key
	 * 
	 * @param array $params
	 * @return array 
	 */
	private function init_params ($params)
	{
		$params["api_key"] = $this->api_key;
		$params["api_secret"] = $this->api_secret;
		return $params;
	}

    /**
     * Get the best selling product details
     *
     *
     * @param array $params
     * @return array
     */
    public function get_products_best_sellers($params){

        $default_fields = array('Product.product_id','Product.title');
        $default_limit=3;
        $best_sellers = array();
        $best_sellers_details = new stdClass();

        // Save the parms that we dont need in the request
        $fields = (array_key_exists('fields',$params)?$params['fields']:$default_fields);
        unset ($params["fields"]);

        $limit = (array_key_exists('limit',$params)?$params['limit']:$default_limit);
        unset ($params["limit"]);


        // We get the sales from the api
        $sales_results = $this->GET ("sales/all", $params);
        if($sales_results->is_error) die("Error in sales request");

        //Iterate the results to get the best selling products
        foreach($sales_results->data as $data){
            foreach($data->Order->Sales->Sale as $sale){
                if(array_key_exists($sale->product_id,$best_sellers)){
                    $best_sellers[$sale->product_id] += $sale->quantity;
                }else{
                    $best_sellers[$sale->product_id] = $sale->quantity;
                }
            }
        }

        // We order the best sellers
        arsort($best_sellers,SORT_NUMERIC);
        $product_ids = array_keys($best_sellers);

        // Check if the products are enough for the limit
        $limit = ($limit<=count($product_ids)?$limit:count($product_ids));

        // Get for everyone of the best sellers their product details
        for($i=0; $i<$limit; $i++){
            $product_details = $this->GET("products", array('product_id'=>$product_ids[$i]));
            if($product_details->is_error) die("Error in product request");

            $object = new stdClass();
            // Select just the required fields
            foreach($fields as $field_name){
                list($class, $property) = explode(".",$field_name);
                if(property_exists($product_details->data[0],$class)){
                    if(property_exists($product_details->data[0]->$class,$property)){
                        $object->$class->$property = $product_details->data[0]->$class->$property;
                    }
                }
            }

            // Overwrite just with the object with the required details
            $best_sellers_details->is_error = $product_details->is_error;
            $best_sellers_details->status_code = $product_details->status_code;
            $best_sellers_details->status_text = $product_details->status_text;
            $best_sellers_details->data[] = $object;

        }

        return $best_sellers_details;
    }

}
?>
