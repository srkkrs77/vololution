CHANGELOG-15.md — Раздел 21–23
Часть 15 из 15. GDPR, CookieConsent, фиксы установщика для PHP 8.4 и правки seoVolo.

---

## РАЗДЕЛ 21. GDPR / COOKIE CONSENT
**Дата:** 09.11.2026

### 21.1 `index.php` — отложенный старт сессии
**Проблема:** Evo ставит cookie до согласия (нарушение EU GDPR / TTDSG).
**Решение:** Сессия стартует только для админки или авторизованных пользователей.
**Код:** Добавлена проверка `$isManagerRequest` и `$hasEvoCookie` перед `startCMSSession()`.

### 21.2 Сниппет `cookieConsent`
**Создан:** `assets/snippets/cookieConsent/snippet.cookieConsent.php` (v1.1.0).
**Функционал:** Мультиязычный (RU, EN, DE, FR, ES, IT) GDPR-баннер с сохранением в `localStorage`.
**Дескриптор:** `install/assets/snippets/cookieConsent.tpl`.

---

## РАЗДЕЛ 22. ФИКСЫ УСТАНОВЩИКА ДЛЯ PHP 8.4
**Дата:** 09.11.2026

**Проблема:** PHP 8.4 вызывает `Fatal TypeError` при установке, если в пакетах нет `@installset`.
**Решение:**
1. `install/actions/action_options.php` (стр. 82) — добавлено `(array)` перед `$preset[5]`.
2. `install/instprocessor.php` (стр. 410, 464) — добавлено `(array)` перед `$moduleChunk[5]` и `$moduleModule[7]`.
3. `install/setup.info.php` (стр. 173) — замена `: false` на `: []`.

---

## РАЗДЕЛ 23. ФИКСЫ ПАКЕТА seoVolo (Фронтенд)
**Дата:** 09.11.2026

1. **`snippet.seoVolo.php`** — устранение HTML-сущностей (кракозябр `&auml;`):
   - В Разделы 8, 10, 11 и функцию `seoVolo_truncate()` добавлена очистка через `html_entity_decode()`.
2. **`snippet.seoVolo.php`** — исправлен приоритет OG-картинки:
   - В Разделе 15 `og_default_image` (картинка по умолчанию) теперь перебивает `seo_org_logo` (логотип).
3. **`mm_rules.tpl`** (v2.1.0) — добавлен JS-индикатор длины `seo_description` с мультиязычной поддержкой.