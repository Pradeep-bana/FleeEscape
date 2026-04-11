<?php
include 'config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>404 - Page Not Found</title>

<link rel="icon" href="<?= BASE_URL ?>assets/images/icon.png" type="image/gif" sizes="16x16">

<style>
    body {
        margin: 0;
        background: #010101;
        color: #ffffff;
        display: flex;
        flex-direction: column;
        min-height: 100vh;
    }

    header {
        padding: 18px 20px;
        background: #111;
        text-align: center;
        font-size: 22px;
        font-weight: bold;
        border-bottom: 1px solid #222;
        letter-spacing: 1px;
        color: #00d4ff;
    }

    .container {
        flex: 1;
        display: flex;
        flex-direction: column;
        justify-content: center;
        text-align: center;
        padding: 00px;
        padding-top: 0;
    }

    .container img {
        max-width: 280px;
        width: 90%;
        margin: 0 auto 10px;
        display: block;
        
    }

    p {
        font-size: 16px;
        margin-bottom: 10px;
        color: #cccccc;
        line-height: 1.6;
        color : #aaa;
    }

   a.home-link {
    color: #00d4ff;
    font-weight: bold;
    font-size: 18px;
    text-decoration: none;
    padding: 10px 20px;
    border: 2px solid #00d4ff;
    border-radius: 6px;
    transition: 0.3s;
    display: inline-block;
    margin-top: 10px;
    width: max-content;
    margin: 0 auto;
}

    a.home-link:hover {
        background: #00d4ff;
        color: #000;
    }

    footer {
        padding: 10px;
        /*background: #111;*/
        color: #777;
        text-align: center;
        /*border-top: 1px solid #222;*/
        font-size: 14px;
    }

    @media (max-width: 600px) {
        h1 {
            font-size: 42px;
        }
        p, a.home-link {
            font-size: 16px;
        }
    }
</style>

</head>
<body>

<header>
    Flee Escape 
</header>
<div class="container">
    <img src="https://img.freepik.com/free-vector/glitch-error-404-page_23-2148105404.jpg?semt=ais_se_enriched&w=740&q=80" 
         alt="404 Error Image">
    <p>
        The page you're looking for doesn't exist.<br>
        You can always start from our home page below.
    </p>
    <a class="home-link" href="<?= BASE_URL ?>">Go To Home Page</a>
</div>

<footer>
    © 2025 Flee Escape. All Rights Reserved.
</footer>

</body>
</html>
