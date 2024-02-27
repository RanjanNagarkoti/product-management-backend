<?php

namespace Tests\Feature;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class ProductControllerTest extends TestCase
{
    use RefreshDatabase;


    /**
     * @return void
     */
    public function test_user_can_get_list_of_products(): void
    {
        $user = User::factory()->create();

        $categories = Category::factory(5)->create()->pluck('id')->toArray();

        $product = Product::factory()->create();

        $product->categories()->attach($categories);

        Storage::delete($product->thumbnail);

        $response = $this->actingAs($user)->getJson(route('products.index'));

        $response->assertStatus(200)
            ->assertJson(
                fn (AssertableJson $json) => $json->has(
                    'data',
                    fn (AssertableJson $json) => $json->each(
                        fn (AssertableJson $json) => $json->whereAllType([
                            'id' => 'integer',
                            'name' => 'string',
                            'description' => 'string',
                            'price' => 'integer',
                            'quantity' => 'integer',
                            'slug' => 'string',
                            'thumbnail_url' => 'string',
                            'status' => 'integer',
                            'categories' => 'array'
                        ])
                    )
                )->has(
                    'meta',
                    fn (AssertableJson $json) => $json->where('per_page', 12)
                        ->where('total', 1)
                        ->etc()
                )->etc()
            );
    }


    /**
     * @dataProvider validProductData
     * @return void
     */
    public function test_user_can_create_products(array $data): void
    {
        Storage::fake('local');

        $user = User::factory()->create();

        $categories = Category::factory(5)->create()->pluck('id')->toArray();

        $data['categories'] = $categories;

        $response = $this->actingAs($user)->postJson(route('products.store'), $data);

        $product = Product::first();

        $response->assertStatus(200)
            ->assertJson(
                fn (AssertableJson $json) => $json->first(
                    fn (AssertableJson $json) => $json->where('id', $product->id)
                        ->where('name', $product->name)
                        ->where('description', $product->description)
                        ->where('price', $product->price)
                        ->where('quantity', $product->quantity)
                        ->where('slug', $product->slug)
                        ->where('thumbnail_url', $product->getThumbnailUrlAttribute())
                        ->where('status', $product->status)
                )->where('message', 'Successfully Created')
            );

        $this->assertDatabaseCount('category_product', 5);
    }


    /**
     * @dataProvider invalidProductData
     * @return void
     */
    public function test_user_cannot_create_products(array $data): void
    {
        Storage::fake('local');

        $thumbnail = UploadedFile::fake()->image('product.png');

        $user = User::factory()->create();

        $categories = Category::factory(5)->create()->pluck('id')->toArray();

        $data['categories'] = $categories;

        $this->actingAs($user)->postJson(route('products.store'), [
            'name' => 'Tp-Link Archer 5GHz',
            'description' => 'Product description goes here.',
            'price' => 4500,
            'quantity' => 50,
            'slug' => 'tp-link-archer-5ghz',
            'thumbnail' => $thumbnail,
            'stauts' => 1,
            'categories' => $categories,
        ]);

        $response = $this->actingAs($user)->postJson(route('products.store'), $data);

        $response->assertStatus(422);
    }


    /**
     * @dataProvider invalidCategories
     * @return void
     */
    public function test_user_cannot_create_product(array $data): void
    {
        Storage::fake('local');

        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson(route('products.store'), $data);

        $response->assertStatus(422);
    }


    /**
     * @dataProvider validProductData
     * @return void
     */
    public function test_user_can_view_product_with_existing_product_id(array $data): void
    {
        Storage::fake('local');

        $user = User::factory()->create();

        $categories = Category::factory(5)->create()->pluck('id')->toArray();

        $data['categories'] = $categories;

        $this->actingAs($user)->postJson(route('products.store'), $data);

        $response =  $this->actingAs($user)->getJson(route('products.show', 1));

        $product = Product::first();

        $response->assertStatus(200)
            ->assertJson(
                fn (AssertableJson $json) => $json->has('data')
                    ->first(
                        fn (AssertableJson $json) => $json->where('id', $product->id)
                            ->where('name', $product->name)
                            ->where('description', $product->description)
                            ->where('price', $product->price)
                            ->where('quantity', $product->quantity)
                            ->where('slug', $product->slug)
                            ->where('thumbnail_url', $product->getThumbnailUrlAttribute())
                            ->where('status', $product->status)
                            ->has('categories', 5)
                    )
            );
    }


    /**
     * @dataProvider validProductData
     * @return void
     */
    public function test_user_can_update_products(array $data): void
    {
        Storage::fake('local');

        $thumbnail = UploadedFile::fake()->image('product.png');

        $user = User::factory()->create();

        $categories = Category::factory(5)->create()->pluck('id')->toArray();

        $data['categories'] = $categories;

        $this->actingAs($user)->postJson(route('products.store'), $data);

        $response = $this->actingAs($user)->putJson(route('products.update', 1), [
            "name" => "Headphones",
            "description" => "List of items",
            "price" => 199,
            "quantity" => 20,
            "slug" => "noise-cancelling-headphones",
            "thumbnail" => $thumbnail,
            "status" => 1,
            'categories' => [1, 3, 5]
        ]);

        $product = Product::first();

        $response->assertStatus(200)
            ->assertJson(
                fn (AssertableJson $json) => $json->first(
                    fn (AssertableJson $json) => $json->where('id', $product->id)
                        ->where('name', $product->name)
                        ->where('description', $product->description)
                        ->where('price', $product->price)
                        ->where('quantity', $product->quantity)
                        ->where('slug', $product->slug)
                        ->where('thumbnail_url', $product->getThumbnailUrlAttribute())
                        ->where('status', $product->status)
                )->where('message', 'Successfully Updated')
            );

        $this->assertDatabaseCount('category_product', 3);
    }


    /**
     * @dataProvider validProductData
     * @return void
     */
    public function test_user_can_update_products_with_unchanged_data(array $data): void
    {
        Storage::fake('local');

        $user = User::factory()->create();

        $categories = Category::factory(5)->create()->pluck('id')->toArray();

        $data['categories'] = $categories;

        $this->actingAs($user)->postJson(route('products.store'), $data);

        $response = $this->actingAs($user)->putJson(route('products.update', 1), $data);

        $product = Product::first();

        $response->assertStatus(200)
            ->assertJson(
                fn (AssertableJson $json) => $json->first(
                    fn (AssertableJson $json) => $json->where('id', $product->id)
                        ->where('name', $product->name)
                        ->where('description', $product->description)
                        ->where('price', $product->price)
                        ->where('quantity', $product->quantity)
                        ->where('slug', $product->slug)
                        ->where('thumbnail_url', $product->getThumbnailUrlAttribute())
                        ->where('status', $product->status)
                )->where('message', 'No changes were made')
            );
    }


    /**
     * @dataProvider validProductData
     * @return void
     */
    public function test_user_cannot_create_products_without_unique_slug(array $data): void
    {
        Storage::fake('local');

        $user = User::factory()->create();

        $categories = Category::factory(5)->create()->pluck('id')->toArray();

        $data['categories'] = $categories;

        $this->actingAs($user)->postJson(route('products.store'), $data);

        $response = $this->actingAs($user)->postJson(route('products.store'), $data);

        $response->assertStatus(422);
    }

    /**
     * @dataProvider validProductData
     * @return void
     */
    public function test_user_can_delete_products_with_existing_product_id(array $data): void
    {
        Storage::fake('local');

        $user = User::factory()->create();

        $categories = Category::factory(5)->create()->pluck('id')->toArray();

        $data['categories'] = $categories;

        $this->actingAs($user)->postJson(route('products.store'), $data);

        $this->assertDatabaseCount('category_product', 5);

        $response = $this->actingAs($user)->deleteJson(route('products.destroy', Product::first()));

        $response->assertStatus(204)
            ->assertNoContent();

        $this->assertDatabaseCount('category_product', 0);
    }

    /**
     * @return array
     */
    public static function validProductData(): array
    {
        $thumbnail = UploadedFile::fake()->image('product.png');

        return [
            "valid" => [
                [
                    'name' => 'Tp-Link Archer 5GHz',
                    'description' => 'Product description goes here.',
                    'price' => 4500,
                    'quantity' => 50,
                    'slug' => 'tp-link-archer-5ghz',
                    'thumbnail' => $thumbnail,
                    'stauts' => 1
                ]
            ]
        ];
    }


    /**
     * @return array
     */
    public static function invalidProductData(): array
    {
        $thumbnail = UploadedFile::fake()->image('product.png');

        return [
            "name.required" => [
                [
                    'name' => '',
                    'description' => 'Product description goes here.',
                    'price' => 4500,
                    'quantity' => 50,
                    'slug' => 'tp-link-archer-5ghz',
                    'thumbnail' => $thumbnail,
                    'stauts' => 1
                ]
            ],
            "name.string" => [
                [
                    'name' => 123,
                    'description' => 'Product description goes here.',
                    'price' => 4500,
                    'quantity' => 50,
                    'slug' => 'tp-link-archer-5ghz',
                    'thumbnail' => $thumbnail,
                    'stauts' => 1
                ],
                [
                    'name' => ['product name'],
                    'description' => 'Product description goes here.',
                    'price' => 4500,
                    'quantity' => 50,
                    'slug' => 'tp-link-archer-5ghz',
                    'thumbnail' => $thumbnail,
                    'stauts' => 1
                ],
                [
                    'name' => true,
                    'description' => 'Product description goes here.',
                    'price' => 4500,
                    'quantity' => 50,
                    'slug' => 'tp-link-archer-5ghz',
                    'thumbnail' => $thumbnail,
                    'stauts' => 1
                ]
            ],
            "name.max:255" => [
                [
                    'name' => Str::random(256),
                    'description' => 'Product description goes here.',
                    'price' => 4500,
                    'quantity' => 50,
                    'slug' => 'tp-link-archer-5ghz',
                    'thumbnail' => $thumbnail,
                    'stauts' => 1
                ]
            ],
            "description.required" => [
                [
                    "name" => "New product",
                    "description" => "",
                    "price" => 4500,
                    "quantity" => 50,
                    "slug" => "tp-link-archer-5ghz",
                    "thumbnail" => $thumbnail,
                    "status" => 1,
                ]
            ],
            "description.string" => [
                [
                    "name" => "New product",
                    "description" => 123,
                    "price" => 4500,
                    "quantity" => 50,
                    "slug" => "new-product",
                    "thumbnail" => $thumbnail,
                    "status" => 1,
                ],
                [
                    "name" => "Another product",
                    "description" => ["List", "of", "items"],
                    "price" => 3999,
                    "quantity" => 20,
                    "slug" => "another-product",
                    "thumbnail" => $thumbnail,
                    "status" => 0,
                ],
                [
                    "name" => "Headphones",
                    "description" => true,
                    "price" => 199,
                    "quantity" => 100,
                    "slug" => "noise-cancelling-headphones",
                    "thumbnail" => $thumbnail,
                    "status" => 1,
                ],
                [
                    "name" => "Smartwatch",
                    "description" => null,
                    "price" => 249,
                    "quantity" => 30,
                    "slug" => "fitness-tracker-watch",
                    "thumbnail" => $thumbnail,
                    "status" => 0,
                ],
            ],
            "description.max:5000" => [
                [
                    "name" => "New product",
                    "description" => str_repeat("a", 5001),
                    "price" => 4500,
                    "quantity" => 50,
                    "slug" => "tp-link-archer-5ghz",
                    "thumbnail" => $thumbnail,
                    "status" => 1,
                ]
            ],
            "price.required" => [
                [
                    "name" => "New product",
                    "description" => "Product description goes here.",
                    "price" => "", // Invalid: price is required
                    "quantity" => 50,
                    "slug" => "new-product",
                    "thumbnail" => $thumbnail,
                    "status" => 1,
                ]
            ],
            "price.integer" => [
                [
                    "name" => "Another product",
                    "description" => "Another product description",
                    "price" => "123.45", // Invalid: price must be an integer
                    "quantity" => 20,
                    "slug" => "another-product",
                    "thumbnail" => $thumbnail,
                    "status" => 0,
                ]
            ],
            "price.min:5" => [
                [
                    "name" => "Headphones",
                    "description" => "List of items",
                    "price" => 4, // Invalid: price must be a minimum of 5
                    "quantity" => 100,
                    "slug" => "noise-cancelling-headphones",
                    "thumbnail" => $thumbnail,
                    "status" => 1,
                ]
            ],
            "price.max:4294967295" => [
                [
                    "name" => "Expensive watch",
                    "description" => "Luxury watch",
                    "price" => 4294967296, // Invalid: price exceeds the maximum allowed value
                    "quantity" => 1,
                    "slug" => "limited-edition-watch",
                    "thumbnail" => $thumbnail,
                    "status" => 1,
                ]
            ],
            "quantity.required" => [
                [
                    "name" => "New product",
                    "description" => "Product description goes here.",
                    "price" => 4500,
                    "quantity" => "", // Invalid: quantity is required
                    "slug" => "new-product",
                    "thumbnail" => $thumbnail,
                    "status" => 1,
                ]
            ],
            "quantity.integer" => [
                [
                    "name" => "Another product",
                    "description" => "Another product description",
                    "price" => 1234,
                    "quantity" => "20.5", // Invalid: quantity must be an integer
                    "slug" => "another-product",
                    "thumbnail" => $thumbnail,
                    "status" => 0,
                ]
            ],
            "quantity.min:1" => [
                [
                    "name" => "Headphones",
                    "description" => "List of items",
                    "price" => 199,
                    "quantity" => 0, // Invalid: quantity must be at least 1
                    "slug" => "noise-cancelling-headphones",
                    "thumbnail" => $thumbnail,
                    "status" => 1,
                ]
            ],
            "quantity.max:10000" => [
                [
                    "name" => "Expensive watch",
                    "description" => "Luxury watch",
                    "price" => 4294967295,
                    "quantity" => 10001, // Invalid: quantity exceeds the maximum allowed value
                    "slug" => "limited-edition-watch",
                    "thumbnail" => $thumbnail,
                    "status" => 1,
                ]
            ],
            "slug.required" => [
                [
                    "name" => "New product",
                    "description" => "Product description goes here.",
                    "price" => 4500,
                    "quantity" => 50,
                    "slug" => "", // Invalid: slug is required
                    "thumbnail" => $thumbnail,
                    "status" => 1,
                ]
            ],
            "slug.string" => [
                [
                    "name" => "Another product",
                    "description" => "Another product description",
                    "price" => 1234,
                    "quantity" => 20,
                    "slug" => 12345, // Invalid: slug should be a string
                    "thumbnail" => $thumbnail,
                    "status" => 0,
                ]
            ],
            "slug.max:255" => [
                [
                    "name" => "Headphones",
                    "description" => "List of items",
                    "price" => 199,
                    "quantity" => 100,
                    "slug" => str_repeat("a", 256), // Invalid: slug exceeds the maximum length
                    "thumbnail" => $thumbnail,
                    "status" => 1,
                ]
            ],
            "slug.unique:products,slug" => [
                [
                    "name" => "Similar product",
                    "description" => "Similar description",
                    "price" => 249,
                    "quantity" => 30,
                    "slug" => "tp-link-archer-5ghz", // Invalid: slug should be unique
                    "thumbnail" => $thumbnail,
                    "status" => 0,
                ]
            ],
            "thumbnail.required" => [
                [
                    "name" => "Similar product",
                    "description" => "Similar description",
                    "price" => 249,
                    "quantity" => 30,
                    "slug" => "tp-link-archer-5ghz",
                    "thumbnail" => null,
                    "status" => 0,
                ]
            ],
        ];
    }


    /**
     * @return array
     */
    public static function invalidCategories(): array
    {
        $thumbnail = UploadedFile::fake()->image('product.png');

        return [
            "categories.required" => [
                [ // Entry missing the "categories" key entirely
                    "name" => "New product",
                    "description" => "Product description goes here.",
                    "price" => 4500,
                    "quantity" => 50,
                    "slug" => "new-product",
                    "thumbnail" => $thumbnail // Replace with your image path/data
                ]
            ],
            "categories.array" => [
                [ // "categories" is not an array
                    "name" => "Another product",
                    "description" => "Another product description",
                    "price" => 1234,
                    "quantity" => 20,
                    "slug" => "another-product",
                    "thumbnail" => $thumbnail,
                    "categories" => "This is not an array"
                ]
            ],
            "categories.*.required" => [
                [ // "categories" array is empty
                    "name" => "Headphones",
                    "description" => "List of items",
                    "price" => 199,
                    "quantity" => 100,
                    "slug" => "noise-cancelling-headphones",
                    "thumbnail" => $thumbnail,
                    "categories" => []
                ],
                [ // "categories" array contains an empty string
                    "name" => "Expensive watch",
                    "description" => "Luxury watch",
                    "price" => 4294967295,
                    "quantity" => 1,
                    "slug" => "limited-edition-watch",
                    "thumbnail" => $thumbnail,
                    "categories" => [""]
                ]
            ],
            "categories.*.exists:categories,id" => [
                [ // "categories" contains an ID that doesn't exist
                    "name" => "Smartwatch",
                    "description" => "This product belongs to a non-existent category",
                    "price" => 249,
                    "quantity" => 30,
                    "slug" => "fitness-tracker-watch",
                    "thumbnail" => $thumbnail,
                    "categories" => [1000] // Assuming this ID doesn't exist
                ]
            ],
        ];
    }
}
