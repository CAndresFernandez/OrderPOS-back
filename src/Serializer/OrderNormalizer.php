<?php

namespace App\Serializer;

use App\Entity\Order;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

class OrderNormalizer implements NormalizerInterface
{   
    private ObjectNormalizer $normalizer;
    public function __construct(
        ObjectNormalizer $normalizer
    ){
        $this->normalizer = $normalizer;
    }

    public function normalize($order, string $format = null, array $context = []): array
    {
        $data = $this->normalizer->normalize($order, $format, $context);

        // Here, add, edit, or delete some data:
        if (array_key_exists('orderItems', $data)) {
            // force orderItems to be an array
            $data['orderItems'] = array_values($data['orderItems']);
        }
        // dd($data, $order);
        return $data;
    }

    public function supportsNormalization($data, string $format = null, array $context = []): bool
    {
        return $data instanceof Order;
    }
}