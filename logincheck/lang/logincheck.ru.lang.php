
<?php
	
/**
 * logincheck.ru.lang.php for the Plugin logincheck
 *
 * logincheck plugin for Cotonti 0.9.26, PHP 8.4+
 * Filename: logincheck.ru.lang.php
 *
 * Date: Apr 25Th, 2026
 * @package logincheck
 * @version 2.7.8
 * @author webitproff
 * @copyright Copyright (c) webitproff 2026 | https://github.com/webitproff/logincheck-cotonti
 * @license BSD
 */


defined('COT_CODE') or die('Wrong URL.');

$L['cfg_invalidnames'] = ['Запрещенные логины', 'через запятую'];

/**
 * Plugin Info
 */
$L['info_name'] = 'LoginCheck';
$L['info_desc'] = 'Автоматическая нормализация формата логина, транслитерация и строгая проверки логина пользователя при регистрации, в том числе на запрещенные логины. Преобразовать произвольный никнейма пользователя в правильный формат.';
$L['info_notes'] = 'Требуется Cotonti 0.9.26 и выше. Всегда внимание на наличие соответствующего <code>translit.XX.lang.php</code>. <br>
<a href="https://github.com/webitproff/logincheck-cotonti" target="_blank">
<abbr title="Актуальная версия плагина" class="initialism"><strong>Актуальная версия на GitHub</strong></abbr>
</a>';

$L['logincheck_error_invalidchars'] = 'Логин должен состоять из латинского алфавита, цифр и-или символов _ и - ';
$L['logincheck_error_invalidname'] = 'Пожалуйста, укажите другой логин';

