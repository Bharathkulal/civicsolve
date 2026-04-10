<?php
function getDepartment($category) {
    $map = [
        'road' => 'road',
        'garbage' => 'garbage',
        'water' => 'water',
        'electricity' => 'electricity'
    ];
    return $map[$category] ?? 'road';
}
?>