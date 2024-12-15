@php($shouldUseStatus = version_compare(app()->version(), '7', '<'))
namespace Tests\Feature;

@if ($withAuth)
use {{$modelsNamespace}}\User;
@endif
@if($shouldUseStatus)
use Symfony\Component\HttpFoundation\Response;
@endif
use RonasIT\Support\Traits\AuthTestTrait;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\{{$entity}};

class {{$entity}}Test extends TestCase
{
    use RefreshDatabase;    
@if ($withAuth)
    use AuthTestTrait;
    protected $user;

@endif
    public function setUp() : void
    {
        parent::setUp();
@if ($withAuth)
        $this->user = User::factory(1)->createOne();
@endif
    }

@if (in_array('C', $options))
@foreach($fields as $field)
@if ($field['type']==='string')
    public function test_{{$field['name']}}_should_have_a_max_of_{{config('entity-generator.max_size_string', 50)}}_characters_when_create_{{$entities}}()
     {   
         $data = [];
         $data['name'] = str_repeat('x', ({{config('entity-generator.max_size_string', 50)}} + 5));
         $response = $this->actingAs($this->user)->postJson('/api/{{$entities}}', $data);
         $response->assertStatus(422);
         $response->assertJson([
             'details' => ['{{$field['name']}}' =>  [__('validation.max.string', ['attribute' => trans('validation.attributes.{{$field['name']}}'), 'max' => {{config('entity-generator.max_size_string', 50)}}])]],
         ]);
     }

@endif
@if ($field['condition']==='required')
    public function test_{{$field['name']}}_should_be_required_when_create_{{$entities}}()
     {        
         $response = $this->actingAs($this->user)->postJson(route('{{$entities}}.store'), []);
         $response->assertStatus(422);
         $response->assertJson([
             'details' => ['{{$field['name']}}' =>  [__('validation.required', ['attribute' => trans('validation.attributes.{{$field['name']}}')])]],
         ]);
     }

@endif
@endforeach
    public function test_create()
    {
        $data = Post::factory(1)->makeOne()->toArray();
@if (!$withAuth)
        $response = $this->postJson('/api/{{$entities}}', $data);
@else
        $response = $this->actingAs($this->user)->postJson('/api/{{$entities}}', $data);
@endif
@if($shouldUseStatus)
        $response->assertStatus(Response::HTTP_CREATED);
@else
        $response->assertCreated();
@endif

        $response->assertJson([
            'message' => trans('validation.message.record_inserted_successfully'),
            'data' => [
@foreach($fields as $field)
                '{{$field['name']}}' => $data['{{$field['name']}}'],
@endforeach
            ]
        ]);

        $this->assertDatabaseHas('{{$databaseTableName}}', [
@foreach($fields as $field)
            '{{$field['name']}}' => $data['{{$field['name']}}'],
@endforeach
        ]);
    }

@if ($withAuth)
    public function test_create_no_auth()
    {
        $data = Post::factory(1)->makeOne()->toArray();
        $response = $this->postJson('/api/{{$entities}}', $data);

@if($shouldUseStatus)
        $response->assertStatus(Response::HTTP_UNAUTHORIZED);
@else
        $response->assertUnauthorized();
@endif
    }

@endif
@endif
@if (in_array('U', $options))

    public function test_update()
    {
        ${{\Illuminate\Support\Str::snake($entity)}} = {{$entity}}::factory(1)->createOne();
        ${{\Illuminate\Support\Str::snake($entity)}}New = {{$entity}}::factory(1)->makeOne()->toArray();
        
        $response = $this->actingAs($this->user)->putJson('/api/{{$entities}}/' . ${{\Illuminate\Support\Str::snake($entity)}}->id, ${{\Illuminate\Support\Str::snake($entity)}}New);
@if($shouldUseStatus)
        $response->assertStatus(Response::HTTP_OK);
@else
        $response->assertOK();
@endif
        $response->assertJson([
            'message' => trans('validation.message.record_updated_successfully'),
            'data' => [
@foreach($fields as $field)
                '{{$field['name']}}' => ${{\Illuminate\Support\Str::snake($entity)}}New['{{$field['name']}}'],
@endforeach
            ]
        ]);
        $this->assertDatabaseHas('{{$databaseTableName}}', [
@foreach($fields as $field)
            '{{$field['name']}}' => ${{\Illuminate\Support\Str::snake($entity)}}New['{{$field['name']}}'],
@endforeach
        ]);
    }

    public function test_update_not_exists()
    {
        {{$entity}}::factory(1)->createOne();
        ${{\Illuminate\Support\Str::snake($entity)}}New = {{$entity}}::factory(1)->makeOne()->toArray();
@if (!$withAuth)
        $response = $this->putJson('/api/{{$entities}}/' . 0, ${{\Illuminate\Support\Str::snake($entity)}}New);
@else
        $response = $this->actingAs($this->user)->putJson('/api/{{$entities}}/' . 0, ${{\Illuminate\Support\Str::snake($entity)}}New);
@endif
@if($shouldUseStatus)
        $response->assertStatus(Response::HTTP_NOT_FOUND);
@else
        $response->assertNotFound();
@endif
    }

@if ($withAuth)

    public function test_update_no_auth()
    {
        ${{\Illuminate\Support\Str::snake($entity)}} = {{$entity}}::factory(1)->createOne();
        ${{\Illuminate\Support\Str::snake($entity)}}New = {{$entity}}::factory(1)->makeOne()->toArray();
        $response = $this->putJson('/api/{{$entities}}/' . ${{\Illuminate\Support\Str::snake($entity)}}->id, ${{\Illuminate\Support\Str::snake($entity)}}New);
@if($shouldUseStatus)
        $response->assertStatus(Response::HTTP_UNAUTHORIZED);
@else
        $response->assertUnauthorized();
@endif
    }

@endif
@endif
@if (in_array('D', $options))

    public function test_delete_post()
    {
        ${{\Illuminate\Support\Str::snake($entity)}} = {{$entity}}::factory(1)->createOne();   
@if (!$withAuth)
        $response = $this->deleteJson('/api/{{$entities}}/' . ${{\Illuminate\Support\Str::snake($entity)}}->id);
@else
        $response = $this->actingAs($this->user)->deleteJson('/api/{{$entities}}/' . ${{\Illuminate\Support\Str::snake($entity)}}->id);
@endif    
@if($shouldUseStatus)
        $response->assertStatus(Response::HTTP_NO_CONTENT);
@else
        $response->assertNoContent();
@endif
        $this->assertDatabaseMissing('{{$databaseTableName}}', [
            'id' => ${{\Illuminate\Support\Str::snake($entity)}}->id
        ]);
    }

    public function test_delete_not_exists()
    {
        {{$entity}}::factory(1)->createOne();
        ${{\Illuminate\Support\Str::snake($entity)}}New = {{$entity}}::factory(1)->makeOne()->toArray();

@if (!$withAuth)
        $response = $this->deleteJson('/api/{{$entities}}/' . 0, ${{\Illuminate\Support\Str::snake($entity)}}New);
@else
        $response = $this->actingAs($this->user)->deleteJson('/api/{{$entities}}/' . 0, $postNew);
@endif
@if($shouldUseStatus)
        $response->assertStatus(Response::HTTP_NOT_FOUND);
@else
        $response->assertNotFound();
@endif
        $this->assertDatabaseMissing('{{$databaseTableName}}', [
            'id' => 0
        ]);
    }

@if ($withAuth)
    public function test_delete_no_auth()
    {
        {{$entity}}::factory(1)->createOne();
        $response = $this->deleteJson( '/api/{{$entities}}/1');

@if($shouldUseStatus)
        $response->assertStatus(Response::HTTP_UNAUTHORIZED);
@else
        $response->assertUnauthorized();
@endif
    }

@endif
@endif
@if (in_array('R', $options))
    public function test_get_post_by_id()
    {
        ${{\Illuminate\Support\Str::snake($entity)}} = Post::factory(1)->createOne();
@if (!$withAuth)
		$response = $this->getJson('/api/{{$entities}}/' . ${{\Illuminate\Support\Str::snake($entity)}}->id);
@else
		$response = $this->actingAs($this->user)->getJson('/api/{{$entities}}/' . ${{\Illuminate\Support\Str::snake($entity)}}->id);
@endif
@if($shouldUseStatus)
        $response->assertStatus(Response::HTTP_OK);
@else
        $response->assertOk();
@endif
        $response->assertJsonStructure([
            'data' => [
@foreach($fields as $field)
            '{{$field['name']}}',
@endforeach
            ]
        ]);
    }

    public function test_get_by_id_not_exists()
    {
        {{$entity}}::factory(1)->createOne();
        ${{\Illuminate\Support\Str::snake($entity)}}New = {{$entity}}::factory(1)->makeOne()->toArray();

@if (!$withAuth)
        $response = $this->getJson('/api/{{$entities}}/' . 0, ${{\Illuminate\Support\Str::snake($entity)}}New);
@else
        $response = $this->actingAs($this->user)->getJson('/api/{{$entities}}/' . 0, ${{\Illuminate\Support\Str::snake($entity)}}New);
@endif
@if($shouldUseStatus)
        $response->assertStatus(Response::HTTP_NOT_FOUND);
@else
        $response->assertNotFound();
@endif
        $this->assertDatabaseMissing('{{$databaseTableName}}', [
            'id' => 0
        ]);
    }

@if ($withAuth)
    public function test_get_by_id_no_auth()
    {
        {{$entity}}::factory(1)->createOne();
        $response = $this->getJson( '/api/{{$entities}}/1');

@if($shouldUseStatus)
        $response->assertStatus(Response::HTTP_UNAUTHORIZED);
@else
        $response->assertUnauthorized();
@endif
    }

@endif


    public function getSearchFilters()
    {
        return [
            [
                'filter' => ['all' => 1],
                'result' => 'search_all.json'
            ],
            [
                'filter' => [
                    'page' => 2,
                    'per_page' => 2
                ],
                'result' => 'search_by_page_per_page.json'
            ],
        ];
    }


	public function test_search_all()
    {
        $filter = ['all' => 1];
        $total_{{$entities}}=3;
        {{$entity}}::factory($total_posts)->create();
@if (!$withAuth)
		$response = $this->getJson('/api/{{$entities}}', $filter);
@else
		$response = $this->actingAs($this->user)->getJson('/api/{{$entities}}', $filter);
@endif
        $this->assertEquals($response->json('meta.total'),$total_{{$entities}});
        $response->assertJsonCount($total_{{$entities}}, 'data');
        $this->assertDatabaseCount('{{$entities}}', $total_{{$entities}});
        $response->assertJsonStructure([
            'data' => [
                0 => [
@foreach($fields as $field)
            	'{{$field['name']}}',
@endforeach
                    ]
                ],
            'links',
            'meta' => [
                'current_page',
                'from',
                'last_page',
                'links',
                'path',
                'per_page',
                "to",
                "total"
                ],
        ]);
        foreach ($response->json('data') as $post) {
            $this->assertDatabaseHas('posts', [
                'name' => $post['name'],
                'priority' => $post['priority'],
                'user_id' => $post['user_id'],
            ]);
        }
@if($shouldUseStatus)
        $response->assertStatus(Response::HTTP_OK);
@else
        $response->assertOk();
@endif
    }

	public function test_search_query()
    {
        $filter = ['query' => 'test'];
        $total_{{$entities}}=3;
        {{$entity}}::factory($total_posts)->create();
		$post = Post::factory()->createOne(['name'=>'test 1']);
@if (!$withAuth)
		$response = $this->getJson('/api/{{$entities}}?' . http_build_query($filter));
@else
		$response = $this->actingAs($this->user)->getJson('/api/{{$entities}}?' . http_build_query($filter));
@endif
        $this->assertEquals($response->json('meta.total'),1);
        $response->assertJsonCount(1, 'data');
        $this->assertDatabaseCount('{{$entities}}', $total_{{$entities}} +1);
        $response->assertJsonStructure([
            'data' => [
                0 => [
@foreach($fields as $field)
            	'{{$field['name']}}',
@endforeach
                    ]
                ],
            'links',
            'meta' => [
                'current_page',
                'from',
                'last_page',
                'links',
                'path',
                'per_page',
                "to",
                "total"
                ],
        ]);
        foreach ($response->json('data') as $post) {
            $this->assertDatabaseHas('posts', [
                'name' => $post['name'],
                'priority' => $post['priority'],
                'user_id' => $post['user_id'],
            ]);
        }
@if($shouldUseStatus)
        $response->assertStatus(Response::HTTP_OK);
@else
        $response->assertOk();
@endif
    }

@endif
}


