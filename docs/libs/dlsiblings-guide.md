# 📖 DLSiblings — полный справочник

**Версия:** 2.1 (от Aharito, на основе DLPrevNext от Agel_Nash)  
**Файл сниппета:** `assets/snippets/DLSiblings/snippet.DLSiblings.php`  
**Назначение:** вывод соседних ресурсов с шаблонизацией (множественная кольцевая перелинковка)

---

## 0. Как читать справочник

DLSiblings — обёртка над DocLister. Всё, что принимает DocLister, принимает и DLSiblings. Плюс три своих параметра.

Раздел 1 — минимум для работы.  
Раздел 2 — свои параметры.  
Раздел 3 — параметры DocLister.  
Раздел 4 — плейсхолдеры.  
Раздел 5 — рецепты.  
Раздел 6 — кольцевая перелинковка.  
Раздел 7 — типичные ошибки.

---

## 1. Минимум для работы

**Правило №1:** у DLSiblings **нет** параметров `&prev=` и `&next=`. Есть `&prevQty=` и `&nextQty=` — **количество** соседей в каждую сторону.

**Правило №2:** по умолчанию `&prevQty=2` и `&nextQty=2`. Всегда указывай оба явно, включая `0`.

**Минимальный вызов:**

```
[[DLSiblings?
    &idType=`parents`
    &parents=`[*parent*]`
    &prevQty=`1`
    &nextQty=`1`
    &tpl=`@CODE:<a href="[+url+]">[+title+]</a>`
]]
```

**Что произойдёт:**
- Возьмёт всех потомков родителя текущего документа.
- Найдёт среди них текущий.
- Вернёт по одному соседу в каждую сторону.
- Обернёт каждого в шаблон.

---

## 2. Свои параметры DLSiblings

| Параметр | По умолчанию | Что |
|---|---|---|
| `&renderSnippet` | `DocLister` | Сниппет для вывода (обычно не трогать) |
| `&prevQty` | `2` | Кол-во предшественников |
| `&nextQty` | `2` | Кол-во последователей |

### 2.1. `&prevQty`

Сколько документов **до** текущего вернуть.

```
&prevQty=`1`    ← один предыдущий
&prevQty=`0`    ← не возвращать предыдущих
&prevQty=`3`    ← три предыдущих
```

### 2.2. `&nextQty`

Сколько документов **после** текущего вернуть.

```
&nextQty=`1`    ← один следующий
&nextQty=`0`    ← не возвращать следующих
&nextQty=`3`    ← три следующих
```

### 2.3. `&renderSnippet`

**Обычно не трогать.** Оставь `DocLister`. Менять только если используешь другой рендер.

---

## 3. Параметры DocLister для DLSiblings

Все параметры DocLister работают. Ключевые:

### 3.1. `&idType` — откуда брать группу

| Значение | Что |
|---|---|
| `parents` | Потомки указанного родителя (стандарт) |
| `documents` | Конкретные документы по ID |
| `siblings` | Братья по родителю (то же, что parents) |

**Для портфолио — `parents`.**

```
&idType=`parents`
&parents=`[*parent*]`
```

### 3.2. `&parents` — родитель

ID документа, чьих детей перебираем.

```
&parents=`[*parent*]`
```

**`[*parent*]`** — TV-плейсхолдер ID родителя текущего документа. Работает в шаблоне.

### 3.3. `&orderBy` — порядок

По умолчанию — `menuindex ASC`. Можно изменить.

```
&orderBy=`menuindex DESC`
&orderBy=`pagetitle ASC`
&orderBy=`editedon DESC`
```

### 3.4. `&addWhereList` — SQL-фильтр

Работает с полями `site_content` (алиас `c`).

```
&addWhereList=`c.template = 7`
```
Только документы с шаблоном 7.

```
&addWhereList=`c.published = 1 AND c.deleted = 0`
```
Обычно не нужно — DocLister фильтрует сам.

### 3.5. `&tvList` — какие TV подтянуть

```
&tvList=`image,alt,caption`
```

TV доступны в шаблоне как `[+image+]`, `[+alt+]`, `[+caption+]`.

### 3.6. `&totalRows` — ограничение

Не для соседей, а для другого. Обычно не используется.

---

## 4. Плейсхолдеры в `&tpl`

Доступны в шаблоне соседа:

| Плейсхолдер | Что |
|---|---|
| `[+url+]` | Полный URL документа |
| `[+id+]` | ID документа |
| `[+title+]` | Заголовок (`pagetitle`) |
| `[+pagetitle+]` | То же |
| `[+menutitle+]` | Заголовок для меню |
| `[+longtitle+]` | Расширенный заголовок |
| `[+introtext+]` | Описание |
| `[+content+]` | Контент |
| `[+image+]` | TV `image` (если в `&tvList=`) |
| `[+date+]` | Дата |
| `[+любое_поле+]` | Поля `site_content` |
| `[+любая_tv+]` | TV (если в `&tvList=`) |

---

## 5. Рецепты

### 5.1. Кнопки «Previous / Next» для карточки

```
<nav class="project-nav">
    [[DLSiblings?
        &idType=`parents`
        &parents=`[*parent*]`
        &prevQty=`1`
        &nextQty=`0`
        &tpl=`@CODE:<a href="[+url+]" class="pn-prev">← Previous Project</a>`
    ]]
    [[DLSiblings?
        &idType=`parents`
        &parents=`[*parent*]`
        &prevQty=`0`
        &nextQty=`1`
        &tpl=`@CODE:<a href="[+url+]" class="pn-next">Next Project →</a>`
    ]]
</nav>
```

### 5.2. Со ссылкой «All Projects» между кнопками

```
<nav class="project-nav">
    [[DLSiblings?
        &idType=`parents`
        &parents=`[*parent*]`
        &prevQty=`1`
        &nextQty=`0`
        &tpl=`@CODE:<a href="[+url+]" class="pn-prev">← Previous Project</a>`
    ]]
    <a href="projects.html" class="pn-all">All Projects</a>
    [[DLSiblings?
        &idType=`parents`
        &parents=`[*parent*]`
        &prevQty=`0`
        &nextQty=`1`
        &tpl=`@CODE:<a href="[+url+]" class="pn-next">Next Project →</a>`
    ]]
</nav>
```

### 5.3. С картинками-превью соседей

```
[[DLSiblings?
    &idType=`parents`
    &parents=`[*parent*]`
    &prevQty=`1`
    &nextQty=`1`
    &tvList=`image,alt`
    &tpl=`@CODE:<a href="[+url+]" class="sibling-card"><img src="[+image+]" alt="[+alt+]"><span>[+title+]</span></a>`
]]
```

### 5.4. Список всех соседей (сортировка по menuindex)

```
[[DLSiblings?
    &idType=`parents`
    &parents=`[*parent*]`
    &prevQty=`5`
    &nextQty=`5`
    &tpl=`@CODE:<li><a href="[+url+]">[+title+]</a></li>`
    &ownerTPL=`@CODE:<ul>[+dl.wrap+]</ul>`
]]
```

`&ownerTPL` — обёртка всего списка.

### 5.5. Только определённые шаблоны

```
[[DLSiblings?
    &idType=`parents`
    &parents=`[*parent*]`
    &prevQty=`1`
    &nextQty=`1`
    &addWhereList=`c.template = 7`
    &tpl=`@CODE:<a href="[+url+]">[+title+]</a>`
]]
```

### 5.6. Сортировка по дате

```
[[DLSiblings?
    &idType=`parents`
    &parents=`[*parent*]`
    &prevQty=`1`
    &nextQty=`1`
    &orderBy=`editedon DESC`
    &tpl=`@CODE:<a href="[+url+]">[+title+]</a>`
]]
```

---

## 6. Кольцевая перелинковка

**Встроена в сниппет.** Работает по кругу:

- **У первого элемента** `prev` = последний элемент.
- **У последнего элемента** `next` = первый элемент.

**Отключить нельзя.** Это by design.

**Для портфолио — плюс.** Клиент листает проекты по кругу, не упирается в тупик.

**Пример:**

Проекты: `A → B → C → D`

- На `A`: prev = `D`, next = `B`.
- На `B`: prev = `A`, next = `C`.
- На `D`: prev = `C`, next = `A`.

---

## 7. Типичные ошибки

### 7.1. Выводит больше соседей, чем нужно

**Причина:** не указан `&prevQty=` или `&nextQty=`. Сработал дефолт `2`.

**Решение:** указывать оба параметра **всегда**.

```
&prevQty=`1`
&nextQty=`0`
```

### 7.2. Ничего не выводит

**Причины:**
1. `&parents=` указывает на несуществующий ID.
2. Родитель имеет только одного потомка (текущий) — соседей нет.
3. `&addWhereList` отфильтровал всё.

**Решение:**
- Проверить `[*parent*]` — вывести его значение в шаблоне.
- Проверить, есть ли у родителя другие потомки.
- Убрать фильтр временно.

### 7.3. Ссылки ведут не туда

**Причина:** соседями становятся документы из другого раздела, если `&parents` указывает не туда.

**Решение:** проверить `&parents`. Обычно должно быть `[*parent*]` — родитель текущего документа.

### 7.4. Пустые ссылки (без title)

**Причина:** у соседа пустой `pagetitle` или `menutitle`.

**Решение:** заполнить заголовки у всех документов раздела.

### 7.5. `<` вместо стрелки в HTML

**Причина:** HTML-энтити в шаблоне не экранированы.

**Решение:** использовать `&larr;` и `&rarr;` вместо `←` и `→`, или вставить UTF-8 символы напрямую.

```
&tpl=`@CODE:<a href="[+url+]">← Previous</a>`
```
Заменить на:
```
&tpl=`@CODE:<a href="[+url+]">&larr; Previous</a>`
```

---

## 8. Отличия от похожих сниппетов

| Сниппет | Что делает |
|---|---|
| **DLSiblings** | Соседи документа (prev/next), с кольцевой перелинковкой |
| **DLPrevNext** | Только prev/next, без шаблонов |
| **DLSitemap** | Sitemap.xml |
| **DLMenu** | Меню из документов |
| **DLLast** | Последние документы |

**DLSiblings — самый гибкий.** Работает с шаблонами, множественные соседи, кольцевая перелинковка.

---

## 9. Полный рабочий пример для портфолио

```
<nav class="project-nav">
    [[DLSiblings?
        &idType=`parents`
        &parents=`[*parent*]`
        &prevQty=`1`
        &nextQty=`0`
        &tpl=`@CODE:<a href="[+url+]" class="pn-prev">&larr; Previous Project</a>`
    ]]
    <a href="projects.html" class="pn-all">All Projects</a>
    [[DLSiblings?
        &idType=`parents`
        &parents=`[*parent*]`
        &prevQty=`0`
        &nextQty=`1`
        &tpl=`@CODE:<a href="[+url+]" class="pn-next">Next Project &rarr;</a>`
    ]]
</nav>
```

**Работает:** навигация по проектам в разделе «Projects» (`/de/projects/` или `/en/projects/`).

---

**Конец справочника.**