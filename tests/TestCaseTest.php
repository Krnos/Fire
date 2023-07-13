<?php

namespace Krnos\Fire\Tests;

use Orchestra\Testbench\TestCase as OrchestraTestCase;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Krnos\Fire\Change;
use Krnos\Fire\FireServiceProvider;
use Krnos\Fire\Events\FireEvent;

class TestCaseTest extends OrchestraTestCase
{
    protected function getPackageProviders($app)
    {
        return [
            FireServiceProvider::class,
        ];
    }

    protected function getPackageAliases($app)
    {
        return [
            'App\Change' => Change::class
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('app.debug', true);
        $app['config']->set('database.default', 'testing');
        $app['config']->set('fire.console_enabled', true);
        $app['config']->set('fire.test_enabled', true);
        $app['config']->set('fire.attributes_blacklist', [
            User::class => [
                'password'
            ]
        ]);

        $app['router']->post('articles', function(Request $request) {
            return Article::create(['title' => $request->title]);
        });
        $app['router']->put('articles/{id}', function(Request $request, $id) {
            $model = Article::find($id);
            $model->title = $request->title;
            $model->save();
            return $model;
        });
        $app['router']->delete('articles/{id}', function($id) {
            Article::destroy($id);
        });        
        $app['router']->post('articles/{id}/restore', function($id) {
            Article::withTrashed()->find($id)->restore();
        });        
        $app['router']->get('articles/{id}', function($id) {
            $model = Article::find($id);
            if(!is_null($model)) {
                event(new FireEvent($model,Change::TYPE_CREATED,'Query Article ' . $model->title, $model->pluck('id')->toArray(), auth()->user()));
            }
            return $model;
        });        
    }

    public function setUp(): void
    {
        parent::setUp();
        $this->setUpDatabase();
    }

    protected function setUpDatabase()
    {   
        $builder = $this->app['db']->connection()->getSchemaBuilder();

        $builder->create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('password');
        });

        $builder->create('articles', function (Blueprint $table) {
            $table->increments('id');
            $table->string('title');
            $table->softDeletes();
        });

        User::create(['name' => 'Esther', 'password' => '6ecd6a17b723']);

        $this->loadMigrationsFrom(realpath(__DIR__.'/../src/migrations'));
    }

    public function testChange()
    {
        $content = ['title' => 'enim officiis omnis'];
        $this->json('POST', '/articles', $content)->assertJson($content);
        $change = Change::first();
        $article = Article::first();
        $this->assertNotNull($change);
        $this->assertEquals(Article::class, $change->model_type);
        $this->assertEquals($article->id, $change->model_id);
        $this->assertEquals('Created Article ' . $content['title'], $change->message);
        $this->assertTrue($change->recorded_at instanceof \Illuminate\Support\Carbon);
        $change->delete();

        $data = ['title' => 'eligendi fugiat culpa'];
        $this->json('PUT', '/articles/' . $article->id, $data)->assertJson($data);
        $change = Change::first();
        $this->assertNotNull($change);
        $this->assertEquals($article->id, $change->model_id);
        $this->assertEquals('Updated Article ' . $content['title'], $change->message);
        $this->assertEquals(['before' => ['title' => 'enim officiis omnis'], 'after' => ['title' => 'eligendi fugiat culpa' ]], $change->changes);
        $change->delete();
        $article->refresh();

        $this->json('DELETE', '/articles/' . $article->id);
        $change = Change::first();
        $this->assertNotNull($change);
        $this->assertEquals($article->id, $change->model_id);
        $this->assertEquals('Deleted Article ' . $article->title, $change->message);
        $change->delete();

        $this->json('POST', '/articles/' . $article->id . '/restore');
        $change = Change::first();
        $this->assertNotNull($change);
        $this->assertEquals($article->id, $change->model_id);
        $this->assertEquals('Restored Article ' . $article->title, $change->message);
    }

    public function testAuthed()
    {
        $user = User::first();
        $this->assertNotNull($user);

        $content = ['title' => 'voluptas ut rem'];
        $this->actingAs($user)->json('POST', '/articles', $content)->assertJson($content);

        $article = Article::first();
        $this->assertNotNull($article);
        $changes = $article->changes;
        $this->assertNotNull($changes);
        $this->assertEquals(1, count($changes));
        $change = $changes[0];
        $this->assertTrue($change->hasUser());
        $this->assertNotNull($change->user());
        $this->assertEquals($user->toJson(), $change->user()->toJson());
        $this->assertEquals($article->makeHidden('changes')->toJson(), $change->model()->toJson());
        
        $operations = $user->operations;
        $this->assertNotNull($operations);
        $this->assertEquals(1, count($operations));
        $operation = $operations[0];
        $this->assertEquals($change->toJson(), $operation->toJson());
    }

    public function testAnonymous()
    {
        $content = ['title' => 'quae et est'];
        $this->json('POST', '/articles', $content)->assertJson($content);

        $article = Article::first();
        $this->assertNotNull($article);
        $changes = $article->changes;
        $this->assertNotNull($changes);
        $this->assertEquals(1, count($changes));
        $change = $changes[0];
        $this->assertNotTrue($change->hasUser());
        $this->assertNull($change->user());
    }

    public function testCustomEvent()
    {
        Article::create(['title' => 'maxime fugit saepe']);
        $article = Article::first();
        $this->assertNotNull($article);
        $this->json('GET', '/articles/' . $article->id);
        $change = Change::skip(1)->first();
        $this->assertNotNull($change);
        $this->assertEquals($article->id, $change->model_id);
        $this->assertEquals('Query Article ' . $article->title, $change->message);
        $this->assertEquals([$article->id], $change->changes);
    }
}