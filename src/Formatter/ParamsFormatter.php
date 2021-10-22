<?php

declare(strict_types=1);

namespace Yiisoft\Swagger\Formatter;

use Yiisoft\Arrays\ArrayHelper;
use Yiisoft\Html\Html;

final class ParamsFormatter
{
    public function format(array $params)
    {
        if (ArrayHelper::isAssociative($params)) {
            $result = [];

            foreach ($params as $key => $value) {
                $element = Html::encode($key) . ':';

                if (is_array($value)) {
                    $result[] = $element . $this->format($value);
                    continue;
                }

                $result[] = $element . '"' . Html::encode($value) . '"';
            }

            return '{' . implode(', ', $result) . '}';
        }

        $result = array_map([Html::class, 'encode'], $params);

        return '[' . implode(', ', $result) . ']';
    }
}
