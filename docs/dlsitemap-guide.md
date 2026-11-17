# Справочник по DLSitemap (VoloLution CMS)

Этот документ описывает правильный, проверенный способ генерации `sitemap.xml` в VoloLution CMS с использованием сниппета `DLSitemap` (из пакета DocLister).

## 1. Правильный вызов сниппета (Golden Standard)

Этот код должен находиться **в содержимом документа** `sitemap.xml` (или в шаблоне, назначенном этому документу). Тип содержимого документа должен быть `text/xml`.

```php
<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
[!DLSitemap?
    &parents=`0`
    &maxDepth=`10`
    &addWhereList=`c.alias NOT IN ('sitemap.xml','de','en','404','404.html')`
    &tvList=`sitemap_priority,sitemap_changefreq`
    &dateFormat=`Y-m-d`
    &tpl=`@CODE:<url><loc>[+url+]</loc><lastmod>[+date+]</lastmod><changefreq>[+update+]</changefreq><priority>[+priority+]</priority></url>`
!]
</urlset>

2. Разбор параметров вызова
Параметр
Значение
Назначение и пояснение
&parents
`0`
Начинать обход дерева документов с корня (ID=0).
&maxDepth
`10`
Максимальная глубина вложенности. 10 достаточно для любого сайта, чтобы захватить все уровни.
&addWhereList
`c.alias NOT IN (...)`
Критически важно. Исключает из карты:
• sitemap.xml (чтобы карта не ссылалась сама на себя)
• de, en (корневые папки-контейнеры языков, чтобы избежать дублей)
• 404, 404.html (страницы ошибок, которые категорически нельзя индексировать).
&tvList
`sitemap_priority,sitemap_changefreq`
Указывает DLSitemap читать значения из наших SEO TV. Если TV заполнены, они переопределяют автоматический расчёт.
&dateFormat
`Y-m-d`
Формат даты для тега <lastmod>. Строго Y-m-d по стандарту sitemaps.org.
&tpl
`@CODE:...`
Шаблон одной строки карты.
Важно: используется плейсхолдер [+update+], а НЕ [+changefreq+]. Это особенность DLSitemap.
3. Критические правила и частые ошибки
3.1. Пространство имён (xmlns)
В теге <urlset> должен быть указан строго http://, а не https://.
✅ Правильно: xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"
❌ Неправильно: xmlns="https://www.sitemaps.org/schemas/sitemap/0.9"
Причина: Стандарт sitemaps.org определяет это как строгий URI-идентификатор схемы, а не как веб-ссылку. Валидаторы Google помечают https:// как ошибку.
3.2. Настройка TV-параметров (sitemap_priority и sitemap_changefreq)
В настройках TV (поле "Возможные значения" / @input_options) порядок должен быть либо просто значения через ||, либо в формате label==value.
✅ Правильно: always||hourly||daily||weekly||monthly||yearly||never
✅ Правильно: Ежемесячно==monthly||Еженедельно==weekly
❌ Неправильно: monthly==Ежемесячно (в БД сохранится слово "Ежемесячно", что сломает XML).
3.3. Кэширование
После любого изменения в вызове DLSitemap или в TV-параметрах документов обязательно очистить кэш:
Админка → Сайт → Очистить кэш.
3.4. Мультиязычность (evoBabel)
Если используется evoBabel, данный вызов корректно обрабатывает документы в папках de и en, так как мы исключили только сами папки-контейнеры (de, en), но не их содержимое. Hreflang-теги генерируются отдельно сниппетом seoVolo.