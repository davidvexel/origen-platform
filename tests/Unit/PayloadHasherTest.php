<?php

namespace Tests\Unit;

use App\Domain\Sales\Support\PayloadHasher;
use PHPUnit\Framework\TestCase;

class PayloadHasherTest extends TestCase
{
    public function test_object_key_order_does_not_change_hash(): void
    {
        $first = ['ticket' => 10, 'totals' => ['tax' => 1, 'total' => 10]];
        $second = ['totals' => ['total' => 10, 'tax' => 1], 'ticket' => 10];

        $this->assertSame(PayloadHasher::hash($first), PayloadHasher::hash($second));
    }

    public function test_list_order_changes_hash(): void
    {
        $first = ['items' => [['product_id' => 'A'], ['product_id' => 'B']]];
        $second = ['items' => [['product_id' => 'B'], ['product_id' => 'A']]];

        $this->assertNotSame(PayloadHasher::hash($first), PayloadHasher::hash($second));
    }
}
