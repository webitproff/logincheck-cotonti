<?php

/**
 * logincheck.en.lang.php for the Plugin logincheck
 *
 * logincheck plugin for Cotonti 0.9.26, PHP 8.4+
 * Filename: logincheck.en.lang.php
 *
 * Date: Apr 25Th, 2026
 * @package logincheck
 * @version 2.7.8
 * @author webitproff
 * @copyright Copyright (c) webitproff 2026 | https://github.com/webitproff/logincheck-cotonti
 * @license BSD
 */

defined('COT_CODE') or die('Wrong URL.');

$L['cfg_invalidnames'] = ['Blocked usernames', 'comma separated'];

/**
 * Plugin Info
 */
$L['info_name'] = 'LoginCheck';
$L['info_desc'] = 'Automatic username normalization, transliteration and strict validation during user registration, including blocked usernames. Converts any user nickname into a valid standardized format.';
$L['info_notes'] = 'Requires Cotonti 0.9.26 or higher. Make sure the corresponding <code>translit.XX.lang.php</code> file exists. <br>
<a href="https://github.com/webitproff/logincheck-cotonti" target="_blank">
<abbr title="Latest plugin version" class="initialism"><strong>Latest version on GitHub</strong></abbr>
</a>';

$L['logincheck_error_invalidchars'] = 'Username must contain only Latin letters, digits and/or symbols _ and -';
$L['logincheck_error_invalidname'] = 'Please choose a different username';