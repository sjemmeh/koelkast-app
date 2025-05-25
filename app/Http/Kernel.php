// In app/Http/Kernel.php
protected $middleware = [
    // \App\Http\Middleware\TrustHosts::class, // In nieuwere Laravel versies
    \App\Http\Middleware\TrustProxies::class,   // <<-- DEZE MOET ER STAAN
    // ... andere globale middleware ...
    \Illuminate\Http\Middleware\HandleCors::class,
    \App\Http\Middleware\PreventRequestsDuringMaintenance::class,
    \Illuminate\Foundation\Http\Middleware\ValidatePostSize::class,
    \App\Http\Middleware\TrimStrings::class,
    \Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull::class,
];