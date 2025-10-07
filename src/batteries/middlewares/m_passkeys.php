<?php

// 100 % CREDITS TO: https://github.com/web-auth/cose-lib as this Middleware is
// like 99 % based upon! 100 % of the technical part is from them. I am just trying
// to understand whatever I am using here to the best of my ability and
// implement it in FunkPHP as a Middleware. So all credits to them!

// First created 2025-10-06 22:53
// TODO: Still WIP!!! Might be deleted due to complexity and skill issues from my side!
namespace FunkPHP\Middlewares\m_passkeys;

return function (&$c, $passedValue = null) {

    // List of COSE Algorithms
    $COSE_HASH_ALGS = [
        'sha1' => [
            'name' => 'sha1',
            'length' => 20,
            'prefix' => "\x30\x21\x30\x09\x06\x05\x2b\x0e\x03\x02\x1a\x05\x00\x04\x14"
        ],
        'sha256' => [
            'name' => 'sha256',
            'length' => 32,
            'prefix' => "\x30\x31\x30\x0d\x06\x09\x60\x86\x48\x01\x65\x03\x04\x02\x01\x05\x00\x04\x20"
        ],
        'sha384' => [
            'name' => 'sha384',
            'length' => 48,
            'prefix' => "\x30\x41\x30\x0d\x06\x09\x60\x86\x48\x01\x65\x03\x04\x02\x02\x05\x00\x04\x30"
        ],
        'sha512' => [
            'name' => 'sha512',
            'length' => 64,
            'prefix' => "\x30\x51\x30\x0d\x06\x09\x60\x86\x48\x01\x65\x03\x04\x02\x03\x05\x00\x04\x40"
        ],
    ];

    // List of COSE numeric algorithms mapped to hash algorithms name
    $COSE_HASH_MAP =
        [
            -7 => $COSE_HASH_ALGS['sha256'],   // ES256
            -35 => $COSE_HASH_ALGS['sha384'],  // ES384
            -36 => $COSE_HASH_ALGS['sha512'],  // ES512
            -257 => $COSE_HASH_ALGS['sha256'], // RS256
            -258 => $COSE_HASH_ALGS['sha384'], // RS384
            -259 => $COSE_HASH_ALGS['sha512'], // RS512
            -65535 => $COSE_HASH_ALGS['sha1'], // PS256 (RSASSA-PSS using SHA-256 and MGF1 with SHA-256)
            -65536 => $COSE_HASH_ALGS['sha384'], // PS384 (RSASSA-PSS using SHA-384 and MGF1 with SHA-384)
            -65537 => $COSE_HASH_ALGS['sha512'], // PS512 (RSASSA-PSS using SHA-512 and MGF1 with SHA-512)
        ];

    // Retrieve hash algorithm details based on COSE algorithm ID or log error and return null
    $get_hash_alg = function ($coseAlgId) use ($COSE_HASH_ALGS, $COSE_HASH_MAP) {
        if (!isset($coseAlgId) || !is_int($coseAlgId)) {
            $c['err']['MIDDLEWARES']['m_passkeys'][] = 'COSE algorithm ID must be an integer!';
            return null;
        }
        if (!isset($COSE_HASH_MAP[$coseAlgId])) {
            $c['err']['MIDDLEWARES']['m_passkeys'][] = 'Unsupported COSE algorithm ID to match hash against!';
            return null;
        }
        $hashName = $COSE_HASH_MAP[$coseAlgId];
        return $COSE_HASH_ALGS[$hashName];
    };
};
