<?php
/**
 * Migration script — Run once to add new columns to the complaints table.
 * Access via browser: http://localhost/CIVICSOLVE/database/migrate.php
 */
require_once __DIR__ . '/../backend/config/db.php';

$queries = [
    "ALTER TABLE complaints ADD COLUMN IF NOT EXISTS image_path VARCHAR(500) DEFAULT NULL AFTER description",
    "ALTER TABLE complaints ADD COLUMN IF NOT EXISTS latitude DECIMAL(10,6) DEFAULT NULL AFTER image_path",
    "ALTER TABLE complaints ADD COLUMN IF NOT EXISTS longitude DECIMAL(10,6) DEFAULT NULL AFTER latitude",
    "ALTER TABLE complaints ADD COLUMN IF NOT EXISTS address VARCHAR(500) DEFAULT NULL AFTER longitude",
    "ALTER TABLE users ADD COLUMN IF NOT EXISTS phone VARCHAR(20) DEFAULT NULL AFTER department",
    "ALTER TABLE users ADD COLUMN IF NOT EXISTS address VARCHAR(500) DEFAULT NULL AFTER phone",
    "ALTER TABLE users ADD COLUMN IF NOT EXISTS profile_image VARCHAR(500) DEFAULT NULL AFTER address"
];

echo "<h2>CivicSolve — Database Migration</h2>";
echo "<pre>";

foreach ($queries as $q) {
    if ($conn->query($q)) {
        echo "✅ OK: " . htmlspecialchars($q) . "\n";
    } else {
        // Column may already exist, that's fine
        echo "ℹ️  Skipped (may already exist): " . htmlspecialchars($q) . " — " . $conn->error . "\n";
    }
}

echo "\n🎉 Migration complete!\n";
echo "</pre>";
echo '<p><a href="../user/home.php">← Go to Dashboard</a></p>';
?>
