namespace {{$namespace}};

@inject('str', 'Illuminate\Support\Str')
use {{$resourcesNamespace}}\{{$str::plural($entity)}}CollectionResource;
use {{$requestsNamespace}}\{{$entity}}Request;
use {{$resourcesNamespace}}\{{$entity}}Resource;
use {{$servicesNamespace}}\{{$entity}}Service;
@if (in_array('D', $options) || in_array('U', $options))
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Routing\Controller;

@endif
class {{$entity}}Controller extends Controller
{
    public function __construct(private {{$entity}}Service $service)
    {        
    }

@if (in_array('C', $options))
    public function create({{$entity}}Request $request)
    {
        $data = $request->validated();
        $result = $this->service->create($data);
        $response =  {{$entity}}Resource::make($result);

        return response()->json(['message' => trans('validation.message.record_inserted_successfully'),'data' => $response], Response::HTTP_CREATED);
    }

@endif
@if (in_array('R', $options))
    public function show({{$entity}}Request $request, $id): \Illuminate\Http\JsonResponse
    {
        $result = $this->service
            ->with($request->input('with', []))
            ->withCount($request->input('with_count', []))
            ->find($id);

        return response()->json(new {{$entity}}Resource($result), Response::HTTP_OK);
    }    

{{--    public function search(Search{{$str::plural($entity)}}Request $request)
    {
        $result = $this->service->search($request->validated());

        return {{$str::plural($entity)}}CollectionResource::make($result);
    }
--}}
@endif
@if (in_array('U', $options))
    public function update({{$entity}}Request $request, $id)
    {
        $this->service->update($id, $request->validated());
        $response = {{$entity}}Resource::make($this->service->find($id));

        return response()->json(['message' => trans('validation.message.record_updated_successfully'),'data' => $response], Response::HTTP_OK);        
    }

@endif
@if (in_array('D', $options))
    public function delete(int $id)
    {
        $this->service->delete($id);

        return response('', Response::HTTP_NO_CONTENT);
    }
@endif
}