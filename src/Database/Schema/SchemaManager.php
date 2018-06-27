<?php

namespace LaravelAdminPanel\Database\Schema;

use Doctrine\DBAL\Schema\SchemaException;
use Doctrine\DBAL\Schema\Table as DoctrineTable;
use Illuminate\Support\Facades\DB;
use LaravelAdminPanel\Database\Types\Type;

abstract class SchemaManager
{
    private static $connection = null;

    // todo: trim parameters
    public static function __callStatic($method, $args)
    {
        return static::manager()->$method(...$args);
    }

    /**
     * Set a connection to the database.
     *
     * @param string $connectionName
     *
     * @return void
     */
    public static function setConnection($connectionName)
    {
        static::$connection = $connectionName;
    }

    /**
     * Get a connection to the database.
     *
     * @return $connectionName
     */
    public static function getConnection()
    {
        if (!static::$connection) {
            static::setConnection(request('connection'));
        }

        return static::$connection;
    }

    public static function manager()
    {
        return DB::connection(self::getConnection())->getDoctrineSchemaManager();
    }

    public static function getDatabaseConnection()
    {
        return DB::connection(self::getConnection())->getDoctrineConnection();
    }

    public static function tableExists($table)
    {
        if (!is_array($table)) {
            $table = [$table];
        }

        return static::manager()->tablesExist($table);
    }

    public static function listTables()
    {
        $tables = [];

        foreach (static::manager()->listTableNames() as $tableName) {
            $tables[$tableName] = static::listTableDetails($tableName);
        }

        return $tables;
    }

    /**
     * @param string $tableName
     *
     * @return \LaravelAdminPanel\Database\Schema\Table
     */
    public static function listTableDetails($tableName)
    {
        $columns = static::manager()->listTableColumns($tableName);

        $foreignKeys = [];
        if (static::manager()->getDatabasePlatform()->supportsForeignKeyConstraints()) {
            $foreignKeys = static::manager()->listTableForeignKeys($tableName);
        }

        $indexes = static::manager()->listTableIndexes($tableName);

        return new Table($tableName, $columns, $indexes, $foreignKeys, false, []);
    }

    /**
     * Describes given table.
     *
     * @param string $tableName
     *
     * @return \Illuminate\Support\Collection
     */
    public static function describeTable($tableName)
    {
        Type::registerCustomPlatformTypes();

        $table = static::listTableDetails($tableName);

        return collect($table->columns)->map(function ($column) use ($table) {
            $columnArr = Column::toArray($column);

            $columnArr['field'] = $columnArr['name'];
            $columnArr['type'] = $columnArr['type']['name'];

            // Set the indexes and key
            $columnArr['indexes'] = [];
            $columnArr['key'] = null;
            if ($columnArr['indexes'] = $table->getColumnsIndexes($columnArr['name'], true)) {
                // Convert indexes to Array
                foreach ($columnArr['indexes'] as $name => $index) {
                    $columnArr['indexes'][$name] = Index::toArray($index);
                }

                // If there are multiple indexes for the column
                // the Key will be one with highest priority
                $indexType = array_values($columnArr['indexes'])[0]['type'];
                $columnArr['key'] = substr($indexType, 0, 3);
            }

            return $columnArr;
        });
    }

    public static function describeTableFromModel($modelName)
    {
        $model = app($modelName);
        return static::describeTable($model->getTable());
    }

    public static function listTableColumnNames($tableName)
    {
        Type::registerCustomPlatformTypes();

        $columnNames = [];

        foreach (static::manager()->listTableColumns($tableName) as $column) {
            $columnNames[] = $column->getName();
        }

        return $columnNames;
    }

    public static function createTable($table)
    {
        if (!($table instanceof DoctrineTable)) {
            $table = Table::make($table);
        }

        static::manager()->createTable($table);
    }

    public static function getDoctrineTable($table)
    {
        $table = trim($table);

        if (!static::tableExists($table)) {
            throw SchemaException::tableDoesNotExist($table);
        }

        return static::manager()->listTableDetails($table);
    }

    public static function getDoctrineColumn($table, $column)
    {
        return static::getDoctrineTable($table)->getColumn($column);
    }
}
