<?php

namespace Tests\Feature\Purchase;

use App\Models\User;
use Tests\TestCase;

class PurchaseMenuTest extends TestCase
{
    private function makeUser(string $role): User
    {
        $user = new User([
            'userid' => '650001',
            'name' => 'Test User',
            'position' => 'Staff',
            'department' => 'ฝ่ายจัดซื้อ',
            'division' => 'ฝ่ายจัดซื้อ',
        ]);
        $user->role = $role;

        return $user;
    }

    public function test_purchase_role_menu_contains_purchase_items(): void
    {
        $menu = $this->makeUser('purchase')->menu;

        $this->assertEmpty($menu['groups']);
        $this->assertNotEmpty($menu['lists']);
        $this->assertTrue(collect($menu['lists'])->contains(fn (array $item): bool => ($item['link'] ?? null) === 'admin.purchase.newlist'));
        $this->assertTrue(collect($menu['count'])->contains(fn (array $item): bool => ($item['route'] ?? null) === 'admin.purchase.count'));
    }

    public function test_admin_role_menu_uses_full_group_selector(): void
    {
        $menu = $this->makeUser('admin')->menu;
        $groupKeys = collect($menu['groups'])->pluck('key')->all();

        $this->assertEmpty($menu['lists']);
        $this->assertSame(
            ['admin', 'it', 'purchase', 'media', 'pac', 'lab', 'heartstream', 'register'],
            $groupKeys
        );
    }
}
