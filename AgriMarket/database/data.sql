--Insert Fake Data Here
USE agrimarket

--Subscription plan
INSERT INTO subscription (plan_name, subscription_price, upload_limit, has_low_stock_alert, has_staff_support, has_analytics_access)  
VALUES 
    ('Tier_I', 0.00, 1, FALSE, FALSE, FALSE),
    ('Tier_II', 9.99, 5, TRUE, FALSE, FALSE),
    ('Tier_III', 39.99, 100, TRUE, TRUE, TRUE);

