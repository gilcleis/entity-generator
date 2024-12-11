namespace {{$namespace}};

interface {{$entity}}RepositoryInterface
{
    public function all(): Collection;
    public function create(array $data): Model
    public function update($where, array $data): ?Model
    public function delete($where): int
    public function find($id): ?Model
}