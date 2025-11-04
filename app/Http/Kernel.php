// ...
    protected $middlewareAliases = [
        // ... (auth, guest などはそのまま) ...

        // --- API用とWeb用の両方で、同じMiddlewareを使うようにする ---
        'admin' => \App\Http\Middleware\AdminMiddleware::class,
        
        // ★★★ この行を修正 ★★★
        'admin.web' => \App\Http\Middleware\AdminMiddleware::class, 
        // (EnsureUserIsAdmin::class ではない)
    ];
}