<?php


namespace App\Service\Provider;

use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * SerializerProvider file description here.
 *
 * @package    SerializerProvider
 * @copyright  2023 Carlitosry <reyes.syscom@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class SerializerProvider
{
    public function create(): SerializerInterface
    {
        $encoders = [new JsonEncoder()];
        $normalizers = [new ObjectNormalizer()];
        return new Serializer($normalizers, $encoders);
    }

}
