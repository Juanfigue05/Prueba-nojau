<?php

namespace Tests\Feature;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\Group;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

/**
 * Test that all required routes are properly defined in web.php
 */
#[Group('route-validation')]
class RouteDefinitionTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that basic user routes exist
     */
    #[Test]
    public function test_basic_user_routes_are_defined()
    {
        $this->assertTrue(Route::has('users.index'), 'Route users.index should be defined');
        $this->assertTrue(Route::has('users.create'), 'Route users.create should be defined');
        $this->assertTrue(Route::has('users.store'), 'Route users.store should be defined');
        $this->assertTrue(Route::has('users.edit'), 'Route users.edit should be defined');
        $this->assertTrue(Route::has('users.update'), 'Route users.update should be defined');
        $this->assertTrue(Route::has('users.destroy'), 'Route users.destroy should be defined');
    }

    /**
     * Test that mass creation routes exist
     */
    #[Test]
    public function test_mass_creation_routes_are_defined()
    {
        $this->assertTrue(Route::has('users.mass-create'), 'Route users.mass-create should be defined');
        $this->assertTrue(Route::has('users.mass-store'), 'Route users.mass-store should be defined');
        $this->assertTrue(Route::has('users.mass-preview'), 'Route users.mass-preview should be defined');
        $this->assertTrue(Route::has('users.csv-template'), 'Route users.csv-template should be defined');
    }

    /**
     * Test that mass deletion routes exist
     */
    #[Test]
    public function test_mass_deletion_routes_are_defined()
    {
        $this->assertTrue(Route::has('users.mass-delete'), 'Route users.mass-delete should be defined');
        $this->assertTrue(Route::has('users.mass-destroy'), 'Route users.mass-destroy should be defined');
    }

    /**
     * Test that routes point to correct controller methods
     */
    #[Test]
    public function test_routes_point_to_correct_controller_methods()
    {
        $routes = [
            'users.index' => ['GET', 'users', 'index'],
            'users.create' => ['GET', 'users/create', 'create'],
            'users.store' => ['POST', 'users', 'store'],
            'users.edit' => ['GET', 'users/{id}/edit', 'edit'],
            'users.update' => ['PUT', 'users/{id}', 'update'],
            'users.destroy' => ['DELETE', 'users/{id}', 'destroy'],
            'users.mass-create' => ['GET', 'users/mass-create', 'massCreate'],
            'users.mass-store' => ['POST', 'users/mass-store', 'massStore'],
            'users.mass-preview' => ['POST', 'users/mass-preview', 'massPreview'],
            'users.csv-template' => ['GET', 'users/csv-template', 'csvTemplate'],
            'users.mass-delete' => ['GET', 'users/mass-delete', 'massDelete'],
            'users.mass-destroy' => ['DELETE', 'users/mass-destroy', 'massDestroy'],
        ];

        foreach ($routes as $routeName => [$method, $uri, $action]) {
            $route = Route::getRoutes()->getByName($routeName);
            
            $this->assertNotNull($route, "Route {$routeName} should exist");
            $this->assertContains($method, $route->methods(), "Route {$routeName} should accept {$method} method");
            $this->assertStringContainsString('UsersController@' . $action, $route->getActionName(), "Route {$routeName} should point to UsersController@{$action}");
        }
    }

    /**
     * Test that home route redirects to users index
     */
    #[Test]
    public function test_home_route_is_defined()
    {
        $homeRoute = Route::getRoutes()->match(
            request()->create('/', 'GET')
        );
        
        $this->assertNotNull($homeRoute, 'Home route (/) should be defined');
        $this->assertStringContainsString('UsersController@index', $homeRoute->getActionName(), 'Home route should point to UsersController@index');
    }

    /**
     * Test that route parameters are correctly defined
     */
    #[Test]
    public function test_route_parameters_are_correctly_defined()
    {
        $editRoute = Route::getRoutes()->getByName('users.edit');
        $updateRoute = Route::getRoutes()->getByName('users.update');
        $destroyRoute = Route::getRoutes()->getByName('users.destroy');

        $this->assertNotNull($editRoute, 'Edit route should exist');
        $this->assertNotNull($updateRoute, 'Update route should exist');  
        $this->assertNotNull($destroyRoute, 'Destroy route should exist');

        // Check that these routes have the {id} parameter
        $this->assertStringContainsString('{id}', $editRoute->uri(), 'Edit route should have {id} parameter');
        $this->assertStringContainsString('{id}', $updateRoute->uri(), 'Update route should have {id} parameter');
        $this->assertStringContainsString('{id}', $destroyRoute->uri(), 'Destroy route should have {id} parameter');
    }

    /**
     * Test that all routes use the correct HTTP methods
     */
    #[Test]
    public function test_routes_use_correct_http_methods()
    {
        $httpMethods = [
            'users.index' => ['GET'],
            'users.create' => ['GET'],
            'users.store' => ['POST'],
            'users.edit' => ['GET'],
            'users.update' => ['PUT'],
            'users.destroy' => ['DELETE'],
            'users.mass-create' => ['GET'],
            'users.mass-store' => ['POST'],
            'users.mass-preview' => ['POST'],
            'users.csv-template' => ['GET'],
            'users.mass-delete' => ['GET'],
            'users.mass-destroy' => ['DELETE'],
        ];

        foreach ($httpMethods as $routeName => $expectedMethods) {
            $route = Route::getRoutes()->getByName($routeName);
            $this->assertNotNull($route, "Route {$routeName} should exist");
            
            foreach ($expectedMethods as $method) {
                $this->assertContains($method, $route->methods(), "Route {$routeName} should accept {$method} method");
            }
        }
    }

    /**
     * Test that no extra API routes are accidentally defined
     */
    #[Test]
    public function test_no_api_routes_are_defined()
    {
        $allRoutes = Route::getRoutes()->getRoutes();
        
        foreach ($allRoutes as $route) {
            $uri = $route->uri();
            $this->assertStringNotContainsString('api/', $uri, "No API routes should be defined in web.php. Found: {$uri}");
        }
    }

    /**
     * Test route generation works correctly
     */
    #[Test]
    public function test_route_generation_works()
    {
        $routes = [
            'users.index' => '/users',
            'users.create' => '/users/create',
            'users.store' => '/users',
            'users.mass-create' => '/users/mass-create',
            'users.mass-delete' => '/users/mass-delete',
            'users.csv-template' => '/users/csv-template',
        ];

        foreach ($routes as $routeName => $expectedUrl) {
            $generatedUrl = route($routeName);
            $this->assertStringEndsWith($expectedUrl, $generatedUrl, "Route {$routeName} should generate URL ending with {$expectedUrl}");
        }
    }

    /**
     * Test routes with parameters generate correctly
     */
    #[Test]
    public function test_parameterized_routes_generation()
    {
        $testId = 123;
        
        $editUrl = route('users.edit', ['id' => $testId]);
        $this->assertStringEndsWith("/users/{$testId}/edit", $editUrl, 'Edit route should generate URL with ID parameter');
        
        $updateUrl = route('users.update', ['id' => $testId]);
        $this->assertStringEndsWith("/users/{$testId}", $updateUrl, 'Update route should generate URL with ID parameter');
        
        $destroyUrl = route('users.destroy', ['id' => $testId]);
        $this->assertStringEndsWith("/users/{$testId}", $destroyUrl, 'Destroy route should generate URL with ID parameter');
    }
}