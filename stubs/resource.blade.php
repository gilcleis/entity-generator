namespace {{$namespace}};

use Illuminate\Support\Str;
use Illuminate\Http\Resources\Json\JsonResource;

class {{$entity}}Resource extends JsonResource
{
    public function toArray($request): array
    {
        // return parent::toArray($request);
@if (isset($relations['belongsTo'][0]))
        $data = [
            'id' => $this->id,
@foreach($fields as $field)
            '{{$field}}' => $this->{{$field}},
@endforeach
            'created_at' => optional($this->created_at)->format('Y-m-d H:i:s')
        ];
@foreach($relations['belongsTo'] as $relation)
        {!!addForeignKey($relation);!!}
@endforeach
        return $data;   
@else
        return 
            [
            'id' => $this->id,
@foreach($fields as $field)
            '{{$field}}' => $this->{{$field}},
@endforeach
            'created_at' => optional($this->created_at)->format('Y-m-d H:i:s')
        ];
@endif
    }
}



@php
function addForeignKey($relation)
{        
    $field = Str::snake($relation);
return "
        if(isset($this->resource->getRelations()['user'])){
            \$data['{$field}'] = ['id' => \$this->{$field}->id,'name' => \$this->{$field}->name];
        }
";
    return "isset(\$this->resource->getRelations()['{$field}'] ) ? ['{$field}' => ['id' => \$this->{$field}->id,'name' => \$this->{$field}->name]] : [])";
}
@endphp


