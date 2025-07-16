<?php return function (&$c) {
    $c['ROUTES'] = [];
    if (!is_readable(ROOT_FOLDER . '/config/routes.php')) {
        $c['err']['ROUTES'][] = "Routes in File `funkphp/config/routes.php` not found or non-readable!";
        critical_err_json_or_html(500, "Routes File Not Found OR it is not Readable/Writable!");
    } elseif (!is_readable(ROOT_FOLDER . '/_internals/compiled/troute_route.php')) {
        $c['err']['ROUTES'][] = "Compiled Routes in File `funkphp/_internals/compiled/troute_route.php` not found or non-readable!";
        critical_err_json_or_html(500, "Compiled Routes File Not Found OR it is not Readable/Writable!");
    } else {
        $c['ROUTES'] = [
            'COMPILED' => include ROOT_FOLDER . '/_internals/compiled/troute_route.php',
            'DEVELOPER' => include ROOT_FOLDER . '/config/routes.php',
        ];
    }
    if (
        empty($c['ROUTES'])
        || !isset($c['ROUTES']['COMPILED'])
        || empty($c['ROUTES']['COMPILED'])
    ) {
        $c['err']['PIPELINE']['REQUEST']['pl_match_route'][] = "Compiled Routes in File `funkphp/_internals/compiled/troute_route.php` seems empty, please check!";
        critical_err_json_or_html(500, "Compiled Routes File loaded but is Empty OR not properly formatted?!");
    }
    if (
        empty($c['ROUTES'])
        || !isset($c['ROUTES']['DEVELOPER'])
        || !is_array($c['ROUTES']['DEVELOPER'])
        || empty($c['ROUTES']['DEVELOPER'])
        || !isset($c['ROUTES']['DEVELOPER']['ROUTES'])
        || !is_array($c['ROUTES']['DEVELOPER']['ROUTES'])
        || empty($c['ROUTES']['DEVELOPER']['ROUTES'])
    ) {
        $c['err']['PIPELINE']['REQUEST']['pl_match_route'][] = "Routes in File `funkphp/config/routes.php` seems empty, please check!";
        critical_err_json_or_html(500, "Routes File loaded but is Empty OR not properly formatted?!");
    }
    $FPHP_MATCHED_ROUTE = funk_match_developer_route(
        $c,
        $c['req']['method'],
        $c['req']['uri'],
        $c['ROUTES']['COMPILED'] ?? [],
        $c['ROUTES']['DEVELOPER']['ROUTES'] ?? [],
        $c['ROUTES']['DEVELOPER']['ROUTES'] ?? [],
    );
};
