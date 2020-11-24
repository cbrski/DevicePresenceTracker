<?php


namespace Tests\Helpers;

use Illuminate\Foundation\Testing\Concerns\InteractsWithDatabase;

trait AssertHelpers
{
    use InteractsWithDatabase;

    public function assertDatabaseCountConditions(string $table, array $conditions, $connection = null)
    {
        $connection = $this->getConnection($connection);
        $count = $connection->table($table)->count();
        list($sign, $expectedCount) = $conditions;
        switch ($sign) {
            case '=':
                return parent::assertDatabaseCount($table, $expectedCount, $connection);
                break;
            case '>':
                $this->assertGreaterThan($expectedCount, $count);
                break;
            case '>=':
                $this->assertGreaterThanOrEqual($expectedCount, $count);
                break;
            case '<':
                $this->assertLessThan($expectedCount, $count);
                break;
            case '<=':
                $this->assertLessThanOrEqual($expectedCount, $count);
                break;
        }
        return $this;
    }
}
