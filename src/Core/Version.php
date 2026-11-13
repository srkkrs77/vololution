<?php

declare(strict_types=1);

namespace Vololution\Core;

/**
 * Класс для управления версией системы.
 * Class for managing system version.
 */
class Version
{
    /**
     * Возвращает текущую версию VoloLution CMS.
     * Returns the current VoloLution CMS version.
     */
    public static function get(): string
    {
        return '1.4.37-vololution';
    }
}