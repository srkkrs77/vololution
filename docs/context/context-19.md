# CONTEXT-19.md — Полезные ссылки

**Часть 19 из 19.** Последняя. Читать после `CONTEXT.md`, `CONTEXT-01.md` — `CONTEXT-18.md`.

**Обновлено:** 13.10.2026 (DLSiblings-guide, обновления карты документации).

---

## 19. ПОЛЕЗНЫЕ ССЫЛКИ

### 19.1. Наши ресурсы

| Что | Адрес |
|---|---|
| **Форк (план)** | `github.com/srkkrs77/vololution` |
| **Каталог пакетов Store** | `github.com/srkkrs77/vololution/packages` |
| **Файл каталога** | `github.com/srkkrs77/vololution/packages/catalog.json` |
| **Тестовый сайт** | `https://vol.test/` |
| **Прод** | `studio-milstein.de` |

### 19.2. Апстрим (ядро)

| Что | Адрес |
|---|---|
| **Апстрим CE** | `github.com/evocms-community/evolution` |
| **Точка форка** | тег `1.4.37` (13.11.2025) |
| **Релизы CE** | `github.com/evocms-community/evolution/releases.atom` |
| **Diff апстрима** | `github.com/evocms-community/evolution/compare/1.4.37...X.Y.Z` |
| **Мониторинг безопасности** | `github.com/extras-evolution/security-fix` |
| **RSS безопасности** | `github.com/extras-evolution/security-fix/releases.atom` |

### 19.3. Разработчики пакетов

| Разработчик | GitHub | Пакеты |
|---|---|---|
| **Pathologic** | `github.com/Pathologic` | DocLister, FormLister, MultiCategories, editDocs (соавтор), SimpleGallery, SimpleTube, evoSearch, DLSiblings, MonthDate, phpThumb, WebP Converter |
| **Grinyaha** | `github.com/Grinyaha` | editDocs |
| **Aharito** | `github.com/aharito` | DLSiblings (на основе DLPrevNext от Agel_Nash) |
| **webber** | web-ber12@yandex.ru | evoBabel |
| **DivanDesign** | `github.com/DivanDesign` | mm_ddMultipleFields, ddTools, ddGetMultipleField и др. (не установлены, могут понадобиться) |

**Документация editDocs:** `editdocs.grishin.net`

### 19.4. Сообщество Evo

| Ресурс | Адрес | Назначение |
|---|---|---|
| **Telegram** | `@evo_cms` | Оперативная помощь, обсуждения |
| **Форум** | `community.evocms.ru` | Долгие обсуждения, гайды |
| **Русскоязычный ресурс** | `evocms.ru` | Новости, документация |

### 19.5. Мониторинг безопасности

| Источник | Адрес |
|---|---|
| **Официальные патчи** | `github.com/extras-evolution/security-fix` |
| **CVE-базы** | `opencve.io`, `attackerb.com` |
| **Релизы CE** | `github.com/evocms-community/evolution/releases.atom` |

**Частота проверки:** раз в месяц.

### 19.6. Что НЕ используем

| Ресурс | Почему |
|---|---|
| **`evo.im`** | Не упоминаем в коде (устаревшая точка) |
| **`extras.evo.im`** | Отвязка (свой Store) |
| **`extras.evocms.ru`** | Отвязка |
| **`github.com/evolution-cms`** | Старая ветка |
| **`github.com/evolution-cms/evolution`** | Не наш апстрим |

### 19.7. Локальные пути (Windows)

| Что | Путь |
|---|---|
| **Рабочая копия** | `C:\laragon\www\vol.test\` |
| **Эталон (дистрибутив)** | `D:\VoloLution-CMS-1.4.37\` |
| **Nginx конфиг** | `C:\laragon\etc\nginx\sites-enabled\vol.test.conf` |
| **Laragon** | `C:\laragon\` |
| **GitHub Desktop** | Установлен пользователем (`GitHubDesktopSetup-x64`) |

### 19.8. БД (локально)

| Параметр | Значение |
|---|---|
| **Имя БД** | `vol` |
| **Префикс** | `gyl8_` |
| **Пользователь** | `root` |
| **Пароль** | (пусто, Laragon по умолчанию) |
| **Хост** | `localhost` |
| **Порт** | `3306` |
| **MySQL** | 8.0.30 |

### 19.9. БД (прод, studio-milstein.de)

| Параметр | Значение |
|---|---|
| **Имя БД** | `dbs16208630` |
| **Префикс** | `eh2v_` |
| **Проблема** | SimpleGallery не создал таблицу `eh2v_sg_images` (решено вручную) |

### 19.10. Git — команды (двуязычный формат коммитов)

**Обычный коммит:**
git add .
git commit -m "fix: sitemap TV label/value order"
-m "фикс: порядок label/value в TV sitemap"
-m "Было always==always (every access), стало always (every access)==always."

text

**Однострочный коммит (без тела):**
git commit -m "docs: update roadmap for fork model"
-m "док: обновлён roadmap под форк-модель"

text

**Формат в GitHub Desktop:**

В поле «Summary» (одна строка):
fix: sitemap TV label/value order / фикс: порядок label/value в TV sitemap

text

В поле «Description» (тело):
Было always==always (every access), стало always (every access)==always.

text

**Важно:** GitHub Desktop не позволяет ввести две строки в Summary. Поэтому формат для GUI — через ` / `.

**Для командной строки — две строки (правильный формат).**

**Теги:**
git tag v1.4.37-vololution
git push --tags

text

**Ветки:**
git checkout -b feature/schema-guard
git checkout main
git merge feature/schema-guard
git branch -d feature/schema-guard

text

**Синхронизация:**
git fetch origin
git pull origin main
git push origin main

text

**Мерж апстрима:**
git fetch upstream
git checkout -b merge/upstream-2026-11
git merge upstream/1.4.x
git checkout main
git merge merge/upstream-2026-11

text

**Откат:**
До push
git reset --soft HEAD~1

После push
git revert <hash>

Восстановить файл
git checkout HEAD -- path/to/file

Полный сброс к origin/main
git fetch origin
git reset --hard origin/main

text

### 19.11. Связанные документы в проекте

| Документ | Файл |
|---|---|
| **Журнал правок** | `docs/changelog/CHANGELOG-01.md` … `CHANGELOG-14.md` |
| **Индекс контекста** | `CONTEXT.md` |
| **Контекст (19 частей)** | `docs/context/CONTEXT-01.md` … `CONTEXT-19.md` |
| **Реестр отличий от апстрима** | `docs/patches-manifest.md` |
| **План развития** | `docs/roadmap.md` |
| **Экосистема CE** | `docs/CE-community.md` |
| **Справочник DLSitemap** | `docs/dlsitemap-guide.md` |
| **Справочник DLSiblings** | `docs/dlsiblings-guide.md` |
| **Учебник SEO (устарел)** | `docs/seoVolo-guide.md` |
| **Каталог Store** | `packages/catalog.json` |
| **Итоговая справка** | прикладывается отдельно к новому чату |

### 19.12. Продолжение работы

**В новой сессии:**

1. Открыть чат с ассистентом.
2. Приложить 19 частей `CONTEXT-01.md` … `CONTEXT-19.md` (по одной).
3. Приложить **итоговую справку** (см. отдельный файл).
4. Написать: «Прочитай все части и справку, работаем над VoloLution CMS».

**Приоритеты на сейчас** (см. `docs/roadmap.md`):

1. **Форк на GitHub** — установка GitHub Desktop, создание форка, клонирование.
2. **Инфраструктура форка** — `composer.json`, `src/`, `.gitignore`.
3. **SchemaGuard** — плагин-сторож для таблиц пакетов.
4. **CSRF-техдолг** — задачи A, B, C (D закрыта).
5. **Живой поиск** — Этап 1.2 roadmap.

### 19.13. Терминология — шпаргалка

| EN | RU | Что |
|---|---|---|
| **fork** | форк | Копия чужого репозитория в своём аккаунте |
| **clone** | клонировать | Скачать репозиторий на компьютер |
| **commit** | коммит | Сохранение изменений в истории |
| **push** | пуш | Отправить коммиты на GitHub |
| **pull** | пул | Забрать коммиты с GitHub |
| **branch** | ветка | Отдельная линия разработки |
| **merge** | мерж | Слияние веток |
| **upstream** | апстрим | Исходный репозиторий (наш — `evocms-community/evolution`) |
| **origin** | ориджин | Наш форк на GitHub |
| **tag** | тег | Метка на коммите (например, `v1.4.37-vololution`) |
| **conflict** | конфликт | Несовпадение изменений в одном файле |

### 19.14. Что делать при типовых проблемах

| Проблема | Решение |
|---|---|
| **«Отказано. Устаревшая версия»** при push | `git pull`, разрешить конфликты, `git push` |
| **Конфликт при pull** | Открыть файл, найти `<<<<<<<`, `=======`, `>>>>>>>`, разрешить вручную |
| **Случайно закоммитил пароль** | Сменить пароль, `git filter-repo`, force-push |
| **Не пускает в репозиторий** | Проверить, что вошёл в тот же GitHub-аккаунт (`srkkrs77`) |
| **Файлы не синхронизируются между компьютерами** | Забыл `pull` перед работой |
| **`config.inc.php` не сохраняется** | Он в `.gitignore` — так и должно быть |

### 19.15. Что делать при проблемах с сайтом (прод)

| Проблема | Решение |
|---|---|
| **Страница «поехала» после правок шаблона** | `cache_type = 1`. Сайт → Очистить кэш |
| **SimpleGallery: «не удалось обработать ответ сервера»** | Проверить `cache_snippets`, `cache_plugins`, `cache_chunks` — все `1` |
| **Белая страница при сохранении** | Удалить плагин AutoCacheClear (если создан). Отключить сторонние плагины на `OnDocFormSave` |
| **Белая страница при отправке формы** | Проверить патч FormLister (`Validator.php`, `sanitizeEmail`) |
| **Мусор в `sitemap.xml`** | Проверить TV `sitemap_changefreq`, `sitemap_priority` — должны быть без описаний |
| **Дубли URL в мультиязычном сайте** | У одной папки языка снять галку «Участвует в URL», у другой оставить |

---

**Конец части 19.**

**Конец CONTEXT.**