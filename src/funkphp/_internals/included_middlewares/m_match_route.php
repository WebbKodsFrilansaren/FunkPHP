<?php return function (&$c) {
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
