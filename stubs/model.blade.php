namespace {{$namespace}};

use App\Traits\ModelTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/** class {{$entity}}
@foreach($fields_type as $field)
 * @property {{$field['type']}} ${{$field['name']}}
@endforeach
*/
class {{$entity}} extends Model
{
    use HasFactory,ModelTrait;

    protected $table = '{{\Illuminate\Support\Str::plural(\Illuminate\Support\Str::snake($entity))}}';

    protected $perPage = 12;

    protected $fillable = [
@foreach($fields as $field)
        '{{$field}}',
@endforeach
    ];

    protected $hidden = ['pivot'];

@if(!empty($casts))
    protected $casts = [
@foreach($casts as $fieldName => $cast)
        '{{$fieldName}}' => '{{$cast}}',
@endforeach
    ];
@endif
@foreach($relations as $relation)

    @include(config('entity-generator.stubs.relation'), $relation)
@endforeach
}