<?php

if (!function_exists('flee_get_cached_products_data')) {
    function flee_get_cached_products_data($pdo)
    {
        $stmt = $pdo->prepare("SELECT product_data FROM bookeo_products_cache WHERE id = 1 LIMIT 1");
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            return [];
        }

        $productsData = json_decode($row['product_data'], true);
        if (!isset($productsData['data']) || !is_array($productsData['data'])) {
            return [];
        }

        return $productsData['data'];
    }
}

if (!function_exists('flee_find_cached_product')) {
    function flee_find_cached_product(array $products, $productCode)
    {
        $target = trim((string)$productCode);
        foreach ($products as $product) {
            if (trim((string)($product['productCode'] ?? '')) === $target) {
                return $product;
            }
        }

        return null;
    }
}

if (!function_exists('flee_parse_money_value')) {
    function flee_parse_money_value($value)
    {
        if (is_numeric($value)) {
            return (float)$value;
        }

        if (is_string($value) && preg_match('/-?\d+(?:\.\d+)?/', $value, $match)) {
            return (float)$match[0];
        }

        return 0.0;
    }
}

if (!function_exists('flee_is_weekend_slot')) {
    function flee_is_weekend_slot($slot)
    {
        $slot = trim((string)$slot);
        if ($slot === '') {
            return null;
        }

        try {
            $date = new DateTime($slot);
        } catch (Exception $e) {
            return null;
        }

        $dayOfWeek = (int)$date->format('N');
        $hour = (int)$date->format('G');

        if ($dayOfWeek >= 5) {
            return true;
        }

        return false;
    }
}

if (!function_exists('flee_extract_price_from_text')) {
    function flee_extract_price_from_text($text, $slot = '')
    {
        $text = html_entity_decode(strip_tags((string)$text), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = preg_replace('/\s+/', ' ', $text);
        $text = trim($text);

        if ($text === '') {
            return 0.0;
        }

        $isWeekend = flee_is_weekend_slot($slot);

        if ($isWeekend === false && preg_match('/\$([0-9]+(?:\.[0-9]{1,2})?)\s*\/?\s*(?:person|player|hr|hour)?[^$]{0,60}weekdays?/i', $text, $match)) {
            return (float)$match[1];
        }

        if ($isWeekend === true && preg_match('/\$([0-9]+(?:\.[0-9]{1,2})?)\s*\/?\s*(?:person|player|hr|hour)?[^$]{0,60}weekends?/i', $text, $match)) {
            return (float)$match[1];
        }

        if (preg_match('/\$([0-9]+(?:\.[0-9]{1,2})?)/', $text, $match)) {
            return (float)$match[1];
        }

        return 0.0;
    }
}

if (!function_exists('flee_get_bookeo_option_price')) {
    function flee_get_bookeo_option_price(array $product, $optId, $slot = '')
    {
        $target = trim((string)$optId);
        if ($target === '') {
            return 0.0;
        }

        foreach (['numberOptions', 'onOffOptions'] as $optionKey) {
            if (empty($product[$optionKey]) || !is_array($product[$optionKey])) {
                continue;
            }

            foreach ($product[$optionKey] as $opt) {
                if (trim((string)($opt['id'] ?? '')) !== $target) {
                    continue;
                }

                $candidatePaths = [
                    $opt['price']['amount'] ?? null,
                    $opt['defaultRate']['price']['amount'] ?? null,
                    $opt['defaultPrice']['amount'] ?? null,
                    $opt['amount'] ?? null,
                    $opt['defaultValue'] ?? null,
                ];

                foreach ($candidatePaths as $candidate) {
                    $price = flee_parse_money_value($candidate);
                    if ($price > 0) {
                        return $price;
                    }
                }

                $priceFromText = flee_extract_price_from_text(($opt['description'] ?? '') . ' ' . ($opt['name'] ?? ''), $slot);
                if ($priceFromText > 0) {
                    return $priceFromText;
                }

                return 0.0;
            }
        }

        return 0.0;
    }
}
