<?php
function generateSlots($start, $end, $interval = 30) {
    $slots = [];
    $current = strtotime($start);
    $end = strtotime($end);

    while ($current < $end) {
        $slots[] = date("H:i:s", $current);
        $current = strtotime("+{$interval} minutes", $current);
    }
    return $slots;
}
