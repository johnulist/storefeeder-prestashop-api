<?php
/*
 * THE QUERIES IN THIS FILE ARE RESPONSIBLE FOR INTERACTING WITH YOUR DATABASE.
 * PLEASE ALTER THE QUERIES BELOW TO MAKE THEM COMPATIBLE WITH YOUR SYSTEM.
 */

/*
 * Orders Query
 *
 * There are no input variables for this query.
 *
 * It needs to return all the orders which are ready to ship. The criteria are
 * different depending on the shopping cart system used, usually it can be
 * determined by the status of the orders and/or the fact whether payment was
 * successfully received.
 *
 * The query needs to return all fields indicated below. If some of the fields
 * are not present in the shopping cart system then it's best to return an empty
 * string (eg SELECT '' as 'order_notes').
 *
 * The above also applies to fields which are optional and might not be present
 * for every order.
 *
 * Optional fields include:
 * discount
 * coupon_code
 * order_notes
 * customer_email
 * customer_telephone
 * shipping_line2
 * shipping company
 * shipping_county
 * billing_line2
 * billing company
 * billing_county
 *
 * COMPLETED
 */
$ordersQuery = "SELECT
    o.`total_products` AS 'subtotal',
    o.`total_products_wt` - o.`total_products` AS 'tax',
    o.`total_shipping_tax_incl` AS 'shipping',
    o.`total_paid` AS 'total',
    o.`total_discounts_tax_incl` AS 'discount',
    NULL AS 'coupon_code',
    o.`date_add` AS 'order_date',
    ca.`name` AS 'shipping_method',
    cu.`iso_code` AS 'currency_code',
    CONCAT(o.`id_order`, '-',o.`reference`) AS 'order_ref',
    NULL AS 'order_notes',
    c.`id_customer` AS 'customer_ref',
    c.`firstname` AS 'customer_first_name',
    c.`lastname` AS 'customer_last_name',
    c.`email` AS 'customer_email',
    sa.`firstname` AS 'shipping_first_name',
    sa.`lastname` AS 'shipping_last_name',
    sa.`address1` AS 'shipping_line1',
    sa.`address2` AS 'shipping_line2',
    sa.`company` AS 'shipping_company',
    sa.`city` AS 'shipping_city',
    sas.`name` AS 'shipping_county',
    sa.`postcode` AS 'shipping_postcode',
    sc.`name` AS 'shipping_country',
    ba.`firstname` AS 'billing_first_name',
    ba.`lastname` AS 'billing_last_name',
    ba.`address1` AS 'billing_line1',
    ba.`address2` AS 'billing_line2',
    ba.`company` AS 'billing_company',
    ba.`city` AS 'billing_city',
    CASE WHEN ba.`phone` IS NULL or ba.`phone` = '' THEN CASE WHEN ba.`phone_mobile`
    IS NULL or ba.`phone_mobile` = '' THEN NULL ELSE ba.`phone_mobile` END
    ELSE ba.`phone` END AS 'customer_telephone',
    bas.`name` AS 'billing_county',
    ba.`postcode` AS 'billing_postcode',
    bc.`name` AS 'billing_country'
    FROM ps_orders o
    INNER JOIN ps_customer c ON o.id_customer = c.id_customer
    INNER JOIN ps_address sa ON o.id_address_delivery = sa.id_address
    INNER JOIN ps_address ba ON o.id_address_invoice = ba.id_address
	INNER JOIN ps_currency cu ON o.id_currency = cu.id_currency
	INNER JOIN ps_carrier ca ON o.id_carrier = ca.id_carrier
	INNER JOIN ps_country_lang sc on sa.id_country = sc.id_country
	INNER JOIN ps_country_lang bc on ba.id_country = bc.id_country
    LEFT JOIN ps_state sas on sa.id_state = sas.id_state
    LEFT JOIN ps_state bas on ba.id_state = bas.id_state
    WHERE o.current_state='3' AND sc.id_lang = 1 AND bc.id_lang = 1";

/*
 * Order Items Query
 *
 * This query is executed for every order returned by the Orders Query.
 *
 * It should return all items that belong to the order $ordeRref. The
 * $ordeRref variable will contain the order_ref returned by the Orders Query.
 *
 * Required fields are sku and quantity. Other fields also have to be present in
 * the output of the query, but may be left empty (see example in the comments
 * to the Orders Query about empty fields). If the optional fields are left
 * empty the values for name and totals will be taken from the StoreFeeder
 * product database, which might not be accurate, as the prices and names might
 * be different on each channel. Therefore, it is strongly advised that all the
 * field of the Order Items Query are populated with the correct data.
 * COMPLETED
 */
$orderItemsQuery = "SELECT
`product_reference` AS 'sku',
`product_name` AS 'name',
`product_quantity` as 'quantity',
`total_price_tax_excl` as 'line_subtotal',
`total_price_tax_incl` - `total_price_tax_excl` as 'line_tax',
`total_price_tax_incl` as 'line_total'
FROM ps_order_detail WHERE id_order=(SELECT id_order FROM ps_orders WHERE id_order='$orderRef')";

/*
 * Inventory Update Query
 *
 * This query is expected to update the inventory level of a product in the
 * shopping cart system's database.
 *
 * The input variables are:
 * $sku - holds the sku of the product which should have inventory updated
 * $inventory - the inventory level that should be set for the above product
 *
 * COMPLETED
 */
//$inventoryUpdateQuery = "UPDATE ps_product SET quantity='$inventory' WHERE reference='$sku'";
$inventoryUpdateQuery = "UPDATE ps_product, ps_stock, ps_stock_available
SET ps_stock.physical_quantity = '$inventory',
	ps_stock.usable_quantity = '$inventory',
	ps_stock_available.quantity = '$inventory',
	ps_product.quantity = '$inventory'
WHERE ps_product.reference = '$sku'
AND ps_stock.id_product = ps_product.id_product
AND ps_stock_available.id_product = ps_product.id_product";

/*
 * Shipping Update Query
 *
 * This query is responsible for marking an order sa dispatched in the shopping
 * cart system.
 *
 * The input variables are:
 * $orderRef - the reference number for the order that was shipped (the same
 * that was returned in the Orders Query)
 * $carrierName - the name of the carrier used to dispatch the order
 * $trackingNumber - the tracking number of the parcel (or multiple tracking
 * numbers if the order was dispatched in multiple parcels)
 *
 * In most cases all that is required of this query is to update the status of
 * the order, howerver, more steps might be required depending on the shopping
 * cart system used.
 *
 * If the shopping cart system is not design to keep carrier names or tracking
 * number info, you can just ignore the input variables and not use them in the
 * query.
 */
$shippingUpdateQuery = "UPDATE ps_orders SET shipping_number='$trackingNumber', current_state='4' WHERE id_order='$orderRef'";
$orderHistoryQuery = "INSERT INTO ps_order_history (id_employee, id_order, id_order_state, date_add) VALUES (0, '$order_ref', 4, '$date')";
//carrier_name='$carrierName',  Need to set carrier up!
//$carrierUpdate = "SELECT id_carrier 
//					FROM ps_carrier car 
//					INNER JOIN ps_carrier_lang carl ON car.id_carrier = carl.id_carrier 
//					WHERE carl.id_lang = 1 AND carl.name = '$carrierName'"; 

/*
 * Product Download Query
 *
 * This query is responsible for fetching all products from your website.
 *
 * It should return all products that you want to send to StoreFeeder.
 *
 * Required fields are sku, name and price_ex_vat. Other fields also have to be present in
 * the output of the query, but may be left empty (see example in the comments
 * to the Orders Query about empty fields).
 *
 * Note that you can 'hardcode' some of the fields, e.g. you could enter:
 *
 * ... 'Default Warehouse' as 'warehouse'...
 * if you want all your products to be assigned to the 'Default Warehouse' inside StoreFeeder
 *
 * COMPLETED
 */
$productDownloadQuery = "SELECT
p.`reference` AS 'sku',
p.`price` as 'price_ex_vat',
p.`price` AS 'rrp',
p.`wholesale_price` as 'cost',
p.`weight` as 'weight',
stock.`quantity` as 'inventory',
p.`ean13` as 'ean',
p.`upc` as 'upc',
CONCAT(p.id_product,  '-',pl.link_rewrite) as 'website_url',
pl.`name` AS 'name',
pl.`description_short` as 'description',
s.`name` as 'supplier',
'Default Warehouse' as 'warehouse',
'' AS 'mpn',
'21' as 'vat_percent',
pi.`id_image` as 'image_url'
FROM ps_product p
INNER JOIN ps_product_lang pl ON p.id_product = pl.id_product
INNER JOIN ps_supplier s ON p.id_supplier = s.id_supplier
INNER JOIN ps_image pi on p.id_product = pi.id_product
INNER JOIN ps_stock_available stock on p.id_product = stock.id_product
WHERE pl.id_lang = 1 AND pi.position = 1 AND pi.cover = 1";

?>