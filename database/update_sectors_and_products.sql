-- =====================================================================
-- STONE ENERGY INT'L LTD - Sector & Product Scope Update
-- Execute this script in phpMyAdmin on cPanel to update live data
-- =====================================================================

-- 1. Update Services: Oil & Gas and Agroprocessing
UPDATE `services` 
SET 
  `title` = 'Oil & Gas Supply',
  `short_description` = 'Dependable commercial supply of crude oil and refined petroleum products (PMS, AGO/Diesel, DPK) with dedicated logistics and quality compliance across Nigeria.',
  `full_description` = '<p>STONE ENERGY INT\'L LTD is a trusted Nigerian energy partner specializing in the commercial supply and haulage of crude oil and refined petroleum products. We serve downstream distributors, industrial power plants, telecommunication infrastructure hubs, commercial transport fleets, and filling stations with timely, high-specification fuel deliveries.</p><p>From bulk AGO (Automotive Gas Oil / Diesel) and PMS (Premium Motor Spirit) supply to crude oil offtake logistics and industrial fuel supplies, our operations maintain rigorous fuel purity checks, calibrated metering, and disciplined haulage tracking across Nigerian energy hubs.</p>',
  `features` = 'Commercial supply of crude oil & petroleum products||Bulk Automotive Gas Oil (AGO / Diesel) distribution||Premium Motor Spirit (PMS) & Dual Purpose Kerosene (DPK)||Industrial fuel supplies for manufacturing & power generation||Dedicated haulage logistics & fleet coordination||Fuel quality assurance & calibrated metering compliance',
  `meta_title` = 'Supply of Crude Oil & Petroleum Products Nigeria | STONE ENERGY INT\'L LTD',
  `meta_description` = 'Dependable commercial supply of crude oil and refined petroleum products (PMS, AGO diesel, DPK) across Ibadan and Nigeria.'
WHERE `slug` = 'oil-and-gas-supply' OR `id` = 1;

UPDATE `services` 
SET 
  `title` = 'Agroprocessing & Animal Feeds',
  `short_description` = 'Production of premium quality floating fish feed and general animal feeds, alongside grain processing and feed milling solutions.',
  `full_description` = '<p>At STONE ENERGY INT\'L LTD, our agroprocessing division focuses on the production and formulation of premium quality floating fish feed and general animal feeds. We understand that balanced nutrition directly drives growth rates, optimal Feed Conversion Ratios (FCR), and overall farm profitability for commercial farmers.</p><p>Utilizing high-protein formulations, essential amino acids, and modern extrusion and milling technology, we manufacture water-stable floating fish feeds for catfish and tilapia at all growth stages, as well as nutrient-dense general feeds for poultry, swine, and livestock.</p>',
  `features` = 'Production of quality floating fish feed (catfish & tilapia)||Formulation of general animal feeds (poultry, pig & livestock feeds)||High protein-to-energy balance for optimal feed conversion ratio (FCR)||Water-stable floating pellets with minimal dissolution wastage||Bulk distribution to commercial farms & agricultural cooperatives||Feed milling, grain processing & raw material formulation',
  `meta_title` = 'Floating Fish Feed & Animal Feeds Production Nigeria | STONE ENERGY INT\'L LTD',
  `meta_description` = 'Production of high quality floating fish feed and general animal feeds for commercial aquaculture and livestock farms in Nigeria.'
WHERE `slug` = 'agroprocessing' OR `id` = 5;

-- 2. Update Product Categories
UPDATE `product_categories` 
SET `name` = 'Oil & Gas Petroleum', `description` = 'Crude oil, refined petroleum products (AGO diesel, PMS, DPK) and bulk energy supplies'
WHERE `id` = 1 OR `slug` = 'oil-and-gas';

UPDATE `product_categories` 
SET `name` = 'Animal Feeds & Agroprocessing', `description` = 'Quality floating fish feed, poultry & livestock feeds, and feed milling supplies'
WHERE `id` = 7 OR `slug` = 'agroprocessing';

-- 3. Update Existing Products 1 & 6 to Match Scope
UPDATE `products` SET
  `name` = 'Automotive Gas Oil (AGO / Diesel) Bulk Petroleum Supply',
  `slug` = 'automotive-gas-oil-ago-diesel-bulk-petroleum-supply',
  `sku` = 'SEI-PET-001',
  `category_id` = 1,
  `short_description` = 'Depot-direct metered supply and tanker haulage of high-grade Automotive Gas Oil (AGO / Diesel) for corporate fleets, telecom hubs, and manufacturing facilities.',
  `description` = '<p>High-flashpoint, clean-combustion Automotive Gas Oil (AGO) sourced from certified downstream depots. Engineered for peak performance in commercial heavy trucks, industrial power generators, and institutional boiler systems across Nigeria.</p>',
  `specifications` = 'Product: Automotive Gas Oil (AGO / Diesel)||Specification: NMDPRA Standard Compliant||Flash Point: Min 66°C||Density @ 15°C: 0.820 - 0.860 kg/L||Delivery Capacities: 11,000L / 22,000L / 33,000L / 45,000L Metered Tankers',
  `brand` = 'Stone Energy Petroleum',
  `manufacturer` = 'Certified Downstream Depots',
  `availability` = 'in_stock'
WHERE `id` = 1;

UPDATE `products` SET
  `name` = 'Premium Floating Catfish & Tilapia Feed (Extruded Pellets)',
  `slug` = 'premium-floating-catfish-tilapia-feed-extruded-pellets',
  `sku` = 'SEI-FEED-006',
  `category_id` = 7,
  `short_description` = 'High-protein, highly digestible extruded floating fish feed pellets formulated for rapid weight gain and optimal feed conversion ratios.',
  `description` = '<p>Manufactured using quality marine fishmeal, toasted soybeans, essential amino acids, and balanced premixes. Extruded with advanced buoyant technology ensuring pellets stay floating on the water surface for over 30 minutes, preventing pond water fouling and maximizing fish nutrient absorption.</p>',
  `specifications` = 'Crude Protein: 42% - 45% (Fingerling/Juvenile) | 38% - 40% (Grower/Finisher)||Pellet Diameters: 1.5mm, 2mm, 3mm, 4mm, 6mm, 9mm||Buoyancy: >95% Floating Buoyant Pellets||Packaging: 15kg & 25kg Durable Woven Bags',
  `brand` = 'Stone Feeds',
  `manufacturer` = 'Stone Energy Agroprocessing Division',
  `availability` = 'in_stock'
WHERE `id` = 6;

-- 4. Insert Additional Core Products (Crude Oil Supply & Animal Feeds)
INSERT INTO `products` (`id`, `name`, `slug`, `sku`, `category_id`, `short_description`, `description`, `specifications`, `brand`, `manufacturer`, `availability`, `featured`, `status`)
VALUES 
(7, 'Crude Oil Commercial Supply & Offtake Logistics', 'crude-oil-commercial-supply-offtake-logistics', 'SEI-PET-007', 1, 'Commercial brokerage, off-take coordination, and haulage logistics for certified crude oil allocations and industrial users.', '<p>Facilitating structured crude oil supply arrangements and terminal logistics for approved buyers, refineries, and commercial industrial end-users across West Africa.</p>', 'Grade: Light Sweet Crude / Domestic Heavy Blends||Quality Verification: Independent SGS / Intertek Inspection Available||Logistics: Marine Vessel Charter / Pipeline & Road Haulage Coordination', 'Stone Energy Petroleum', 'Terminal Offtake Partners', 'available_on_order', 1, 'published'),
(8, 'Commercial Animal Feeds (Poultry, Swine & Livestock)', 'commercial-animal-feeds-poultry-swine-livestock', 'SEI-FEED-008', 7, 'Scientifically balanced complete mash and pellet feeds for poultry broilers, layers, pigs, and cattle.', '<p>Nutrient-dense feeds formulated with accurate energy-to-protein ratios to support high egg production in layers, fast meat development in broilers, and healthy swine growth with minimum feed wastage.</p>', 'Feed Types: Broiler Starter/Finisher, Layer Mash, Pig Grower, Cattle Concentrate||Packaging: 25kg / 50kg Branded Bags||Key Ingredients: Maize, Soya Meal, Wheat Offal, Bone Meal, Premix', 'Stone Feeds', 'Stone Energy Agroprocessing Division', 'in_stock', 1, 'published')
ON DUPLICATE KEY UPDATE 
  `name` = VALUES(`name`),
  `short_description` = VALUES(`short_description`),
  `description` = VALUES(`description`),
  `specifications` = VALUES(`specifications`);
