<?php

use App\Models\Category;
use App\Models\Product;
use App\Models\Tag;
use App\Models\User;

test('guest cannot access products api', function () {
    $response = $this->getJson('/products');

    $response->assertStatus(401);
});

test('regular user cannot access products api', function () {
    $user = User::factory()->user()->create();

    $response = $this->actingAs($user)->getJson('/products');

    $response->assertStatus(403);
});

test('admin and editor can list products', function () {
    $admin = User::factory()->admin()->create();
    $editor = User::factory()->editor()->create();
    $category = Category::factory()->create();
    Product::factory()->count(3)->create(['category_id' => $category->id, 'user_id' => $admin->id]);

    $this->actingAs($admin)->getJson('/products')
        ->assertStatus(200)
        ->assertJsonStructure(['data' => [['id', 'name', 'price', 'stock', 'formatted_price']]]);

    $this->actingAs($editor)->getJson('/products')
        ->assertStatus(200)
        ->assertJsonStructure(['data' => [['id', 'name', 'price', 'stock', 'formatted_price']]]);
});

test('admin can create a product with tags', function () {
    $admin = User::factory()->admin()->create();
    $category = Category::factory()->create();
    $tags = Tag::factory()->count(2)->create();

    $payload = [
        'name' => 'Produk Baru Unggulan',
        'sku' => 'PROD-NEW-001',
        'category_id' => $category->id,
        'price' => 150000,
        'stock' => 25,
        'description' => 'Deskripsi produk baru unggulan.',
        'discount_percentage' => 10,
        'rating' => 4.5,
        'thumbnail' => 'https://picsum.photos/640/480',
        'tags' => $tags->pluck('id')->toArray(),
    ];

    $response = $this->actingAs($admin)->postJson('/products', $payload);

    $response->assertStatus(201)
        ->assertJsonPath('data.name', 'Produk Baru Unggulan')
        ->assertJsonPath('data.sku', 'PROD-NEW-001')
        ->assertJsonPath('data.price', 150000)
        ->assertJsonPath('data.stock', 25)
        ->assertJsonPath('data.formatted_price', 'Rp 150.000,00');

    $this->assertDatabaseHas('products', [
        'name' => 'Produk Baru Unggulan',
        'sku' => 'PROD-NEW-001',
        'user_id' => $admin->id,
    ]);

    $productId = $response->json('data.id');
    $product = Product::find($productId);
    expect($product->tags)->toHaveCount(2);
});

test('editor cannot create a product', function () {
    $editor = User::factory()->editor()->create();
    $category = Category::factory()->create();

    $payload = [
        'name' => 'Produk Ilegal Editor',
        'sku' => 'PROD-ILLEGAL',
        'category_id' => $category->id,
        'price' => 50000,
        'stock' => 10,
    ];

    $response = $this->actingAs($editor)->postJson('/products', $payload);

    $response->assertStatus(403);
    $this->assertDatabaseMissing('products', ['sku' => 'PROD-ILLEGAL']);
});

test('admin can update all product fields', function () {
    $admin = User::factory()->admin()->create();
    $category = Category::factory()->create();
    $product = Product::factory()->create([
        'category_id' => $category->id,
        'user_id' => $admin->id,
        'name' => 'Original Name',
        'price' => 100000,
        'stock' => 10,
    ]);

    $updatePayload = [
        'name' => 'Updated by Admin',
        'price' => 200000,
        'stock' => 50,
        'description' => 'Updated description.',
    ];

    $response = $this->actingAs($admin)->putJson("/products/{$product->id}", $updatePayload);

    $response->assertStatus(200)
        ->assertJsonPath('data.name', 'Updated by Admin')
        ->assertJsonPath('data.price', 200000)
        ->assertJsonPath('data.stock', 50);

    $this->assertDatabaseHas('products', [
        'id' => $product->id,
        'name' => 'Updated by Admin',
        'price' => 200000,
        'stock' => 50,
    ]);
});

test('editor can update price, stock and assign tags', function () {
    $admin = User::factory()->admin()->create();
    $editor = User::factory()->editor()->create();
    $category = Category::factory()->create();
    $product = Product::factory()->create([
        'category_id' => $category->id,
        'user_id' => $admin->id,
        'name' => 'Stay Original',
        'price' => 100000,
        'stock' => 10,
    ]);
    $newTags = Tag::factory()->count(2)->create();

    $updatePayload = [
        'price' => 125000,
        'stock' => 35,
        'tags' => $newTags->pluck('id')->toArray(),
    ];

    $response = $this->actingAs($editor)->putJson("/products/{$product->id}", $updatePayload);

    $response->assertStatus(200)
        ->assertJsonPath('data.price', 125000)
        ->assertJsonPath('data.stock', 35);

    $this->assertDatabaseHas('products', [
        'id' => $product->id,
        'name' => 'Stay Original',
        'price' => 125000,
        'stock' => 35,
    ]);

    expect($product->fresh()->tags)->toHaveCount(2);
});

test('editor cannot update prohibited fields like name or sku', function () {
    $admin = User::factory()->admin()->create();
    $editor = User::factory()->editor()->create();
    $category = Category::factory()->create();
    $product = Product::factory()->create([
        'category_id' => $category->id,
        'user_id' => $admin->id,
        'name' => 'Forbidden Change',
    ]);

    $updatePayload = [
        'name' => 'Changed by Editor',
        'price' => 99000,
    ];

    $response = $this->actingAs($editor)->putJson("/products/{$product->id}", $updatePayload);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['name']);

    $this->assertDatabaseHas('products', [
        'id' => $product->id,
        'name' => 'Forbidden Change',
    ]);
});

test('admin can delete a product', function () {
    $admin = User::factory()->admin()->create();
    $category = Category::factory()->create();
    $product = Product::factory()->create([
        'category_id' => $category->id,
        'user_id' => $admin->id,
    ]);

    $response = $this->actingAs($admin)->deleteJson("/products/{$product->id}");

    $response->assertStatus(200)
        ->assertJson(['message' => 'Produk berhasil dihapus.']);

    $this->assertDatabaseMissing('products', ['id' => $product->id]);
});

test('editor cannot delete a product', function () {
    $admin = User::factory()->admin()->create();
    $editor = User::factory()->editor()->create();
    $category = Category::factory()->create();
    $product = Product::factory()->create([
        'category_id' => $category->id,
        'user_id' => $admin->id,
    ]);

    $response = $this->actingAs($editor)->deleteJson("/products/{$product->id}");

    $response->assertStatus(403);
    $this->assertDatabaseHas('products', ['id' => $product->id]);
});
