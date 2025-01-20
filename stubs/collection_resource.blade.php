namespace {{$namespace}};

use Illuminate\Http\Resources\Json\ResourceCollection;

class {{$singular_name}}Collection extends ResourceCollection
{
    public $collects = {{$singular_name}}Resource::class;
}