<?php

namespace Tests\Feature;

use App\Models\Scopes\BranchScope;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Tests\TestCase;

class BranchScopeTest extends TestCase
{
    private function makeUser(?int $branchId): User
    {
        $user = new class extends User {
            public function hasAnyRole(...$roles): bool
            {
                return false;
            }
        };

        $user->branch_id = $branchId;

        return $user;
    }

    private function makeQuery(): array
    {
        $model = new class extends Model {
            protected $table = 'invoices';
        };

        return [$model, $model->newQuery()];
    }

    public function test_regular_user_without_branch_gets_no_records(): void
    {
        $this->actingAs($this->makeUser(null));

        [$model, $builder] = $this->makeQuery();

        (new BranchScope())->apply($builder, $model);

        $this->assertStringContainsString(
            '1 = 0',
            $builder->toSql()
        );
    }

    public function test_regular_user_is_limited_to_their_branch(): void
    {
        $this->actingAs($this->makeUser(1));

        [$model, $builder] = $this->makeQuery();

        (new BranchScope())->apply($builder, $model);

        $this->assertStringContainsString(
            'branch_id',
            $builder->toSql()
        );

        $this->assertContains(1, $builder->getBindings());
    }

    public function test_unauthenticated_web_query_gets_no_records(): void
    {
        auth()->logout();

        [$model, $builder] = $this->makeQuery();

        (new BranchScope(false, false))->apply($builder, $model);

        $this->assertStringContainsString(
            '1 = 0',
            $builder->toSql()
        );
    }

    public function test_unauthenticated_console_query_is_not_filtered(): void
    {
        auth()->logout();

        [$model, $builder] = $this->makeQuery();

        (new BranchScope(false, true))->apply($builder, $model);

        $this->assertStringNotContainsString(
            'branch_id',
            $builder->toSql()
        );
    }
}