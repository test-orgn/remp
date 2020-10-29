<?php
declare(strict_types=1);

namespace Remp\MailerModule\Tracker;

class EventOptions
{
    private $user;

    private $fields = [];

    private $value;

    /**
     * @return User
     */
    public function getUser(): User
    {
        return $this->user;
    }

    /**
     * @return array
     */
    public function getFields(): array
    {
        return $this->fields;
    }

    /**
     * @return mixed
     */
    public function getValue(): float
    {
        return $this->value;
    }

    public function __construct()
    {
        $this->user = new User;
    }

    /**
     * @param User $user
     */
    public function setUser(User $user): void
    {
        $this->user = $user;
    }

    /**
     * @param array $fields
     */
    public function setFields(array $fields): void
    {
        $this->fields = $fields;
    }

    /**
     * @param float $value
     */
    public function setValue(float $value): void
    {
        $this->value = $value;
    }
}
