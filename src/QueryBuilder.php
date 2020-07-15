<?php

namespace App;

class QueryBuilder
{
    private $from;
    private $order = [];
    private $limit;
    private $offset;
    private $condition;
    private $fields = ["*"];
    private $params = [];

    public function from($table, $alias = null): self
    {
        $this->from = $alias === null ? $table : "$table $alias";
        return $this;
    }

    public function orderBy($key, $dir): self
    {
        $dir = strtoupper($dir);
        if (!in_array($dir, ['ASC', 'DESC'])) {
            $this->order[] = $key;
        } else {
            $this->order[] = "$key $dir";
        }
        return $this;
    }

    public function limit($nbr): self
    {
        $this->limit = $nbr;
        return $this;
    }

    public function offset($nbr): self
    {
        $this->offset = $nbr;
        return $this;
    }

    public function page($nbr): self
    {
        if ($nbr > 0) {
            $z = ($nbr * 10) - 10;
            return $this->offset($z);
        }
    }

    public function where($condition): self
    {
        $this->condition = $condition;
        return $this;
    }

    public function select(...$fields): self
    {
        if (is_array($fields[0])) {
            $fields = $fields[0];
        }
        if ($this->fields === ["*"]) {
            $this->fields = $fields;
        } else {
            $this->fields = array_merge($this->fields, $fields);
        }
        return $this;
    }

    public function setParam($key, $value): self
    {
        $this->params[$key] = $value;
        return $this;
    }

    public function toSQL(): string
    {
        $fields = implode(', ', $this->fields);
        $sql = "SELECT $fields FROM {$this->from}";

        if ($this->condition) {
            $sql .= " WHERE {$this->condition}";
        }
        if (!empty($this->order)) {
            $sql .= " ORDER BY " . implode(', ', $this->order);
        }
        if ($this->limit > 0) {
            $sql .= " LIMIT " . $this->limit;
        }
        if ($this->offset !== null) {
            $sql .= " OFFSET " . $this->offset;
        }
        return $sql;
    }
    public function fetch($pdo, $field): ?string
    {
        $query = $pdo->prepare($this->toSQL());
        $query->execute($this->params);
        $result = $query->fetch();
        if ($result === false) {
            return null;
        }

        return $result[$field] ?? null;
    }
    public function count($pdo): int
    {
        /*Advance */
        $query = clone $this;
        return (int)$query->select('COUNT(id) count')->fetch($pdo, 'count');
        /*
        more standard
        $query = $pdo->prepare($this->toSQL());
        $query->execute($this->params);
        $result = $query->fetchAll();
        return count($result);*/
    }
}