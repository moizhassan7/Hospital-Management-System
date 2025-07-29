<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Db Conn</title>
</head>
<body>
    <?php
    use Illuminate\Support\Facades\DB;

    try {
        DB::connection()->getPdo();
        echo "<h2>Database connection successful!</h2>";
    } catch (\Exception $e) {
        echo "<h2>Database connection failed: " . $e->getMessage() . "</h2>";
    }
    ?>
</body>
</html>