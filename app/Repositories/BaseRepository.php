<?php

declare(strict_types=1);

namespace App\Repositories;

use Illuminate\Container\Container as Application;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

abstract class BaseRepository
{
    /**
     * @var Model
     */
    protected $model;

    /**
     * @var array
     */
    protected $fieldSearchable = [];

    /**
     * @var Application
     */
    protected $app;

    /**
     * @throws \Exception
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
        $this->makeModel();
    }

    /**
     * Get searchable fields array
     *
     * @return array
     */
    abstract public function getFieldsSearchable();

    /**
     * Configure the Model
     *
     * @return string
     */
    abstract public function model();

    /**
     * Make Model instance
     *
     * @return Model
     *
     * @throws \Exception
     */
    public function makeModel()
    {
        $model = $this->app->make($this->model());

        if (!$model instanceof Model) {
            throw new \Exception("Class {$this->model()} must be an instance of Illuminate\\Database\\Eloquent\\Model");
        }

        return $this->model = $model;
    }

    /**
     * Paginate records for scaffold.
     *
     * @param  int  $perPage
     * @param  array  $columns
     * @return LengthAwarePaginator
     */
    public function paginate($perPage, $columns = ['*']): LengthAwarePaginator
    {
        $query = $this->allQuery();

        return $query->paginate($perPage, $columns);
    }

    /**
     * Build a query for retrieving all records.
     *
     * @param  array  $search
     * @param  int|null  $skip
     * @param  int|null  $limit
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function allQuery($search = [], $skip = null, $limit = null)
    {
        $query = $this->model->newQuery();

        if (count($search)) {
            foreach ($search as $key => $value) {
                if (in_array($key, $this->getFieldsSearchable())) {
                    $query->where($key, $value);
                }
            }
        }

        if (!is_null($skip)) {
            $query->skip($skip);
        }

        if (!is_null($limit)) {
            $query->limit($limit);
        }

        return $query;
    }

    /**
     * Retrieve all records with given filter criteria
     *
     * @param  array  $search
     * @param  int|null  $skip
     * @param  int|null  $limit
     * @param  array  $columns
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function all($search = [], $skip = null, $limit = null, $columns = ['*'])
    {
        $query = $this->allQuery($search, $skip, $limit);

        return $query->get($columns);
    }

    /**
     * Create model record
     *
     * @param  array  $input
     * @return Model
     */
    public function create($input): Model
    {
        $model = $this->model->newInstance($input);

        $model->save();

        return $model;
    }

    /**
     * Find model record for given id
     *
     * @param  int  $id
     * @param  array  $columns
     * @return Model|null
     */
    public function find($id, $columns = ['*']): ?Model
    {
        $query = $this->model->newQuery();

        return $query->find($id, $columns);
    }

    /**
     * Update model record for given id
     *
     * @param  array  $input
     * @param  int  $id
     * @return Model
     */
    public function update($input, $id): Model
    {
        $query = $this->model->newQuery();

        $model = $query->findOrFail($id);

        $model->fill($input);

        $model->save();

        return $model;
    }

    /**
     * @param  int  $id
     * @return bool
     *
     * @throws \Exception
     */
    public function delete($id): bool
    {
        $query = $this->model->newQuery();

        $model = $query->findOrFail($id);

        return $model->delete();
    }

    /**
     * Add a basic where clause to the query.
     *
     * @param  \Closure|string|array  $reference
     * @param  mixed  $operator
     * @param  mixed  $value
     * @param  string  $boolean
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function findBy(\Closure|array|string $reference, $value = null, $operator = '=', $boolean = 'and')
    {
        $query = $this->model->newQuery();

        if ($reference instanceof \Closure) {
            return $query->where($reference);
        }

        if (is_string($reference)) {
            return $query->where($reference, $operator, $value, $boolean);
        }

        if (is_array($reference)) {
            return $query->where($reference);
        }

        throw new \InvalidArgumentException('Invalid argument type for findBy');
    }

    /**
     * Add a basic where clause to the query.
     *
     * @param  Request  $request
     * @param  array  $relations
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function findAllFieldsAnd(Request $request, $relations = [])
    {
        $inputs = $request->all();
        if ($relations) {
            $baseModel = $this->model->newQuery()->with($relations);
        } else {
            $baseModel = $this->model->newQuery();
        }

        if ($request->exists('fields')) {
            $baseModel->select($this->mountFieldsToSelect($request));
        }

        foreach ($inputs as $key => $value) {
            if (method_exists($this->model(), 'getFieldType')) {
                $type = $this->model()::getFieldType($key);
                if ($type) {
                    if ($type == 'string') {
                        $baseModel->where($key, 'like', '%' . $value . '%');
                    } else {
                        $baseModel->where($key, $value);
                    }
                }
            }
        }

        return $this->getWherehas($baseModel, $request, $relations, 'OR');
    }

    /**
     * Busca em todos os campos da tabela pela string enviada.
     * Função utiliza OR por padrão
     *
     * @param  Request  $request
     * @param  array  $relations
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function advancedSearch(Request $request, $relations = [])
    {
        $input = $request->get('search');

        if ($relations) {
            $baseModel = $this->model->newQuery()->with($relations);
        } else {
            $baseModel = $this->model->newQuery();
        }

        if ($request->exists('fields')) {
            $baseModel->select($this->mountFieldsToSelect($request));
        }

        $filtros = '';
        if ((trim($input) != 'Busca') && (trim($input) != '')) {
            $filtros = explode(',', $input);
        } else {
            $input = '';
            foreach ($this->fieldSearchable as $colum) {
                if ($input != '') {
                    $input = $request->exists($colum) ? $input . ',' . $colum . '=' . $request->get($colum) : $input;
                } else {
                    $input = $request->exists($colum) ? $colum . '=' . $request->get($colum) : '';
                }
            }
            if ($input != '') {
                $filtros = explode(',', trim($input));
            }
        }

        if ($filtros != '') {
            foreach ($filtros as $reg) {
                $column = explode('=', $reg);
                $baseModel->where(trim($column[0]), '=', trim($column[1]));
            }
        }

        return $this->getWherehas($baseModel, $request, $relations, 'OR');
    }

    /**
     * Busca em todos os campos da tabela pela string enviada.
     * Função utiliza OR por padrão
     *
     * @param  Request  $request
     * @param  array  $relations
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function searchLike(Request $request, $relations = [])
    {
        $input = $request->get('searchLike');
        $inputWhereIn = $request->get('whereIn');

        if ($relations) {
            $baseModel = $this->model->newQuery()->with($relations);
        } else {
            $baseModel = $this->model->newQuery();
        }

        if ($request->exists('fields')) {
            $baseModel->select($this->mountFieldsToSelect($request));
        }

        $filtros = [];

        if ((trim($input) != 'Incremental') && (trim($input) != '')) {
            $filtros = explode(',', $input);
        }

        // Ajuste para comparações >, >= , <=, < onde } representa maior igual e } representa menor igual
        if (!empty($filtros)) {
            if ($request->get('searchLikeType') == 'OR') {
                $baseModel->where(function ($baseModel) use ($filtros) {
                    foreach ($filtros as $reg) {
                        if (strpos($reg, '=')) {
                            $column = explode('=', $reg);
                            $baseModel->orWhere(function ($subQuery) use ($column) {
                                $subQuery->where(trim($column[0]), '=', trim($column[1]))
                                    ->orWhere(trim($column[0]), 'like', trim('%' . $column[1] . '%'));
                            });
                        } elseif (strpos($reg, '>')) {
                            $column = explode('>', $reg);
                            $baseModel->orWhere(trim($column[0]), '>', trim($column[1]));
                        } elseif (strpos($reg, '}')) {
                            $column = explode('}', $reg);
                            $baseModel->orWhere(trim($column[0]), '>=', trim($column[1]));
                        } elseif (strpos($reg, '<')) {
                            $column = explode('<', $reg);
                            $baseModel->orWhere(trim($column[0]), '<', trim($column[1]));
                        } elseif (strpos($reg, '{')) {
                            $column = explode('{', $reg);
                            $baseModel->orWhere(trim($column[0]), '<=', trim($column[1]));
                        }
                    }
                });
            } else {
                $baseModel->where(function ($baseModel) use ($filtros) {
                    foreach ($filtros as $reg) {
                        if (strpos($reg, '=')) {
                            $column = explode('=', $reg);
                            $baseModel->where(function ($subQuery) use ($column) {
                                $subQuery->where(trim($column[0]), '=', trim($column[1]))
                                    ->orWhere(trim($column[0]), 'like', trim('%' . $column[1] . '%'));
                            });
                        } elseif (strpos($reg, '>')) {
                            $column = explode('>', $reg);
                            $baseModel->where(trim($column[0]), '>', trim($column[1]));
                        } elseif (strpos($reg, '}')) {
                            $column = explode('}', $reg);
                            $baseModel->where(trim($column[0]), '>=', trim($column[1]));
                        } elseif (strpos($reg, '<')) {
                            $column = explode('<', $reg);
                            $baseModel->where(trim($column[0]), '<', trim($column[1]));
                        } elseif (strpos($reg, '{')) {
                            $column = explode('{', $reg);
                            $baseModel->where(trim($column[0]), '<=', trim($column[1]));
                        }
                    }
                });
            }
        }

        if ($request->exists('whereIn')) {
            $whereInParams = $request->get('whereIn');

            if (is_string($whereInParams)) {
                $whereInParams = explode(',', $whereInParams);
            }

            foreach ($whereInParams as $query) {
                list($column, $values) = explode('=', $query);
                $valuesArray = explode('|', $values);
                if (!empty($valuesArray)) {
                    $baseModel->whereIn(trim($column), $valuesArray);
                }
            }
        }

        if ($request->exists('additionalQueries')) {
            $additionalQueries = explode(',', $request->get('additionalQueries'));
            foreach ($additionalQueries as $query) {
                if (strpos($query, '=')) {
                    $queryParts = explode('=', $query);

                    $column = trim($queryParts[0]);
                    $value = trim($queryParts[1]);

                    $baseModel->where(function ($queryBuilder) use ($column, $value) {
                        $queryBuilder->where($column, '=', $value);
                    });
                } elseif (strpos($query, '>')) {
                    $column = explode('>', $query);
                    $baseModel->where(trim($column[0]), '>', trim($column[1]));
                } elseif (strpos($query, '}')) {
                    $column = explode('}', $query);
                    $baseModel->where(trim($column[0]), '>=', trim($column[1]));
                } elseif (strpos($query, '<')) {
                    $column = explode('<', $query);
                    $baseModel->where(trim($column[0]), '<', trim($column[1]));
                } elseif (strpos($query, '{')) {
                    $column = explode('{', $query);
                    $baseModel->where(trim($column[0]), '<=', trim($column[1]));
                }
            }
        }

        return $this->getWherehas($baseModel, $request, $relations ?? [], 'OR');
    }

    /**
     * Busca para autocomplete executar o select e o where de acordo com os parametros
     * Função utiliza OR por padrão
     *
     * @param  Request  $request
     * @param  array  $select
     * @param  array  $conditions
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function autocompleteSearch(Request $request, array $select, array $conditions)
    {
        $baseModel = $this->model->newQuery()->select($select);

        foreach ($conditions as $condition) {
            $baseModel->orWhere($condition, 'like', '%' . $request->get('term') . '%');
        }

        return $baseModel->limit(10);
    }

    /**
     * Função responsável por montar o where para tabelas relacionadas
     * de acordo com os parâmetros
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $baseModel
     * @param  Request  $request
     * @param  array  $relations
     * @param  string  $type
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function getWherehas($baseModel, Request $request, array $relations, string $type)
    {
        if ($type == 'AND') {
            foreach ($relations as $relation) {
                if (
                    !empty($request->get(Str::snake($relation) . '_id')) ||
                    !empty($request->get(Str::snake($relation) . '_name'))
                ) {
                    $baseModel->whereHas($relation, function ($query) use ($request, $relation) {
                        empty($request->get(Str::snake($relation) . '_id')) ?: $query
                            ->where(
                                Str::plural(
                                    Str::snake($relation)
                                ) . '.id',
                                $request->get(
                                    Str::snake($relation) . '_id'
                                )
                            );
                        empty($request->get(Str::snake($relation) . '_name')) ?: $query
                            ->where(
                                Str::plural(
                                    Str::snake($relation)
                                ) . '.name',
                                'like',
                                '%' . $request->get(
                                    Str::snake($relation) . '_name'
                                ) . '%'
                            );
                    });
                }
            }
        }

        return $baseModel;
    }

    /**
     * Montar o array para sincronizar na tabela relacionada
     * montagem obrigatória para ManyToMany
     *
     * @param  array  $input
     * @param  string  $fieldsInsert
     * @return array
     */
    public function mountValueRelation(array $input, string $fieldsInsert): array
    {
        $type = [];
        foreach ($input as $value) {
            if (empty($type)) {
                $type = [$value[$fieldsInsert]];
            } else {
                array_push($type, $value[$fieldsInsert]);
            }
        }

        return $type;
    }

    /**
     * Cria a estrutura para sincronizar a tabela de ManyToMany
     *
     * @param  array  $input Array de entrada do request
     * @param  string  $relation Nome da tabela de relação no SINGULAR
     * @return array Dados que serão sincronizados
     */
    public function createSync(array $input, string $relation): array
    {
        $syncs = [];
        foreach ($input[Str::plural($relation)] as $value) {
            $syncs[][$relation . '_id'] = $value;
        }

        return $syncs;
    }

    /**
     * Monta os campos passados por parâmetros para o select
     * Remove os campos que não fazem parte da Model para evitar quebra de SQL
     *
     * @param  Request  $request
     * @return array
     */
    public function mountFieldsToSelect(Request $request): array
    {
        $fields = explode(',', $request->get('fields'));
        foreach ($fields as $key => $field) {
            if (trim($field) == 'id') {
                $fields[$key] = $this->model->getTable() . '.id';
            }
            if (!array_key_exists(trim($field), $this->model->getCasts())) {
                unset($fields[$key]);
            }
        }

        return array_map('trim', $fields);
    }

    /**
     * Truncate table
     */
    public function truncate()
    {
        return $this->model->truncate();
    }

    /**
     * Update multiple records
     *
     * @param  array  $ids
     * @param  array  $data
     * @return int
     * @throws \InvalidArgumentException
     */
    public function updateBatch(array $ids, array $data): int
    {
        if (empty($ids) || empty($data)) {
            return 0;
        }

        $result = $this->model->whereIn('id', $ids)->update($data);
        return is_bool($result) ? 0 : $result;
    }

    /**
     * Find records by array of values
     *
     * @param  string  $column
     * @param  array  $values
     * @param  array  $with
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function findByIn(string $column, array $values, array $with = [])
    {
        $query = $this->model->whereIn($column, $values);
        if (!empty($with)) {
            $query = $query->with($with);
        }
        return $query->get();
    }

    /**
     * Update without firing events
     *
     * @param  array  $data
     * @param  int  $id
     * @return bool
     */
    public function updateQuietly(array $data, int $id): bool
    {
        return $this->model->withoutEvents(function () use ($id, $data) {
            return $this->model->findOrFail($id)->update($data);
        });
    }

    /**
     * Create without firing events
     *
     * @param  array  $data
     * @return Model
     */
    public function createQuietly(array $data): Model
    {
        return $this->model->withoutEvents(function () use ($data) {
            return $this->model->create($data);
        });
    }

    /**
     * Get model primary key name
     *
     * @return string
     */
    public function getKeyName(): string
    {
        return $this->model->getKeyName();
    }

    /**
     * Create model record using DTO
     * Override this method in child repositories for DTO support
     *
     * @param  object  $dto
     * @return object
     */
    public function createFromDTO($dto)
    {
        return $this->create($dto->toArray());
    }

    /**
     * Update model record using DTO
     * Override this method in child repositories for DTO support
     *
     * @param  int  $id
     * @param  array  $data
     * @return object
     */
    public function updateToDTO(int $id, array $data)
    {
        return $this->update($data, $id);
    }

    /**
     * Find model and convert to DTO
     * Override this method in child repositories for DTO support
     *
     * @param  int  $id
     * @return object|null
     */
    public function findToDTO(int $id)
    {
        return $this->find($id);
    }

    /**
     * Paginate with advanced filters
     * Override this method in child repositories for custom filter logic
     *
     * @param  int  $perPage
     * @param  array  $filters
     * @return LengthAwarePaginator
     */
    public function paginateWithFilters(int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        $query = $this->model->newQuery();

        // Apply filters
        foreach ($filters as $key => $value) {
            if (in_array($key, $this->getFieldsSearchable()) && $value !== null) {
                if (is_string($value)) {
                    $query->where($key, 'like', "%{$value}%");
                } else {
                    $query->where($key, $value);
                }
            }
        }

        return $query->paginate($perPage);
    }
}
