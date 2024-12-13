@php($shouldUseStatus = version_compare(app()->version(), '7', '<'))
namespace Tests\Feature\Models;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\{{$entity}};

class {{$entity}}ModelTest extends TestCase
{
    use RefreshDatabase;    
    protected ${{\Illuminate\Support\Str::snake($entity)}};

    private $campaign;

    public function setUp() : void
    {
        parent::setUp();
        $this->refreshDatabase();
        $this->artisan('migrate:fresh');
        $this->{{\Illuminate\Support\Str::snake($entity)}} = new {{$entity}}();
    }

    /** @test */
    public function getTableName(): void
    {
        $this->assertEquals('{{$databaseTableName}}', $this->{{\Illuminate\Support\Str::snake($entity)}}->getTable());
    }

    /** @test */
    public function getPerPage(): void
    {
        $this->assertEquals({{config('entity-generator.items_per_page', 50)}}, $this->{{\Illuminate\Support\Str::snake($entity)}}->getPerPage());
    }

    /** @test */
    public function getKeyName(): void
    {
        $this->assertEquals('id', $this->{{\Illuminate\Support\Str::snake($entity)}}->getKeyName());
    }

    /** @test */
    public function getKeyType(): void
    {
        $this->assertEquals('int', $this->{{\Illuminate\Support\Str::snake($entity)}}->getKeyType());
    }

    /** @test */
    public function getIncrementing(): void
    {
        $this->assertEquals(true, $this->{{\Illuminate\Support\Str::snake($entity)}}->getIncrementing());
    }

    /** @test */
    public function fillable(): void
    {
        $this->assertEquals([
@foreach($fields as $field)
            '{{$field['name']}}',
@endforeach
        ], $this->{{\Illuminate\Support\Str::snake($entity)}}->getFillable());
    }

    /** @test */
    public function hidden(): void
    {
        $this->assertEquals([
            'pivot'
        ], $this->{{\Illuminate\Support\Str::snake($entity)}}->getHidden());
    }

    /** @test */
    public function CastsAttribute(): void
    {
        $casts = [
        'id' => 'int',
@foreach($casts as $fieldName => $cast)
        '{{$fieldName}}' => '{{$cast}}',
@endforeach
        ];
        $this->assertEquals($casts, $this->{{\Illuminate\Support\Str::snake($entity)}}->getCasts());
    }

}


