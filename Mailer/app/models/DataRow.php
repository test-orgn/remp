<?php
declare(strict_types=1);

namespace Remp\MailerModule\Components\GeneratorWidgets\Widgets;

use Exception;
use IteratorAggregate;
use ArrayIterator;
use Nette\Database\Table\GroupedSelection;
use Nette\Database\Table\IRow;
use Nette\Database\Table\Selection;

class DataRow implements IteratorAggregate, IRow
{
    private $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function __set(string $column, $value): void
    {
        throw new Exception('Not supported');
    }

    public function __isset(string $key): bool
    {
        return isset($this->data[$key]);
    }

    public function &__get(string $key)
    {
        return $this->data[$key];
    }

    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->data);
    }

    public function offsetExists($offset)
    {
        throw new Exception('Not supported');
    }

    public function offsetGet($offset)
    {
        throw new Exception('Not supported');
    }

    public function offsetSet($offset, $value)
    {
        throw new Exception('Not supported');
    }

    public function offsetUnset($offset)
    {
        throw new Exception('Not supported');
    }

    public function setTable(Selection $name)
    {
        throw new Exception('Not supported');
    }

    public function getTable(): Selection
    {
        throw new Exception('Not supported');
    }

    public function getPrimary($throw = true)
    {
        throw new Exception('Not supported');
    }

    public function getSignature(bool $throw = true): string
    {
        throw new Exception('Not supported');
    }

    public function related(string $key, string $throughColumn = null): GroupedSelection
    {
        throw new Exception('Not supported');
    }

    public function ref(string $key, string $throughColumn = null): ?IRow
    {
        throw new Exception('Not supported');
    }
}
