<?php

declare(strict_types=1);

namespace Yiisoft\Swagger\Formatter;

use Yiisoft\Arrays\ArrayHelper;
use Yiisoft\Html\Html;

final class ParamsFormatter
{
    public function format(array $params): string
    {
        if (ArrayHelper::isAssociative($params)) {
            $result = [];

            $keys = array_keys($params);
            foreach ($keys as $key) {
                $element = Html::encode($key) . ':';

                if (is_array($params[$key])) {
                    $result[] = $element . $this->format($params[$key]);
                    continue;
                }

                $result[] = $element . '"' . Html::encode((string)$params[$key]) . '"';
            }

            return '{' . implode(', ', $result) . '}';
        }

        $result = array_map([Html::class, 'encode'], $params);

        return '[' . implode(', ', $result) . ']';
    }
}
