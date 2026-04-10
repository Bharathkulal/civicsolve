<?php
function classifyImage($imagePath) {
    $categories = ['road', 'garbage', 'water', 'electricity'];
    $category = $categories[array_rand($categories)];
    
    $priorities = ['high', 'medium', 'low'];
    $priority = $priorities[array_rand($priorities)];
    
    return [
        'category' => $category,
        'priority' => $priority,
        'confidence' => 0.85
    ];
}
?>