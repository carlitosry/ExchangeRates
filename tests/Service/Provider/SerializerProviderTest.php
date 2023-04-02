<?php

namespace App\Tests\Service\Provider;

use App\Service\Provider\SerializerProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\SerializerInterface;

class SerializerProviderTest extends TestCase
{
    public function testCreate(): void
    {
        $serializerProvider = new SerializerProvider();
        $result = $serializerProvider->create();
        $this->assertInstanceOf(SerializerInterface::class, $result);
    }
}
