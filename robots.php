<?php
/**
 * STONE ENERGY INT'L LTD - Dynamic robots.txt Generator
 */
require_once __DIR__ . '/config/config.php';

header('Content-Type: text/plain; charset=utf-8');

$sitemapUrl = rtrim(BASE_URL, '/') . '/sitemap.xml';
?>
User-agent: *
Allow: /
Disallow: /admin/
Disallow: /config/
Disallow: /core/
Disallow: /logs/
Disallow: /database/
Disallow: /install.php

Sitemap: <?= $sitemapUrl ?>
