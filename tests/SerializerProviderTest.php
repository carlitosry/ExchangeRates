<?php

namespace App\Tests;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\SerializerInterface;
use App\Service\Provider\SerializerProvider;

class SerializerProviderTest extends TestCase
{
    public function testCreate(): void
    {
        $serializerProvider = new SerializerProvider();
        $result = $serializerProvider->create();
        $this->assertInstanceOf(SerializerInterface::class, $result);
    }
}
