<?php return function (&$c, $passedValue = null) {
    // `pl_match_route` - Match the Request URI & Method to a Route configured in `funkphp/routes/routes.php`
    // It needs a $passedValue of what "no_match" to do when no route is matched, even though it does not do
    // anything during a match besides setting values for other Pipeline Functions to use that would actually
    // return JSON or a Complied Page File!
    if (
        !isset($passedValue)
        || !isset($passedValue['no_match'])
        || !is_array($passedValue['no_match'])
        || count($passedValue['no_match']) >= 1
    ) {
        $err = 'Tell The Developer: The Pipepline `pl_match_route` Function needs a default configured Page/JSON Response or a Callback Function to call when no route is matched! Either validate correct Route is configured in the Routes `funkphp/routes/routes.php` File and/or add a default Page/JSON Response or Callback Function to call when no Route is matched! IMPORTANT: You only return a Comipled Page File or a JSON response, so any Route Keys of the Route you might actually choose will NOT be ran. It is HIGHLY RECOMMENDED that you configure BOTH a JSON Response and Page so default Internal Error Page is not shown because it was never configured.';
        funk_use_custom_error($c, ['json_or_page', ['json' => ["custom_error" => $err], 'page' => '500'], $err], 500);
    }
    $c['ROUTES'] = [];
    if (!is_readable(ROOT_FOLDER . '/routes/routes.php')) {
        $err = 'Tell The Developer: The Developer Routes in File `funkphp/routes/routes.php` not found or is not readable!';
        funk_use_custom_error($c, ['json_or_page', ['json' => ["custom_error" => $err], 'page' => '500'], $err], 500);
    } elseif (!is_readable(ROOT_FOLDER . '/_internals/compiled/troute_route.php')) {
        $err = 'Tell The Developer: The Compiled Routes in File `funkphp/_internals/compiled/troute_route.php` not found or is not readable!';
        funk_use_custom_error($c, ['json_or_page', ['json' => ["custom_error" => $err], 'page' => '500'], $err], 500);
    } else {
        $c['ROUTES'] = [
            'COMPILED' => include_once ROOT_FOLDER . '/_internals/compiled/troute_route.php',
            'DEVELOPER' => include_once ROOT_FOLDER . '/routes/routes.php',
        ];
    }
    if (
        !isset($c['ROUTES'])
        || !is_array($c['ROUTES'])
        || empty($c['ROUTES'])
        || !isset($c['ROUTES']['COMPILED'])
        || !is_array($c['ROUTES']['COMPILED'])
        || empty($c['ROUTES']['COMPILED'])
    ) {
        $err = 'Tell The Developer: The Compiled Routes in File `funkphp/_internals/compiled/troute_route.php` seems empty, please check!';
        funk_use_custom_error($c, ['json_or_page', ['json' => ["custom_error" => $err], 'page' => '500'], $err], 500);
    }
    if (
        !isset($c['ROUTES']['DEVELOPER'])
        || !is_array($c['ROUTES']['DEVELOPER'])
        || empty($c['ROUTES']['DEVELOPER'])
        || !isset($c['ROUTES']['DEVELOPER']['ROUTES'])
        || !is_array($c['ROUTES']['DEVELOPER']['ROUTES'])
        || empty($c['ROUTES']['DEVELOPER']['ROUTES'])
    ) {
        $err = 'Tell The Developer: The Developer Routes in File `funkphp/routes/routes.php` seems empty, please check!';
        funk_use_custom_error($c, ['json_or_page', ['json' => ["custom_error" => $err], 'page' => '500'], $err], 500);
    }
    // Try match route and if it fails, we check if we should
    $FPHP_MATCHED_ROUTE = funk_match_developer_route(
        $c,
        $c['req']['method'],
        $c['req']['uri'],
        $c['ROUTES']['COMPILED'] ?? [],
        $c['ROUTES']['DEVELOPER']['ROUTES'] ?? [],
    );
    // HM: What to do when NOT MATCHED? Allow for setting default non-found json/page/callback ?
    if (!$FPHP_MATCHED_ROUTE) {
        if (
            !isset($passedValue['no_match'])
            || !is_array($passedValue['no_match'])
            || count($passedValue['no_match']) >= 1
        ) {
            $err = 'Tell The Developer: The Pipepline `pl_match_route` Function needs a default configured Page/JSON Response or a Callback Function to call when no route is matched! Either validate correct Route is configured in the Routes `funkphp/routes/routes.php` File and/or add a default Page/JSON Response or Callback Function to call when no Route is matched! IMPORTANT: You only return a Comipled Page File or a JSON response, so any Route Keys of the Route you might actually choose will NOT be ran. It is HIGHLY RECOMMENDED that you configure BOTH a JSON Response and Page so default Internal Error Page is not shown because it was never configured.';
            funk_use_custom_error($c, ['json_or_page', ['json' => ["custom_error" => $err], 'page' => '404'], $err], 404);
        }
        // Case 1: Only 1 key
        if (count($passedValue['no_match']) === 1) {
        }
    }
    // When matched, data is stored in $c['req'] and it is up to the Developer to do whatever they want with it!
    // Recommended is to first use `pl_run_matched_route_middlewares` to run any matched middlewares and then
    // use the `pl_run_matched_route_keys` to run the matched Route Keys that has been stored after the match!
};
