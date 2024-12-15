namespace {{$namespace}};

@inject('str', 'Illuminate\Support\Str')
use {{$resourcesNamespace}}\{{$entity}}Collection;
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
    /**
    * Create a new.
    *
    * @param \App\Http\Requests\{{$entity}}Request $request The request containing the {{$str::lower($entity)}} data.
    * @return \Illuminate\Http\JsonResponse The created {{$str::lower($entity)}} resource.
    */
    public function store({{$entity}}Request $request)
    {
        $data = $request->validated();
        $result = $this->service->create($data);
        $response =  new {{$entity}}Resource($result);

        return response()->json(['message' => trans('validation.message.record_inserted_successfully'),'data' => $response], Response::HTTP_CREATED);
    }

@endif
@if (in_array('R', $options))
    /**
    * Get by ID.
    *
    * @param int $id The ID of the {{$str::lower($entity)}}  to retrieve.
    * @return \Illuminate\Http\JsonResponse The {{$str::lower($entity)}} resource.
    */
    public function show({{$entity}}Request $request, $id): \Illuminate\Http\JsonResponse
    {
        $result = $this->service
            ->with($request->input('with', []))
            ->withCount($request->input('with_count', []))
            ->find($id);

        return response()->json(new {{$entity}}Resource($result), Response::HTTP_OK);
    }    

    /**
    * Get all.
    *
    * @return \Illuminate\Http\JsonResponse The collection of {{$str::lower($entity)}} resources.
    */
    public function index({{$entity}}Request $request): \Illuminate\Http\JsonResponse
    {
        $result = $this->service
            ->with($request->input('with', []))
            ->withCount($request->input('with_count', []))
            ->all();
        return response()->json(new {{$entity}}Collection($result), Response::HTTP_OK);
    }    

{{--    public function search(Search{{$str::plural($entity)}}Request $request)
    {
        $result = $this->service->search($request->validated());

        return {{$str::plural($entity)}}CollectionResource::make($result);
    }
--}}
@endif
@if (in_array('U', $options))
    /**
    * Update by ID.
    *
    * @param \App\Http\Requests\{{$entity}}Request $request The request containing the updated {{$str::lower($entity)}} data.
    * @param int $id The ID of the {{$str::lower($entity)}} to update.
    * @return \Illuminate\Http\JsonResponse The updated {{$str::lower($entity)}} resource.
    */
    public function update({{$entity}}Request $request, $id): \Illuminate\Http\JsonResponse
    {
        $this->service->update($id, $request->validated());
        $response = new {{$entity}}Resource($this->service->find($id));

        return response()->json(['message' => trans('validation.message.record_updated_successfully'),'data' => $response], Response::HTTP_OK);        
    }

@endif
@if (in_array('D', $options))
    /**
    * Delete by ID.
    *
    * @param int $id The ID of the {{$str::lower($entity)}}  to delete.
    * @return \Illuminate\Http\Response A response with a 204 No Content status.
    */
    public function delete(int $id): Response
    {
        $this->service->delete($id);

        return response('', Response::HTTP_NO_CONTENT);
    }
@endif
}