Logincheck Plugin for Cotonti: Complete Guide
---------------------------------------------


[![Version](https://img.shields.io/badge/version-2.7.8-green.svg)](https://github.com/webitproff/logincheck-cotonti/releases)
[![Cotonti Compatibility](https://img.shields.io/badge/Cotonti_Siena-0.9.26-orange.svg)](https://github.com/Cotonti/Cotonti)
[![PHP](https://img.shields.io/badge/PHP-8.4-purple.svg)](https://www.php.net/releases/8_4_0.php)
[![MySQL](https://img.shields.io/badge/MySQL-8.0-blue.svg)](https://www.mysql.com/)
[![Bootstrap v5.3.8](https://img.shields.io/badge/Bootstrap-v5.3.8-blueviolet.svg)](https://getbootstrap.com/)
[![License](https://img.shields.io/badge/license-BSD-blue.svg)](https://github.com/webitproff/logincheck-cotonti/blob/main/LICENSE)


#### 🇷🇺 **[на русском](https://abuyfile.com/ru/market/cotonti/plugs/logincheck)**



#### 🇬🇧 **Logincheck description**
### 1\. Purpose and Concept

The **Logincheck** plugin is designed for automatic normalization and strict validation of a user's login during registration in the Cotonti content management system (version 0.9.26 and higher, PHP 8.4+ support). Its primary task is to transform arbitrary user input in the "Username" field (which may contain Cyrillic characters, spaces, hyphens, underscores, digits, and even random special characters) into a uniform format that fully complies with Cotonti's internal restrictions and additional security requirements.

The idea for the new plugin was taken and redesigned [**based on this extension.**](https://github.com/webitproff/cot_2waydeal_build/tree/master/public_html/plugins/logincheck)

Cotonti imposes the following restrictions on the `user_name` field:

*   maximum length of 32 characters (type `VARCHAR(32)` in the database);
*   the first character must be a Latin letter (lowercase or uppercase);
*   subsequent characters may be Latin letters, digits, underscores, or hyphens.

However, users often enter names with Cyrillic, spaces, or random special characters. The Logincheck plugin intervenes at the registration form validation stage, intercepts the entered value, performs a series of transformations, and returns an already cleaned and validated value to the system. If after all transformations the login does not meet the requirements or appears in the blacklist, registration is rejected with a clear error message.

Thus, the administrator is relieved of the need to manually moderate invalid names, and users can enter characters they are accustomed to (for example, Cyrillic) and receive a valid Latin login as the result.

### 2\. Technical Implementation: Hook in the Cotonti System

The plugin connects to the system via the standard hook mechanism. The header of the file `logincheck.users.register.add.validate.php` contains the directive:

    Hooks=users.register.add.validate

This means that the plugin code will be executed at the moment of validation of the data submitted by the new user registration form. The hook fires before Cotonti checks the data against its internal rules and attempts to create a record in the database. The `Order=1` parameter ensures that this hook runs first among other plugins using the same hook, which is important for sequential login processing.

Inside the hook, the `$ruser` array is available, containing all fields submitted from the registration form. The field of interest to us is `$ruser['user_name']`. The plugin extracts its value, performs all necessary manipulations, and writes it back to the same array, after which control is passed to subsequent hooks and, ultimately, to the Cotonti core.

### 3\. Step-by-Step Breakdown of Login Normalization

Let's examine the sequence of transformations applied to the original login string. Each step is accompanied by a comment about its purpose and an example.

#### 3.1. Retrieving the Original Value

    $username = $ruser['user_name'] ?? '';

Safe retrieval of the value using the null coalescing operator. If for some reason the key is absent, the variable will be an empty string. This prevents PHP errors in case of incorrect data.

#### 3.2. Transliteration: cot\_translit\_encode

    $username = cot_translit_encode($username);

The key stage is the conversion of non-Latin characters (for example, Cyrillic) into Latin. The standard Cotonti function `cot_translit_encode()` is used, which loads a mapping array from the language file `translit.XX.lang.php`, where `XX` is the current system language. The function's operation mechanism:

*   Checks if the current language is not English (`$lang == 'en'`).
*   If the language is not English and the file `translit.XX.lang.php` exists, it is included.
*   The file defines the global array `$cot_translit`, where keys are the original characters (in UTF-8), and values are their Latin equivalents.
*   The `strtr()` function replaces all occurrences of keys with the corresponding values.

It is important to note that the order of elements in the `$cot_translit` array matters: longer combinations (digraphs, trigraphs) should be placed earlier to be processed correctly. For example, in the Ukrainian table, 'Щ' → 'Shch' comes before 'Ш' → 'Sh'.

**Example (Russian language):** the user entered `Иван Петров`. After transliteration, it becomes `Ivan Petrov` 

(assuming the transliteration table contains standard replacements: ).

    'И'→'I', 'в'→'v', 'а'→'a', 'н'→'n', ' '→' ', 'П'→'P', 'е'→'e', 'т'→'t', 'р'→'r', 'о'→'o', 'в'→'v'

**Example (Ukrainian language):** `Євгенія` → `Yevheniia` (according to the table: ).

    'Є'→'Ye', 'в'→'v', 'г'→'h', 'е'→'e', 'н'→'n', 'і'→'i', 'я'→'ia'

Transliteration is the foundation of login "internationalization," allowing users to enter names in their native language while receiving a representation suitable for URLs and system identifiers.

#### 3.3. Replacing Whitespace Characters with Underscore

    $username = preg_replace('/\s+/u', '_', $username);

The regular expression `/\s+/u` finds any sequences of whitespace characters (space, tab, line break, etc.) and replaces them with a single underscore character. The `u` modifier enables UTF-8 support. As a result, all spaces are collapsed into single underscores.

**Example:** `Ivan Petrov` → `Ivan_Petrov`.

#### 3.4. Removing All Characters Except Allowed Ones

    $username = preg_replace('/[^a-zA-Z0-9_-]/', '-', $username);

Anything that is not a Latin letter (any case), digit, underscore, or hyphen is replaced with a hyphen. This step ensures that only allowed characters remain in the string. Any "exotic" signs, punctuation, or characters from other alphabets (if transliteration somehow missed them) will turn into hyphens.

**Example:** `Ivan_Petrov!` → `Ivan_Petrov-`.

#### 3.5. Collapsing Multiple Hyphens

    $username = preg_replace('/-+/', '-', $username);

Any sequence of one or more hyphens is replaced with a single hyphen. Prevents the appearance of logins like `user----name`.

#### 3.6. Collapsing Multiple Underscores

    $username = preg_replace('/_+/', '_', $username);

Similar to the previous step, but for underscores. Eliminates constructs like `user___name`.

#### 3.7. Removing Leading and Trailing Hyphens and Underscores

    $username = trim($username, '-_');

The `trim()` function removes the specified characters from both ends of the string. A login should not start or end with separators — this is a Cotonti rule and common practice.

**Example:** `_Ivan-Petrov_` → `Ivan-Petrov`.

#### 3.8. Checking for an Empty String

    if ($username === '') {
        cot_error($L['logincheck_error_invalidchars'], 'rusername');
        return;
    }

If after all transformations nothing remains of the original string (for example, the user entered only spaces or special characters), the plugin triggers an error via the `cot_error()` function. The message is taken from the plugin's language file. The `'rusername'` parameter indicates that the error pertains to the username field (in the registration form, this field is usually named `rusername`). After the error is called, hook execution stops, and Cotonti will not allow registration.

#### 3.9. Limiting Length to 32 Characters

    $username = mb_substr($username, 0, 32);

Using the multibyte function `mb_substr()` ensures correct truncation of a UTF-8 string to 32 characters, which matches the maximum length of the `user_name` field in the `cot_users` table.

**Important Note:** truncation is performed before checking the first character and the possible addition of a prefix. In the current implementation, this may cause the `user_` prefix to be added to an already truncated string, and the final length may exceed 32 characters. It is recommended to change the order: first check the first character and add the prefix if necessary, then truncate to 32 characters. However, the current version of the plugin retains the described sequence.

#### 3.10. Checking the First Character and Adding a Prefix

    if (!preg_match('/^[a-zA-Z]/', $username)) {
        $username = 'user_' . $username;
    }

If after all manipulations the login does not start with a Latin letter (for example, only a numeric identifier or underscore remains), the prefix `user_` is added. This guarantees that the first character will be a letter, which is a mandatory requirement of Cotonti (otherwise it will fail the final regular expression check).

**Example:** `12345` → `user_12345`.

#### 3.11. Writing the Processed Value Back to the Array

    $ruser['user_name'] = $username;

The normalized login is placed back into the registration data. Subsequent hooks and the Cotonti system itself will work with the corrected value.

### 4\. Final Strict Check

    if (!empty($ruser['user_name']) && 
        !preg_match("/^[a-zA-Z][_a-zA-Z0-9-]*$/", $ruser['user_name'])) {
        cot_error($L['logincheck_error_invalidchars'], 'rusername');
    }

Even after all transformations, the plugin performs a control comparison against a regular expression that precisely describes the allowed format:

*   `^[a-zA-Z]` — the first character must be a Latin letter;
*   `[_a-zA-Z0-9-]*` — then any number of letters, digits, underscores, or hyphens;
*   `$` — end of the string.

If for some reason the string does not match the pattern (for example, contains an invalid character missed during cleaning, or has length 0), registration will be rejected.

### 5\. Blacklist Check for Forbidden Names

    if (!empty($cfg['plugin']['logincheck']['invalidnames'])) {
        $invalidnames = array_map('trim', explode(',', $cfg['plugin']['logincheck']['invalidnames']));
        if (in_array($ruser['user_name'], $invalidnames, true)) {
            cot_error($L['logincheck_error_invalidname'], 'rusername');
        }
    }

The plugin allows the administrator to specify a list of disallowed logins through the settings in the control panel. The configuration option `invalidnames` stores a comma-separated string. During the check, the plugin:

*   splits the string into an array using `explode()`;
*   applies `trim()` to each element to remove possible spaces around the names;
*   performs a strict comparison (`in_array(..., true)`) to check for a match with the normalized login.

If the login matches one of the forbidden names, the user will see an error with the text "Please specify a different login".

The administrator can add standard reserved names to this list: `admin, system, guest, root, support, moderator` and any others at their discretion.

### 6\. Plugin Configuration

The plugin is managed via the standard configuration file `logincheck.setup.php` and the Cotonti administration interface. In the `[BEGIN_COT_EXT_CONFIG]` section, one setting is defined:

    invalidnames=01:textarea:::Invalid names

This means that a text field (textarea) will be available in the plugin management panel for entering forbidden logins. The field label is localized through the language file: `$L['cfg_invalidnames']`.

The plugin's language files (`logincheck.ru.lang.php` and similar) contain two strings:

*   `$L['logincheck_error_invalidchars']` — message about invalid characters;
*   `$L['logincheck_error_invalidname']` — message when a blacklist match occurs.

These strings can be translated into any system language.

### 7\. Interaction with Cotonti Transliteration

The key role in converting Cyrillic is played by the Cotonti transliteration mechanism. The plugin does not contain its own replacement tables but relies entirely on the system files `translit.XX.lang.php`. This ensures uniformity with other parts of the system (for example, generating aliases for pages) and facilitates maintenance: updating transliteration tables in language packs automatically improves the plugin's operation as well.

The administrator can modify or supplement the `$cot_translit` table in the corresponding language file to adapt transliteration rules to specific needs (for example, change the conversion of "Щ" from "Shch" to "Sch"). It is important to observe the order of elements: longer sequences should be placed higher.

The `cot_translit_encode()` function statically caches the loading of the language file (variable `$lang_loaded`), so for multiple calls within a single request, the file is included only once, saving resources.

### 8\. Examples of Full Processing Cycle

Below are examples of how the plugin transforms various input data into a final login suitable for Cotonti.

#### Example 1. Cyrillic with a Space

*   User input: `Иван Петров`
*   After transliteration: `Ivan Petrov`
*   Replacing spaces with underscores: `Ivan_Petrov`
*   Cleaning invalid characters: `Ivan_Petrov` (no changes)
*   Collapsing repeats, trimming edges: no changes
*   Length within 32, first character is a letter → prefix not added
*   Result: `Ivan_Petrov`

#### Example 2. Name with Hyphen and Special Characters

*   Input: `Анна-Мария!!!`
*   Transliteration: `Anna-Mariya!!!`
*   No spaces, cleaning: exclamation marks are replaced with hyphens → `Anna-Mariya---`
*   Collapsing hyphens: `Anna-Mariya-`
*   Removing trailing hyphen: `Anna-Mariya`
*   Result: `Anna-Mariya`

#### Example 3. Digits Only

*   Input: `12345`
*   Transliteration: `12345` (no changes)
*   Cleaning: digits remain
*   First character is not a letter → `user_` prefix added
*   Result: `user_12345`

#### Example 4. Name with Multiple Spaces and Mixed Case

*   Input: `John Doe`
*   Transliteration: `John Doe` (Latin characters unchanged)
*   Replacing spaces: all whitespace sequences are replaced with single underscores → `_John_Doe_`
*   Cleaning: allowed characters remain
*   Removing leading/trailing underscores: `John_Doe`
*   Result: `John_Doe`

#### Example 5. String Consisting Only of Invalid Characters

*   Input: `!!!@@@###`
*   Transliteration: no changes
*   Cleaning: all characters are replaced with hyphens → `-------`
*   Collapsing hyphens: `-`
*   Trimming edges: empty string → error "Login must consist of Latin alphabet, digits and/or symbols \_ and -"

Input Login (Original Data)

Result After Plugin Processing

Note and Explanation

`Иван Петров`

`Ivan_Petrov`

Transliteration of Cyrillic to Latin: И→I, в→v, а→a, н→n, П→P, е→e, т→t, р→r, о→o, в→v.  
Space replaced with underscore (`_`).  
First character is a Latin letter, no prefix added.

`Анна-Мария!!!`

`Anna-Mariya`

Transliteration: А→A, н→n, а→a, М→M, а→a, р→r, и→i, я→ya.  
Exclamation marks (`!!!`) replaced with hyphens → `Anna-Mariya---`.  
Hyphen sequence collapsed into one, trailing hyphen removed by `trim()`.

`12345`

`user_12345`

Digits are not transliterated and remain unchanged.  
First character is not a Latin letter, so the `user_` prefix is added.  
Length does not exceed 32 characters.

`John Doe`

`John_Doe`

Latin characters remain unchanged.  
All whitespace sequences (including leading and trailing) are replaced with a single underscore.  
Leading/trailing underscores removed by `trim($username, '-_')`.

`!!!@@@###`

Error: _"Login must consist of Latin alphabet, digits and/or symbols \_ and -"_

All characters are not in the allowed set `[a-zA-Z0-9_-]`, so each is replaced with a hyphen → `-------`.  
Hyphen collapse results in `-`.  
`trim()` removes the hyphen at the beginning and end, leaving an empty string.  
Empty string triggers a validation error.

`Євгенія` (Ukrainian)

`Yevheniia`

Transliteration according to the Ukrainian table (DSTU 8583:2015): Є→Ye, в→v, г→h, е→e, н→n, і→i, я→ia.  
No spaces or invalid characters, first character is a Latin letter.  
The result fully complies with Cotonti requirements.

`admin` (if present in the blacklist)

Error: _"Please specify a different login"_

The login `admin` passes all normalization steps unchanged (Latin letters, allowed symbols).  
However, it matches a value from the plugin's `invalidnames` setting.  
The blacklist check is triggered, and registration is rejected.

`User_Name-2024`

`User_Name-2024`

**Example of a login that undergoes no changes.**  
The string consists only of Latin letters, digits, underscores, and hyphens.  
First character is a Latin letter.  
No spaces, edge separators, or invalid characters.  
Length is less than 32 characters, not in the blacklist.  
The plugin passes this login without modifications.

`user with multiple___spaces---and symbols!@#`

`user_with_multiple_spaces-and-symbols`

Transliteration not required (Latin).  
Multiple spaces replaced with a single underscore → `user_with_multiple___spaces---and_symbols!@#`.  
Characters `!@#` replaced with hyphens → `user_with_multiple___spaces---and_symbols---`.  
Underscore and hyphen sequences collapsed: `___` → `_`, `---` → `-`.  
Trailing hyphens removed. Final result: `user_with_multiple_spaces-and-symbols`.

`_Alice_`

`Alice`

Leading and trailing underscores removed by `trim($username, '-_')`.  
The remaining part complies with the rules, no further changes needed.

`ОченьДлинноеИмяКотороеПревышаетТридцатьДваСимвола`

`OchenDlinnoeImyaKotoroePrevy` (exactly 32 characters)

Transliteration: О→O, ч→ch, е→e, н→n, ь→ (disappears), etc.  
After transliteration, length exceeds 32 characters.  
The `mb_substr($username, 0, 32)` function truncates the string to 32 characters.  
First character is a Latin letter, no prefix added.

#### Example 6. Blacklist Match

*   Input: `admin` (if `admin` is specified in the plugin settings)
*   All normalization steps pass successfully, login remains `admin`
*   Blacklist check finds a match → error "Please specify a different login"

### 9\. Recommendations for Improvement and Possible Enhancements

Despite the fact that the plugin successfully performs its functions, in the current version several points can be noted that, if desired, can be improved:

*   **Order of adding prefix and truncating length:** as already mentioned, truncating to 32 characters before adding the prefix may cause the limit to be exceeded. It is recommended to swap the steps: first add `user_`, then perform `mb_substr()`.
*   **Extending the blacklist with system names:** the plugin could automatically add standard Cotonti reserved names (`admin`, `guest`, `system`, etc.) to the user-defined list to prevent their accidental use.
*   **Logging transformations:** for debugging purposes, the original and resulting values could be written to the plugin's log file, which would simplify analysis of issues during mass registration.
*   **Case handling:** in the current implementation, case is preserved. An optional conversion to lowercase could be added for uniformity (e.g., `$username = mb_strtolower($username);`).

All these improvements are not critical, and the plugin works stably in the presented version.

### 10\. Conclusion

The **Logincheck** plugin is an effective tool for automatically conforming user logins to Cotonti requirements. It relieves the administrator of the need to manually correct or reject registrations with invalid names, and also enhances convenience for users by allowing them to enter familiar Cyrillic characters. Thanks to the use of standard transliteration mechanisms and a thoughtful normalization sequence, the plugin easily integrates into existing projects and does not require complex configuration.

To install, simply place the plugin files in the appropriate directory `plugins/logincheck`, activate it in the Cotonti control panel, and, if necessary, populate the list of forbidden logins. Further operation is fully automated and requires no intervention.

Thus, Logincheck is an indispensable assistant for any Cotonti site where database cleanliness and uniformity of user identifiers are important.
