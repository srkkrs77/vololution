CONTEXT-17.md — Правила работы с git и форком
Часть 17 из 19. Читать после CONTEXT.md, CONTEXT-01.md — CONTEXT-16.md.

Обновлено: 11.10.2026 (двуязычные коммиты, EN+RU).

markdown
## 17. ПРАВИЛА РАБОТЫ С GIT И ФОРКОМ

**ВАЖНО:** пользователь не знаком с Git и GitHub. Объяснять каждый шаг. Не предполагать знание терминов.

### 17.1. Инструменты

- **GitHub Desktop** — GUI-клиент для Git. Скачан пользователем (`GitHubDesktopSetup-x64`).
- **Git** (внутри GitHub Desktop) — командная строка.
- **Терминал Git Bash** — для команд, которых нет в GUI.

### 17.2. Базовые принципы

#### 17.2.1. Никогда не коммитить в `main` напрямую

Работа — в отдельной ветке. Потом merge в `main` через pull request (или локально).

**Исключение:** мелкие правки документации (`README.md`, `CHANGELOG.md`) — можно и в `main`.

#### 17.2.2. Коммиты — атомарные

Одна логическая правка — один коммит. Не «сделал всё сразу».

**Плохо:**
Fix CSRF + update docs + refactor header

text

**Хорошо:**
fix: CSRF JS XHR interceptor in header.inc.php
фикс: перехватчик CSRF JS для XHR в header.inc.php

docs: update roadmap for fork model
док: обновлён roadmap под форк-модель

text

#### 17.2.3. Сообщения коммитов — двуязычные (две строки)

**Формат заголовка — две строки:**
<EN-type>: <EN-subject>
<RU-type>: <RU-subject>

text

**Типы — двуязычные:**

| EN | RU |
|---|---|
| `feat` | `фича` |
| `fix` | `фикс` |
| `docs` | `док` |
| `refactor` | `рефактор` |
| `chore` | `служебное` |
| `style` | `стиль` |
| `test` | `тест` |

**Примеры:**
feat: live search AJAX autocomplete
фича: живой поиск AJAX-автокомплит

text
docs: update roadmap for fork model
док: обновлён roadmap под форк-модель

text
fix: sitemap TV label/value order
фикс: порядок label/value в TV sitemap

Было always==always (every access) — в БД сохранялась метка
с описанием. Стало always (every access)==always.

text
chore: bump seoVolo to 1.0.1
служебное: обновлён seoVolo до 1.0.1

text
fix: WebP Converter DOMDocument empty content
фикс: WebP Converter — пустой контент в DOMDocument

PHP 8.5 падал на loadHTML() при пустом documentOutput.
Заменён mb_convert_encoding 'HTML-ENTITIES' на mb_encode_numericentity.

text

**Тело коммита (после пустой строки):**

- **На русском** — пояснение сути, детали, ссылки.
- **Опционально.** Если заголовка достаточно — тело не нужно.
- **Ссылки на CHANGELOG:** `Closes: CHANGELOG-19`, `Refs: CHANGELOG-14.3`.

**Правила:**

1. **Заголовок — ровно две строки.** Первая — EN, вторая — RU.
2. **Обе строки короткие** (до 72 символов).
3. **Не заканчивать точкой.**
4. **Императив:** `fix`, не `fixed`. `фикс`, не `исправлено`.
5. **Тело — на русском**, если нужно.
6. **Технические термины** (`CSRF`, `AJAX`, `TV`, `fetch`, `header.inc.php`) — не переводятся.

**Зачем две строки:**

- **EN-заголовок** — стандарт GitHub, CI, Conventional Commits, международное сообщество.
- **RU-заголовок** — понятно команде без чтения тела.
- В `git log --oneline` видно обе строки.

#### 17.2.4. Перед push — pull

Всегда. Чтобы не получить конфликты при push.
git pull
git push

text

В GitHub Desktop: **Fetch origin** → **Pull origin** → **Push origin**.

#### 17.2.5. Никогда не коммитить

- `vendor/` — composer-зависимости (ставятся через `composer install`).
- `assets/cache/` — кэш Evo (генерируется).
- `manager/includes/config.inc.php` — содержит пароли БД.
- `.env` и подобные файлы секретов.
- Временные файлы (`*.bak`, `*.tmp`, `*.log`).

**Всё это — в `.gitignore`.**

#### 17.2.6. Секреты — не в репо

Пароли БД, API-ключи, токены — только локально. Никогда в коде.

Если случайно попали — **срочно сменить** и удалить из истории через `git filter-repo`.

#### 17.2.7. Тест на `vol.test` до push в `main`

Любая правка сначала тестируется на тестовом сайте. Потом — в `main`.

### 17.3. Ветки

| Ветка | Назначение |
|---|---|
| `main` | Стабильная. Готовая к развёртыванию. |
| `dev` | Разработка новых фич. Промежуточная. |
| `merge/upstream-YYYY-MM` | Временная, для мержа апстрима. |
| `feature/<название>` | Для крупных фич (магазин, авторизация). |
| `fix/<название>` | Для багфиксов. |

**Правило:** в `main` — только протестированное на `vol.test` и на проде.

### 17.4. Теги

- `v1.4.37-vololution` — точка форка (первый коммит).
- `v1.4.37-vololution-N` — после каждого мержа апстрима (N = 1, 2, 3...).
- `v1.5.0-vololution` — когда накопятся наши крупные фичи (магазин, авторизация).

### 17.5. Мерж апстрима

#### 17.5.1. Когда

- **Security-фиксы** — обязательно.
- **Крупные багфиксы** — по ситуации.
- **Новые фичи** — только если нужны нам.

**Не гоняемся за каждым релизом.**

#### 17.5.2. Пошагово
git fetch upstream

git checkout -b merge/upstream-2026-11

git merge upstream/1.4.x

Разрешаем конфликты — сверяясь с docs/patches-manifest.md

Тест на vol.test

git checkout main

git merge merge/upstream-2026-11

git push

git tag v1.4.37-vololution-N

git push --tags

Обновить CHANGELOG-.md, patches-manifest.md, CONTEXT-.md

text

#### 17.5.3. Настройка upstream

**Один раз при создании форка:**
git remote add upstream https://github.com/evocms-community/evolution.git
git remote -v

text

Должно показать:
origin https://github.com/srkkrs77/vololution.git (fetch)
origin https://github.com/srkkrs77/vololution.git (push)
upstream https://github.com/evocms-community/evolution.git (fetch)
upstream https://github.com/evocms-community/evolution.git (push)

text

### 17.6. Что делать, если сломалось

#### 17.6.1. Случайно закоммитил не то

**До push:**
git reset --soft HEAD~1

text
Коммит отменится, изменения останутся в рабочей директории.

**После push:**
git revert <hash>

text
Создаст новый коммит, отменяющий изменения.

#### 17.6.2. Случайно закоммитил секрет

**Срочно:**
1. Сменить пароль/ключ.
2. Удалить из истории через `git filter-repo`.
3. Force-push.
4. Предупредить всех, кто клонировал репо.

#### 17.6.3. Слил не то при мерже

**До push:**
git merge --abort

text
Мерж отменится.

**После push:**
git revert -m 1 <hash>

text
Отменит мерж.

#### 17.6.4. Случайно удалил файл
git checkout HEAD -- path/to/file

text
Восстановит из последнего коммита.

#### 17.6.5. Всё сломалось, хочу начать с чистого листа
git fetch origin
git reset --hard origin/main

text

**ВНИМАНИЕ:** удалит все несохранённые изменения. Использовать с осторожностью.

### 17.7. Что нельзя делать

- **Не force-push в `main`** — сломает историю всем, кто клонировал.
- **Не переписывать историю после push в общие ветки.**
- **Не коммитить секреты.**
- **Не игнорировать `.gitignore`.**
- **Не работать в `main` напрямую** (кроме мелкой документации).
- **Не мержить апстрим вслепую** — только выборочно, после теста.

### 17.8. Что делать перед первым коммитом в форк

1. Убедиться, что GitHub Desktop установлен.
2. Форк создан на GitHub: `srkkrs77/vololution`.
3. Форк клонирован локально.
4. Настроен `upstream`.
5. Проверен `.gitignore` — vendor, cache, config.inc.php исключены.
6. Прочитаны разделы 17.1–17.7 этого документа.

### 17.9. План первых коммитов в форк

1. **Pre-fork snapshot.** Залить текущее состояние (13 патчей ядра, Store, SEO-пакет) как есть.
chore: pre-fork snapshot 1.4.37-vololution
служебное: pre-fork снимок 1.4.37-vololution

text

2. **Инфраструктура.** `composer.json`, `.gitignore`, `src/` с базовой структурой.
chore: add composer.json and PSR-4 autoload
служебное: добавлен composer.json и PSR-4 автозагрузка

text

3. **README.md.** Описание проекта, установка, разработка.
docs: add README with installation guide
док: добавлен README с инструкцией по установке

text

4. **SchemaGuard.** Плагин-сторож для таблиц пакетов.
feat: SchemaGuard plugin for package tables
фича: плагин SchemaGuard для таблиц пакетов

text

### 17.10. Обращение с пользователем (Git-новичок)

- **Объяснять шаг за шагом.** Не «сделайте fork и push», а «откройте браузер, зайдите на github.com, нажмите кнопку Fork в правом верхнем углу...».
- **Скриншоты / описания кнопок.** Не только команды. Где кнопка — описать.
- **Не предполагать знание терминов.** `fork`, `clone`, `commit`, `push`, `pull` — объяснять.
- **Если команда — давать точный текст.** Не «примерно так».
- **Если ошибка — просить текст ошибки целиком.** Не «что-то не работает».

### 17.11. Двуязычные коммиты — примеры из практики

**Пример: патч CSRF-JS (11.10.2026)**
fix: CSRF JS XHR interceptor in header.inc.php
фикс: перехватчик CSRF JS для XHR в header.inc.php

setRequestHeader вызывался в open до origOpen.apply() —
InvalidStateError в SimpleGallery. Перенесён в send.
Второй JS-блок удалён, объединён с первым.

Closes: CHANGELOG-19

text

**Пример: патч WebP Converter (11.10.2026)**
fix: WebP Converter DOMDocument empty content
фикс: WebP Converter — пустой контент в DOMDocument

PHP 8.5 падал на loadHTML() при пустом documentOutput.
HTML-ENTITIES заменён на mb_encode_numericentity (удалён в PHP 8.4+).

Closes: CHANGELOG-04.7

text

**Пример: обновление sitemap TV (11.10.2026)**
fix: sitemap TV label/value order
фикс: порядок label/value в TV sitemap

Было always==always (every access) — в БД сохранялась метка
с описанием. Стало always (every access)==always.
В sitemap.xml попадал мусор в <changefreq> и <priority>.

Closes: CHANGELOG-15.13

text

**Пример: работа с DLSitemap (11.10.2026)**
docs: add DLSitemap complete reference
док: добавлен полный справочник по DLSitemap

Новый документ docs/dlsitemap-guide.md — все параметры,
плейсхолдеры, рецепты, типичные ошибки.

text

---

**Конец части 17.**