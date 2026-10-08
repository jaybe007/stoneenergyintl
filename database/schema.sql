-- =====================================================================
-- STONE ENERGY INT'L LTD - Complete MySQL Database Schema
-- Multi-Sector Supply Solutions & General Contracting Enterprise CMS
-- Engine: InnoDB | Charset: utf8mb4 | Collation: utf8mb4_unicode_ci
-- =====================================================================

SET FOREIGN_KEY_CHECKS = 0;
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

-- ---------------------------------------------------------------------
-- 1. Roles Table
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS `roles`;
CREATE TABLE `roles` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(50) NOT NULL UNIQUE,
    `slug` VARCHAR(50) NOT NULL UNIQUE,
    `description` VARCHAR(255) DEFAULT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `roles` (`id`, `name`, `slug`, `description`) VALUES
(1, 'Super Admin', 'super_admin', 'Full system access and administrator management'),
(2, 'Admin', 'admin', 'Manage business content, RFQs, messages and settings'),
(3, 'Editor', 'editor', 'Manage catalog, projects, services and publications'),
(4, 'Content Manager', 'content_manager', 'Manage content pages, media, blog and portfolio');

-- ---------------------------------------------------------------------
-- 2. Permissions Table
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS `permissions`;
CREATE TABLE `permissions` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `slug` VARCHAR(100) NOT NULL UNIQUE,
    `module` VARCHAR(50) NOT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `permissions` (`name`, `slug`, `module`) VALUES
('Manage Users', 'users.manage', 'users'),
('Manage Roles', 'roles.manage', 'roles'),
('Manage Settings', 'settings.manage', 'settings'),
('Manage RFQs', 'rfqs.manage', 'rfqs'),
('Manage Messages', 'messages.manage', 'messages'),
('Manage Services', 'services.manage', 'services'),
('Manage Products', 'products.manage', 'products'),
('Manage Projects', 'projects.manage', 'projects'),
('Manage Blog', 'blog.manage', 'blog'),
('Manage Media', 'media.manage', 'media'),
('Manage Pages', 'pages.manage', 'pages'),
('View Audit Logs', 'audit.view', 'audit'),
('Export Backups', 'backup.export', 'backup');

-- ---------------------------------------------------------------------
-- 3. Role Permissions Mapping
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS `role_permissions`;
CREATE TABLE `role_permissions` (
    `role_id` INT UNSIGNED NOT NULL,
    `permission_id` INT UNSIGNED NOT NULL,
    PRIMARY KEY (`role_id`, `permission_id`),
    CONSTRAINT `fk_rp_role` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_rp_perm` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Super Admin: all permissions (1-13)
INSERT INTO `role_permissions` (`role_id`, `permission_id`)
SELECT 1, id FROM `permissions`;

-- Admin: everything except users.manage, roles.manage, backup.export
INSERT INTO `role_permissions` (`role_id`, `permission_id`)
SELECT 2, id FROM `permissions` WHERE `slug` NOT IN ('users.manage', 'roles.manage', 'backup.export');

-- Editor: content, services, products, projects, blog, media, pages
INSERT INTO `role_permissions` (`role_id`, `permission_id`)
SELECT 3, id FROM `permissions` WHERE `module` IN ('services', 'products', 'projects', 'blog', 'media', 'pages');

-- Content Manager: media, blog, products, projects, services, pages
INSERT INTO `role_permissions` (`role_id`, `permission_id`)
SELECT 4, id FROM `permissions` WHERE `module` IN ('media', 'blog', 'products', 'projects', 'services', 'pages');

-- ---------------------------------------------------------------------
-- 4. Users Table
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `username` VARCHAR(50) NOT NULL UNIQUE,
    `email` VARCHAR(120) NOT NULL UNIQUE,
    `password_hash` VARCHAR(255) NOT NULL,
    `full_name` VARCHAR(100) NOT NULL,
    `role_id` INT UNSIGNED NOT NULL DEFAULT 4,
    `status` ENUM('active', 'inactive', 'suspended') NOT NULL DEFAULT 'active',
    `avatar` VARCHAR(255) DEFAULT NULL,
    `last_login` DATETIME DEFAULT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_users_role` (`role_id`),
    INDEX `idx_users_status` (`status`),
    CONSTRAINT `fk_users_role` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Default Super Admin (Password: AdminPassword2026!)
-- Hash generated using password_hash('AdminPassword2026!', PASSWORD_BCRYPT)
INSERT INTO `users` (`id`, `username`, `email`, `password_hash`, `full_name`, `role_id`, `status`) VALUES
(1, 'admin', 'admin@stoneenergyintl.com', '$2y$10$teTpwuNLl4hG1swqD3ELLeyuKOmCOFNiY/B04io53nIJEUyVhzSAa', 'Principal Administrator', 1, 'active');

-- ---------------------------------------------------------------------
-- 5. User Roles Junction (for multi-role support)
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS `user_roles`;
CREATE TABLE `user_roles` (
    `user_id` INT UNSIGNED NOT NULL,
    `role_id` INT UNSIGNED NOT NULL,
    PRIMARY KEY (`user_id`, `role_id`),
    CONSTRAINT `fk_ur_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_ur_role` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `user_roles` (`user_id`, `role_id`) VALUES (1, 1);

-- ---------------------------------------------------------------------
-- 6. Login Rate Limiting / Throttling Table
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS `login_attempts`;
CREATE TABLE `login_attempts` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `ip_address` VARCHAR(45) NOT NULL,
    `username` VARCHAR(100) NOT NULL,
    `attempted_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_attempts_ip_time` (`ip_address`, `attempted_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 7. Password Resets Table
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS `password_resets`;
CREATE TABLE `password_resets` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `email` VARCHAR(120) NOT NULL,
    `token_hash` VARCHAR(64) NOT NULL UNIQUE,
    `expires_at` DATETIME NOT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_pw_email` (`email`),
    INDEX `idx_pw_token` (`token_hash`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 8. Audit Logs Table
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS `audit_logs`;
CREATE TABLE `audit_logs` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNSIGNED DEFAULT NULL,
    `action` VARCHAR(100) NOT NULL,
    `module` VARCHAR(50) NOT NULL,
    `record_id` VARCHAR(50) DEFAULT NULL,
    `description` TEXT NOT NULL,
    `ip_address` VARCHAR(45) DEFAULT NULL,
    `user_agent` VARCHAR(255) DEFAULT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_audit_user` (`user_id`),
    INDEX `idx_audit_module` (`module`),
    INDEX `idx_audit_created` (`created_at`),
    CONSTRAINT `fk_audit_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 9. Site Settings Table
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS `site_settings`;
CREATE TABLE `site_settings` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `setting_key` VARCHAR(100) NOT NULL UNIQUE,
    `setting_value` MEDIUMTEXT DEFAULT NULL,
    `setting_group` VARCHAR(50) NOT NULL DEFAULT 'general',
    `label` VARCHAR(150) NOT NULL,
    `field_type` ENUM('text', 'textarea', 'editor', 'image', 'boolean', 'select', 'number') NOT NULL DEFAULT 'text',
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_settings_group` (`setting_group`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `site_settings` (`setting_key`, `setting_value`, `setting_group`, `label`, `field_type`) VALUES
('company_name', 'STONE ENERGY INT\'L LTD', 'general', 'Company Legal Name', 'text'),
('tagline', 'GENERAL CONTRACTOR & MULTI-SECTOR SUPPLY SOLUTIONS', 'general', 'Company Tagline', 'text'),
('motto', 'Building. Supplying. Delivering.', 'general', 'Brand Motto', 'text'),
('cac_number', '[ADD CAC NUMBER]', 'general', 'CAC Registration Number', 'text'),
('rc_number', '[ADD RC NUMBER]', 'general', 'Corporate Affairs RC Number', 'text'),
('logo_url', 'assets/images/logo.svg', 'general', 'Header Logo Image', 'image'),
('favicon_url', 'assets/images/favicon.svg', 'general', 'Website Favicon', 'image'),
('phone_primary', '08037745881', 'contact', 'Primary Phone Number', 'text'),
('phone_secondary', '08084949840', 'contact', 'Secondary Phone Number', 'text'),
('email_primary', '[ADD COMPANY EMAIL]', 'contact', 'Official Email Address', 'text'),
('email_support', '[ADD SUPPORT EMAIL]', 'contact', 'Support / RFQ Email', 'text'),
('office_address', '22, Oyelude Layout, Aba Alfa, Ojo, Ibadan, Oyo State, Nigeria.', 'contact', 'Physical Headquarters Address', 'textarea'),
('city', 'Ibadan', 'contact', 'Base City', 'text'),
('state', 'Oyo State', 'contact', 'State', 'text'),
('country', 'Nigeria', 'contact', 'Country', 'text'),
('business_hours', 'Monday - Friday: 8:00 AM - 5:00 PM | Saturday: By Appointment', 'contact', 'Official Business Hours', 'text'),
('whatsapp_number', '2348037745881', 'whatsapp', 'WhatsApp Business Number (digits only)', 'text'),
('whatsapp_message', 'Hello Stone Energy Int\'l Ltd, I would like to inquire about your general contracting and supply solutions.', 'whatsapp', 'WhatsApp Default Message', 'textarea'),
('whatsapp_enabled', '1', 'whatsapp', 'Enable Floating WhatsApp Widget', 'boolean'),
('google_maps_embed', '', 'contact', 'Google Maps Embed URL', 'textarea'),
('social_facebook', '', 'social', 'Facebook URL', 'text'),
('social_linkedin', '', 'social', 'LinkedIn Company URL', 'text'),
('social_twitter', '', 'social', 'X / Twitter URL', 'text'),
('social_instagram', '', 'social', 'Instagram URL', 'text'),
('social_youtube', '', 'social', 'YouTube Channel URL', 'text'),
('smtp_host', 'localhost', 'smtp', 'SMTP Server Host', 'text'),
('smtp_port', '587', 'smtp', 'SMTP Port', 'number'),
('smtp_username', '', 'smtp', 'SMTP Username', 'text'),
('smtp_password', '', 'smtp', 'SMTP Password', 'text'),
('smtp_encryption', 'tls', 'smtp', 'SMTP Encryption (tls / ssl / none)', 'text'),
('smtp_from_email', 'noreply@stoneenergyintl.com', 'smtp', 'System Sender Email', 'text'),
('smtp_from_name', 'Stone Energy Int\'l Ltd Notification', 'smtp', 'Sender Display Name', 'text'),
('smtp_enabled', '0', 'smtp', 'Enable Live SMTP Sending', 'boolean'),
('analytics_ga_id', '', 'analytics', 'Google Analytics Measurement ID (G-XXXXXXXX)', 'text'),
('analytics_gtm_id', '', 'analytics', 'Google Tag Manager Container ID', 'text'),
('analytics_gsc_tag', '', 'analytics', 'Google Search Console Verification Tag', 'text'),
('hero_headline', 'Building, Supplying and Delivering Solutions That Move Businesses Forward.', 'homepage', 'Hero Main Headline', 'textarea'),
('hero_subheadline', 'STONE ENERGY INT\'L LTD is a Nigerian general contracting and multi-sector supply company providing solutions across oil & gas, construction, healthcare, agro-allied and agroprocessing industries.', 'homepage', 'Hero Sub-headline', 'textarea'),
('hero_cta_primary_text', 'REQUEST A QUOTE', 'homepage', 'Hero Primary Button Text', 'text'),
('hero_cta_primary_link', 'quote.php', 'homepage', 'Hero Primary Button Link', 'text'),
('hero_cta_secondary_text', 'EXPLORE OUR SERVICES', 'homepage', 'Hero Secondary Button Text', 'text'),
('hero_cta_secondary_link', 'services.php', 'homepage', 'Hero Secondary Button Link', 'text'),
('section_hero_enabled', '1', 'homepage', 'Enable Hero Section', 'boolean'),
('section_services_enabled', '1', 'homepage', 'Enable Services Section', 'boolean'),
('section_why_choose_enabled', '1', 'homepage', 'Enable Why Choose Us Section', 'boolean'),
('section_products_enabled', '1', 'homepage', 'Enable Featured Products Section', 'boolean'),
('section_projects_enabled', '1', 'homepage', 'Enable Featured Projects Section', 'boolean'),
('section_quote_cta_enabled', '1', 'homepage', 'Enable Fast Quote Banner', 'boolean'),
('section_testimonials_enabled', '1', 'homepage', 'Enable Client Testimonials Section', 'boolean'),
('about_overview', 'STONE ENERGY INT\'L LTD is an indigenous Nigerian enterprise structured to deliver integrated engineering, multi-sector procurement, construction, and specialized supply solutions. Operating from Ibadan, Oyo State, we bridge industrial procurement gaps with transparent coordination, quality assurance, and timely execution.', 'about', 'Corporate Overview', 'textarea'),
('about_mission', 'To provide dependable multi-sector supply chain and general contracting solutions across Nigeria through uncompromising integrity, technical competence, and customer-first execution.', 'about', 'Mission Statement', 'textarea'),
('about_vision', 'To stand as Nigeria\'s most trusted multi-sector supply partner and contractor of choice for energy, construction, healthcare, and agro-allied enterprises.', 'about', 'Vision Statement', 'textarea'),
('about_core_values', 'Reliability, Professional Integrity, Quality Assurance, Procurement Transparency, Timely Delivery, Safety First', 'about', 'Core Values', 'textarea'),
('about_company_history', '[ADD COMPANY HISTORY - Awaiting corporate profile document]', 'about', 'Company History Placeholder', 'textarea'),
('about_management_message', '[ADD MANAGEMENT MESSAGE - Awaiting executive address]', 'about', 'Management Message Placeholder', 'textarea');

-- ---------------------------------------------------------------------
-- 10. Pages Table (Legal and Custom Content)
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS `pages`;
CREATE TABLE `pages` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `slug` VARCHAR(100) NOT NULL UNIQUE,
    `title` VARCHAR(200) NOT NULL,
    `content` LONGTEXT NOT NULL,
    `meta_title` VARCHAR(255) DEFAULT NULL,
    `meta_description` VARCHAR(300) DEFAULT NULL,
    `status` ENUM('published', 'draft') NOT NULL DEFAULT 'published',
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_pages_slug` (`slug`),
    INDEX `idx_pages_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `pages` (`slug`, `title`, `content`, `meta_title`, `meta_description`, `status`) VALUES
('privacy-policy', 'Privacy Policy', '<h2>Privacy Policy for STONE ENERGY INT\'L LTD</h2><p>At STONE ENERGY INT\'L LTD, accessible from our official website, your privacy is of paramount importance to us. This Privacy Policy document outlines the types of information that is collected and recorded by us and how we utilize it.</p><h3>Information We Collect</h3><p>We collect information you supply when submitting a Request for Quotation (RFQ), using our contact form, or communicating with our offices. This includes your name, company name, phone number, email address, project requirements, and location.</p><h3>Use of Your Information</h3><p>We use collected data solely to provide customized commercial quotations, execute general contracting agreements, fulfill procurement orders, and respond to commercial inquiries.</p><h3>Data Security</h3><p>We employ administrative and technical precautions to safeguard submitted information against unauthorized access, alteration, or disclosure.</p>', 'Privacy Policy | STONE ENERGY INT\'L LTD', 'Learn how STONE ENERGY INT\'L LTD protects your corporate and personal data.', 'published'),
('terms-and-conditions', 'Terms & Conditions', '<h2>Terms & Conditions</h2><p>Welcome to STONE ENERGY INT\'L LTD. By accessing this website and utilizing our RFQ and contracting portals, you agree to comply with the terms and conditions set forth herein.</p><h3>Commercial Quotations</h3><p>Quotations issued via our website or following RFQ submission represent non-binding estimates until confirmed through formal commercial purchase orders or signed contracts.</p><h3>Intellectual Property</h3><p>All brand assets, service specifications, website content, and media remain the property of STONE ENERGY INT\'L LTD.</p>', 'Terms & Conditions | STONE ENERGY INT\'L LTD', 'Review the terms and conditions governing engagement with STONE ENERGY INT\'L LTD.', 'published'),
('cookie-policy', 'Cookie Policy', '<h2>Cookie Policy</h2><p>STONE ENERGY INT\'L LTD uses essential cookies to ensure safe browsing, CSRF security verification, and administrative session management.</p><p>We do not sell personal browsing data to external advertisers.</p>', 'Cookie Policy | STONE ENERGY INT\'L LTD', 'Detailed explanation of cookie usage and session security on our platform.', 'published'),
('disclaimer', 'Legal Disclaimer', '<h2>Legal Disclaimer</h2><p>The information on this website is provided for general informational and procurement reference. While STONE ENERGY INT\'L LTD strives to maintain accurate and updated information, specifications and material availability may vary depending on project scale, market availability, and contractual requirements.</p><p>Technical and regulatory compliance requirements for oil & gas, civil construction, and healthcare equipment supplies are formally verified on a per-contract basis.</p>', 'Legal Disclaimer | STONE ENERGY INT\'L LTD', 'Official disclaimer regarding product availability and general contracting services.', 'published');

-- ---------------------------------------------------------------------
-- 11. Service Categories Table
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS `service_categories`;
CREATE TABLE `service_categories` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL UNIQUE,
    `slug` VARCHAR(100) NOT NULL UNIQUE,
    `description` VARCHAR(255) DEFAULT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `service_categories` (`id`, `name`, `slug`, `description`) VALUES
(1, 'Energy & Resources', 'energy-resources', 'Oil & gas supply chain and energy procurement solutions'),
(2, 'Infrastructure & Civil', 'infrastructure-civil', 'Building construction, renovations, and civil engineering'),
(3, 'Healthcare & Medical', 'healthcare-medical', 'Hospital equipment, medical furniture, and consumables supply'),
(4, 'Agriculture & Processing', 'agriculture-processing', 'Agro-allied inputs, bulk distribution, and processing support'),
(5, 'Contracting & Procurement', 'contracting-procurement', 'General contracting, subcontracting, and turnkey procurement');

-- ---------------------------------------------------------------------
-- 12. Services Table
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS `services`;
CREATE TABLE `services` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `title` VARCHAR(150) NOT NULL,
    `slug` VARCHAR(150) NOT NULL UNIQUE,
    `category_id` INT UNSIGNED NOT NULL,
    `icon` VARCHAR(100) DEFAULT 'wrench',
    `short_description` VARCHAR(300) NOT NULL,
    `full_description` LONGTEXT NOT NULL,
    `features` TEXT DEFAULT NULL,
    `image` VARCHAR(255) DEFAULT NULL,
    `status` ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
    `sort_order` INT NOT NULL DEFAULT 0,
    `meta_title` VARCHAR(255) DEFAULT NULL,
    `meta_description` VARCHAR(300) DEFAULT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_services_cat` (`category_id`),
    INDEX `idx_services_status` (`status`),
    INDEX `idx_services_order` (`sort_order`),
    CONSTRAINT `fk_services_cat` FOREIGN KEY (`category_id`) REFERENCES `service_categories` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `services` (`id`, `title`, `slug`, `category_id`, `icon`, `short_description`, `full_description`, `features`, `sort_order`, `meta_title`, `meta_description`) VALUES
(1, 'Oil & Gas Supply', 'oil-and-gas-supply', 1, 'fuel', 'Dependable commercial supply of crude oil and refined petroleum products (PMS, AGO/Diesel, DPK) with dedicated logistics and quality compliance across Nigeria.', '<p>STONE ENERGY INT\'L LTD is a trusted Nigerian energy partner specializing in the commercial supply and haulage of crude oil and refined petroleum products. We serve downstream distributors, industrial power plants, telecommunication infrastructure hubs, commercial transport fleets, and filling stations with timely, high-specification fuel deliveries.</p><p>From bulk AGO (Automotive Gas Oil / Diesel) and PMS (Premium Motor Spirit) supply to crude oil offtake logistics and industrial fuel supplies, our operations maintain rigorous fuel purity checks, calibrated metering, and disciplined haulage tracking across Nigerian energy hubs.</p>', 'Commercial supply of crude oil & petroleum products||Bulk Automotive Gas Oil (AGO / Diesel) distribution||Premium Motor Spirit (PMS) & Dual Purpose Kerosene (DPK)||Industrial fuel supplies for manufacturing & power generation||Dedicated haulage logistics & fleet coordination||Fuel quality assurance & calibrated metering compliance', 1, 'Supply of Crude Oil & Petroleum Products Nigeria | STONE ENERGY INT\'L LTD', 'Dependable commercial supply of crude oil and refined petroleum products (PMS, AGO diesel, DPK) across Ibadan and Nigeria.'),
(2, 'Building Construction', 'building-construction', 2, 'building', 'End-to-end building construction, structural renovation, routine facility maintenance, civil works, and comprehensive project coordination.', '<p>From groundbreaking civil works to multi-story construction and institutional renovations, STONE ENERGY INT\'L LTD acts as a principal general contractor for private, commercial, and institutional projects across Oyo State and nationwide.</p><p>We coordinate skilled labor, structural engineers, material procurement, and rigorous site supervision to deliver enduring structures built to exacting engineering standards.</p>', 'Turnkey building construction & civil engineering||Structural renovations and commercial remodeling||Facility maintenance & structural preservation||Site preparation, earthworks, and drainage||Project coordination & subcontractor management', 2, 'Building Construction Company in Ibadan | STONE ENERGY INT\'L LTD', 'Expert building construction, renovation, civil works, and general contracting in Ibadan, Oyo State, Nigeria.'),
(3, 'Hospital Equipment & Consumables Supply', 'hospital-equipment-consumables-supply', 3, 'heart-pulse', 'Reliable sourcing and supply of medical devices, hospital furniture, diagnostic equipment, and certified healthcare consumables.', '<p>Healthcare delivery demands uncompromising quality and rapid supply response. STONE ENERGY INT\'L LTD partners with public and private health institutions, clinics, and diagnostic centers to supply vital medical hardware, patient care furniture, and day-to-day clinical consumables.</p><p>We prioritize transparent manufacturer sourcing, proper transit packaging, and reliable delivery to support doctors, nurses, and patient care.</p>', 'Hospital beds, examination tables & clinical furniture||Diagnostic and monitoring equipment supply||Sterile clinical consumables & personal protective equipment||Surgical sundries and laboratory consumables||Institutional healthcare procurement packages', 3, 'Hospital Equipment & Medical Consumables Supplier Ibadan | STONE ENERGY INT\'L LTD', 'Supply of hospital equipment, medical furniture, and healthcare consumables to clinics and hospitals in Nigeria.'),
(4, 'Agro-Allied', 'agro-allied', 4, 'sprout', 'Comprehensive procurement and distribution of agricultural inputs, farm products, quality crop supplies, and agribusiness materials.', '<p>Nigeria\'s agricultural sector requires robust procurement channels to connect primary producers, commercial farms, and agricultural processors. STONE ENERGY INT\'L LTD delivers dependable agro-allied supply services, sourcing vital inputs, seed varieties, fertilizers, crop protection solutions, and agricultural sundries.</p><p>We provide supply stability that empowers agricultural businesses to operate efficiently throughout planting and harvest cycles.</p>', 'Agricultural inputs & bulk farm supplies||Quality crop procurement and distribution||Fertilizer, agrochemical & soil input sourcing||Farm logistics & regional haulage coordination||Wholesale agricultural commodities handling', 4, 'Agro-Allied Company in Ibadan, Oyo State | STONE ENERGY INT\'L LTD', 'Dependable agro-allied supplies, agricultural inputs, and crop distribution solutions in Ibadan and nationwide.'),
(5, 'Agroprocessing & Animal Feeds', 'agroprocessing', 5, 'cog', 'Production of premium quality floating fish feed and general animal feeds, alongside grain processing and feed milling solutions.', '<p>At STONE ENERGY INT\'L LTD, our agroprocessing division focuses on the production and formulation of premium quality floating fish feed and general animal feeds. We understand that balanced nutrition directly drives growth rates, optimal Feed Conversion Ratios (FCR), and overall farm profitability for commercial farmers.</p><p>Utilizing high-protein formulations, essential amino acids, and modern extrusion and milling technology, we manufacture water-stable floating fish feeds for catfish and tilapia at all growth stages, as well as nutrient-dense general feeds for poultry, swine, and livestock.</p>', 'Production of quality floating fish feed (catfish & tilapia)||Formulation of general animal feeds (poultry, pig & livestock feeds)||High protein-to-energy balance for optimal feed conversion ratio (FCR)||Water-stable floating pellets with minimal dissolution wastage||Bulk distribution to commercial farms & agricultural cooperatives||Feed milling, grain processing & raw material formulation', 5, 'Floating Fish Feed & Animal Feeds Production Nigeria | STONE ENERGY INT\'L LTD', 'Production of high quality floating fish feed and general animal feeds for commercial aquaculture and livestock farms in Nigeria.'),
(6, 'General Contracting', 'general-contracting', 6, 'briefcase', 'Multi-sector project coordination, procurement management, subcontracting oversight, and dependable turnkey project execution.', '<p>As a versatile general contractor, STONE ENERGY INT\'L LTD unifies multi-disciplinary capabilities under single-point responsibility. We handle complex procurement challenges, mobilize specialized technical teams, oversee subcontracted specialists, and guarantee delivery standards for government bodies, corporations, and private clients.</p><p>Our systematic project management methodology ensures budget discipline, timeline compliance, and high construction standards from inception to handover.</p>', 'Single-point general contracting & project management||Multi-sector procurement & vendor consolidation||Subcontractor coordination & quality oversight||Tender execution & contract delivery||Turnkey material sourcing and logistics', 6, 'General Contractor in Ibadan & Oyo State | STONE ENERGY INT\'L LTD', 'Premier general contracting and multi-sector project coordination in Ibadan, Oyo State, Nigeria.');

-- ---------------------------------------------------------------------
-- 13. Product Categories Table
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS `product_categories`;
CREATE TABLE `product_categories` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL UNIQUE,
    `slug` VARCHAR(100) NOT NULL UNIQUE,
    `description` VARCHAR(255) DEFAULT NULL,
    `image` VARCHAR(255) DEFAULT NULL,
    `sort_order` INT NOT NULL DEFAULT 0,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `product_categories` (`id`, `name`, `slug`, `description`, `sort_order`) VALUES
(1, 'Oil & Gas Petroleum', 'oil-and-gas', 'Crude oil, refined petroleum products (AGO diesel, PMS, DPK) and bulk energy supplies', 1),
(2, 'Construction', 'construction', 'Building materials, aggregates, structural steel and civil supplies', 2),
(3, 'Hospital Equipment', 'hospital-equipment', 'Clinical furniture, monitoring devices and hospital ward fittings', 3),
(4, 'Hospital Consumables', 'hospital-consumables', 'Sterile medical consumables, protective gear and laboratory sundries', 4),
(5, 'Agriculture', 'agriculture', 'Grains, seeds, bulk agricultural produce and farm inputs', 5),
(6, 'Agro-Allied', 'agro-allied', 'Fertilizers, agrochemicals and soil management supplies', 6),
(7, 'Animal Feeds & Agroprocessing', 'agroprocessing', 'Quality floating fish feed, poultry & livestock feeds, and feed milling supplies', 7),
(8, 'Industrial Supplies', 'industrial-supplies', 'Tools, safety equipment, fasteners and workshop machinery', 8),
(9, 'Other', 'other', 'Specialized commercial procurement and custom supply items', 9);

-- ---------------------------------------------------------------------
-- 14. Products Table
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS `products`;
CREATE TABLE `products` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(200) NOT NULL,
    `slug` VARCHAR(200) NOT NULL UNIQUE,
    `sku` VARCHAR(50) DEFAULT NULL UNIQUE,
    `category_id` INT UNSIGNED NOT NULL,
    `short_description` VARCHAR(350) NOT NULL,
    `description` LONGTEXT NOT NULL,
    `specifications` LONGTEXT DEFAULT NULL,
    `brand` VARCHAR(100) DEFAULT NULL,
    `manufacturer` VARCHAR(100) DEFAULT NULL,
    `image` VARCHAR(255) DEFAULT NULL,
    `datasheet` VARCHAR(255) DEFAULT NULL,
    `availability` ENUM('in_stock', 'available_on_order', 'procured_on_demand') NOT NULL DEFAULT 'available_on_order',
    `featured` TINYINT(1) NOT NULL DEFAULT 0,
    `status` ENUM('published', 'draft', 'archived') NOT NULL DEFAULT 'published',
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_prod_cat` (`category_id`),
    INDEX `idx_prod_status` (`status`),
    INDEX `idx_prod_featured` (`featured`),
    CONSTRAINT `fk_prod_cat` FOREIGN KEY (`category_id`) REFERENCES `product_categories` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `products` (`id`, `name`, `slug`, `sku`, `category_id`, `short_description`, `description`, `specifications`, `brand`, `manufacturer`, `availability`, `featured`, `status`) VALUES
(1, 'Automotive Gas Oil (AGO / Diesel) Bulk Petroleum Supply', 'automotive-gas-oil-ago-diesel-bulk-petroleum-supply', 'SEI-PET-001', 1, 'Depot-direct metered supply and tanker haulage of high-grade Automotive Gas Oil (AGO / Diesel) for corporate fleets, telecom hubs, and manufacturing facilities.', '<p>High-flashpoint, clean-combustion Automotive Gas Oil (AGO) sourced from certified downstream depots. Engineered for peak performance in commercial heavy trucks, industrial power generators, and institutional boiler systems across Nigeria.</p>', 'Product: Automotive Gas Oil (AGO / Diesel)||Specification: NMDPRA Standard Compliant||Flash Point: Min 66°C||Density @ 15°C: 0.820 - 0.860 kg/L||Delivery Capacities: 11,000L / 22,000L / 33,000L / 45,000L Metered Tankers', 'Stone Energy Petroleum', 'Certified Downstream Depots', 'in_stock', 1, 'published'),
(2, 'High-Tensile TMT Deformed Reinforcing Steel Bars', 'high-tensile-tmt-reinforcing-steel-bars', 'SEI-CON-002', 2, 'Grade Fe 500 / 500D high-yield thermo-mechanically treated deformed rebars for heavy civil and commercial building foundations.', '<p>Tested reinforcement steel bars sourced directly for high-stress structural civil works, bridges, commercial complexes, and industrial warehouses across Nigeria. Compliant with national building codes.</p>', 'Grade: Fe 500 / 500D||Diameters Available: 10mm, 12mm, 16mm, 20mm, 25mm, 32mm||Length: Standard 12m Bundles||Bendability: Superior Cold Bend Capability', 'Standard Grade', 'Certified Mill', 'available_on_order', 1, 'published'),
(3, 'Multi-Function Electric ICU Patient Bed', 'multi-function-electric-icu-patient-bed', 'SEI-MED-003', 3, 'Advanced multi-position medical ward bed with electric remote control, backup battery, and integrated ABS fold-down side rails.', '<p>Built to enhance clinical care and patient comfort in emergency wards, ICUs, and private hospital rooms. Provides smooth trendelenburg, reverse trendelenburg, backrest, and knee elevation adjustments.</p>', 'Frame: Epoxy Powder-Coated Steel||Functions: 5-Function Electric Adjustment||Castors: 125mm Central Locking Castors||Safe Working Load: 250 kg||Safety: Emergency CPR Quick Release', 'Medical Solutions', 'Accredited Medical Supplier', 'procured_on_demand', 1, 'published'),
(4, 'Sterile Surgical Gloves & Clinical PPE Pack', 'sterile-surgical-gloves-clinical-ppe-pack', 'SEI-MED-004', 4, 'Powder-free textured latex and nitrile surgical gloves packaged with disposable barrier gowns and surgical masks for healthcare providers.', '<p>Essential infection prevention consumables supplied in bulk cartons to clinics, general hospitals, and research facilities across Oyo State and surrounding regions.</p>', 'Material: Medical Grade Latex / Nitrile||Sterilization: Gamma Ray / EO Gas||Sizes: 6.5, 7.0, 7.5, 8.0||Packaging: 50 Pairs/Box, 10 Boxes/Carton', 'Standard Medical', 'Approved Manufacturer', 'in_stock', 1, 'published'),
(5, 'Bulk Clean Maize & Soybeans Agricultural Supply', 'bulk-clean-maize-soybeans-agricultural-supply', 'SEI-AG-005', 5, 'High-grade commercial yellow/white maize and non-GMO soybeans for commercial feed mills and agroprocessors.', '<p>Procured from verified farmer cooperatives and organized agricultural clusters. Cleaned, graded, and moisture-tested to ensure maximum conversion efficiency for poultry and livestock feed millers.</p>', 'Moisture Content: Max 12% - 13%||Foreign Matter: Under 1.5%||Broken Grains: Max 2%||Packaging: 50kg / 100kg Polypropylene Bags or Bulk Tipper', 'Stone Agro Supply', 'Farm Cluster Aggregation', 'available_on_order', 1, 'published'),
(6, 'Premium Floating Catfish & Tilapia Feed (Extruded Pellets)', 'premium-floating-catfish-tilapia-feed-extruded-pellets', 'SEI-FEED-006', 7, 'High-protein, highly digestible extruded floating fish feed pellets formulated for rapid weight gain and optimal feed conversion ratios.', '<p>Manufactured using quality marine fishmeal, toasted soybeans, essential amino acids, and balanced premixes. Extruded with advanced buoyant technology ensuring pellets stay floating on the water surface for over 30 minutes, preventing pond water fouling and maximizing fish nutrient absorption.</p>', 'Crude Protein: 42% - 45% (Fingerling/Juvenile) | 38% - 40% (Grower/Finisher)||Pellet Diameters: 1.5mm, 2mm, 3mm, 4mm, 6mm, 9mm||Buoyancy: >95% Floating Buoyant Pellets||Packaging: 15kg & 25kg Durable Woven Bags', 'Stone Feeds', 'Stone Energy Agroprocessing Division', 'in_stock', 1, 'published'),
(7, 'Crude Oil Commercial Supply & Offtake Logistics', 'crude-oil-commercial-supply-offtake-logistics', 'SEI-PET-007', 1, 'Commercial brokerage, off-take coordination, and haulage logistics for certified crude oil allocations and industrial users.', '<p>Facilitating structured crude oil supply arrangements and terminal logistics for approved buyers, refineries, and commercial industrial end-users across West Africa.</p>', 'Grade: Light Sweet Crude / Domestic Heavy Blends||Quality Verification: Independent SGS / Intertek Inspection Available||Logistics: Marine Vessel Charter / Pipeline & Road Haulage Coordination', 'Stone Energy Petroleum', 'Terminal Offtake Partners', 'available_on_order', 1, 'published'),
(8, 'Commercial Animal Feeds (Poultry, Swine & Livestock)', 'commercial-animal-feeds-poultry-swine-livestock', 'SEI-FEED-008', 7, 'Scientifically balanced complete mash and pellet feeds for poultry broilers, layers, pigs, and cattle.', '<p>Nutrient-dense feeds formulated with accurate energy-to-protein ratios to support high egg production in layers, fast meat development in broilers, and healthy swine growth with minimum feed wastage.</p>', 'Feed Types: Broiler Starter/Finisher, Layer Mash, Pig Grower, Cattle Concentrate||Packaging: 25kg / 50kg Branded Bags||Key Ingredients: Maize, Soya Meal, Wheat Offal, Bone Meal, Premix', 'Stone Feeds', 'Stone Energy Agroprocessing Division', 'in_stock', 1, 'published');

-- ---------------------------------------------------------------------
-- 15. Product Images Table
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS `product_images`;
CREATE TABLE `product_images` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `product_id` INT UNSIGNED NOT NULL,
    `image_path` VARCHAR(255) NOT NULL,
    `is_primary` TINYINT(1) NOT NULL DEFAULT 0,
    `sort_order` INT NOT NULL DEFAULT 0,
    CONSTRAINT `fk_pimg_prod` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 16. Project Categories Table
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS `project_categories`;
CREATE TABLE `project_categories` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL UNIQUE,
    `slug` VARCHAR(100) NOT NULL UNIQUE,
    `description` VARCHAR(255) DEFAULT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `project_categories` (`id`, `name`, `slug`, `description`) VALUES
(1, 'Building & Civil Works', 'building-civil-works', 'Commercial, residential, and institutional construction works'),
(2, 'Procurement & Supply', 'procurement-supply', 'Multi-sector materials and industrial supply fulfillment'),
(3, 'Healthcare Infrastructure', 'healthcare-infrastructure', 'Medical facility equipment fit-outs and clinical supplies'),
(4, 'Agro & Industrial Logistics', 'agro-industrial-logistics', 'Agroprocessing plant support and commodity haulage');

-- ---------------------------------------------------------------------
-- 17. Projects Table
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS `projects`;
CREATE TABLE `projects` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `title` VARCHAR(200) NOT NULL,
    `slug` VARCHAR(200) NOT NULL UNIQUE,
    `category_id` INT UNSIGNED NOT NULL,
    `location` VARCHAR(150) NOT NULL,
    `client` VARCHAR(150) NOT NULL DEFAULT '[ADD CLIENT NAME]',
    `description` LONGTEXT NOT NULL,
    `scope` TEXT NOT NULL,
    `start_date` DATE DEFAULT NULL,
    `completion_date` DATE DEFAULT NULL,
    `status` ENUM('Upcoming', 'Ongoing', 'Completed') NOT NULL DEFAULT 'Upcoming',
    `featured_image` VARCHAR(255) DEFAULT NULL,
    `published_status` ENUM('published', 'draft') NOT NULL DEFAULT 'published',
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_proj_cat` (`category_id`),
    INDEX `idx_proj_status` (`status`),
    INDEX `idx_proj_pub` (`published_status`),
    CONSTRAINT `fk_proj_cat` FOREIGN KEY (`category_id`) REFERENCES `project_categories` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Clean editable placeholder projects with zero fake claims:
INSERT INTO `projects` (`id`, `title`, `slug`, `category_id`, `location`, `client`, `description`, `scope`, `status`, `published_status`) VALUES
(1, 'Commercial Office Complex Renovation & Facility Upgrade', 'commercial-office-complex-renovation', 1, 'Ibadan, Oyo State', '[ADD CLIENT NAME]', '<p>Comprehensive architectural remodeling, interior civil partitioning, electrical upgrading, and external structural weatherproofing for a commercial corporate facility in Ibadan.</p>', 'Structural inspection & surface preparation||Interior partitioning and acoustic ceiling installation||Civil masonry and structural reinforcement||Electrical system upgrade & finishing', 'Completed', 'published'),
(2, 'Institutional Healthcare Ward Equipment Procurement', 'institutional-healthcare-ward-equipment-procurement', 3, 'Oyo State, Nigeria', '[ADD CLIENT NAME]', '<p>Turnkey sourcing, inspection, logistics, and on-site positioning of clinical ward beds, examination equipment, and baseline diagnostic kits for a regional medical center.</p>', 'Sourcing and quality certification verification||Safe freight transit and packaging||Ward placement and mechanical assembly||Handover documentation and spare parts provision', 'Ongoing', 'published'),
(3, 'Multi-Sector Industrial Piping & Valve Supply Package', 'multi-sector-industrial-piping-valve-supply', 2, 'South-West Nigeria', '[ADD CLIENT NAME]', '<p>Consolidated procurement and scheduled delivery of heavy-gauge carbon steel pipeline fittings, flanged valves, and gasket sets for an industrial manufacturing facility.</p>', 'Specification cross-matching and mill testing checks||Direct factory consignment coordination||Road haulage and offloading coordination||Delivery verification against client engineering drawings', 'Ongoing', 'published'),
(4, 'Integrated Agroprocessing Facility Sourcing & Civil Foundations', 'integrated-agroprocessing-facility-sourcing-civil-foundations', 4, 'Oyo State, Nigeria', '[ADD CLIENT NAME]', '<p>Preliminary earthworks, concrete machine foundations, and equipment sourcing for a commercial grain processing and storage installation.</p>', 'Site surveying and soil assessment coordination||Mass concrete machinery mounting pads||Power transmission ducting and civil channels||Equipment sourcing and logistics timeline management', 'Upcoming', 'published');

-- ---------------------------------------------------------------------
-- 18. Project Images Table
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS `project_images`;
CREATE TABLE `project_images` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `project_id` INT UNSIGNED NOT NULL,
    `image_path` VARCHAR(255) NOT NULL,
    `caption` VARCHAR(255) DEFAULT NULL,
    `sort_order` INT NOT NULL DEFAULT 0,
    CONSTRAINT `fk_pimg2_proj` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 19. Blog Categories Table
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS `blog_categories`;
CREATE TABLE `blog_categories` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL UNIQUE,
    `slug` VARCHAR(100) NOT NULL UNIQUE,
    `description` VARCHAR(255) DEFAULT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `blog_categories` (`id`, `name`, `slug`, `description`) VALUES
(1, 'General Contracting', 'general-contracting', 'Insights on construction management, engineering standards, and project execution'),
(2, 'Procurement & Supply Chain', 'procurement-supply-chain', 'Best practices for multi-sector industrial procurement and vendor management'),
(3, 'Healthcare Supplies', 'healthcare-supplies', 'Updates on medical equipment standards, hospital fittings, and consumables'),
(4, 'Agribusiness & Processing', 'agribusiness-processing', 'Modern agricultural supply chains and value addition technologies in Nigeria');

-- ---------------------------------------------------------------------
-- 20. Blog Posts Table
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS `blog_posts`;
CREATE TABLE `blog_posts` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `title` VARCHAR(255) NOT NULL,
    `slug` VARCHAR(255) NOT NULL UNIQUE,
    `excerpt` VARCHAR(400) NOT NULL,
    `content` LONGTEXT NOT NULL,
    `featured_image` VARCHAR(255) DEFAULT NULL,
    `category_id` INT UNSIGNED NOT NULL,
    `author_id` INT UNSIGNED NOT NULL,
    `tags` VARCHAR(255) DEFAULT NULL,
    `seo_title` VARCHAR(255) DEFAULT NULL,
    `seo_description` VARCHAR(300) DEFAULT NULL,
    `status` ENUM('Draft', 'Published', 'Archived') NOT NULL DEFAULT 'Published',
    `published_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_blog_cat` (`category_id`),
    INDEX `idx_blog_author` (`author_id`),
    INDEX `idx_blog_status` (`status`),
    INDEX `idx_blog_pubdate` (`published_at`),
    CONSTRAINT `fk_blog_cat` FOREIGN KEY (`category_id`) REFERENCES `blog_categories` (`id`) ON DELETE RESTRICT,
    CONSTRAINT `fk_blog_author` FOREIGN KEY (`author_id`) REFERENCES `users` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `blog_posts` (`id`, `title`, `slug`, `excerpt`, `content`, `category_id`, `author_id`, `tags`, `seo_title`, `seo_description`, `status`) VALUES
(1, 'Key Considerations When Selecting a General Contractor in Ibadan and Oyo State', 'key-considerations-general-contractor-ibadan', 'Choosing the right general contractor determines the financial efficiency, structural integrity, and timeline success of commercial and civil building projects.', '<p>In Nigeria\'s evolving construction sector, successful building execution requires more than just laborers on site; it demands rigorous general contracting coordination, transparent bill of quantities management, and strict quality control on materials.</p><p>When planning a commercial building or industrial project in Ibadan or across Oyo State, project owners should prioritize contractors with proven supply chain linkages, hands-on site coordination, and verified procurement channels for cement, aggregates, and reinforcing steel.</p>', 1, 1, 'Construction, Contracting, Ibadan, Civil Engineering', 'Choosing a General Contractor in Ibadan | Stone Energy Int\'l Ltd', 'Essential guidelines for selecting dependable general contractors in Ibadan and Oyo State for commercial building projects.', 'Published'),
(2, 'Streamlining Healthcare Facility Procurement in Nigeria', 'streamlining-healthcare-facility-procurement-nigeria', 'How hospitals, medical centers, and diagnostic clinics can overcome procurement delays and maintain dependable consumable supplies.', '<p>Procuring clinical furniture, diagnostic devices, and sterile medical consumables in Nigeria requires dependable supply partners who understand the criticality of hospital operations.</p><p>By partnering with established procurement specialists, medical administrators minimize equipment downtime, guarantee proper transport packaging, and eliminate unverified middleman markups.</p>', 3, 1, 'Hospital Equipment, Healthcare, Medical Consumables, Procurement', 'Healthcare Facility Equipment Procurement in Nigeria | Stone Energy Int\'l Ltd', 'Best practices for sourcing hospital equipment and medical consumables efficiently across healthcare facilities in Nigeria.', 'Published'),
(3, 'Optimizing Agro-Allied Supply Chains for Agroprocessors in South-West Nigeria', 'optimizing-agro-allied-supply-chains-south-west-nigeria', 'Bridging the logistical gap between primary agricultural clusters and agroprocessing factories through coordinated procurement.', '<p>Agroprocessors frequently face challenges with variable moisture levels, transportation bottlenecks, and seasonal commodity price fluctuations. An organized agro-allied supply contractor acts as a crucial buffer by consolidating farm clusters, conducting preliminary quality checks, and coordinating timely freight delivery to processing plants.</p>', 4, 1, 'Agro-Allied, Agroprocessing, Agribusiness, Nigeria Logistics', 'Agro-Allied Supply Chains in Nigeria | Stone Energy Int\'l Ltd', 'How structured procurement and logistics empower agroprocessors in South-West Nigeria to maintain continuous production.', 'Published');

-- ---------------------------------------------------------------------
-- 21. Blog Tags Table
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS `blog_tags`;
CREATE TABLE `blog_tags` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(50) NOT NULL UNIQUE,
    `slug` VARCHAR(50) NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `blog_tags` (`name`, `slug`) VALUES
('Construction', 'construction'),
('Procurement', 'procurement'),
('Healthcare', 'healthcare'),
('Agribusiness', 'agribusiness'),
('Ibadan', 'ibadan');

-- ---------------------------------------------------------------------
-- 22. Blog Post Tags Mapping
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS `blog_post_tags`;
CREATE TABLE `blog_post_tags` (
    `post_id` INT UNSIGNED NOT NULL,
    `tag_id` INT UNSIGNED NOT NULL,
    PRIMARY KEY (`post_id`, `tag_id`),
    CONSTRAINT `fk_bpt_post` FOREIGN KEY (`post_id`) REFERENCES `blog_posts` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_bpt_tag` FOREIGN KEY (`tag_id`) REFERENCES `blog_tags` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 23. Media Library Table
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS `media`;
CREATE TABLE `media` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `filename` VARCHAR(255) NOT NULL,
    `original_filename` VARCHAR(255) NOT NULL,
    `mime_type` VARCHAR(100) NOT NULL,
    `file_size` BIGINT UNSIGNED NOT NULL,
    `file_path` VARCHAR(255) NOT NULL,
    `alt_text` VARCHAR(255) DEFAULT '',
    `uploaded_by` INT UNSIGNED DEFAULT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_media_mime` (`mime_type`),
    INDEX `idx_media_created` (`created_at`),
    CONSTRAINT `fk_media_user` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 24. Request for Quotation (RFQ) Table
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS `rfqs`;
CREATE TABLE `rfqs` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `rfq_number` VARCHAR(30) NOT NULL UNIQUE,
    `customer_name` VARCHAR(150) NOT NULL,
    `company_name` VARCHAR(150) DEFAULT NULL,
    `email` VARCHAR(120) NOT NULL,
    `phone` VARCHAR(30) NOT NULL,
    `industry` VARCHAR(100) NOT NULL,
    `service_or_product` VARCHAR(200) NOT NULL,
    `quantity` VARCHAR(100) DEFAULT NULL,
    `project_location` VARCHAR(200) DEFAULT NULL,
    `required_delivery_date` DATE DEFAULT NULL,
    `project_description` LONGTEXT NOT NULL,
    `attachment_path` VARCHAR(255) DEFAULT NULL,
    `additional_notes` TEXT DEFAULT NULL,
    `status` ENUM('NEW', 'UNDER REVIEW', 'QUOTATION PREPARED', 'SENT', 'NEGOTIATION', 'APPROVED', 'COMPLETED', 'CANCELLED') NOT NULL DEFAULT 'NEW',
    `admin_notes` TEXT DEFAULT NULL,
    `ip_address` VARCHAR(45) DEFAULT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_rfq_num` (`rfq_number`),
    INDEX `idx_rfq_status` (`status`),
    INDEX `idx_rfq_email` (`email`),
    INDEX `idx_rfq_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 25. RFQ Items Table (for multi-item RFQ requests)
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS `rfq_items`;
CREATE TABLE `rfq_items` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `rfq_id` INT UNSIGNED NOT NULL,
    `item_name` VARCHAR(200) NOT NULL,
    `specifications` TEXT DEFAULT NULL,
    `quantity` VARCHAR(50) DEFAULT NULL,
    `unit` VARCHAR(50) DEFAULT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_rfq_items` FOREIGN KEY (`rfq_id`) REFERENCES `rfqs` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 26. Contact Messages Table
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS `contact_messages`;
CREATE TABLE `contact_messages` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `email` VARCHAR(120) NOT NULL,
    `phone` VARCHAR(30) DEFAULT NULL,
    `subject` VARCHAR(200) NOT NULL,
    `message` TEXT NOT NULL,
    `is_read` TINYINT(1) NOT NULL DEFAULT 0,
    `is_archived` TINYINT(1) NOT NULL DEFAULT 0,
    `ip_address` VARCHAR(45) DEFAULT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_msg_read` (`is_read`),
    INDEX `idx_msg_archived` (`is_archived`),
    INDEX `idx_msg_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 27. SEO Metadata Table
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS `seo_metadata`;
CREATE TABLE `seo_metadata` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `entity_type` VARCHAR(50) NOT NULL,
    `entity_id` INT UNSIGNED NOT NULL,
    `meta_title` VARCHAR(255) DEFAULT NULL,
    `meta_description` VARCHAR(300) DEFAULT NULL,
    `keywords` VARCHAR(255) DEFAULT NULL,
    `canonical_url` VARCHAR(255) DEFAULT NULL,
    `og_image` VARCHAR(255) DEFAULT NULL,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `uk_seo_entity` (`entity_type`, `entity_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 28. Social Links Table
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS `social_links`;
CREATE TABLE `social_links` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `platform` VARCHAR(50) NOT NULL,
    `url` VARCHAR(255) NOT NULL,
    `icon` VARCHAR(50) NOT NULL,
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `sort_order` INT NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `social_links` (`platform`, `url`, `icon`, `is_active`, `sort_order`) VALUES
('LinkedIn', '#', 'linkedin', 1, 1),
('Facebook', '#', 'facebook', 1, 2),
('Twitter', '#', 'twitter', 1, 3),
('Instagram', '#', 'instagram', 1, 4),
('YouTube', '#', 'youtube', 0, 5);

-- ---------------------------------------------------------------------
-- 29. Team Members Table (Editable Placeholders)
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS `team_members`;
CREATE TABLE `team_members` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL DEFAULT '[ADD TEAM MEMBER NAME]',
    `position` VARCHAR(100) NOT NULL DEFAULT '[ADD POSITION / TITLE]',
    `bio` TEXT DEFAULT NULL,
    `image` VARCHAR(255) DEFAULT NULL,
    `sort_order` INT NOT NULL DEFAULT 0,
    `status` ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 30. Testimonials Table (Editable Placeholders)
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS `testimonials`;
CREATE TABLE `testimonials` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `client_name` VARCHAR(100) NOT NULL DEFAULT '[ADD CLIENT NAME]',
    `organization` VARCHAR(150) NOT NULL DEFAULT '[ADD ORGANIZATION NAME]',
    `content` TEXT NOT NULL,
    `rating` TINYINT UNSIGNED NOT NULL DEFAULT 5,
    `status` ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
    `sort_order` INT NOT NULL DEFAULT 0,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 31. FAQs Table
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS `faqs`;
CREATE TABLE `faqs` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `question` VARCHAR(255) NOT NULL,
    `answer` TEXT NOT NULL,
    `category` VARCHAR(50) NOT NULL DEFAULT 'General',
    `sort_order` INT NOT NULL DEFAULT 0,
    `status` ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `faqs` (`question`, `answer`, `category`, `sort_order`, `status`) VALUES
('What sectors does STONE ENERGY INT\'L LTD operate in?', 'We operate as a multi-sector general contractor and supply company covering six principal areas: Oil & Gas Supply, Building Construction, Hospital Equipment & Consumables, Agro-Allied supplies, Agroprocessing support, and General Contracting.', 'Services', 1, 'active'),
('How does the Request for Quotation (RFQ) process work?', 'You can submit your project or procurement requirements online through our quote page. We assign a unique RFQ tracking number (e.g., RFQ-2026-000001), our technical procurement team reviews the specifications, and we issue a formal commercial proposal within 24 to 48 business hours.', 'Procurement', 2, 'active'),
('Where is your corporate headquarters located?', 'Our office is located at 22, Oyelude Layout, Aba Alfa, Ojo, Ibadan, Oyo State, Nigeria. We serve clients across South-West Nigeria and nationwide.', 'General', 3, 'active'),
('Can STONE ENERGY INT\'L LTD handle custom procurement and equipment sourcing?', 'Yes. Through our general contracting and supply chain networks, we source certified industrial materials, construction rebars, specialized medical devices, and agroprocessing machinery to client specifications.', 'Procurement', 4, 'active'),
('How do I speak directly with your procurement team?', 'You can call our hotlines at 08037745881 or 08084949840, reach us via WhatsApp using the on-screen button, or submit our contact form.', 'Contact', 5, 'active');

SET FOREIGN_KEY_CHECKS = 1;
