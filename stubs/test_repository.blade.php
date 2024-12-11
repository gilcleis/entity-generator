@php($shouldUseStatus = version_compare(app()->version(), '7', '<'))
namespace Tests\Feature\Repositories;

@if ($withAuth)
use {{$modelsNamespace}}\User;
@endif
@if($shouldUseStatus)
use Symfony\Component\HttpFoundation\Response;
@endif
use Gilcleis\Support\Traits\AuthTestTrait;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\{{$entity}};
use App\Repositories\{{$entity}}Repository;

class {{$entity}}RepositoryTest extends TestCase
{
    use RefreshDatabase;    
    protected $repository;
    protected $model;

@if ($withAuth)
    use AuthTestTrait;
    protected $user;

@endif
    public function setUp() : void
    {
        parent::setUp();
        $this->refreshDatabase();
        $this->artisan('migrate:fresh');
        $this->model = new {{$entity}}();
        $this->repository = new {{$entity}}Repository($this->model);
@if ($withAuth)
        $this->user = User::factory(1)->createOne();
@endif
    }

@if (in_array('C', $options))
    public function test_create(): void
    {
        $data = {{$entity}}::factory()->makeOne(['name' => 'New {{$entity}}'])->toArray();

        ${{\Illuminate\Support\Str::snake($entity)}} = $this->repository->create($data);

        $this->assertDatabaseHas('{{$entities}}', ['id' => 1]);
        $this->assertEquals(1, $post->id);
    }
 
@endif
@if (in_array('U', $options))
    public function test_update(): void
    {
        ${{\Illuminate\Support\Str::snake($entity)}} = {{$entity}}::factory(1)->createOne();
        ${{\Illuminate\Support\Str::snake($entity)}}New = {{$entity}}::factory(1)->makeOne()->toArray();

        $this->repository->update($post->id, ${{\Illuminate\Support\Str::snake($entity)}}New);

        $this->assertDatabaseHas('{{$databaseTableName}}', [
@foreach($fields as $field)
            '{{$field['name']}}' => ${{\Illuminate\Support\Str::snake($entity)}}New['{{$field['name']}}'],
@endforeach
        ]);
    }   

@endif
@if (in_array('D', $options))
    public function test_delete(): void
    {
        ${{\Illuminate\Support\Str::snake($entity)}} = {{$entity}}::factory(1)->createOne();   

        $this->repository->delete(${{\Illuminate\Support\Str::snake($entity)}}->id);

        // $this->assertSoftDeleted('posts', ['id' => $post->id]);
        $this->assertDatabaseMissing('{{$databaseTableName}}', [
            'id' => ${{\Illuminate\Support\Str::snake($entity)}}->id
        ]);
    }
   
@endif
@if (in_array('R', $options))
    public function test_get_all(): void
    {
        {{$entity}}::factory(3)->create();

        $result = $this->repository->all();

        $this->assertCount(3, $result);
    }   

    public function test_find()
    {
        ${{\Illuminate\Support\Str::snake($entity)}} = {{$entity}}::factory()->createOne();

        $found = $this->repository->find(${{\Illuminate\Support\Str::snake($entity)}}->id);

        $this->assertEquals(${{\Illuminate\Support\Str::snake($entity)}}->id, $found->id);
    }

    public function test_exists()
    {
        ${{\Illuminate\Support\Str::snake($entity)}} = {{$entity}}::factory()->createOne();

        $this->assertTrue($this->repository->exists(['id' => ${{\Illuminate\Support\Str::snake($entity)}}->id]));        
        $this->assertFalse($this->repository->exists(['id' => 999]));
    }

    public function test_count()
    {
        {{$entity}}::factory(5)->create();
        $this->assertEquals(5, $this->repository->count());
        // $this->assertEquals(2, $this->repository->count(['name' => 'draft']));
    }

    public function test_get()
    {
        {{$entity}}::factory(3)->create();
        ${{$entities}} = $this->repository->get();
        $this->assertCount(3, ${{$entities}});
    }

    public function test_first()
    {
        $expected = {{$entity}}::factory()->create();
        {{$entity}}::factory(2)->create();
        ${{\Illuminate\Support\Str::snake($entity)}} = $this->repository->first(['id' => $expected->id]);
        $this->assertEquals($expected->id, ${{\Illuminate\Support\Str::snake($entity)}}->id);
    }

    public function test_last()
    {
        {{$entity}}::factory(3)->create();
        $last{{$entity}} = {{$entity}}::factory()->create(['name' => 'Last Post']);
        ${{\Illuminate\Support\Str::snake($entity)}} = $this->repository->last([], 'id');
        $this->assertEquals($last{{$entity}}->id, ${{\Illuminate\Support\Str::snake($entity)}}->id);
    }

    public function test_find_by()
    {
        ${{\Illuminate\Support\Str::snake($entity)}} = {{$entity}}::factory()->create(['name' => 'Find Me']);
        $found = $this->repository->findBy('name', 'Find Me');
        $this->assertEquals(${{\Illuminate\Support\Str::snake($entity)}}->id, $found->id);
    }

    public function test_with_trashed()
    {
        $traits = class_uses(get_class($this->model));
        if (in_array(\Illuminate\Database\Eloquent\SoftDeletes::class, $traits)) {
            ${{\Illuminate\Support\Str::snake($entity)}} = {{$entity}}::factory()->create();
            ${{\Illuminate\Support\Str::snake($entity)}}->delete();
            $result = $this->repository->withTrashed()->first(['id' => ${{\Illuminate\Support\Str::snake($entity)}}->id]);
            $this->assertNotNull($result);
        } else {
            $this->assertTrue(true);
        }
    }

    public function test_only_trashed()
    {
        $traits = class_uses(get_class($this->model));
        if (in_array(\Illuminate\Database\Eloquent\SoftDeletes::class, $traits)) {
            ${{\Illuminate\Support\Str::snake($entity)}} = {{$entity}}::factory()->create();
            ${{\Illuminate\Support\Str::snake($entity)}}->delete();
            $result = $this->repository->onlyTrashed()->get();
            $this->assertCount(1, $result);
        } else {
            $this->assertTrue(true);
        }
    }

    public function test_restore()
    {
        $traits = class_uses(get_class($this->model));
        if (in_array(\Illuminate\Database\Eloquent\SoftDeletes::class, $traits)) {
            ${{\Illuminate\Support\Str::snake($entity)}} = {{$entity}}::factory()->create();
            ${{\Illuminate\Support\Str::snake($entity)}}->delete();
            $this->repository->restore(${{\Illuminate\Support\Str::snake($entity)}}->id);
            $this->assertDatabaseHas('{{$databaseTableName}}', ['id' => ${{\Illuminate\Support\Str::snake($entity)}}->id, 'deleted_at' => null]);
        } else {
            $this->assertTrue(true);
        }
    }
@endif
}


