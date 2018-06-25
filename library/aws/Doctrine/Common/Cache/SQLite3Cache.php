<?php
namespace Doctrine\Common\Cache;
use SQLite3;
use SQLite3Result;
class SQLite3Cache extends CacheProvider
{
    const ID_FIELD = 'k';
    const DATA_FIELD = 'd';
    const EXPIRATION_FIELD = 'e';
    private $sqlite;
    private $table;
    public function __construct(SQLite3 $sqlite, $table)
    {
        $this->sqlite = $sqlite;
        $this->table  = (string) $table;
        list($id, $data, $exp) = $this->getFields();
        return $this->sqlite->exec(sprintf(
            'CREATE TABLE IF NOT EXISTS %s(%s TEXT PRIMARY KEY NOT NULL, %s BLOB, %s INTEGER)',
            $table,
            $id,
            $data,
            $exp
        ));
    }
    protected function doFetch($id)
    {
        if ($item = $this->findById($id)) {
            return unserialize($item[self::DATA_FIELD]);
        }
        return false;
    }
    protected function doContains($id)
    {
        return null !== $this->findById($id, false);
    }
    protected function doSave($id, $data, $lifeTime = 0)
    {
        $statement = $this->sqlite->prepare(sprintf(
            'INSERT OR REPLACE INTO %s (%s) VALUES (:id, :data, :expire)',
            $this->table,
            implode(',', $this->getFields())
        ));
        $statement->bindValue(':id', $id);
        $statement->bindValue(':data', serialize($data), SQLITE3_BLOB);
        $statement->bindValue(':expire', $lifeTime > 0 ? time() + $lifeTime : null);
        return $statement->execute() instanceof SQLite3Result;
    }
    protected function doDelete($id)
    {
        list($idField) = $this->getFields();
        $statement = $this->sqlite->prepare(sprintf(
            'DELETE FROM %s WHERE %s = :id',
            $this->table,
            $idField
        ));
        $statement->bindValue(':id', $id);
        return $statement->execute() instanceof SQLite3Result;
    }
    protected function doFlush()
    {
        return $this->sqlite->exec(sprintf('DELETE FROM %s', $this->table));
    }
    protected function doGetStats()
    {
    }
    private function findById($id, $includeData = true)
    {
        list($idField) = $fields = $this->getFields();
        if (!$includeData) {
            $key = array_search(static::DATA_FIELD, $fields);
            unset($fields[$key]);
        }
        $statement = $this->sqlite->prepare(sprintf(
            'SELECT %s FROM %s WHERE %s = :id LIMIT 1',
            implode(',', $fields),
            $this->table,
            $idField
        ));
        $statement->bindValue(':id', $id, SQLITE3_TEXT);
        $item = $statement->execute()->fetchArray(SQLITE3_ASSOC);
        if ($item === false) {
            return null;
        }
        if ($this->isExpired($item)) {
            $this->doDelete($id);
            return null;
        }
        return $item;
    }
    private function getFields()
    {
        return array(static::ID_FIELD, static::DATA_FIELD, static::EXPIRATION_FIELD);
    }
    private function isExpired(array $item)
    {
        return isset($item[static::EXPIRATION_FIELD]) &&
            $item[self::EXPIRATION_FIELD] !== null &&
            $item[self::EXPIRATION_FIELD] < time();
    }
}
