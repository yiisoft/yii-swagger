<?php

declare(strict_types=1);

namespace Yiisoft\Swagger\Formatter;

use Yiisoft\Arrays\ArrayHelper;
use Yiisoft\Html\Html;

use function array_keys;
use function array_map;
use function implode;
use function is_array;

final class ParamsFormatter
{
    public function format(array $params): string
    {
        if (ArrayHelper::isAssociative($params)) {
            $keys = array_keys($params);
            $result = [];

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

        $result = array_map(Html::encode(...), $params);

        return '[' . implode(', ', $result) . ']';
    }
}
