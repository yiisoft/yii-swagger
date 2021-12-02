<?php

declare(strict_types=1);

namespace Yiisoft\Swagger\Tests\Support;

use Yiisoft\Csrf\Synchronizer\Storage\CsrfTokenStorageInterface;

final class CsrfTokenStorage implements CsrfTokenStorageInterface
{
    private ?string $token = null;

    public function get(): ?string
    {
        return $this->token;
    }

    public function set(string $token): void
    {
        $this->token = $token;
    }

    public function remove(): void
    {
        $this->token = null;
    }
}
