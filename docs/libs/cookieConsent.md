# Сниппет cookieConsent (GDPR / TTDSG) / cookieConsent Snippet (GDPR / TTDSG)

**Версия / Version:** 2.0.1
**Назначение / Purpose:** Вывод баннера согласия на использование cookie в соответствии с законодательством ЕС / Outputs a cookie consent banner compliant with EU legislation.
**Зависимости / Dependencies:** Требуется патч ядра `index.php` / Requires `index.php` core patch (delayed session start).

## 1. Архитектура / Architecture
Для удобства кастомизации сниппет разделен на 4 части / For easy customization, the snippet is divided into 4 parts:
1. **Сниппет `cookieConsent` / Snippet `cookieConsent`** — логика (определение языка, передача параметров) / logic (language detection, parameter passing).
2. **Чанк `cookieConsent.tpl` / Chunk `cookieConsent.tpl`** — HTML-разметка баннера / banner HTML markup.
3. **Чанк `cookieConsent.css` / Chunk `cookieConsent.css`** — стили / styles (with developer comments).
4. **Чанк `cookieConsent.js` / Chunk `cookieConsent.js`** — логика работы (сохранение в `localStorage`) / logic (saving to `localStorage`).

## 2. Как это работает / How it works
1. Сниппет определяет язык текущей страницы / The snippet detects the current page language.
2. Подключает стили из чанка `cookieConsent.css` / Includes styles from the `cookieConsent.css` chunk.
3. Выводит HTML из чанка `cookieConsent.tpl`, подставляя переведенный текст / Outputs HTML from the `cookieConsent.tpl` chunk, substituting translated text.
4. Подключает скрипт из чанка `cookieConsent.js` / Includes script from the `cookieConsent.js` chunk.
5. Если пользователь уже дал согласие (хранится 180 дней в `localStorage`), баннер не показывается / If the user has already given consent (stored for 180 days in `localStorage`), the banner is not shown.

## 3. Мультиязычность / Multilingual Support
Сниппет автоматически определяет язык страницы по первому сегменту URL / The snippet automatically detects the page language by the first URL segment:
- `/de/` → Немецкий / German
- `/ru/` → Русский / Russian
- `/fr/` → Французский / French
- `/es/` → Испанский / Spanish
- `/it/` → Итальянский / Italian
- `/en/` или без префикса / or without prefix → Английский (по умолчанию) / English (default)

*Если язык не найден, берется английский / If the language is not found, English is used.*

## 4. Установка и вызов / Installation and Calling
Вызывать сниппет нужно **один раз** на странице, лучше всего перед закрывающим тегом `</body>` / Call the snippet **once** per page, preferably before the closing `</body>` tag (e.g., in the `footer.tpl` chunk).

**Базовый вызов / Basic call:**

[!cookieConsent!]

## 5. Варианты вызова и параметры / Call Options and Parameters
Параметр / Param	По умолчанию / Default	Описание / Description
&position	bottom-left	Позиция баннера / Banner position. Доступно / Available: bottom-left, bottom-right, bottom-center.
&css	cookieConsent.css	Имя чанка со стилями / Styles chunk name. Если указать &css=0, стили не выводятся / If &css=0, styles are not output.
&tpl	cookieConsent.tpl	Имя чанка с HTML-разметкой / HTML markup chunk name.
&js	cookieConsent.js	Имя чанка с JavaScript / JavaScript chunk name.
Примеры вызовов / Call examples:
1. Справа внизу / Bottom right:

[!cookieConsent? &position=`bottom-right`!]

2. По центру внизу / Bottom center:

[!cookieConsent? &position=`bottom-center`!]

3. Использование своих стилей / Using custom styles:

[!cookieConsent? &css=`0`!]

(Классы .cc-banner, .cc-actions, .cc-btn-all останутся в HTML / Classes .cc-banner, .cc-actions, .cc-btn-all remain in HTML).

6. Кастомизация / Customization
6.1. Изменение цветов и отступов / Changing colors and margins
Откройте чанк cookieConsent.css / Open the cookieConsent.css chunk. В файле есть двуязычные комментарии / The file has bilingual comments.

6.2. Изменение ссылок / Changing links (Privacy Policy / Политика конфиденциальности)
Откройте сниппет cookieConsent / Open the cookieConsent snippet. Найдите массив $texts / Find the $texts array. Поменяйте ссылки href="/datenschutz.html" / Change the href="/datenschutz.html" links.

6.3. Изменение положения / Changing position
Используйте параметр &position= при вызове сниппета / Use the &position= parameter in the snippet call.

7. Внедрение в шаблон / Template Integration
Для вывода в футер добавьте / To output in the footer, add to the footer chunk:

[!cookieConsent!]