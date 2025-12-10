<?php
try {
    require __DIR__.'/../vendor/autoload.php';
    $app = require_once __DIR__.'/../bootstrap/app.php';
    $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
    $kernel->bootstrap();

    echo "Attempting to instantiate User model...\n";
    $user = new App\Models\User();
    echo "User model instantiated successfully.\n";

    echo "Attempting to instantiate AuthController...\n";
    $controller = new App\Http\Controllers\AuthController();
    echo "AuthController instantiated successfully.\n";

} catch (\Throwable $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
}
