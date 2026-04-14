<?php
$file = basename($_GET['file'] ?? '');
$imagePath = '';
$valid = false;
if ($file !== '') {
    $uploadsDir = __DIR__ . '/uploads/issues';
    $candidate = $uploadsDir . '/' . $file;
    if (is_file($candidate)) {
        $imagePath = 'uploads/issues/' . $file;
        $valid = true;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Issue Image - CivicSolve</title>
    <link rel="stylesheet" href="assets/css/theme.css">
    <style>
        body {
            margin: 0;
            font-family: 'Poppins', sans-serif;
            background: #111;
            color: #fff;
        }
        .viewer-shell {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        .viewer-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 1rem 1.5rem;
            background: rgba(0,0,0,0.7);
            position: sticky;
            top: 0;
            z-index: 10;
        }
        .viewer-header h1 {
            margin: 0;
            font-size: 1rem;
            letter-spacing: 0.5px;
        }
        .btn-back {
            color: #fff;
            text-decoration: none;
            font-weight: 600;
            padding: 0.5rem 0.9rem;
            border: 1px solid rgba(255,255,255,0.4);
            border-radius: 999px;
            transition: background 0.2s, border-color 0.2s;
        }
        .btn-back:hover {
            background: rgba(255,255,255,0.08);
            border-color: #fff;
        }
        .viewer-body {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
        }
        .viewer-image {
            max-width: 100%;
            max-height: calc(100vh - 100px);
            border-radius: 12px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.6);
        }
        .viewer-error {
            text-align: center;
            color: #f2f2f2;
        }
        .viewer-error p {
            margin: 0.75rem 0 0;
            color: #ddd;
        }
    </style>
</head>
<body>
    <div class="viewer-shell">
        <header class="viewer-header">
            <a href="javascript:history.back()" class="btn-back">← Back</a>
            <h1>Issue Image Viewer</h1>
            <div></div>
        </header>
        <main class="viewer-body">
            <?php if ($valid): ?>
                <img src="<?php echo htmlspecialchars($imagePath); ?>" alt="Issue image" class="viewer-image">
            <?php else: ?>
                <div class="viewer-error">
                    <h2>Image not found</h2>
                    <p>Either the file doesn't exist or the link is invalid.</p>
                </div>
            <?php endif; ?>
        </main>
    </div>
</body>
</html>
