<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Info PHP Server</title>
    <style>
        body {
            font-family: sans-serif;
            padding: 2em;
            line-height: 1.6;
        }

        .container {
            border: 1px solid #ddd;
            padding: 1em;
        }

        strong {
            color: #333;
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>Info PHP dari Server Apache</h1>
        <p><strong>Versi PHP:</strong> <?php echo phpversion(); ?></p>
        <p><strong>File Konfigurasi (php.ini) yang Dimuat:</strong> <?php echo php_ini_loaded_file(); ?></p>
        <p><strong>Server API:</strong> <?php echo php_sapi_name(); ?></p>
    </div>
</body>

</html>