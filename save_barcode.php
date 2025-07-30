<?php
/* हमेशा JSON ही भेजें */
header('Content-Type: application/json');
error_reporting(0);        // PHP Warning भी न दिखे

/* ----- POST वैल्यू पढ़ो (जितना चाहिए उतना) ----- */
$product = isset($_POST['product_name']) ? trim($_POST['product_name']) : 'UNKNOWN';

/* ----- अपने हिसाब से कोई यूनिक कोड बना लो  ----- */
/* यहां example के लिये  3‑letter slug + time() ले रहे हैं */
$slug   = strtoupper(substr(preg_replace('/[^A-Za-z0-9]/','',$product),0,3)); // e.g. TUR
$unique = $slug . time();                                                     // e.g. TUR1720425526

/* ----- फ्रंट‑एंड के लिये साफ JSON ----- */
echo json_encode([
    'success'        => true,
    'barcode_string' => $unique          // यही JsBarcode को जाएगा
]);
?>
