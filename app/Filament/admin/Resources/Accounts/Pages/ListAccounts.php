<?php

namespace App\Filament\Admin\Resources\Accounts\Pages;

use App\Filament\Admin\Resources\Accounts\AccountResource;
use App\Models\Account;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Collection;

class ListAccounts extends ListRecords
{
    protected static string $resource = AccountResource::class;
    protected string $view = 'filament.admin.resources.accounts.pages.list-accounts';

    public string $accountSearch = '';
    public string $accountType = 'all';
    public string $accountLevel = 'all';
    public string $accountStatus = 'active';

    public function getAccountStats(): array
    {
        return [
            'total'    => Account::count(),
            'main'     => Account::where('level', 'main')->count(),
            'detail'   => Account::where('level', 'detail')->count(),
            'inactive' => Account::where('is_active', false)->count(),
        ];
    }

    public function getTreeRows(): array
    {
        $all = Account::query()
            ->with('parent')
            ->orderBy('code')
            ->get();

        $search = trim($this->accountSearch);

        $matches = $all->filter(function ($account) use ($search) {
            if ($this->accountStatus === 'active' && !$account->is_active) {
                return false;
            }

            if ($this->accountStatus === 'inactive' && $account->is_active) {
                return false;
            }

            if ($this->accountType !== 'all' && $account->type !== $this->accountType) {
                return false;
            }

            if ($this->accountLevel !== 'all' && $account->level !== $this->accountLevel) {
                return false;
            }

            if ($search !== '') {
                $haystack = mb_strtolower(
                    (string) $account->code . ' ' .
                    (string) $account->name . ' ' .
                    (string) ($account->name_en ?? '')
                );

                if (!str_contains($haystack, mb_strtolower($search))) {
                    return false;
                }
            }

            return true;
        });

        $includeIds = $matches->pluck('id')->map(fn ($id) => (int) $id)->all();

        foreach ($matches as $account) {
            $parentId = $account->parent_id;

            while ($parentId) {
                $parent = $all->firstWhere('id', $parentId);

                if (!$parent) {
                    break;
                }

                $includeIds[] = (int) $parent->id;
                $parentId = $parent->parent_id;
            }
        }

        $includeIds = array_values(array_unique($includeIds));

        $children = [];
        foreach ($all as $account) {
            if (!in_array((int) $account->id, $includeIds, true)) {
                continue;
            }

            $parentKey = $account->parent_id ? (int) $account->parent_id : 0;
            $children[$parentKey][] = $account;
        }

        foreach ($children as &$group) {
            usort($group, fn ($a, $b) => strnatcasecmp((string) $a->code, (string) $b->code));
        }
        unset($group);

        $rows = [];

        $walk = function (int $parentId, int $depth, array $ancestors) use (&$walk, &$rows, $children): void {
            foreach ($children[$parentId] ?? [] as $account) {
                $hasChildren = !empty($children[(int) $account->id] ?? []);

                $rows[] = [
                    'account'      => $account,
                    'depth'        => $depth,
                    'ancestors'    => $ancestors,
                    'has_children' => $hasChildren,
                ];

                $walk(
                    (int) $account->id,
                    $depth + 1,
                    [...$ancestors, (int) $account->id]
                );
            }
        };

        $walk(0, 0, []);

        return $rows;
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->label('إضافة حساب'),
        ];
    }
}
