<?php

namespace App\Services;

use App\Models\Category;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class CategoryTree
{
    public static function query(): Builder
    {
        $tree = "WITH RECURSIVE tree AS (
            SELECT id, 0 AS depth, ARRAY[lower(name) || ':' || lpad(id::text, 20, '0')] AS sort_path, ARRAY[id] AS ancestors
            FROM categories WHERE parent_id IS NULL
            UNION ALL
            SELECT child.id, tree.depth + 1, tree.sort_path || (lower(child.name) || ':' || lpad(child.id::text, 20, '0')), tree.ancestors || child.id
            FROM categories child JOIN tree ON child.parent_id = tree.id WHERE NOT child.id = ANY(tree.ancestors)
        ) SELECT id, depth, sort_path FROM tree";

        return Category::query()->join(DB::raw('('.$tree.') AS hierarchy'), 'categories.id', '=', 'hierarchy.id')
            ->select('categories.*', 'hierarchy.depth')->orderBy('hierarchy.sort_path');
    }

    public static function nodes(?Category $except = null): array
    {
        $nodes = [];
        $excluded = $except ? [$except->id] : [];
        foreach (self::query()->get() as $category) {
            if (in_array($category->id, $excluded) || in_array($category->parent_id, $excluded)) {
                $excluded[] = $category->id;

                continue;
            }
            $nodes[] = ['id' => $category->id, 'name' => $category->name, 'depth' => (int) $category->depth, 'parent_id' => $category->parent_id];
        }

        return $nodes;
    }

    public static function options(?Category $except = null): array
    {
        return collect(self::nodes($except))->mapWithKeys(fn ($row) => [$row['id'] => str_repeat('— ', $row['depth']).$row['name']])->all();
    }
}
