<?php

use App\Models\User;

it('shows the operational store overview to authorized admin staff', function (): void {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $this->actingAs($admin)
        ->get('/admin')
        ->assertOk()
        ->assertSee('Admin workspace')
        ->assertSee('Welcome back,')
        ->assertSee('Manage orders')
        ->assertSee('View catalog')
        ->assertSee('.djh-welcome__content', false)
        ->assertSee('.djh-operations', false)
        ->assertSee('grid-template-columns', false)
        ->assertSee('Store overview')
        ->assertSee("Today's orders")
        ->assertSee('Pending orders')
        ->assertSee('This month revenue')
        ->assertSee('Low-stock variants')
        ->assertSee('Sales — last 7 days')
        ->assertSee('Recent orders')
        ->assertSee('Low-stock inventory')
        ->assertSee('Best sellers — last 30 days');
});

it('does not expose dashboard widgets to customer accounts', function (): void {
    $customer = User::factory()->create();
    $customer->assignRole('customer');

    $this->actingAs($customer)
        ->get('/admin')
        ->assertForbidden();
});
