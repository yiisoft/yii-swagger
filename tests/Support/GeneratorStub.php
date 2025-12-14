<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Swagger\Tests\Support;

use OpenApi\Analysis;
use OpenApi\Annotations as OA;
use OpenApi\Generator;

final class GeneratorStub extends Generator
{
    /** @var array<int,string> */
    public array $receivedDirectories = [];
    public ?Analysis $receivedAnalysis = null;
    public bool $receivedValidate = true;

    public function __construct(private readonly ?OA\OpenApi $result)
    {
        parent::__construct();
    }

    public function generate(iterable $sources, ?Analysis $analysis = null, bool $validate = true): ?OA\OpenApi
    {
        $this->receivedDirectories = is_array($sources) ? array_values($sources) : iterator_to_array($sources, false);
        $this->receivedAnalysis = $analysis;
        $this->receivedValidate = $validate;

        return $this->result;
    }
}
