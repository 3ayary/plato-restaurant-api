<?php

use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('category', function () {

    test('get All categories', function () {
        $response = $this->getJson('/api/categories');

        $response->assertStatus(200);
        $response->assertJsonStructure(['data']);
    });

    test('create category', function () {

        $response = $this->postJson('/api/categories',
            [
                'name' => 'test',
            ]);

        $response->assertStatus(201);
        $response->assertJsonStructure(['message', 'data']);

    });

    test('update category', function () {

        $category = Category::create(['name' => 'test']);
        $response = $this->putJson("/api/categories/{$category->id}",['name'=>'updated Test']);

        $response->assertStatus(200);
        $response->assertJsonStructure(['message','data']);
    });


    test('delete category',function(){

    $category = Category::create(['name'=>'test']);
        $response = $this->deleteJson("/api/categories/{$category->id}");

        $response->assertStatus(200);
        $response->assertJsonStructure(['message','data']);

    });

});
