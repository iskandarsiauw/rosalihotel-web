<?php
/* front_init.php — emit a <script> block setting window.ROSALI before shared.jsx loads.
   Must be placed inside <head> or before shared.jsx so values are ready when shared.jsx runs.
   Requires functions.php to be loaded already by the parent page. */

if (!function_exists('getActiveTheme')) {
    require_once __DIR__ . '/functions.php';
}

$rosali = [
    'theme'        => getActiveTheme(),
    'lang'         => getActiveLang(),
    'pageVisibility' => pageVisibility(),
    'pageOrder'    => pageOrder(),
    'content'      => allContentOverrides(),
    'layout'       => allLayoutPrefs(),
    'colors'       => colorOverridesAll(),
    'images'       => mediaSlotMap(),
    'splatEnabled' => isSplatEnabled(),
];
?>
<script>window.ROSALI = <?= json_encode($rosali, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;</script>
