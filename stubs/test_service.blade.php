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
use App\Services\{{$entity}}Service;

class {{$entity}}ServiceTest extends TestCase
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
        $repository = new {{$entity}}Repository(new {{$entity}}());
        $service = new {{$entity}}Service($repository);

        ${{\Illuminate\Support\Str::snake($entity)}} = $service->create($data);

        $this->assertDatabaseHas('{{$entities}}', ['id' => 1]);
        $this->assertEquals(1, $post->id);
    } 
@endif

@if (in_array('U', $options))
    public function test_update(): void
    {
        ${{\Illuminate\Support\Str::snake($entity)}} = {{$entity}}::factory(1)->createOne();
        ${{\Illuminate\Support\Str::snake($entity)}}New = {{$entity}}::factory(1)->makeOne()->toArray();
        $repository = new {{$entity}}Repository(new {{$entity}}());
        $service = new {{$entity}}Service($repository);

        $service->update($post->id, ${{\Illuminate\Support\Str::snake($entity)}}New);

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
        $repository = new {{$entity}}Repository(new {{$entity}}());
        $service = new {{$entity}}Service($repository);

        $service->delete(${{\Illuminate\Support\Str::snake($entity)}}->id);

        // $this->assertSoftDeleted('posts', ['id' => $post->id]);
        $this->assertDatabaseMissing('{{$databaseTableName}}', [
            'id' => ${{\Illuminate\Support\Str::snake($entity)}}->id
        ]);
    }

    public function test_restore()
    {
        $traits = class_uses(get_class($this->model));
        if (in_array(\Illuminate\Database\Eloquent\SoftDeletes::class, $traits)) {
            ${{\Illuminate\Support\Str::snake($entity)}} = {{$entity}}::factory()->create();
            $repository = new {{$entity}}Repository(new {{$entity}}());
            $service = new {{$entity}}Service($repository);
            ${{\Illuminate\Support\Str::snake($entity)}}->delete();
            $service->restore(${{\Illuminate\Support\Str::snake($entity)}}->id);
            $this->assertDatabaseHas('{{$databaseTableName}}', ['id' => ${{\Illuminate\Support\Str::snake($entity)}}->id, 'deleted_at' => null]);
        } else {
            $this->assertTrue(true);
        }
    }   
@endif
@if (in_array('R', $options))
    /** @test */
    public function getAll(): void
    {
        ${{$databaseTableName}} = {{$entity}}::factory(3)->create();
        $repository = new {{$entity}}Repository(new {{$entity}}());
        $service = new {{$entity}}Service($repository);
        $this->assertDatabaseCount('{{$databaseTableName}}', $service->getAll()->count());
        foreach (${{$databaseTableName}} as ${{\Illuminate\Support\Str::snake($entity)}}) {
            $this->assertDatabaseHas('{{$databaseTableName}}', [
@foreach($fields as $field)
            '{{$field['name']}}' => ${{\Illuminate\Support\Str::snake($entity)}}['{{$field['name']}}'],
@endforeach
            ]);
        }
    }

    /** @test */
    public function findById(): void
    {
        ${{\Illuminate\Support\Str::snake($entity)}} = {{$entity}}::factory(1)->createOne();
        $repository = new {{$entity}}Repository(new {{$entity}}());
        $service = new {{$entity}}Service($repository);
        ($service->find(${{\Illuminate\Support\Str::snake($entity)}}->id))->count();
        $this->assertDatabaseCount('{{$databaseTableName}}', ($service->find(${{\Illuminate\Support\Str::snake($entity)}}->id))->count());
        $this->assertDatabaseHas('{{$databaseTableName}}', [
@foreach($fields as $field)
            '{{$field['name']}}' => ${{\Illuminate\Support\Str::snake($entity)}}['{{$field['name']}}'],
@endforeach
        ]);
    }

    public function test_exists()
    {
        ${{\Illuminate\Support\Str::snake($entity)}} = {{$entity}}::factory()->createOne();
        $repository = new {{$entity}}Repository(new {{$entity}}());
        $service = new {{$entity}}Service($repository);

        $this->assertTrue($service->exists(['id' => ${{\Illuminate\Support\Str::snake($entity)}}->id]));        
        $this->assertFalse($this->repository->exists(['id' => 999]));
    }

    public function test_count()
    {
        {{$entity}}::factory(5)->create();
        $repository = new {{$entity}}Repository(new {{$entity}}());
        $service = new {{$entity}}Service($repository);

        $this->assertEquals(5, $service->count());
    }
    
    public function test_get()
    {
        {{$entity}}::factory(5)->create();
        $repository = new {{$entity}}Repository(new {{$entity}}());
        $service = new {{$entity}}Service($repository);

        ${{$entities}} = $service->get();

        $this->assertCount(5, ${{$entities}});
    }

    
    public function test_first()
    {
        $expected = {{$entity}}::factory()->create();
        {{$entity}}::factory(2)->create();
        $repository = new {{$entity}}Repository(new {{$entity}}());

        $service = new {{$entity}}Service($repository);

        ${{\Illuminate\Support\Str::snake($entity)}} = $service->first(['id' => $expected->id]);
        $this->assertEquals($expected->id, ${{\Illuminate\Support\Str::snake($entity)}}->id);
    }
    
    public function test_last()
    {
        {{$entity}}::factory(3)->create();
        $last{{$entity}} = {{$entity}}::factory()->create(['name' => 'Last Post']);
        $repository = new {{$entity}}Repository(new {{$entity}}());
        $service = new {{$entity}}Service($repository);
        
        ${{\Illuminate\Support\Str::snake($entity)}} = $this->repository->last([], 'id');
        $this->assertEquals($last{{$entity}}->id, ${{\Illuminate\Support\Str::snake($entity)}}->id);
    }
   
    public function test_find_by()
    {
        ${{\Illuminate\Support\Str::snake($entity)}} = {{$entity}}::factory()->create(['name' => 'Find Me']);
        $repository = new {{$entity}}Repository(new {{$entity}}());
        $service = new {{$entity}}Service($repository);
        $found = $service->findBy('name', 'Find Me');
        $this->assertEquals(${{\Illuminate\Support\Str::snake($entity)}}->id, $found->id);
    }
    
    public function test_with_trashed()
    {
        $traits = class_uses(get_class($this->model));
        if (in_array(\Illuminate\Database\Eloquent\SoftDeletes::class, $traits)) {
            ${{\Illuminate\Support\Str::snake($entity)}} = {{$entity}}::factory()->create();
            $repository = new {{$entity}}Repository(new {{$entity}}());
            $service = new {{$entity}}Service($repository);
            ${{\Illuminate\Support\Str::snake($entity)}}->delete();
            $result = $service->withTrashed()->first(['id' => ${{\Illuminate\Support\Str::snake($entity)}}->id]);
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
            $repository = new {{$entity}}Repository(new {{$entity}}());
            $service = new {{$entity}}Service($repository);
            ${{\Illuminate\Support\Str::snake($entity)}}->delete();
            $result = $service->onlyTrashed()->get();
            $this->assertCount(1, $result);
        } else {
            $this->assertTrue(true);
        }
    }   
    
@endif
}


