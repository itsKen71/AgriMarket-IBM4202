USE agrimarket;

-- User Data
INSERT INTO user (first_name, last_name, username, user_image, email, password, role, phone_number, home_address, last_online) 
VALUES 
    ('Hau', 'Tien', 'tien tien', 'Assets/img/profile_img/profile_1.png','chonghautian@gmail.com', SHA2('password@123', 256), 'Customer', '6012-3456789', '15 Jalan Bunga Raya, Taman Melati, 53100 Kuala Lumpur, Malaysia', NOW()),
    ('Jacky', 'Lee', 'Lee1314', 'Assets/img/profile_img/profile_2.png','jackylee@gmail.com', SHA2('password@123', 256), 'Customer', '6017-9876543', '22 Lorong Cempaka 3, Taman Sejati, 08000 Sungai Petani, Kedah, Malaysia', NOW()),
    ('Ryan', 'Chong', 'ryan chong', 'Assets/img/profile_img/profile_3.png','ryanchong@gmail.com', SHA2('password@123', 256), 'Customer', '6016-1234567', '88 Jalan Impian Emas, Taman Universiti, 81300 Skudai, Johor, Malaysia', NOW()),
    ('Norman', 'Theo', 'Coding God', 'Assets/img/profile_img/profile_4.png','normanTheo@gmail.com', SHA2('password@123', 256), 'Customer', '6015-2345678', '17 Taman Bukit Jaya, 48000 Rawang, Selangor, Malaysia', NOW()),
    ('Sara', 'Leo', 'Surfer X', 'Assets/img/profile_img/profile_5.png','teacheriiee@gmail.com', SHA2('password@123', 256), 'Customer', '6018-7654321',   '43 Jalan Merdeka, Taman Aman, 42700 Banting, Selangor, Malaysia', NOW()),
    ('Jun', 'Hao', 'JUNHAO', 'Assets/img/profile_img/profile_6.png','ngjunhao9@gmail.com', SHA2('password@123', 256), 'Vendor', '6011-2223334', '19 Jalan Tebrau, Taman Century, 80250 Johor Bahru, Malaysia', NOW()),
  	('Daniel', 'Ng', 'danielZZZ', 'Assets/img/profile_img/profile_7.png','danielng@gmail.com', SHA2('password@123', 256), 'Vendor', '6019-5554321', '34 Jalan Dato Onn, Bandar Hilir, 75000 Melaka, Malaysia', NOW()),
    ('Sarah', 'Lim', 'LimHarlot', 'Assets/img/profile_img/profile_8.png','sarahlim@gmail.com', SHA2('password@123', 256), 'Vendor', '6013-3322110', '56 Jalan Tun Sardon, 11000 Balik Pulau, Pulau Pinang, Malaysia', NOW()),
    ('John', 'Tan', 'John is here', 'Assets/img/profile_img/profile_9.png','johntan@gmail.com', SHA2('password@123', 256), 'Vendor', '6014-9988776',  '29 Lorong Jati, Taman Permai, 85000 Segamat, Johor, Malaysia', NOW()),
    ('Maya', 'Chong', 'MayaBlack', 'Assets/img/profile_img/profile_10.png','mayachong@gmail.com', SHA2('password@123', 256), 'Vendor','6010-1225672', '12 Jalan Bunga Mawar, Taman Sentosa, 80100 Johor Bahru, Johor, Malaysia', NOW()),
    ('Yu', 'Shyang', 'LeGlorious', 'Assets/img/profile_img/profile_11.png','shyang04@gmail.com', SHA2('password@123', 256), 'Staff', '6012-6571231', '72 Jalan Air Itam, Farlim, 11500 George Town, Penang, Malaysia', NOW()),
    ('Yu', 'Yang', 'Aiqing', 'Assets/img/profile_img/profile_12.png','yuyang234@gmail.com', SHA2('password@123', 256), 'Staff', '6013-1238373', '98 Jalan Sri Cindai, Taman Ria, 06000 Jitra, Kedah, Malaysia', NOW()),
    ('Wen', 'Yen', 'WenYen', 'Assets/img/profile_img/profile_13.png','wenyen@gmail.com', SHA2('password@123', 256), 'Staff', '6016-2323524', '101 Jalan Taman Lestari, Bandar Baru Nilai, 71800 Nilai, Negeri Sembilan, Malaysia', NOW()),
    ('Ken', 'Ji', 'itsKJ', 'Assets/img/profile_img/profile_14.png','kenjichong88@gmail.com', SHA2('password@123', 256), 'Admin', '6018-2342342', '68 Jalan Anggerik, Taman Pelangi Indah, 81800 Ulu Tiram, Johor, Malaysia', NOW()),
    ('Jun', 'Hui', 'LeGod', 'Assets/img/profile_img/profile_15.png','junhui22@gmail.com', SHA2('password@123', 256), 'Admin', '6011-2349944', '33 Lorong Seri Delima 2, Taman Delima, 11900 Bayan Lepas, Penang, Malaysia', NOW());

-- Subscription Plan
INSERT INTO subscription (plan_name, subscription_price, upload_limit, has_low_stock_alert, has_staff_support, has_analytics_access) 
VALUES 
    ('Tier_I', 0.00, 1, FALSE, FALSE, FALSE),
    ('Tier_II', 9.99, 5, TRUE, FALSE, FALSE),
    ('Tier_III', 39.99, 100, TRUE, TRUE, TRUE);

-- Vendor Data
INSERT vendor (user_id, subscription_id, store_name, subscription_start_date, subscription_end_date, assist_by) 
VALUES 
    (6, 3, 'Hao Store', '2024-03-29', '2025-04-29', NULL),
	(7, 3, 'AgriGoodsHub', '2024-03-29', '2025-04-29', NULL),
    (8, 2, 'Lighting Store', '2024-03-29', '2025-04-29', NULL),
    (9, 2, 'PasarLokal', '2024-03-29', '2025-04-29', NULL),
    (10, 1, 'Fresh Nest Mart', '2024-03-29', '2025-04-29', NULL);


-- Product Category Data 
INSERT INTO category (category_name) 
VALUES 
    ('Livestock'),
    ('Crops'),
    ('Edible Forestry Products'),
    ('Dairy'),
    ('Fish Farming'),
    ('Miscellaneous Products');

-- Products Data
INSERT INTO product (vendor_id, category_id, product_name, product_image, description, stock_quantity, weight, unit_price, product_status, sold_quantity) 
VALUES  
	(1, 1, 'Dairy Cow', 'Assets/img/product_img/dairy_cow.png', 'High-yield dairy cow, ideal for milk production.', 10, 600.00, 4500.00, 'Pending', 0),
	(1, 1, 'Free-Range Chicken', 'Assets/img/product_img/free_range_chicken.png', 'Healthy free-range chickens, excellent for meat or egg production.', 3, 2.50, 35.00, 'Approved', 0),
	(4, 1, 'Boer Goat', 'Assets/img/product_img/boer_goat.png', 'Strong and healthy Boer goats, perfect for meat production.', 1, 80.00, 800.00, 'Approved', 0),
	(1, 1, 'Piglet', 'Assets/img/product_img/piglet.png', 'Well-fed piglets, ideal for pig farming and meat production.', 2, 15.00, 200.00, 'Approved', 0),
	(5, 1, 'Angus Cattle', 'Assets/img/product_img/angus_cattle.png', 'Premium Angus cattle, known for high-quality beef.', 8, 700.00, 6000.00, 'Approved', 0),
	(2, 1, 'Turkey', 'Assets/img/product_img/turkey.png', 'Large farm-raised turkeys, perfect for meat production.', 30, 7.00, 60.00, 'Approved', 0),
	(2, 1, 'Sheep', 'Assets/img/product_img/sheep.png', 'Healthy sheep, great for wool and meat production.', 12, 70.00, 300.00, 'Approved', 0),
	(5, 1, 'Duck', 'Assets/img/product_img/duck.png', 'Farm-raised ducks, excellent for meat and egg production.', 25, 3.50, 30.00, 'Approved', 0),
	(3, 1, 'Boer Goat', 'Assets/img/product_img/boer_goat_2.png', 'Hardy Boer goats, bred for fast growth and quality meat.', 10, 85.00, 850.00, 'Approved', 0),
	(3, 1, 'Duck', 'Assets/img/product_img/duck_2.png', 'Organic farm-raised ducks, known for tender and flavorful meat.', 20, 3.00, 35.00, 'Approved', 0), 
    (1, 2, 'Corn', 'Assets/img/product_img/corn.png', 'Freshly harvested yellow corn, great for grilling or making flour.', 200, 1.50, 4.50, 'Approved', 0),
    (4, 2, 'Soybeans', 'Assets/img/product_img/soybeans.png', 'High-quality soybeans, perfect for tofu or soy milk production.', 180, 1.50, 5.50, 'Approved', 0),
    (1, 2, 'Wheat', 'Assets/img/product_img/wheat.png', 'Whole grain wheat, ideal for milling into flour.', 250, 3.00, 3.20, 'Approved', 0),
    (1, 2, 'Rice', 'Assets/img/product_img/rice.png', 'Organic white rice, grown with sustainable farming practices.', 300, 1.00, 5.99, 'Approved', 0),
    (5, 2, 'Barley', 'Assets/img/product_img/barley.png', 'Nutritious barley grains, used in soups, baking, and brewing.', 160, 2.50, 4.25, 'Approved', 0),
    (1, 2, 'Hay', 'Assets/img/product_img/hay.png', 'Fresh-cut hay, perfect for livestock feed and bedding.', 500, 30.00, 15.00, 'Approved', 0),
    (2, 2, 'Oats', 'Assets/img/product_img/oats.png', 'Healthy whole grain oats, great for breakfast cereals.', 220, 2.00, 5.00, 'Approved', 0),
    (2, 2, 'Millet', 'Assets/img/product_img/millet.png', 'Nutritious millet grains, a staple food in many cultures.', 150, 1.75, 4.00, 'Approved', 0),
    (5, 2, 'Sorghum', 'Assets/img/product_img/sorghum.png', 'Drought-resistant sorghum grains used for food and livestock feed.', 180, 2.50, 5.50, 'Approved', 0),
    (2, 2, 'Peanuts', 'Assets/img/product_img/peanuts.png', 'Raw peanuts, rich in protein and perfect for snacking or peanut butter.', 140, 0.80, 3.99, 'Approved', 0),
    (2, 2, 'Flaxseeds', 'Assets/img/product_img/flaxseeds.png', 'High-fiber flaxseeds, great for baking and healthy diets.', 100, 0.60, 7.50, 'Approved', 0),
    (2, 2, 'Sunflower Seeds', 'Assets/img/product_img/sunflower_seeds.png', 'Roasted sunflower seeds, a tasty and healthy snack.', 120, 1.00, 6.50, 'Approved', 0),
    (4, 2, 'Corn', 'Assets/img/product_img/corn_2.png', 'Sweet and tender corn, freshly harvested from organic farms.', 180, 1.50, 4.75, 'Approved', 0),
    (3, 2, 'Soybeans', 'Assets/img/product_img/soybeans_2.png', 'Non-GMO soybeans, ideal for cooking and soy-based products.', 160, 1.50, 5.00, 'Approved', 0),
    (3, 2, 'Rice', 'Assets/img/product_img/rice_2.png', 'Locally grown long-grain rice, perfect for daily meals.', 280, 1.00, 6.29, 'Approved', 0),
    (5, 2, 'Peanuts', 'Assets/img/product_img/peanuts_2.png', 'Crunchy and flavorful peanuts, great for roasting or making peanut butter.', 130, 0.80, 4.25, 'Approved', 0),
    (3, 2, 'Sunflower Seeds', 'Assets/img/product_img/sunflower_seeds_2.png', 'High-quality sunflower seeds, roasted and lightly salted.', 110, 1.00, 6.00, 'Approved', 0),
    (1, 3, 'Almonds', 'Assets/img/product_img/almonds.png', 'Organic raw almonds sourced from sustainable farms.', 100, 0.50, 17.99, 'Approved', 0),
    (1, 3, 'Walnuts', 'Assets/img/product_img/walnuts.png', 'High-quality walnuts rich in omega-3 fatty acids.', 150, 0.75, 18.50, 'Approved', 0),
    (1, 3, 'Hazelnuts', 'Assets/img/product_img/hazelnuts.png', 'Fresh and crunchy hazelnuts perfect for snacks or baking.', 120, 0.50, 22.99, 'Approved', 0),
    (5, 3, 'Pine Nuts', 'Assets/img/product_img/pine_nuts.png', 'Premium pine nuts harvested from wild pine trees.', 80, 0.40, 30.99, 'Approved', 0),
    (4, 3, 'Chestnuts', 'Assets/img/product_img/chestnuts.png', 'Sweet and flavorful chestnuts, great for roasting or cooking.', 90, 1.00, 15.99, 'Approved', 0),
    (2, 3, 'Wild Honey', 'Assets/img/product_img/wild_honey.png', 'Organic honey harvested from wild forest bees.', 50, 0.40, 24.99, 'Approved', 0),
    (5, 3, 'Maple Syrup', 'Assets/img/product_img/maple_syrup.png', 'Pure maple syrup tapped from maple trees.', 40, 0.75, 35.99, 'Approved', 0),
    (2, 3, 'Birch Syrup', 'Assets/img/product_img/birch_syrup.png', 'Sweet birch syrup, an alternative to maple syrup.', 30, 0.70, 39.99, 'Approved', 0),
    (5, 3, 'Elderberries', 'Assets/img/product_img/elderberries.png', 'Nutrient-rich elderberries used for syrups and jams.', 90, 0.30, 17.50, 'Approved', 0),
    (2, 3, 'Chaga Mushrooms', 'Assets/img/product_img/chaga_mushrooms.png', 'Medicinal mushrooms harvested from birch trees.', 60, 0.25, 49.99, 'Approved', 0),
    (3, 3, 'Morel Mushrooms', 'Assets/img/product_img/morel_mushrooms.png', 'Highly sought-after gourmet mushrooms.', 40, 0.20, 59.99, 'Approved', 0),
    (2, 3, 'Truffles', 'Assets/img/product_img/truffles.png', 'Rare and luxurious forest-grown truffles.', 25, 0.10, 299.99, 'Approved', 0),
    (2, 3, 'Bamboo Shoots', 'Assets/img/product_img/bamboo_shoots.png', 'Tender bamboo shoots used in Asian cuisine.', 100, 0.50, 10.99, 'Approved', 0),
    (4, 3, 'Almonds', 'Assets/img/product_img/almonds_2.png', 'Premium organic almonds with a rich, nutty flavor.', 110, 0.50, 18.49, 'Approved', 0),
    (3, 3, 'Walnuts', 'Assets/img/product_img/walnuts_2.png', 'Locally sourced walnuts, packed with essential nutrients.', 140, 0.75, 16.99, 'Approved', 0),
    (2, 3, 'Chaga Mushrooms', 'Assets/img/product_img/chaga_mushrooms_2.png', 'Wild-harvested Chaga mushrooms with high antioxidant content.', 55, 0.25, 44.99, 'Approved', 0),
    (3, 3, 'Truffles', 'Assets/img/product_img/truffles_2.png', 'Rare black truffles, handpicked for a bold and earthy taste.', 20, 0.10, 220.00, 'Approved', 0),
    (3, 3, 'Bamboo Shoots', 'Assets/img/product_img/bamboo_shoots_2.png', 'Crisp and tender bamboo shoots, freshly harvested.', 90, 0.50, 10.50, 'Approved', 0),
    (5, 3, 'Maple Syrup', 'Assets/img/product_img/maple_syrup_2.png', 'Rich and smooth maple syrup, 100% pure and natural.', 35, 0.75, 33.50, 'Approved', 0), 
	(1, 4, 'Fresh Cow Milk', 'Assets/img/product_img/fresh_cow_milk.png', 'Organic fresh cow milk from grass-fed cows.', 50, 1.00, 3.00, 'Approved', 0),
	(4, 4, 'Goat Milk', 'Assets/img/product_img/goat_milk.png', 'Pure and fresh goat milk, rich in nutrients.', 40, 1.00, 3.50, 'Approved', 0),
	(1, 4, 'Greek Yogurt', 'Assets/img/product_img/greek_yogurt.png', 'Thick and creamy Greek yogurt with probiotics.', 30, 0.50, 5.00, 'Approved', 0),
	(4, 4, 'Cheddar Cheese', 'Assets/img/product_img/cheddar_cheese.png', 'Aged cheddar cheese with a sharp, rich taste.', 25, 0.50, 7.50, 'Approved', 0),
	(5, 4, 'Butter', 'Assets/img/product_img/butter.png', 'Fresh, unsalted butter made from farm-fresh cream.', 35, 0.25, 4.00, 'Approved', 0),
	(1, 4, 'Sour Cream', 'Assets/img/product_img/sour_cream.png', 'Rich and creamy sour cream, perfect for cooking.', 20, 0.50, 2.75, 'Approved', 0),
	(5, 4, 'Mozzarella Cheese', 'Assets/img/product_img/mozzarella_cheese.png', 'Soft and fresh mozzarella cheese, ideal for pizzas.', 28, 0.50, 6.50, 'Approved', 0),
	(3, 4, 'Almond Milk', 'Assets/img/product_img/almond_milk.png', 'Dairy-free almond milk, a great alternative to cow milk.', 45, 1.00, 3.50, 'Approved', 0),
	(4, 4, 'Cottage Cheese', 'Assets/img/product_img/cottage_cheese.png', 'Soft and fresh cottage cheese, high in protein.', 30, 0.50, 4.25, 'Approved', 0),
	(4, 4, 'Probiotic Yogurt', 'Assets/img/product_img/probiotic_yogurt.png', 'Yogurt packed with live probiotics for gut health.', 35, 0.50, 5.00, 'Approved', 0),
	(2, 4, 'Cream Cheese', 'Assets/img/product_img/cream_cheese.png', 'Smooth and creamy cheese, perfect for spreads.', 25, 0.50, 5.50, 'Approved', 0),
	(2, 4, 'Whipping Cream', 'Assets/img/product_img/whipping_cream.png', 'Rich and smooth whipping cream for desserts.', 22, 0.50, 5.00, 'Approved', 0),
	(5, 4, 'Parmesan Cheese', 'Assets/img/product_img/parmesan_cheese.png', 'Aged Parmesan cheese, great for pasta dishes.', 20, 0.50, 8.50, 'Approved', 0),
	(3, 4, 'Fresh Cow Milk', 'Assets/img/product_img/fresh_cow_milk_2.png', 'Rich and creamy fresh cow milk, sourced from local farms.', 45, 1.00, 3.10, 'Approved', 0),
	(3, 4, 'Greek Yogurt', 'Assets/img/product_img/greek_yogurt_2.png', 'Authentic Greek yogurt with a smooth and tangy taste.', 28, 0.50, 5.25, 'Approved', 0),
	(3, 4, 'Cheddar Cheese', 'Assets/img/product_img/cheddar_cheese_2.png', 'Premium aged cheddar cheese with a deep, bold flavor.', 22, 0.50, 7.75, 'Approved', 0),
	(4, 4, 'Cream Cheese', 'Assets/img/product_img/cream_cheese_2.png', 'Rich and creamy cheese, ideal for spreading and baking.', 20, 0.50, 5.75, 'Approved', 0),
	(3, 4, 'Parmesan Cheese', 'Assets/img/product_img/parmesan_cheese_2.png', 'Finely aged Parmesan, perfect for grating over pasta.', 18, 0.50, 8.75, 'Approved', 0),
	(1, 5, 'Tilapia', 'Assets/img/product_img/tilapia.png', 'Fast-growing tilapia, perfect for freshwater aquaculture.', 100, 1.50, 40.00, 'Approved', 0),
	(3, 5, 'Catfish', 'Assets/img/product_img/catfish.png', 'Healthy and resilient catfish, suitable for pond farming.', 80, 2.50, 120.00, 'Approved', 0),
	(4, 5, 'Rainbow Trout', 'Assets/img/product_img/rainbow_trout.png', 'Premium-quality rainbow trout, known for its rich taste.', 60, 2.00, 25.00, 'Approved', 0),
	(1, 5, 'Shrimp', 'Assets/img/product_img/shrimp.png', 'High-yield freshwater shrimp, ideal for commercial farming.', 120, 0.35, 12.50, 'Approved', 0),
	(1, 5, 'Ornamental Koi', 'Assets/img/product_img/ornamental_koi.png', 'Beautiful ornamental koi, perfect for decorative ponds.', 50, 0.60, 300.00, 'Approved', 0),
	(5, 5, 'Salmon', 'Assets/img/product_img/salmon.png', 'Nutritious farm-raised salmon, high in Omega-3.', 70, 3.00, 280.00, 'Approved', 0),
	(2, 5, 'Eel', 'Assets/img/product_img/eel.png', 'Freshwater eel, known for its high market value.', 40, 2.00, 260.00, 'Approved', 0),
	(2, 5, 'Silver Carp', 'Assets/img/product_img/silver_carp.png', 'Fast-growing silver carp, widely used in aquaculture.', 90, 2.20, 70.00, 'Approved', 0),
	(2, 5, 'Mud Crab', 'Assets/img/product_img/mud_crab.png', 'Premium-quality mud crabs, perfect for seafood markets.', 35, 0.50, 35.00, 'Approved', 0),
	(2, 5, 'Barramundi', 'Assets/img/product_img/barramundi.png', 'Highly sought-after barramundi, known for its delicate flavor.', 55, 2.00, 150.00, 'Approved', 0),
	(3, 5, 'Tilapia', 'Assets/img/product_img/tilapia_2.png', 'High-quality tilapia, raised in clean and controlled water conditions.', 90, 1.50, 42.00, 'Approved', 0),
	(5, 5, 'Shrimp', 'Assets/img/product_img/shrimp_2.png', 'Freshwater shrimp with excellent growth potential and market demand.', 110, 0.35, 12.75, 'Approved', 0),
	(3, 5, 'Barramundi', 'Assets/img/product_img/barramundi_2.png', 'Premium barramundi, carefully bred for superior taste and texture.', 50, 2.00, 180.00, 'Approved', 0),
	(4, 6, 'Pure Honey', 'Assets/img/product_img/pure_honey.png', 'Raw, unfiltered honey harvested from organic bee farms.', 100, 0.50, 10.99, 'Approved', 0),
	(1, 6, 'Beeswax', 'Assets/img/product_img/beeswax.png', '100% natural beeswax, perfect for candles and skincare.', 50, 0.20, 6.50, 'Approved', 0),
	(1, 6, 'Organic Compost', 'Assets/img/product_img/organic_compost.png', 'Nutrient-rich organic compost for better plant growth.', 80, 10.00, 15.00, 'Approved', 0),
	(1, 6, 'Wood Chips', 'Assets/img/product_img/wood_chips.png', 'Premium wood chips for smoking meats or mulching gardens.', 100, 5.00, 8.75, 'Approved', 0),
	(3, 6, 'Organic Fertilizer', 'Assets/img/product_img/organic_fertilizer.png', 'Eco-friendly fertilizer made from natural plant extracts.', 90, 20.00, 18.50, 'Approved', 0),
	(1, 6, 'Dried Herbs', 'Assets/img/product_img/dried_herbs.png', 'Aromatic dried herbs, perfect for cooking and herbal teas.', 120, 0.30, 4.50, 'Approved', 0),
	(2, 6, 'Handmade Soap', 'Assets/img/product_img/handmade_soap.png', 'Natural handmade soap infused with essential oils.', 70, 0.20, 7.00, 'Approved', 0),
	(1, 6, 'Cocoa Powder', 'Assets/img/product_img/cocoa_powder.png', 'Rich and pure cocoa powder, great for baking and drinks.', 85, 0.50, 8.50, 'Approved', 0),
	(4, 6, 'Dried Mushrooms', 'Assets/img/product_img/dried_mushrooms.png', 'Organic dried mushrooms, great for soups and cooking.', 65, 0.40, 10.50, 'Approved', 0),
	(2, 6, 'Wildflower Honey', 'Assets/img/product_img/wildflower_honey.png', 'Floral wildflower honey with a unique natural taste.', 90, 0.50, 10.50, 'Approved', 0),
	(2, 6, 'Beeswax Candles', 'Assets/img/product_img/beeswax_candles.png', 'Handmade beeswax candles, free from synthetic additives.', 40, 0.30, 8.00, 'Approved', 0),
	(5, 6, 'Vermicompost', 'Assets/img/product_img/vermicompost.png', 'Organic vermicompost, enriched with earthworm castings.', 75, 15.00, 17.50, 'Approved', 0),
	(2, 6, 'Charcoal Briquettes', 'Assets/img/product_img/charcoal_briquettes.png', 'Long-lasting charcoal briquettes for grilling and heating.', 95, 10.00, 9.50, 'Approved', 0),
	(2, 6, 'Bone Meal Fertilizer', 'Assets/img/product_img/bone_meal_fertilizer.png', 'Organic bone meal fertilizer, excellent for root growth.', 85, 10.00, 14.00, 'Approved', 0),
	(2, 6, 'Dried Lavender', 'Assets/img/product_img/dried_lavender.png', 'Fragrant dried lavender, perfect for sachets and teas.', 110, 0.30, 6.00, 'Approved', 0),
	(2, 6, 'Herbal Shampoo Bar', 'Assets/img/product_img/herbal_shampoo_bar.png', 'Chemical-free herbal shampoo bar, gentle on hair.', 60, 0.20, 8.50, 'Approved', 0),
	(2, 6, 'Cacao Nibs', 'Assets/img/product_img/cacao_nibs.png', 'Crunchy cacao nibs, packed with antioxidants and flavor.', 70, 0.40, 10.00, 'Approved', 0),
	(2, 6, 'Aloe Vera Gel', 'Assets/img/product_img/aloe_vera_gel.png', 'Pure aloe vera gel, soothing for skin and hair.', 55, 0.50, 7.00, 'Approved', 0),
	(4, 6, 'Pure Honey', 'Assets/img/product_img/pure_honey_2.png', 'Golden raw honey, harvested from local beehives.', 95, 0.50, 10.50, 'Approved', 0),
	(3, 6, 'Beeswax', 'Assets/img/product_img/beeswax_2.png', 'Natural beeswax, great for crafts, balms, and candles.', 45, 0.20, 6.75, 'Approved', 0),
	(3, 6, 'Organic Compost', 'Assets/img/product_img/organic_compost_2.png', 'Premium organic compost to enrich soil fertility.', 70, 10.00, 14.00, 'Approved', 0),
	(5, 6, 'Wood Chips', 'Assets/img/product_img/wood_chips_2.png', 'Natural wood chips for barbecue smoking and landscaping.', 90, 5.00, 8.00, 'Approved', 0),
	(5, 6, 'Dried Herbs', 'Assets/img/product_img/dried_herbs_2.png', 'A selection of organic dried herbs for culinary use.', 115, 0.30, 5.00, 'Approved', 0);

-- Order Data
INSERT INTO orders (user_id, price, order_date, delivery_date, shipping_address) 
VALUES 
  (1, 4522.50, '2025-04-10', '2025-04-17', '101 Commerce Ave, Kuala Lumpur, Malaysia'),
  (1, 186.97,  '2025-04-10', '2025-04-18', '202 Market Street, Kuala Lumpur, Malaysia'),
  (2, 6050.00, '2025-04-11', '2025-04-18', '303 Industrial Road, Penang, Malaysia'),
  (2, 173.97,  '2025-04-11', '2025-04-19', '404 Commerce Park, Penang, Malaysia'),
  (3, 1733.50, '2025-04-12', '2025-04-19', '505 Central Ave, Johor Bahru, Malaysia'),
  (3, 190.32,  '2025-04-12', '2025-04-20', '606 Harbor Road, Johor Bahru, Malaysia'),
  (4, 43.75,   '2025-04-13', '2025-04-18', '707 Garden Street, Melaka, Malaysia'),
  (4, 62.00,   '2025-04-13', '2025-04-19', '808 Riverside Drive, Melaka, Malaysia'),
  (5, 250.00,  '2025-04-14', '2025-04-20', '909 Lake View, Ipoh, Malaysia'),
  (5, 660.00,  '2025-04-14', '2025-04-21', '1010 Hilltop Road, Ipoh, Malaysia');

-- Product Order Data
INSERT INTO product_order (product_id, order_id, quantity, sub_price, packaging, status) 
VALUES
  (1, 1, 1, 4500.00, 'More Protection','Completed'),
  (11, 1, 5, 22.50,'Normal','Completed'),
  (2, 2, 5, 175.00, 'Normal','Completed'),
  (20, 2, 3, 11.97, 'More Protection','Completed'),
  (5, 3, 1, 6000.00, 'More Protection','Completed'),
  (17, 3, 10, 50.00,  'Normal','Completed'),
  (6, 4, 2, 120.00, 'Normal','Completed'),
  (28, 4, 3, 53.97, 'More Protection','Completed'),
  (9, 5, 2, 1700.00, 'Normal','Completed'),
  (46, 5, 1, 33.50,  'Normal','Completed'),
  (10, 6, 4, 140.00, 'More Protection', 'Completed'),
  (25, 6, 8, 50.32,  'Normal','Completed'),
  (47, 7, 10, 30.00, 'Normal','Completed'),
  (52, 7, 5, 13.75,  'More Protection', 'Completed'),
  (49, 8, 8, 40.00, 'Normal','Completed'),
  (57, 8, 4, 22.00, 'Normal','Completed'),
  (65, 9, 5, 200.00, 'More Protection', 'Completed'),
  (68, 9, 2, 50.00,  'Normal','Completed'),
  (66, 10, 3, 360.00, 'Normal','Completed'),
  (69, 10, 1, 300.00, 'More Protection','Completed');

-- Payment Data
INSERT INTO payment (order_id, user_id, total_amount, payment_method, payment_status, transaction_date)
VALUES
  (1, 1, 4522.50, 'Credit Card','Completed', '2025-04-10 10:00:00'),
  (2, 1, 186.97,  'Bank Transfer','Completed', '2025-04-10 11:00:00'),
  (3, 2, 6050.00, 'Cash On Delivery','Pending',   '2025-04-11 09:00:00'),
  (4, 2, 173.97,  'Credit Card','Completed', '2025-04-11 12:00:00'),
  (5, 3, 1733.50, 'Bank Transfer','Completed', '2025-04-12 10:00:00'),
  (6, 3, 190.32,  'Cash On Delivery','Completed', '2025-04-12 15:00:00'),
  (7, 4, 43.75,   'Credit Card','Completed', '2025-04-13 09:00:00'),
  (8, 4, 62.00,   'Bank Transfer','Completed', '2025-04-13 14:00:00'),
  (9, 5, 250.00,  'Cash On Delivery','Completed', '2025-04-14 10:00:00'),
  (10,5, 660.00,  'Credit Card','Completed', '2025-04-14 16:00:00');

-- Shipment Data
INSERT INTO shipment (order_id, tracking_number, status, update_timestamp, estimated_delivery_date) 
VALUES
  (1, 'GJT6X41', 'Packaging','2025-04-10 12:00:00', '2025-04-13'),
  (2, 'HKU823H', 'Pending','2025-04-10 11:30:00', '2025-04-13'),
  (3, 'DFG84B8', 'Packaging','2025-04-11 10:00:00', '2025-04-13'),
  (4, '46YTB68', 'Pending','2025-04-11 12:30:00', '2025-04-13'),
  (5, 'TRYH434', 'Packaging','2025-04-12 11:00:00', '2025-04-13'),
  (6, 'HG56VUH', 'Pending','2025-04-12 15:30:00', '2025-04-13'),
  (7, '45TF45Y', 'Ready to Pickup by Carrier','2025-04-13 10:00:00', '2025-04-13'),
  (8, '456789N', 'Packaging','2025-04-12 14:30:00', '2025-04-13'),
  (9, '3BV74GS', 'Packaging','2025-04-12 10:30:00', '2025-04-23'),
  (10,'7BU97JJD-','Ready to Pickup by Carrier','2025-04-14 16:30:00', '2025-04-13');

-- Order History Data
INSERT INTO customer_order_history (order_id, status, order_date) 
VALUES 
  (1, 'Completed', '2025-04-10'),
  (2, 'Completed', '2025-04-10'),
  (3, 'Completed', '2025-04-11'),
  (4, 'Completed', '2025-04-11'),
  (5, 'Completed', '2025-04-12'),
  (6, 'Completed', '2025-04-12'),
  (7, 'Completed', '2025-04-13'),
  (8, 'Completed', '2025-04-13'),
  (9, 'Completed', '2025-04-14'),
  (10, 'Completed', '2025-04-14');

-- Review Data
INSERT INTO review (product_id, user_id, rating, review_description, review_date)
VALUES 
(79, 1, 5, 'Excellent product! Highly recommend.', CURRENT_DATE),
(79, 2, 4, 'Good quality, but delivery was slow.', CURRENT_DATE),
(1, 1, 5, 'Excellent product, very satisfied', '2025-04-10'),
(11, 1, 4, 'Good quality and performance', '2025-04-11'),
(2, 1, 4, 'Nice and healthy chickens', '2025-04-11'),
(20, 1, 3, 'Average quality, could be better', '2025-04-08'),
(5, 2, 5, 'Superb beef quality', '2025-04-08'),
(17, 2, 4, 'Great value for the price', '2025-04-08'),
(6, 2, 3, 'Satisfactory, but room for improvement', '2025-04-09'),
(28, 2, 4, 'Good product packaging and quality', '2025-04-09'),
(9, 3, 5, 'Highly recommend this product', '2025-04-09'),
(46, 3, 4, 'Meets expectations', '2025-04-09'),
(10, 3, 4, 'Satisfied with the quality', '2025-04-10'),
(25, 3, 3, 'Could be improved in some aspects', '2025-04-10'),
(47, 4, 4, 'Nice packaging, fast delivery', '2025-04-10'),
(52, 4, 4, 'Quality as expected', '2025-04-10'),
(49, 4, 5, 'Excellent product quality', '2025-04-19'),
(57, 4, 5, 'Very satisfied with the product', '2025-04-19'),
(65, 5, 4, 'Good value for money', '2025-04-10'),
(68, 5, 3, 'Product quality is average', '2025-04-10'),
(66, 5, 5, 'Fantastic product', '2025-04-11'),
(69, 5, 5, 'Exceeded expectations', '2025-04-11');