<?php

namespace Flip\Models;

use Flip\Kernel\Database;
use Flip\Models\Department;
use TypeError;

class Employee extends ModelBase implements ModelInterface
{
    public string $emp_id;
    public string $emp_name;
    public string $job_title;
    public string $dep_id;
    public ?Department $department;
    public string $city;
    public string $address;
    public string $phone;
    public string $zip_code;
    public string $month_salary;
    public string $annual_leave;

    use DatabaseUtils;

    public function checkReady(): bool
    {
        return isset($this->emp_id);
    }

    public function batch(Database $db_instance): array
    {
        $sql = "SELECT `emp_id`, `emp_name`, `job_title`, `dep_id`, `city`, `address`, `phone`, `zip_code`, `month_salary`, `annual_leave` FROM `employees`";
        return $this->simpleGrabAll($db_instance, $sql);
    }

    public function batchByDepartmentId(Database $db_instance, string $dep_id): array
    {
        $sql = "SELECT `emp_id`, `emp_name`, `job_title`, `dep_id`, `city`, `address`, `phone`, `zip_code`, `month_salary`, `annual_leave` FROM `employees` WHERE `dep_id` = ?";
        return $this->simpleGrabAll($db_instance, $sql, [$dep_id]);
    }

    public function load(Database $db_instance, $filter): ModelInterface
    {
        if (!is_string($filter)) {
            throw new TypeError();
        }
        $sql = "SELECT `emp_id`, `emp_name`, `job_title`, `dep_id`, `city`, `address`, `phone`, `zip_code`, `month_salary`, `annual_leave` FROM `employees` WHERE `emp_id` = ?";
        return $this->simpleGrabOne($db_instance, $sql, [$filter]);
    }

    public function reload(Database $db_instance): ModelInterface
    {
        return $this->load($db_instance, $this->emp_id);
    }

    public function create(Database $db_instance): bool
    {
        $sql = "INSERT INTO `employees`(`emp_id`, `emp_name`, `job_title`, `dep_id`, `city`, `address`, `phone`, `zip_code`, `month_salary`, `annual_leave`) VALUES (:emp_id, :emp_name, :job_title, :dep_id, :city, :address, :phone, :zip_code, :month_salary, :annual_leave)";
        return $this->simpleModifyFilled($db_instance, $sql);
    }

    public function replace(Database $db_instance): bool
    {
        $sql = "UPDATE `employees` SET `emp_name` = :emp_name, `job_title` = :job_title, `dep_id` = :dep_id, `city` = :city, `address` = :address, `phone` = :phone, `zip_code` = :zip_code, `month_salary` = :month_salary, `annual_leave` = :annual_leave WHERE `emp_id` = :emp_id";
        return $this->simpleModifyFilled($db_instance, $sql);
    }

    public function destroy(Database $db_instance): bool
    {
        $sql = "DELETE FROM `employees` WHERE `emp_id` = ?";
        return $this->simpleModifySafe($db_instance, $sql, ["emp_id"]);
    }

    public function loadDepartment(Database $db_instance): ModelInterface
    {
        $department = new Department();
        $department->load($db_instance, $this->dep_id);
        $this->setDepartment($department);
        unset($this->dep_id);
        return $this;
    }
}
