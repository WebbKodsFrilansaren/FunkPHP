<?php return function (&$c) {
    $c['ROUTES'] = [];
    if (!is_readable(dirname(__DIR__) . '/config/routesa.php')) {
        $c['err']['ROUTES'][] = "Routes in File `funkphp/config/routes.php` not found or non-readable!";
        critical_err_json_or_html(500, "Routes File Not Found OR it is not Readable/Writable!");
    } elseif (!is_readable(dirname(__DIR__) . '/_internals/compiled/troute_route.php')) {
        $c['err']['ROUTES'][] = "Compiled Routes in File `funkphp/_internals/compiled/troute_route.php` not found or non-readable!";
        critical_err_json_or_html(500, "Compiled Routes File Not Found OR it is not Readable/Writable!");
    } else {
        $c['ROUTES'] = [
            'COMPILED' => include dirname(__DIR__) . '/_internals/compiled/troute_route.php',
            'SINGLES' => include dirname(__DIR__) . '/config/routes.php',
        ];
    }
    if (
        empty($c['ROUTES'])
        || !isset($c['ROUTES']['COMPILED'])
        || empty($c['ROUTES']['COMPILED'])
    ) {
        $c['err']['ROUTES'][] = "Compiled Routes in File `funkphp/_internals/compiled/troute_route.php` seems empty, please check!";
        critical_err_json_or_html(500, "Compiled Routes File loaded but is Empty OR not properly formatted?!");
    }
    if (
        empty($c['ROUTES'])
        || !isset($c['ROUTES']['SINGLES'])
        || !is_array($c['ROUTES']['SINGLES'])
        || empty($c['ROUTES']['SINGLES'])
        || !isset($c['ROUTES']['SINGLES']['ROUTES'])
        || !is_array($c['ROUTES']['SINGLES']['ROUTES'])
        || empty($c['ROUTES']['SINGLES']['ROUTES'])
    ) {
        $c['err']['ROUTES'][] = "Routes in File `funkphp/config/routes.php` seems empty, please check!";
        critical_err_json_or_html(500, "Routes File loaded but is Empty OR not properly formatted?!");
    }
    $FPHP_MATCHED_ROUTE = funk_match_developer_route(
        $c['req']['method'],
        $c['req']['uri'],
        $c['ROUTES']['COMPILED'] ?? [],
        $c['ROUTES']['SINGLES']['ROUTES'] ?? [],
        $c['ROUTES']['SINGLES']['ROUTES'] ?? [],
    );
    $c['req']['matched_method'] = $c['req']['method'];
    $c['req']['matched_route'] = $FPHP_MATCHED_ROUTE['route'] ?? null;
    $c['req']['matched_handler'] = $FPHP_MATCHED_ROUTE['handler'] ?? null;
    $c['req']['matched_data'] = $FPHP_MATCHED_ROUTE['data'] ?? null;
    $c['req']['matched_page'] = $FPHP_MATCHED_ROUTE['page'] ?? null;
    $c['req']['matched_params'] = $FPHP_MATCHED_ROUTE['params'] ?? null;
    $c['req']['matched_middlewares'] = $FPHP_MATCHED_ROUTE['middlewares'] ?? null;
    $c['req']['no_matched_in'] = $FPHP_MATCHED_ROUTE['no_match_in'] ?? null;
    unset($FPHP_MATCHED_ROUTE);
};
