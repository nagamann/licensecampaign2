<?php

# 注文番号を取得、incrementしたい場合はtrueを指定すること
function number($increment = false) {
    $save_number_filepath = "./_number";
    $order_number = "";

    $rfp = fopen($save_number_filepath, "r");
    while ($line = fgets($rfp)) {
        $order_number = $line;
    }
    fclose($rfp);

    if ($increment) {
        $sfp = fopen($save_number_filepath, "w");
        fprintf($sfp, "%011d", $order_number + 1);
        fclose($sfp);
    }

    return $order_number;
}

?>
