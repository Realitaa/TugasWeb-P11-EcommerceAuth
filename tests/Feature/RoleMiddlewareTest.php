<?php

use App\Models\User;
use Illuminate\Support\Facades\Route;

beforeEach(function () {
    Route::get('/test-role-admin', fn () => response()->json(['status' => 'ok']))
        ->middleware(['auth', 'role:admin']);

    Route::get('/test-role-editor', fn () => response()->json(['status' => 'ok']))
        ->middleware(['auth', 'role:editor']);

    Route::get('/test-role-multi', fn () => response()->json(['status' => 'ok']))
        ->middleware(['auth', 'role:admin,editor']);
});

test('unauthenticated guest cannot access role-protected route', function () {
    $response = $this->getJson('/test-role-admin');

    $response->assertStatus(401);
});

test('regular user cannot access admin-only route', function () {
    $user = User::factory()->user()->create();

    $response = $this->actingAs($user)->getJson('/test-role-admin');

    $response->assertStatus(403);
});

test('editor cannot access admin-only route', function () {
    $editor = User::factory()->editor()->create();

    $response = $this->actingAs($editor)->getJson('/test-role-admin');

    $response->assertStatus(403);
});

test('admin can access admin-only route', function () {
    $admin = User::factory()->admin()->create();

    $response = $this->actingAs($admin)->getJson('/test-role-admin');

    $response->assertStatus(200)
        ->assertJson(['status' => 'ok']);
});

test('editor can access editor-only route', function () {
    $editor = User::factory()->editor()->create();

    $response = $this->actingAs($editor)->getJson('/test-role-editor');

    $response->assertStatus(200)
        ->assertJson(['status' => 'ok']);
});

test('admin and editor can access multi-role route', function () {
    $admin = User::factory()->admin()->create();
    $editor = User::factory()->editor()->create();
    $user = User::factory()->user()->create();

    $this->actingAs($admin)->getJson('/test-role-multi')
        ->assertStatus(200);

    $this->actingAs($editor)->getJson('/test-role-multi')
        ->assertStatus(200);

    $this->actingAs($user)->getJson('/test-role-multi')
        ->assertStatus(403);
});
