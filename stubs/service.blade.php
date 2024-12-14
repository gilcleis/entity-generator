namespace {{$namespace}};

use App\Repositories\Contracts\{{$entity}}RepositoryInterface;
use {{$repositoriesNamespace}}\{{$entity}}Repository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
{{--
    Laravel inserts two spaces between @property and type, so we are forced
    to use hack here to preserve one space
--}}
@php
echo <<<PHPDOC
/**
 * @mixin {$entity}Repository
 * @property {$entity}Repository \$repository
 */

PHPDOC;
@endphp
class {{$entity}}Service 
{
    public function __construct(
        //private {{$entity}}RepositoryInterface $repository
        private {{$entity}}Repository $repository
    ) {        
    }

@if (in_array('C', $options))
    public function create(array $data): ?Model
    {
        return $this->repository->create($data);
    }
@endif  
@if (in_array('U', $options))
    public function update(int $id, array $data): ?Model
    {
        $where = ['id' => $id];

        return $this->repository->update($where, $data);
    }
@endif  
@if (in_array('D', $options))
    public function delete(array|int $id): int
    {
        return $this->repository->delete($where);
    }

    public function restore(array|int $id): int
    {
        return $this->repository->restore($where);
    }
@endif   
@if (in_array('R', $options))
    public function getAll(): Collection
    {
        return $this->repository->all();
    }

    public function find(int $id): ?Model
    {
        return $this->repository->find($id);
    }

    /**
     * Checks if a record exists
     *
     * @param array $where An associative array of field => value pairs to match against.
     * @return bool 
     */
    public function exists(array|int $where): bool
    {
        return $this->repository->exists($where);
    }

    /**
     * Checks the count of records
     *
     * @param array $where An associative array of field => value pairs to match against.
     * @return int The count of matching records.
     */
    public function count(array $where = []): int
    {
        return $this->repository->count($where);
    }

    /**
     * Retrieves a collection of records that match the given criteria.
     *
     * @param array $where An associative array of field => value pairs to match against.
     * @return \Illuminate\Database\Eloquent\Collection The collection of matching records.
     */
    public function get(array $where = []): Collection
    {
        return $this->repository->get($where);
    }

    /**
     * Retrieves the first record that matches the given criteria.
     *
     * @param array $where An associative array of field => value pairs to match against.
     * @return \Illuminate\Database\Eloquent\Model|null The first matching record, or null if no record is found.
     */
    public function first(array $where = []): ?Model
    {
        return $this->repository->first($where);
    }

    public function findBy($field, $value): ?Model
    {
        return $this->repository->findBy($field,$value);
    }

    public function withTrashed($field, $value)
    {
        return $this->repository->withTrashed($field,$value);
    }

    public function onlyTrashed($field, $value)
    {
        return $this->repository->onlyTrashed($field, $value);
    }

    public function with($relation)
    {
        return $this->repository->with($relation);
    }

    public function withCount($relation)
    {
        return $this->repository->withCount($relation);
    }
@endif
}
