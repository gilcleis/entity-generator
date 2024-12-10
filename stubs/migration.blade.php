use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

@if (version_compare(app()->version(), '9', '>='))
return new class extends Migration
@else
class {{$class}}CreateTable extends Migration
@endif
{
    public function up()
    {
        Schema::create('{{\Illuminate\Support\Str::plural(\Illuminate\Support\Str::snake($entity))}}', function (Blueprint $table) {
            $table->id();
@foreach ($table as $row )
            {!!$row!!}
@endforeach
            $table->timestamps();

@foreach($relations['belongsTo'] as $relation)
            {{addForeignKey($entity,$relation);}}
@endforeach
@foreach($relations['hasOne'] as $relation)
            {{addForeignKey($relation,$entity,true);}}                
@endforeach                
@foreach($relations['hasMany'] as $relation)
            {{addForeignKey($relation,$entity,true);}}
@endforeach
        });

@foreach($relations['belongsToMany'] as $relation)
        {{createBridgeTable($entity, $relation);}}
@endforeach
    }

    public function down()
    {
@foreach($relations['hasOne'] as $relation)
        $this->dropForeignKey('{{$relation}}', '{{$entity}}', true);
@endforeach
@foreach($relations['hasMany'] as $relation)
        $this->dropForeignKey('{{$relation}}', '{{$entity}}', true);
@endforeach
@foreach($relations['belongsToMany'] as $relation)
        {!!dropBridgeTable($entity,$relation)!!}
@endforeach
        Schema::dropIfExists('{{\Illuminate\Support\Str::plural(\Illuminate\Support\Str::snake($entity))}}');
    }

@php
function addForeignKey($fromEntity, $toEntity, $needAddField = false, $onDelete = 'cascade')
{        
    $fieldName = Str::snake($toEntity) . '_id';

    print_r("\$table->foreign('".$fieldName."')->references('id')->on('".getTableName($toEntity)."')->onDelete('".$onDelete."');");
}

function addForeignKeyId($fromEntity, $toEntity, $needAddField = false, $onDelete = 'cascade')
{        
    $fieldName = Str::snake($toEntity) . '_id';

    print_r("\$table->foreignId('".$fieldName."')->references('id')->on('".getTableName($toEntity)."')->onDelete('".$onDelete."');");
}

function getTableName($entityName)
{
    $entityName = Str::snake($entityName);

    return Str::plural($entityName);
}

function createBridgeTable($fromEntity, $toEntity)
{
    $bridgeTableName = getBridgeTable($fromEntity, $toEntity);

    echo "Schema::create('".$bridgeTableName."', function (Blueprint \$table) {
        \$table->id();".PHP_EOL;        
        addForeignKeyId($bridgeTableName, $fromEntity, true);
        addForeignKeyId($bridgeTableName, $toEntity, true);
    echo "});";

    
}

function getBridgeTable($fromEntity, $toEntity)
{
    $entities = [Str::snake($fromEntity), Str::snake($toEntity)];
    sort($entities, SORT_STRING);

    return implode('_', $entities);
}

function dropForeignKey($fromEntity, $toEntity, $needDropField = false)
{
    $field = Str::snake($toEntity) . '_id';
    $table = getTableName($fromEntity);

     return "Schema::table('{$table}', function (Blueprint \$table)  {
        \$table->dropColumn(['{$field}']);
      
    });";

}

function dropBridgeTable($fromEntity, $toEntity)
    {
        $quotation_marks ="'";
        $bridgeTableName = getBridgeTable($fromEntity, $toEntity);
        //print_r(dropForeignKey($bridgeTableName, $fromEntity, true));
        //print_r(dropForeignKey($bridgeTableName, $toEntity, true));
        return "Schema::dropIfExists({$quotation_marks}{$bridgeTableName}{$quotation_marks});";
    }
@endphp

};