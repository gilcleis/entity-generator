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
        $this->assertEquals({{config('entity-generator.max_size_string', 50)}}, $this->{{\Illuminate\Support\Str::snake($entity)}}->getPerPage());
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
        ], $this->campaign->getFillable());
    }

    /** @test */
    public function hidden(): void
    {
        $this->assertEquals([
        ], $this->{{\Illuminate\Support\Str::snake($entity)}}->getHidden());
    }

}


