<?php

use App\Models\User;

test('admin can access filament admin dashboard', function () {
    $admin = User::factory()->admin()->create();

    $response = $this->actingAs($admin)->get('/admin');

    $response->assertSuccessful();
});

test('editor can access filament admin dashboard', function () {
    $editor = User::factory()->editor()->create();

    $response = $this->actingAs($editor)->get('/admin');

    $response->assertSuccessful();
});

test('regular user cannot access filament admin panel', function () {
    $user = User::factory()->user()->create();

    $response = $this->actingAs($user)->get('/admin');

    $response->assertForbidden();
});

test('admin can access product list in filament', function () {
    $admin = User::factory()->admin()->create();

    $response = $this->actingAs($admin)->get('/admin/products');

    $response->assertSuccessful();
});

test('editor can access product list in filament', function () {
    $editor = User::factory()->editor()->create();

    $response = $this->actingAs($editor)->get('/admin/products');

    $response->assertSuccessful();
});

test('admin can access product create page in filament', function () {
    $admin = User::factory()->admin()->create();

    $response = $this->actingAs($admin)->get('/admin/products/create');

    $response->assertSuccessful();
});

test('editor cannot access product create page in filament', function () {
    $editor = User::factory()->editor()->create();

    $response = $this->actingAs($editor)->get('/admin/products/create');

    $response->assertForbidden();
});

test('admin can access tag list in filament', function () {
    $admin = User::factory()->admin()->create();

    $response = $this->actingAs($admin)->get('/admin/tags');

    $response->assertSuccessful();
});

test('editor can access tag list in filament', function () {
    $editor = User::factory()->editor()->create();

    $response = $this->actingAs($editor)->get('/admin/tags');

    $response->assertSuccessful();
});
