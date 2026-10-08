<?php
/**
 * STONE ENERGY INT'L LTD - 500 Internal Server Error Page
 */
http_response_code(500);
$companyName = "STONE ENERGY INT'L LTD";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>500 - Server Encountered an Error | <?= htmlspecialchars($companyName, ENT_QUOTES, 'UTF-8') ?></title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; background: #0a192f; color: #f8fafc; margin: 0; padding: 40px 20px; display: flex; align-items: center; justify-content: center; min-height: 100vh; text-align: center; }
        .box { max-width: 560px; background: #112240; padding: 48px; border-radius: 12px; border: 1px solid rgba(255,255,255,0.1); box-shadow: 0 20px 25px -5px rgba(0,0,0,0.4); }
        .tag { display: inline-block; background: rgba(220,38,38,0.2); color: #f87171; border: 1px solid rgba(220,38,38,0.4); padding: 4px 12px; border-radius: 9999px; font-size: 0.75rem; font-weight: 700; letter-spacing: 1px; text-transform: uppercase; margin-bottom: 16px; }
        h1 { font-size: 2.2rem; margin: 0 0 16px 0; color: #fff; }
        p { color: #94a3b8; font-size: 1rem; line-height: 1.6; margin-bottom: 30px; }
        .btn { display: inline-block; background: #d97706; color: #fff; text-decoration: none; padding: 12px 28px; border-radius: 6px; font-weight: 700; font-size: 0.95rem; }
        .btn:hover { background: #b45309; }
    </style>
</head>
<body>
    <div class="box">
        <span class="tag">INTERNAL SERVER ERROR</span>
        <h1>Service Temporarily Unavailable</h1>
        <p>Our server encountered a temporary technical condition while processing this request. Our engineering team has been notified via internal logging. Please retry shortly.</p>
        <a href="./" class="btn">Return to Homepage</a>
    </div>
</body>
</html>
