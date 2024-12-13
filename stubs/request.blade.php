@inject('requestsGenerator', 'Gilcleis\Support\Generators\RequestsGenerator')
namespace {{$namespace}}\{{$requestsFolder}};

use {{$namespace}}\Request;
@if($needToValidate)
use {{$servicesNamespace}}\{{$entity}}Service;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Response;

@endif

class {{$method}}{{$entity}}Request extends Request
{
@if($method !== $requestsGenerator::DELETE_METHOD)
    public function rules(): array
    {
@if(!empty($parameters))
        return [
@foreach($parameters as $parameter)
            '{{$parameter['name']}}' => '{{implode('|', $parameter['rules'])}}',
@endforeach
        ];
@else
        return [];
@endif
    }
@endif
@if($needToValidate)
@if($method !== $requestsGenerator::DELETE_METHOD)

@endif
@if(version_compare(app()->version(), '5.6', '<'))
    public function validate()
    {
        parent::validate();

@else
    public function validateResolved()
    {
        parent::validateResolved();

@endif
        $service = app({{$entity}}Service::class);

        if (!$service->exists($this->route('id'))) {            
            $response = response()->json([
                "error" => "Erro no envio de dados.",
                "details" => trans('validation.message.record_not_found')
            ], Response::HTTP_NOT_FOUND);

            throw new HttpResponseException($response);
        }
    }
@endif
}


