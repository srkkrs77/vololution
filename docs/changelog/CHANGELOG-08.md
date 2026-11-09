CHANGELOG-08.md — Раздел 5–7
Часть 8 из 14. Пакеты без правок + окружение + структура проекта.

markdown
## РАЗДЕЛ 5. ПАКЕТЫ БЕЗ ПРАВОК (10)

Эти пакеты установлены и работают **без правок** — ни в ядре, ни в самом пакете.

| # | Пакет | Тип | Назначение |
|---|---|---|---|
| 1 | DocLister | сниппет | Универсальный вывод документов |
| 2 | FormLister | сниппет | Обработка форм |
| 3 | SimpleGallery | плагин | Простая галерея изображений |
| 4 | EvoBabel | плагин | Мультиязычность |
| 5 | evoFileManagerDialog | плагин | Диалог файлового менеджера |
| 6 | FirstChildRedirect | сниппет | Редирект родителя на первого потомка |
| 7 | MonthDate | сниппет | Вывод даты по-русски |
| 8 | WebP Converter | плагин | Автоконвертация JPG/PNG в WebP |
| 9 | DLSiblings | сниппет | Вывод соседних документов (prev/next) |
| 10 | phpThumb | библиотека | Обработка изображений |

**Всего:** 10 пакетов. Все — из репозитория `github.com/srkkrs77/vololution/packages`.

### 5.1. Проблема SimpleGallery на проде — 10.10.2026

**Сайт:** `studio-milstein.de`  
**Симптом:** при удалении ресурса в админке:
Table 'dbs16208630.eh2v_sg_images' doesn't exist
SQL > DELETE FROM dbs16208630.eh2v_sg_images WHERE sg_rid IN (1)

text

**Причина:** при установке SimpleGallery на хостинге не создалась таблица `sg_images`. Установщик пакета не выполнил SQL — точная причина не выяснена (права БД, версия MySQL, синтаксис).

**Временное решение (применено):** таблица создана вручную SQL-запросом.

**Стратегическое решение:** **SchemaGuard** — плагин на `OnManagerPageInit`, проверяет и создаёт отсутствующие таблицы сторонних пакетов. Пакеты сторонних авторов (Pathologic, Grinyaha и т.д.) **не патчим** — сторож работает снаружи. См. `docs/roadmap.md`, этап 0.3.

### 5.2. SimpleGallery и CSRF-JS — 11.10.2026

**Симптом:** при открытии вкладки «Галерея» в форме документа:
Server-Fehler: 0
InvalidStateError: Failed to execute 'setRequestHeader' on 'XMLHttpRequest':
The object's state must be OPENED.

text

**Причина:** сломанный CSRF-JS-блок в `header.inc.php` (см. `CHANGELOG-19`). Два блока перехватывали XHR; второй вызывал `setRequestHeader` в `open` до `origOpen.apply()`.

**Решение:** блоки объединены в один, `setRequestHeader` вызывается в `send`. См. раздел 19.

**Важно:** это не проблема SimpleGallery — это была наша проблема.

---

## РАЗДЕЛ 6. ОКРУЖЕНИЕ

| Параметр | Значение |
|---|---|
| **ОС** | Windows |
| **Среда** | Laragon |
| **PHP** | 8.5.10 |
| **MySQL** | 8.0.30 (Laragon 6 не поддерживает 8.4) |
| **Веб-сервер** | nginx |
| **Конфиг nginx** | `C:\laragon\etc\nginx\sites-enabled\vol.test.conf` |
| **Локальный сайт** | `https://vol.test/` |
| **Локальная БД** | `vol`, префикс `gyl8_` |
| **Прод** | `studio-milstein.de` |
| **Прод БД** | `dbs16208630`, префикс `eh2v_` |

**Про strict mode:** предупреждение в installer не блокирует установку.

---

## РАЗДЕЛ 7. СТРУКТУРА ПРОЕКТА

### 7.1. Вспомогательные папки (локально)
_my_extras/
├── core/
├── optional/
├── custom/
└── legacy/

_my_patches/
├── README.md
└── CHANGELOG.md

text

### 7.2. Репозиторий на GitHub
github.com/srkkrs77/vololution
├── packages/ ← каталог Store (catalog.json + 16 ZIP)
├── extras-module/ ← собственные модули (план)
└── dist/ ← готовые сборки (план)

text

### 7.3. После форка (целевая структура)
vololution/ ← форк evolution/evolution
├── src/ ← НОВОЕ: PSR-4 код, namespace Vololution
├── manager/ ← ядро Evo
├── assets/ ← extras, модули, сниппеты
├── install/ ← установщик
├── docs/ ← документация
│ ├── context/ ← 19 частей CONTEXT + индекс
│ ├── patches-manifest.md
│ ├── roadmap.md
│ ├── CE-community.md
│ └── seoVolo-guide.md
├── packages/ ← каталог Store
├── vendor/ ← composer (в .gitignore)
├── composer.json ← НОВОЕ
├── .gitignore ← НОВОЕ / расширенное
├── CHANGELOG.md
├── CONTEXT.md ← индекс на 19 частей
└── README.md

text

### 7.4. Три копии проекта

| Копия | Путь | Назначение |
|---|---|---|
| **Рабочая** | `C:\laragon\www\vol.test\` | Тестирование, эксперименты |
| **Эталон (дистрибутив)** | `D:\VoloLution-CMS-1.4.37\` | Готовый дистрибутив для развёртывания |
| **Форк на GitHub** | `github.com/srkkrs77/vololution` | Источник истины (план) |

**ВАЖНО:** пользователь правит **в двух локальных местах + форке.** Когда выдаёшь файл — указывай пути в обоих или уточняй.

---
Конец части 8.