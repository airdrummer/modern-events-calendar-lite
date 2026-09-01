<?php
/**
 * Release Safety Check.
 *
 * This file lives inside the plugin but is never executed at runtime.
 * It is only run by build/CI before each release to make sure development
 * values have not leaked into production.
 *
 * Usage:
 *   php lic-release-check.php
 *
 * If exit code = 0: everything is OK.
 * If exit code = 1: the release must not ship.
 *
 * @author Webnus <info@webnus.net>
 */

declare(strict_types=1);

// Load the plugin constants without booting the full WordPress stack
$mecFile = __DIR__ . '/mec.php';
$source  = file_get_contents($mecFile);

if ($source === false)
{
    fwrite(STDERR, "FAIL: cannot read mec.php.\n");
    exit(1);
}

$errors   = [];
$warnings = [];

// --- 1. MEC_LICENSE_KEY_ENV must be 'production' ---
if (!preg_match("/define\\(\\s*'MEC_LICENSE_KEY_ENV'\\s*,\\s*'([^']+)'\\s*\\)/", $source, $m))
{
    $errors[] = "MEC_LICENSE_KEY_ENV is not defined.";
}
elseif ($m[1] !== 'production')
{
    $errors[] = "MEC_LICENSE_KEY_ENV = '{$m[1]}' — must be 'production'.";
}

// --- 2. No stale development keys ---
$devKeys = [
    'TyEFE1AOr1ucA1Ht4uETgDOPdWx-Xed2JeeLM9iTXN4',  // pubkey
    // sig and build differ between environments, but the pubkey is constant
];
foreach ($devKeys as $dev)
{
    if (strpos($source, $dev) !== false)
    {
        $errors[] = "Development key still present in mec.php: $dev";
    }
}

// --- 3. No "DO NOT SHIP" warnings should remain ---
if (preg_match('/DO NOT SHIP|THESE ARE DEVELOPMENT VALUES/i', $source))
{
    $errors[] = "Development warning still present in mec.php.";
}

// --- 4. Verify the build signature against the public key ---
if (extension_loaded('sodium'))
{
    if (preg_match("/define\\(\\s*'MEC_LICENSE_PUBKEY'\\s*,\\s*'([^']+)'\\s*\\)/", $source, $pubMatch) &&
        preg_match("/define\\(\\s*'MEC_LICENSE_BUILD'\\s*,\\s*'([^']+)'\\s*\\)/", $source, $buildMatch) &&
        preg_match("/define\\(\\s*'MEC_LICENSE_BUILD_SIG'\\s*,\\s*'([^']+)'\\s*\\)/", $source, $sigMatch))
    {
        $b64dec = function($s) {
            $p = strtr($s, '-_', '+/');
            $r = strlen($p) % 4;
            if ($r) $p .= str_repeat('=', 4 - $r);
            return base64_decode($p, true);
        };

        $pubkey = $b64dec($pubMatch[1]);
        $payload = $b64dec($buildMatch[1]);
        $sig = $b64dec($sigMatch[1]);

        if (strlen($pubkey) !== 32)
        {
            $errors[] = "MEC_LICENSE_PUBKEY must be 32 bytes, got " . strlen($pubkey) . ".";
        }
        elseif (strlen($sig) === 64 && $payload !== false)
        {
            $ok = sodium_crypto_sign_verify_detached($sig, $payload, $pubkey);
            if (!$ok)
            {
                $errors[] = "MEC_LICENSE_BUILD signature is invalid. Run build-constants.php.";
            }
            else
            {
                $data = json_decode($payload, true);
                if (is_array($data))
                {
                    $warnings[] = "release_iat: " . ($data['release_iat'] ?? '?') . " (" . date('Y-m-d H:i:s', $data['release_iat'] ?? 0) . ")";
                    $warnings[] = "phases: " . json_encode($data['phases'] ?? []);
                }
            }
        }
    }
}
else
{
    $warnings[] = "sodium extension not available, signature verification skipped.";
}

// --- Report ---
echo "===== MEC License Release Check =====\n\n";

if ($warnings)
{
    foreach ($warnings as $w) echo "  INFO: $w\n";
    echo "\n";
}

if ($errors)
{
    foreach ($errors as $e) echo "  FAIL: $e\n";
    echo "\n❌ RELEASE BLOCKED — " . count($errors) . " error(s).\n";
    exit(1);
}

echo "✅ All OK. Ready for release.\n";
exit(0);
