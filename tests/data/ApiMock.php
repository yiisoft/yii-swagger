<?php

namespace Yiisoft\Tests\Data;

/**
 * @OA\Info(title="Yii test api", version="1.0")
 */
class ApiMock
{
    /**
     * @OA\Get(
     *     path="/api/test",
     *     @OA\Response(response="200", description="Test api response")
     * )
     */
    public function mockResponse()
    {
    }
}
