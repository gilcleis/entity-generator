namespace {{$namespace}};

use Closure;
use Gilcleis\Support\Exceptions\InvalidModelException;
use Illuminate\Database\Eloquent\Builder as Query;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class BaseRepository extends SearchBaseRepository
{
    protected $model;
    protected $fields;
    protected $primaryKey;

    protected $withTrashed = false;
    protected $onlyTrashed = false;
    protected $forceMode = false;

    protected $shouldSettablePropertiesBeReset = true;

    protected $attachedRelations = [];
    protected $attachedRelationsCount = [];

    public function __construct($modelClass)
    {
        $this->model = new $modelClass();
        $this->fields = $modelClass::getFields();
        $this->primaryKey = $this->model->getKeyName();
        $this->checkPrimaryKey();
    }

    protected function checkPrimaryKey(): void
    {
        if (is_null($this->primaryKey)) {
            $modelClass = get_class($this->model);

            throw new InvalidModelException("Model {$modelClass} must have primary key.");
        }
    }

    public function all(): Collection
    {
        return $this->model->all();
    }

    public function create(array $data): Model
    {
        $entityData = Arr::only($data, $this->fields);
        $modelClass = get_class($this->model);
        $model = new $modelClass();

        if ($this->forceMode) {
            $model->forceFill($entityData);
        } else {
            $model->fill(Arr::only($entityData, $model->getFillable()));
        }

        $model->save();
        $model->refresh();

        if (!empty($this->attachedRelations)) {
            $model->load($this->attachedRelations);
        }

        $this->postQueryHook();

        return $model;
    }

    public function update($where, array $data): ?Model
    {
        $item = $this->getQuery($where)->first();

        if (empty($item)) {
            $this->postQueryHook();

            return null;
        }

        if ($this->forceMode) {
            $item->forceFill(Arr::only($data, $this->fields));
        } else {
            $item->fill(Arr::only($data, $item->getFillable()));
        }

        $item->save();
        $item->refresh();

        if (!empty($this->attachedRelations)) {
            $item->load($this->attachedRelations);
        }

        $this->postQueryHook();

        return $item;
    }

    public function force($value = true): self
    {
        $this->forceMode = $value;
        return $this;
    }

    public function exists($where): bool
    {
        $result = $this->getQuery($where)->exists();
        $this->postQueryHook();
        return $result;
    }

    public function delete($where): int
    {
        $query = $this->getQuery($where);

        if ($this->forceMode) {
            $result = $query->forceDelete();
        } else {
            $result = $query->delete();
        }

        $this->postQueryHook();

        return $result;
    }

    public function count($where = []): int
    {
        $result = $this->getQuery($where)->count();

        $this->postQueryHook();

        return $result;
    }

    public function get(array $where = []): Collection
    {
        $result = $this->getQuery($where)->get();

        $this->postQueryHook();

        return $result;
    }

    public function first($where = []): ?Model
    {
        $result = $this->getQuery($where)->first();
        
        $this->postQueryHook();

        return $result;
    }

    public function last(array $where = [], string $column = 'created_at'): ?Model
    {
        $result = $this
            ->getQuery($where)
            ->latest($column)
            ->first();

        $this->postQueryHook();

        return $result;
    }

    public function findBy(string $field, $value): ?Model
    {
        return $this->first([$field => $value]);
    }

    public function find($id): ?Model
    {
        return $this->first($id);
    }
    
    protected function getQuery($where = []): Query
    {
        $query = $this->model->query();

        if ($this->onlyTrashed) {
            $query->onlyTrashed();

            $this->withTrashed = false;
        }

        if ($this->withTrashed && $this->hasSoftDeleteTrait()) {
            $query->withTrashed();
        }

        if (!empty($this->attachedRelations)) {
            $query->with($this->attachedRelations);
        }

        if (!empty($this->attachedRelationsCount)) {
            foreach ($this->attachedRelationsCount as $requestedRelations) {
                list($countRelation, $relation) = extract_last_part($requestedRelations);

                if (empty($relation)) {
                    $query->withCount($countRelation);
                } else {
                    $query->with([
                        $relation => function ($query) use ($countRelation) {
                            $query->withCount($countRelation);
                        },
                    ]);
                }
            }
        }

        return $this->constructWhere($query, $where);
    }

    protected function postQueryHook(): void
    {
        if ($this->shouldSettablePropertiesBeReset) {
            $this->onlyTrashed(false);
            $this->withTrashed(false);
            $this->force(false);
            $this->with([]);
            $this->withCount([]);
        }
    }

    protected function constructWhere(Query $query, $where = [], ?string $field = null): Query
    {
        if (!is_array($where)) {
            $field = (empty($field)) ? $this->primaryKey : $field;

            $where = [
                $field => $where,
            ];
        }

        foreach ($where as $field => $value) {
            $this->addWhere($query, $field, $value);
        }

        return $query;
    }

    protected function hasSoftDeleteTrait(): bool
    {
        $traits = class_uses(get_class($this->model));

        return in_array(SoftDeletes::class, $traits);
    }

    public function onlyTrashed($enable = true): self
    {
        $this->onlyTrashed = $enable;

        return $this;
    }

    public function withTrashed($enable = true): self
    {
        $this->withTrashed = $enable;

        return $this;
    }

    public function with($relations): self
    {
        $this->attachedRelations = Arr::wrap($relations);

        return $this;
    }

    public function withCount($relations): self
    {
        $this->attachedRelationsCount = Arr::wrap($relations);

        return $this;
    }

    protected function addWhere(Query &$query, string $field, $value, string $sign = '='): void
    {
        $this->applyWhereCallback($query, $field, function (&$query, $field) use ($sign, $value) {
            $query->where($field, $sign, $value);
        });
    }

    protected function applyWhereCallback(Query $query, string $field, Closure $callback): void
    {
        if (Str::contains($field, '.')) {
            list($conditionField, $relations) = extract_last_part($field);

            $query->whereHas($relations, function ($q) use ($callback, $conditionField) {
                $callback($q, $conditionField);
            });
        } else {
            $callback($query, $field);
        }
    }

    public function restore($where): int
    {
        $result = $this->getQuery($where)->onlyTrashed()->restore();

        $this->postQueryHook();

        return $result;
    }
}