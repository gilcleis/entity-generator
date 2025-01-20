namespace {{$namespace}};

use {{$modelNamespace}}\{{$entity}};
@if(!empty($fields['json']))
use Illuminate\Support\Arr;
@endif
{{--
    Laravel inserts two spaces between @property and type, so we are forced
    to use hack here to preserve one space
--}}
@php
echo <<<PHPDOC
/**
 * @property {$entity} \$model
 */

PHPDOC;
@endphp
class {{$entity}}Repository extends BaseRepository implements Contracts\{{$entity}}RepositoryInterface
{
    public function __construct(private {{$entity}} $entity)
    {
        $this->entity = $entity;
        parent::__construct($entity);
    }
}
