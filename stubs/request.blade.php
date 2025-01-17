@inject('requestsGenerator', 'Gilcleis\Support\Generators\RequestsGenerator')
namespace {{$namespace}};

@if($needToValidate)
use {{$servicesNamespace}}\{{$entity}}Service;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Response;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

@endif
class {{$entity}}Request extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {@foreach($data as $d)@if($d['method'] == 'Get') 
        if ($this->method() == 'GET') {
            return $this->rulesGet();
        }
@endif
@if($d['method'] == 'Create') 
        if ($this->method() == 'POST') {
            return $this->rulesCreate();
        }
@endif
@if($d['method'] == 'Update') 
        if (in_array($this->method(), ['PUT', 'PATCH'])) {
            return $this->rulesUpdate();
        }
        return [];
@endif
@endforeach
    }
@foreach($data as $d)      @if(in_array($d['method'], ['Update', 'Create','Get','Search'])) 
    public function rules{{$d['method']}}(): array
    {
        return [
    @foreach($d['parameters'] as $parameter)
        '{{$parameter['name']}}' => '{{implode('|', $parameter['rules'])}}',
    @endforeach
    ];          
    }
    @endif
@endforeach

    public function failedValidation(Validator $validator)
    {
        $errors = $validator->errors();
        $response = response()->json([
            "error" => "Erro no envio de dados.",
            "details" => $errors->messages()
        ], Response::HTTP_UNPROCESSABLE_ENTITY);

        throw new HttpResponseException($response);
    }

@if(in_array(true, array_column($data, 'needToValidate')))
    public function validateResolved()
    {
        parent::validateResolved();

        if (!in_array($this->method(), ['PUT', 'PATCH','DELETE','GET'])) {
            return;
        }

        $service = app({{$entity}}Service::class);

        if ($this->route('id') != null && !$service->exists($this->route('id'))) {            
            $response = response()->json([
                "error" => "Erro no envio de dados.",
                "details" => trans('validation.message.record_not_found')
            ], Response::HTTP_NOT_FOUND);

            throw new HttpResponseException($response);
        }    
    }@endif   
}


