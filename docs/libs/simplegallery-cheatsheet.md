# Шпаргалка по SimpleGallery и sgLister (Evolution CMS)

## 1. Поля SimpleGallery (Админка → База и плейсхолдеры)

| Поле в админке | Плейсхолдер | Что выводит |
| :--- | :--- | :--- |
| **Название** | `[+sg_title+]` / `[+e.sg_title+]` | Заголовок (`e.` экранирует кавычки для `alt="..."`). |
| **Описание** | `[+sg_description+]` / `[+e.sg_description+]` | Текст описания. |
| **Дополнительно** | `[+sg_add+]` | Дополнительное текстовое поле. |
| **Показывать** | `[+sg_isactive+]` | Статус (`1` — активен, `0` — скрыт; неактивные не выводятся). |
| **Файл** | `[+sg_image+]` | Путь к оригиналу фото (`assets/galleries/3/24.jpg`). |
| **ID** | `[+sg_id+]` | Уникальный ID записи в БД. |
| *(Скрытое)* | `[+sg_index+]` | Внутренний вес сортировки (не использовать как порядковый номер `1, 2, 3`!). |

---

## 2. Главные параметры шаблонов (`sgLister` и `DocLister`)

* **`&ownerTPL`** — общая обертка всего списка (`<div class="...">[+dl.wrap+]</div>`). Если фото нет — обертка не выведется.
* **`&tplFirst`** — шаблон **первого** элемента (удобно для добавления класса `active`).
* **`&tpl`** — шаблон **остальных** элементов.
* **`&tplLast`** — шаблон **последнего** элемента.
* **`&tplOdd` / `&tplEven`** — нечетные / четные элементы.
* **`&noneTPL`** — заглушка, если галерея пуста (работает вместе с `&noneWrapOuter=`0``).
* **`&parents=`15``** — взять галерею с другой страницы (ID `15`).
* **`&display=`5``** — вывести только первые 5 фото.

---

## 3. Системные плейсхолдеры и модификаторы (Особенности сборки)

1. **Счетчик цикла `[+dl.iteration+]` + модификатор `:math`**
   * В **`&tplFirst`** первый элемент уже равен `1`, поэтому используем `[+dl.iteration:math=`?+0`+]`.
   * В **`&tpl`** (для 2-го и следующих элементов) используется `[+dl.iteration:math=`?+1`+]`, чтобы получить `2, 3, 4...`.
2. **`[+dl.total+]`** — общее количество выведенных слайдов.
3. **`[+dl.class+]`** — системные классы (`first`, `last`, `odd`, `even`).
4. **Ограничение `@CODE:`** — HTML-код внутри `&tpl=`@CODE: ...`` работает, а PHP-код внутри `&prepare=`@CODE: ...`` заблокирован безопасностью. Для сложной логики нужен отдельный сниппет.

---

## 4. Проверенный рабочий код слайдера

```html
<!-- 1. Слайды -->
<div class="hero-slides">
    [[sgLister?
        &tplFirst=`@CODE: <div class="hero-slide active"><img src="[+sg_image+]" alt="[+e.sg_title+]"></div>`
        &tpl=`@CODE: <div class="hero-slide"><img src="[+sg_image+]" alt="[+e.sg_title+]"></div>`
    ]]
</div>

<!-- 2. Заголовки слайдов -->
<div class="slider-labels">
    [[sgLister?
        &tplFirst=`@CODE: <span class="active">[+sg_title+]</span>`
        &tpl=`@CODE: <span>[+sg_title+]</span>`
    ]]
</div>

<!-- 3. Нумерация 01, 02, 03 -->
<div class="slider-numbers">
    [[sgLister?
        &tplFirst=`@CODE: <span class="active">0[+dl.iteration:math=`?+0`+]</span><div class="num-line"></div>`
        &tpl=`@CODE: <span>0[+dl.iteration:math=`?+1`+]</span>`
    ]]
</div>

<!-- 4. Точки навигации -->
<div class="slider-dots">
    [[sgLister?
        &tplFirst=`@CODE: <button class="dot active" aria-label="Slide [+dl.iteration:math=`?+0`+]"></button>`
        &tpl=`@CODE: <button class="dot" aria-label="Slide [+dl.iteration:math=`?+1`+]"></button>`
    ]]
</div>

<!-- 5. Стрелки -->
<div class="slider-arrows">
    <button class="arrow prev" aria-label="Zurück">←</button>
    <button class="arrow next" aria-label="Weiter">→</button>
</div>
```

---

## 5. Превьюшки через `phpthumb`

```html
[[sgLister?
    &thumbSnippet=`phpthumb`
    &thumbOptions=`w=400&h=300&zc=1`
    &tpl=`@CODE: <img src="[+thumb.sg_image+]" alt="[+e.sg_title+]">`
]]
```

---

## 6. Расширение полей в окне редактирования SimpleGallery

Чтобы добавить свои поля (например, ссылку или цену) без изменения таблиц БД, их можно сохранять в стандартное JSON-поле `sg_properties`:
1. Скопировать шаблон редактирования из `assets/plugins/simplegallery/tpl/` в свою папку (например, `assets/plugins/simplegallery/custom/tpl/`).
2. Добавить инпуты с именами вида `name="properties[link]"` и значением `value="[+properties.link+]"`.
3. Указать путь к кастомным шаблонам в настройках плагина SimpleGallery и при выводе через `sgLister` использовать `&jsonField=`sg_properties`` для доступа к плейсхолдерам `[+sg_properties.link+]`.
