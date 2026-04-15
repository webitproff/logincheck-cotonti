<?php

/**
 * logincheck.ua.lang.php for the Plugin logincheck
 *
 * logincheck plugin for Cotonti 0.9.26, PHP 8.4+
 * Filename: logincheck.ua.lang.php
 *
 * Date: Apr 25Th, 2026
 * @package logincheck
 * @version 2.7.8
 * @author webitproff
 * @copyright Copyright (c) webitproff 2026 | https://github.com/webitproff/logincheck-cotonti
 * @license BSD
 */

defined('COT_CODE') or die('Wrong URL.');

$L['cfg_invalidnames'] = ['Заборонені логіни', 'через кому'];

/**
 * Plugin Info
 */
$L['info_name'] = 'LoginCheck';
$L['info_desc'] = 'Автоматична нормалізація формату логіна, транслітерація та сувора перевірка логіна користувача під час реєстрації, включно із забороненими логінами. Перетворення довільного нікнейма користувача у коректний формат.';
$L['info_notes'] = 'Потрібен Cotonti 0.9.26 або вище. Завжди перевіряйте наявність відповідного <code>translit.XX.lang.php</code>. <br>
<a href="https://github.com/webitproff/logincheck-cotonti" target="_blank">
<abbr title="Актуальна версія плагіна" class="initialism"><strong>Актуальна версія на GitHub</strong></abbr>
</a>';

$L['logincheck_error_invalidchars'] = 'Логін має складатися з латинських літер, цифр та/або символів _ і -';
$L['logincheck_error_invalidname'] = 'Будь ласка, введіть інший логін';