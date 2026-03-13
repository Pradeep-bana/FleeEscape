<?php
session_start();

$response = [
    "promo_code"        => "",
       "promo_code_cart"        => "",
    "promotion_page"    => "",
     "giftCode"        => "",
    "addons"            => []
];

if (!empty($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $item) {

        if (!empty($item['promo_code'])) {
            $response["promo_code"] = $item['promo_code'];
        }
          if (!empty($item['promo_code_cart'])) {
            $response["promo_code_cart"] = $item['promo_code_cart'];
        }


        if (!empty($item['pramotion_page']) && $item['pramotion_page'] == "true") {
            $response["promotion_page"] = "true";
        }

        if (!empty($item['addons']) && is_array($item['addons'])) {
            $response["addons"] = $item['addons'];
        }
        
      

    }
    
}

 if (!empty($_SESSION['giftCode']) && is_array($_SESSION['giftCode'])) {
            $response["giftCode"] = $_SESSION['giftCode'] ;
           
         
        }

echo json_encode($response);
