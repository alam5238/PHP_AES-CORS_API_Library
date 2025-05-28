<?php
    include 'singletone.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Page</title>
</head>
<body>
    

    <h2><?php echo fetch_it("http://nazmulalamshuvo.42web.io/dsell/restapi.php"); ?></h2>
</body>
</html>