<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

abstract class BaseService
{
    protected $repository;

    protected $folderRepo;

    protected $nameRepo;

    protected $keyName;

    public function __construct(string $folderRepo, string $nameRepo)
    {
        $this->repository = App::make('App\Repositories\\' . $folderRepo . '\\' . $nameRepo); // Monta Repositório
        $this->keyName = $this->repository->getKeyName() ?? 'id';
    }

    public function getDados(Request $request): mixed
    {
        if ($request->get('search') != 'Busca') {
            $dados = ($request->get('relations') != 'Relacao') ?
                ($this->getSearchComRelations($request)) : ($this->getSearchSemRelations($request));
        } else {
            if ($request->get('searchLike') != 'Incremental') {
                $dados = ($request->get('relations') != 'Relacao') ?
                    ($this->getSearchLikeComRelations($request)) : ($this->getSearchLikeSemRelations($request));
            } else {
                $dados = ($request->get('relations') != 'Relacao') ? ($this->getAllComRelations($request)) : ($this->getAllSemRelations($request));
            }
        }

        return $dados;
    }

    private function getSearchComRelations(Request $request): mixed
    {
        error_log('getSearchComRelations');
        return $this->repository
            ->advancedSearch($request, explode(',', $request->get('relations')))
            ->orderByRaw(($request->get('order') ?? $this->keyName) . ' ' . ($request->get('direction') ?? 'DESC'))
            ->paginate($request->get('limit'));
    }

    private function getSearchSemRelations(Request $request): mixed
    {
        error_log('getSearchSemRelations');
        return $this->repository
            ->advancedSearch($request)
            ->orderByRaw(($request->get('order') ?? $this->keyName) . ' ' . ($request->get('direction') ?? 'DESC'))
            ->paginate($request->get('limit'));
    }

    private function getSearchLikeComRelations(Request $request): mixed
    {
        error_log('getSearchLikeComRelations');
        return $this->repository
            ->SearchLike($request, explode(',', $request->get('relations')))
            ->orderByRaw(($request->get('order') ?? $this->keyName) . ' ' . ($request->get('direction') ?? 'DESC'))
            ->paginate($request->get('limit'));
    }

    private function getSearchLikeSemRelations(Request $request): mixed
    {
        error_log('getSearchLikeSemRelations');
        return $this->repository
            ->SearchLike($request)
            ->orderByRaw(($request->get('order') ?? $this->keyName) . ' ' . ($request->get('direction') ?? 'DESC'))
            ->paginate($request->get('limit'));
    }

    private function getAllComRelations(Request $request): mixed
    {
        error_log('getAllComRelations');
        return $this->repository
            ->findAllFieldsAnd($request, explode(',', $request->get('relations')))
            ->orderByRaw(($request->get('order') ?? $this->keyName) . ' ' . ($request->get('direction') ?? 'DESC'))
            ->paginate($request->get('limit'));
    }

    private function getAllSemRelations(Request $request): mixed
    {
        error_log('getAllSemRelations');
        return $this->repository
            ->findAllFieldsAnd($request)
            ->orderByRaw(($request->get('order') ?? $this->keyName) . ' ' . ($request->get('direction') ?? 'DESC'))
            ->paginate($request->get('limit'));
    }

    public function all()
    {
        return $this->repository->all();
    }

    public function find(int $id): mixed
    {
        return $this->repository->find($id);
    }

    public function create(array $input): mixed
    {
        return $this->repository->create($input);
    }

    public function update(array $input, int $id): mixed
    {
        $res = $this->find($id);
        if (!empty($res)) {
            return $this->repository->update($input, $id);
        }
        return false;
    }

    public function show(int $id): mixed
    {
        return $this->find($id);
    }

    public function destroy(int $id): mixed
    {
        $res = $this->find($id);
        if (!empty($res)) {
            return $res->delete();
        }
        return false;
    }

    public function truncate()
    {
        return $this->repository->truncate();
    }

    public function findBy($column, $value = null, $operator = '=', $boolean = 'and')
    {
        return $this->repository->findBy($column, $value, $operator, $boolean);
    }

    public function updateBatch(array $ids, array $data)
    {
        return $this->repository->updateBatch($ids, $data);
    }

    public function findByIn(string $column, array $values, array $with = [])
    {
        return $this->repository->findByIn($column, $values, $with);
    }

    public function updateQuietly(array $data, int $id): bool
    {
        return $this->repository->updateQuietly($data, $id);
    }

    public function createQuietly(array $data)
    {
        return $this->repository->createQuietly($data);
    }

    public function getKeyName()
    {
        return $this->keyName;
    }
}
