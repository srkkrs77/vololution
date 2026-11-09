CHANGELOG-06.md — Раздел 4.2–4.3
Часть 6 из 14. Пакеты с правками: FormResults + SimpleTube.

markdown
### 4.2 FormResults — 4 правки в 2 файлах

**Автор:** Pathologic  
**CHANGELOG:** 4.2  
**Причина правок:** PHP 8.5-совместимость + autoload `vendor/`.

---

#### Файл 1: `assets/modules/formresults/core/src/FormResults.php`

**Строки 8–10 (после use-блоков):**

```php
$autoload = MODX_BASE_PATH . 'vendor/autoload.php';
if (is_file($autoload)) { require_once $autoload; }
Что делает: подключает Composer-автозагрузку, если она есть. Без этого модуль падает на PHP 8.5 при отсутствии своих autoload-механизмов.

Файл 2: assets/modules/formresults/core/templates/forms_list.tpl
Строка 23 — ссылка на форму:

diff
- <td><a href="<?= $moduleUrl ?>&type=<?= $form['alias'] ?>"><?= htmlspecialchars($form['caption']) ?></td>
+ <td><a href="<?= $moduleUrl ?>&type=<?= $form['alias'] ?? '' ?>"><?= htmlspecialchars($form['caption'] ?? '') ?></a></td>
Что сделано:

$form['alias'] ?? '' — защита от отсутствующего ключа.

$form['caption'] ?? '' — аналогично.

Закрыт тег </a> — в оригинале его не было (была сломанная разметка).

Строка 25 — количество результатов:

diff
- <?= $form['results_total'] ?>
+ <?= $form['results_total'] ?? 0 ?>
Строка 26 — последний результат:

diff
- <?= $form['last_result'] ?>
+ <?= $form['last_result'] ?? '' ?>
Инструкция при установке
При установке FormResults: переименовать config/*.sample.php → *.php.

Например:

config/db.sample.php → config/db.php

config/main.sample.php → config/main.php

Без этого модуль не работает.

Итого по FormResults
Файл	Правок
core/src/FormResults.php	1 (autoload)
core/templates/forms_list.tpl	3
Итого	4
При установке через Store: ZIP FormResults.zip, alias FormResults, категория «Модули».

4.3 SimpleTube — 2 правки в 2 файлах
Автор: Pathologic
CHANGELOG: 4.3
Причина правок: PHP 8.5-совместимость (??).

Файл 1: assets/plugins/simpletube/lib/controller.class.php
Строка 43 — параметры плагина:

diff
- $params = array(..., 'forceDownload' => $forceDownload, 'lang'=>$lang, 'ytApiKey'=>$ytApiKey, 'vkAccessToken'=>$vkAccessToken);
+ $params = array(..., 'forceDownload' => $forceDownload ?? '', 'lang' => $lang ?? 'ru', 'ytApiKey' => $ytApiKey ?? '', 'vkAccessToken' => $vkAccessToken ?? '');
Что сделано:

forceDownload ?? '' — принудительное скачивание (по умолчанию выключено).

lang ?? 'ru' — язык по умолчанию — русский.

ytApiKey ?? '' — ключ YouTube API.

vkAccessToken ?? '' — токен VK.

Все четыре параметра получают безопасные дефолты при отсутствии.

Файл 2: assets/snippets/simpletube/lib/SimpleTube/simpletube.class.php
Строка 25 — ключ YouTube API:

diff
- if (empty($cfg['ytApiKey'])) $cfg['ytApiKey'] = $pluginParams['ytApiKey'];
+ if (empty($cfg['ytApiKey'])) $cfg['ytApiKey'] = $pluginParams['ytApiKey'] ?? '';
Что делает: если в конфиге сниппета нет ytApiKey, берёт из параметров плагина. Если и там нет — пустая строка, не warning.

Итого по SimpleTube
Файл	Правок
lib/controller.class.php	4
lib/SimpleTube/simpletube.class.php	1
Итого	5
При установке через Store: ZIP SimpleTube.zip, alias SimpleTube, категория «Плагины».

text

**Конец части 6.**