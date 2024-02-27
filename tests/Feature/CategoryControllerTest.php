<?php

namespace Tests\Feature;

use App\Models\Category;
use Illuminate\Support\Str;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class CategoryControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return void
     */
    public function test_user_can_get_paginated_category_list(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->getJson(route('categories.index'));

        $response->assertStatus(200);
    }

    /**
     * @return void
     */
    public function test_user_can_create_category_with_valid_data(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson(route('categories.store'), [
            'title' => 'Challenger 402',
            'slug' => 'challenger-402',
        ]);

        $response->assertStatus(201)
            ->assertJson(
                fn (AssertableJson $json) => $json->first(
                    fn (AssertableJson $json) => $json->where('id', 1)
                        ->where('title', 'Challenger 402')
                        ->where('slug', 'challenger-402')
                )->where('message', 'Successfully Created')
            );
    }

    /**
     * @dataProvider invalidCategoryData
     * @return void
     */
    public function test_user_cannot_create_category(array $data): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->postJson(route('categories.store'), [
            'title' => 'Title 1',
            'slug' => 'this-is-slug-unique',
        ]);

        $response = $this->actingAs($user)->postJson(route('categories.store'), $data);

        $response->assertStatus(422);
    }

    /**
     * @return void
     */
    public function test_user_can_view_category_with_existing_category_id(): void
    {
        $user = User::factory()->create();

        $category = Category::factory()->create();

        $response = $this->actingAs($user)->getJson(route('categories.show', $category));

        $response->assertStatus(200)
            ->assertJson(
                fn (AssertableJson $json) => $json->first(
                    fn (AssertableJson $json) => $json->where('id', $category->id)
                        ->where('title', $category->title)
                        ->where('slug', $category->slug)
                )
            );
    }

    /**
     * @return void
     */
    public function test_user_cannot_view_category_with_non_existing_category_id(): void
    {
        $user = User::factory()->create();

        Category::factory()->create();

        $response = $this->actingAs($user)->getJson(route('categories.show', 2));

        $response->assertStatus(404);
    }

    /**
     * @return void
     */
    public function test_user_can_update_category_with_valid_data(): void
    {
        $user = User::factory()->create();

        $category = Category::factory()->create();

        $response = $this->actingAs($user)->putJson(route('categories.update', $category->id), [
            'title' => 'Challenger 402 updated',
            'slug' => 'challenger-402-updated',
        ]);

        $response->assertStatus(200)
            ->assertJson(
                fn (AssertableJson $json) => $json->first(
                    fn (AssertableJson $json) => $json->where('id', $category->id)
                        ->where('title', 'Challenger 402 updated')
                        ->where('slug', 'challenger-402-updated')
                )->where('message', 'Successfully Updated')
            );
    }

    /**
     * @return void
     */
    public function test_user_can_update_category_with_unchanged_valid_data(): void
    {
        $user = User::factory()->create();

        $category = Category::factory()->create();

        $response = $this->actingAs($user)->putJson(route('categories.update', $category->id), [
            'title' => $category->title,
            'slug' => $category->slug,
        ]);

        $response->assertStatus(200)
            ->assertJson(
                fn (AssertableJson $json) => $json->first(
                    fn (AssertableJson $json) => $json->where('id', $category->id)
                        ->where('title', $category->title)
                        ->where('slug', $category->slug)
                )->where('message', 'No changes were made')
            );
    }

    /**
     * @dataProvider invalidCategoryData
     * @return void
     */
    public function test_user_cannot_update_category(array $data): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->postJson(route('categories.store'), [
            'title' => 'Title 1',
            'slug' => 'this-is-slug-unique',
        ]);

        $category = Category::factory()->create();

        $response = $this->actingAs($user)->putJson(route('categories.update', $category), $data);

        $response->assertStatus(422);
    }

    /**
     * @return void
     */
    public function test_user_can_delete_category_with_existing_category_id(): void
    {
        $user = User::factory()->create();

        $category = Category::factory()->create();

        $response = $this->actingAs($user)->deleteJson(route('categories.destroy', $category));

        $response->assertStatus(204);
    }

    /**
     * @return void
     */
    public function test_user_cannot_delete_category_with_non_existing_category_id(): void
    {
        $user = User::factory()->create();

        Category::factory()->create();

        $response = $this->actingAs($user)->deleteJson(route('categories.destroy', 2));

        $response->assertStatus(404);
    }

    /**
     * @return array
     */
    public static function invalidCategoryData(): array
    {
        return [
            'title.required' => [
                [
                    'title' => '',
                    'slug' => 'this-is-slug'
                ],
            ],
            'title.string' => [
                [
                    'title' => 402,
                    'slug' => 'this-is-slug-1'
                ],
                [
                    'title' => ['array'],
                    'slug' => 'this-is-slug-2',
                ],
                [
                    'title' => true,
                    'slug' => 'this-is-slug-3',
                ],
            ],
            'title.max' => [
                [
                    'title' => Str::random(256),
                    'slug' => 'this-is-slug-1'
                ]
            ],
            'slug.required' => [
                [
                    'title' => 'Title',
                    'slug' => ''
                ],
            ],
            'slug.string' => [
                [
                    'title' => 'Ipad Air 4',
                    'slug' => 402,
                ],
                [
                    'title' => 'Ipad Air 4',
                    'slug' => ['array'],
                ],
                [
                    'title' => 'Ipad Air 4',
                    'slug' => true,
                ],
            ],
            'slug.max' => [
                [
                    'title' => 'Ipad Air 4',
                    'slug' => Str::random(256),
                ]
            ],
            'slug.invalid' => [
                [
                    'title' => 'Invalid Slug',
                    'slug' => 'Invalid Slug',
                ],
                [
                    'title' => 'Invalid Slug',
                    'slug' => 'invalid_slug!',
                ],
                [
                    'title' => 'Invalid Slug',
                    'slug' => 'invalid-slug-',
                ],
                [
                    'title' => 'Invalid Slug',
                    'slug' => '-invalid-slug',
                ],
                [
                    'title' => 'Invalid Slug',
                    'slug' => 'invalid--slug',
                ],
                [
                    'title' => 'Invalid Slug',
                    'slug' => 'invalid- slug',
                ],
                [
                    'title' => 'Invalid Slug',
                    'slug' => 'slug-',
                ],
                [
                    'title' => 'Invalid Slug',
                    'slug' => '-slug',
                ],
            ],
            'slug.unique' => [
                [
                    'title' => 'Title 1',
                    'slug' => 'this-is-slug-unique',
                ],
            ],
        ];
    }
}
