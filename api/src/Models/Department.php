<?php

namespace Flip\Models;

use Flip\Kernel\Database;
use Flip\Models\Employee;
use TypeError;

class Department extends ModelBase implements ModelInterface
{
    public string $dep_name;
    public string $dep_id;
    public ?string $manager_emp_id;
    public ?Employee $manager;
    public ?array $employees;

    use DatabaseUtils;

    public function checkReady(): bool
    {
        return isset($this->dep_id);
    }

    public function batch(Database $db_instance): array
    {
        $sql = "
            SELECT `dep_name`, `departments`.`dep_id`, `manager_emp_id`
            FROM `departments`
            LEFT JOIN `employees` ON `manager_emp_id` = `employees`.`emp_id`;
        ";
        return $this->simpleGrabAll($db_instance, $sql);
    }

    public function load(Database $db_instance, $filter): ModelInterface
    {
        if (!is_string($filter)) {
            throw new TypeError();
        }
        $sql = "SELECT `dep_name`, `dep_id`, `manager_emp_id` FROM `departments` WHERE `dep_id` = ?";
        return $this->simpleGrabOne($db_instance, $sql, [$filter]);
    }

    public function reload(Database $db_instance): ModelInterface
    {
        return $this->load($db_instance, $this->dep_id);
    }

    public function create(Database $db_instance): bool
    {
        $sql = "INSERT INTO `departments`(`dep_name`, `dep_id`, `manager_emp_id`) VALUES (:dep_name, :dep_id, :manager_emp_id)";
        return $this->simpleModifyFilled($db_instance, $sql);
    }

    public function replace(Database $db_instance): bool
    {
        $sql = "UPDATE `departments` SET `dep_name` = :dep_name, `manager_emp_id` = :manager_emp_id WHERE `dep_id` = :dep_id";
        return $this->simpleModifyFilled($db_instance, $sql);
    }

    public function destroy(Database $db_instance): bool
    {
        $sql = "DELETE FROM `departments` WHERE `dep_id` = ?";
        return $this->simpleModifySafe($db_instance, $sql, ["dep_id"]);
    }

    public function loadManager(Database $db_instance): ModelInterface
    {
        if (!$this->manager_emp_id) {
            return $this;
        }
        $employee = (new Employee())->load($db_instance, $this->manager_emp_id);
        $this->setManager($employee);
        unset($this->manager_emp_id);
        return $this;
    }

    public function loadEmployees(Database $db_instance): ModelInterface
    {
        if (!$this->dep_id) {
            return $this;
        }
        $employees = (new Employee())->batchByDepartmentId($db_instance, $this->dep_id);
        $this->setEmployees($employees);
        return $this;
    }
}
