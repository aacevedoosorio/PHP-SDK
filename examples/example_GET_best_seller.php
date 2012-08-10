<?php
/**
 * Created by JetBrains PhpStorm.
 * User: alejandroacevedoosorio
 * Date: 10/08/12
 * Time: 13:52
 * To change this template use File | Settings | File Templates.
 */

/**
 * Ondango Team
 *
 * www.ondango.com
 * apidocs.ondango.com
 */

require_once "../libs/Ondango.php";

$api_key = "f877615136d70e0ffc2fb224d5872d6a8fd2xbxx";
$api_secret = "arnau";	// optional
$ondango = new Ondango ($api_key, $api_secret);


// Retrieve all sales for a specific shop
// See: http://apidocs.ondango.com/rest/sales/all/get.php
//$results = $ondango->GET ("sales/all", array ("shop_id" => 87));
$bestSellers = $ondango->GET ("products/best-sellers-ids", array (
    "shop_id" => 87,
    "fields" => array( 'Product.product_id', 'Product.title' ),
    "limit" => 3
));


// Display results
echo '<pre>';
print_r ($bestSellers);
echo '</pre>';
?>