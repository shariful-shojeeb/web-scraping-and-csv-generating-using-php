<?php
require 'vendor/autoload.php';

use Goutte\Client;

$client = new Client();

//Setting up the target URL
// $target = "https://yourpetpa.com.au/collections/dog-food";
$target = "https://yourpetpa.com.au";
$crawler = $client->request("GET", $target);


/*
    |--------------------------------------------------------------------------
    | Getting All Product's URL to Crawl them one by one from product details page
    |--------------------------------------------------------------------------
    | Why: Because, Product Description, Category info is not available in the Homepage
 */
$product_links = $crawler->filter('.product__content__wrap')->each(function ($node) {
    return $url = "https://yourpetpa.com.au/" . $node->filter('.product-block__title >a')->attr('href');
});

//The final data will be written in this variable
$final_data = array();


//Getting Product Data using product links
foreach ($product_links as $key => $single_link) {
    $single_product = $client->request("GET", $single_link);

    $title = $single_product->filterXpath('//meta[@property="og:title"]')->attr('content');
    $description = $single_product->filterXpath('//meta[@property="og:description"]')->attr('content');
    $image = $single_product->filterXpath('//meta[@property="og:image:secure_url"]')->attr('content');
    $url = $single_product->filterXpath('//meta[@property="og:url"]')->attr('content');
    $price = $single_product->filterXpath('//meta[@property="og:price:amount"]')->attr('content');


    /*
    |--------------------------------------------------------------------------
    | Getting Category Name from product details page
    |--------------------------------------------------------------------------
    | Note:  product from homepage doesn't returns category name
    |--------------------------------------------------------------------------
 */
    $cat =  $single_product->filter('.breadcrumbs-list__link')->text();

    if ($cat == 'Home' && $target == 'https://yourpetpa.com.au') {
        $category = 'N/A';
    } else {
        /*
    |--------------------------------------------------------------------------
    | Getting Category Name from Product Category Page, 
    |--------------------------------------------------------------------------
    | Only works with this kind of url (Category Page): https://yourpetpa.com.au/collections/dog-food
    */
        $category = $category_name =  $crawler->filter('.collection-header__info')->text();
    }

    //Creating the final array to get final output
    array_push($final_data, ['ID' => $key + 800, 'Title' => $title, 'Description' => $description, 'Category' => $category, 'Price' => $price, 'URL' => $single_link, 'ImageURL' => $image]);
}


// Generaating .csv file
$file = fopen(time() . '.csv', 'a');

fputcsv($file, array_keys($final_data[0]));

foreach ($final_data as $row) {
    fputcsv($file, $row);
}
// Close the file
fclose($file);
echo 'Success';
header("Content-type: text/csv");
header("location:./" . time() . ".csv");
