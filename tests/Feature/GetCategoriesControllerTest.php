<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class GetCategoriesControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return void
     */
    public function test_all_categories_is_listed_on_select_tag_while_creating_product(): void
    {
        Category::factory(20)->create();
        $user = User::factory()->create();

        $response = $this->actingAs($user)->getJson(route('get-categories'));

        $response->assertStatus(200)
            ->assertJson(
                fn (AssertableJson $json) => $json->has('data', 20)
                    ->first(
                        fn (AssertableJson $json) => $json->each(
                            fn (AssertableJson $json) => $json->whereAllType([
                                'id' => 'integer',
                                'title' => 'string'
                            ])
                                ->hasAll(['id', 'title'])
                        )
                    )
            );
    }
}
