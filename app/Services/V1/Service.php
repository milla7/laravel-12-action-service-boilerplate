<?php

declare(strict_types=1);

namespace App\Services\V1;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

abstract class Service
{
    /**
     * The model class name
     */
    protected string $modelClass;

    /**
     * Items per page for pagination
     */
    protected int $per_page = 10;

    /**
     * Searchable fields for the search functionality
     */
    protected array $searchableFields = ['name'];

    /**
     * Get the model class instance
     */
    protected function getModel(): Model
    {
        return new $this->modelClass;
    }

    /**
     * Find by ID
     *
     * @param int $id
     * @return Model|null
     */
    public function findById(int $id): ?Model
    {
        return $this->modelClass::find($id);
    }

    /**
     * Find by ID or fail
     *
     * @param int $id
     * @return Model
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function findByIdOrFail(int $id): Model
    {
        return $this->modelClass::findOrFail($id);
    }

    /**
     * Find by field
     *
     * @param string $field
     * @param mixed $value
     * @return Model|null
     */
    public function findBy(string $field, $value): ?Model
    {
        return $this->modelClass::where($field, $value)->first();
    }

    /**
     * Get all records
     *
     * @return Collection
     */
    public function getAll(): Collection
    {
        return $this->modelClass::all();
    }

    /**
     * Get all active records (assumes 'status' field with 'active' value)
     *
     * @return Collection
     */
    public function getActive(): Collection
    {
        return $this->modelClass::where('status', 'active')->get();
    }

    /**
     * Get paginated results with search and filters
     *
     * @param array $filters
     * @param string $search
     * @param string $sort_by
     * @param string $sort_direction
     * @param int|null $per_page
     * @return LengthAwarePaginator
     */
    public function getPaginated(
        array $filters = [],
        string $search = '',
        string $sort_by = 'id',
        string $sort_direction = 'desc',
        ?int $per_page = null
    ): LengthAwarePaginator {
        $query = $this->modelClass::query();

        // Apply search
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                foreach ($this->searchableFields as $field) {
                    $q->orWhere($field, 'like', '%' . $search . '%');
                }
            });
        }

        // Apply filters
        foreach ($filters as $field => $value) {
            if (!empty($value)) {
                if (is_array($value)) {
                    $query->whereIn($field, $value);
                } else {
                    $query->where($field, $value);
                }
            }
        }

        // Apply sorting
        $query->orderBy($sort_by, $sort_direction);

        return $query->paginate($per_page ?? $this->per_page);
    }

    /**
     * Create new record
     *
     * @param array $data
     * @return Model
     */
    public function create(array $data): Model
    {
        return $this->modelClass::create($data);
    }

    /**
     * Update record
     *
     * @param int $id
     * @param array $data
     * @return Model|null
     */
    public function update(int $id, array $data): ?Model
    {
        $record = $this->findById($id);

        if ($record) {
            $record->update($data);
            return $record->fresh();
        }

        return null;
    }

    /**
     * Delete record
     *
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool
    {
        $record = $this->findById($id);

        return $record?->delete() ?? false;
    }

    /**
     * Soft delete record (if the model uses SoftDeletes)
     *
     * @param int $id
     * @return bool
     */
    public function softDelete(int $id): bool
    {
        $record = $this->findById($id);

        if ($record && method_exists($record, 'delete')) {
            return $record->delete();
        }

        return false;
    }

    /**
     * Restore soft deleted record
     *
     * @param int $id
     * @return bool
     */
    public function restore(int $id): bool
    {
        $record = $this->modelClass::onlyTrashed()->find($id);

        if ($record && method_exists($record, 'restore')) {
            return $record->restore();
        }

        return false;
    }

    /**
     * Get count of records
     *
     * @param array $conditions
     * @return int
     */
    public function count(array $conditions = []): int
    {
        $query = $this->modelClass::query();

        foreach ($conditions as $field => $value) {
            $query->where($field, $value);
        }

        return $query->count();
    }

    /**
     * Check if record exists
     *
     * @param int $id
     * @return bool
     */
    public function exists(int $id): bool
    {
        return $this->modelClass::where('id', $id)->exists();
    }

    /**
     * Get temporary URL for file
     *
     * @param string|null $path
     * @param int $minutes
     * @param string $disk
     * @return string|null
     */
    public function getTemporaryUrl(?string $path, int $minutes = 120, string $disk = 's3'): ?string
    {
        if (!$path) {
            return null;
        }

        try {
            return Storage::disk($disk)->temporaryUrl($path, Carbon::now()->addMinutes($minutes));
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Get records where field is in array
     *
     * @param string $field
     * @param array $values
     * @return Collection
     */
    public function whereIn(string $field, array $values): Collection
    {
        return $this->modelClass::whereIn($field, $values)->get();
    }

    /**
     * Get records where field is not in array
     *
     * @param string $field
     * @param array $values
     * @return Collection
     */
    public function whereNotIn(string $field, array $values): Collection
    {
        return $this->modelClass::whereNotIn($field, $values)->get();
    }

    /**
     * Get latest records
     *
     * @param int $limit
     * @param string $column
     * @return Collection
     */
    public function getLatest(int $limit = 10, string $column = 'created_at'): Collection
    {
        return $this->modelClass::latest($column)->limit($limit)->get();
    }

    /**
     * Get oldest records
     *
     * @param int $limit
     * @param string $column
     * @return Collection
     */
    public function getOldest(int $limit = 10, string $column = 'created_at'): Collection
    {
        return $this->modelClass::oldest($column)->limit($limit)->get();
    }
}
