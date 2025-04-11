DROP DATABASE agrimarket;

use agrimarket

select * from refund

select * from shipment

select * from payment

select * from orders
select * from product_order

UPDATE shipment
SET update_timestamp = '2025-04-08',estimated_delivery_date = '2025-04-10'
WHERE tracking_number = 'D4XYGTFS';
