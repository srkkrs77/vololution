CONTEXT-03.md — Структура проекта
Часть 3 из 19. Читать после CONTEXT.md, CONTEXT-01.md, CONTEXT-02.md.

3. СТРУКТУРА ПРОЕКТА
3.1. Три места, где живёт проект
Копия	Путь	Назначение
Рабочая	C:\laragon\www\vol.test\	Тестирование, эксперименты
Эталон (дистрибутив)	D:\VoloLution-CMS-1.4.37\	Готовый дистрибутив для развёртывания
Форк на GitHub	github.com/srkkrs77/vololution	Источник истины, своя ветка
ВАЖНО: пользователь правит в двух локальных местах + форке. Когда даёшь файл — указывай путь в обоих локальных или уточняй.

Пути указывать от корневых папок assets/, manager/, install/, src/. Если файл в самом верху (рядом с assets/, manager/, install/) — просто /.

3.2. Целевая структура репозитория (после форка)
text
vololution/                 ← форк evolution/evolution
├── src/                    ← НОВОЕ: PSR-4 код, namespace Vololution\
├── manager/                ← ядро Evo (постепенно рефакторим)
├── assets/                 ← extras, модули, сниппеты
├── install/                ← установщик
├── docs/                   ← документация
│   ├── context/            ← 19 частей CONTEXT + индекс
│   ├── patches-manifest.md
│   ├── roadmap.md
│   ├── CE-community.md
│   └── seoVolo-guide.md
├── packages/               ← каталог Store (catalog.json + ZIP)
├── vendor/                 ← composer (в .gitignore, не в git)
├── composer.json           ← НОВОЕ
├── .gitignore              ← НОВОЕ / расширенное
├── CHANGELOG.md
├── CONTEXT.md              ← индекс на 19 частей
└── README.md
3.3. Структура дистрибутива (сборка)
text
D:\VoloLution-CMS-1.4.37
├── assets/
│   ├── modules/
│   │   ├── store/          ← Extras-Store
│   │   └── seoVolo/        ← SEO-модуль настроек
│   ├── plugins/
│   │   └── seoVolo/        ← SEO-плагин
│   └── snippets/
│       └── seoVolo/        ← SEO-сниппет
├── install/                ← папка установщика CMS
│   ├── setup.sql           ← SQL ядра
│   ├── setup.info.php      ← патчен
│   ├── instprocessor.php   ← патчен
│   ├── config.inc.tpl      ← CSRF-функции
│   └── assets/
│       ├── chunks/         ← head.tpl, mm_rules.tpl
│       ├── modules/        ← seoVolo.tpl, store.tpl
│       ├── plugins/        ← seoVolo.tpl
│       ├── snippets/       ← seoVolo.tpl
│       └── tvs/            ← 11 × seo_*.tpl
├── manager/                ← ядро (13 патчей)
├── docs/                   ← документация
├── CHANGELOG.md
└── CONTEXT.md
3.4. Рабочая копия (vol.test)
Структура идентична распакованному релизу Evo + наши правки:

manager/ — ядро с 13 патчами

assets/ — пакеты, Store, seoVolo

install/ — установщик (нужен для переустановки)

docs/ — документация

CHANGELOG.md, CONTEXT.md — в корне

3.5. Что где не должно быть
vendor/ — в .gitignore, не коммитится.

assets/cache/ — в .gitignore, не коммитится.

manager/includes/config.inc.php — в .gitignore, не коммитится (содержит пароли БД).

docs/context/ — коммитится, чтобы в новой сессии можно было загрузить части.

Конец части 3.