<?php

namespace Flip\Controllers;

use Flip\Kernel\Context;
use Flip\Kernel\Utils;
use Flip\Models\Department;
use Flip\Models\Employee;
use Flip\Models\User;

class Manage implements ControllerInterface
{
    public function getIndexAction(Context $context)
    {
        $employees = (new Employee())->batch($context->getDatabase());
        $employees_count = count($employees);
        $employees_salaries_avg = (array_sum(array_map(function ($employee) {
            return $employee->getMonthSalary();
        }, $employees))) / $employees_count;
        $employees_city_count = count(array_unique(array_map(function ($employee) {
            return $employee->getCity();
        }, $employees)));
        $departments = (new Department())->batch($context->getDatabase());
        $departments_count = count($departments);
        $departments_employees_salaries_avg = array_sum(array_map(
            function (Department $department) use ($context, $departments_count) {
                $department->loadEmployees($context->getDatabase());
                return array_sum(
                    array_map(function (Employee $employee) {
                        return $employee->getMonthSalary();
                    }, $department->getEmployees())
                ) / $departments_count;
            },
            $departments
        ));
        $departments_employees_counts = array_map(function (Department $department) {
            return [$department->getDepName(), count($department->getEmployees())];
        }, $departments);
        $departments_employees_count_avg = array_sum(
            array_map(
                fn (array $item) => $item[1],
                $departments_employees_counts
            )
        ) / $departments_count;
        $result = [
            "employees" => [
                "count" => $employees_count,
                "city_count" => $employees_city_count,
                "salaries_avg" => round($employees_salaries_avg),
            ],
            "departments" => [
                "count" => $departments_count,
                "salaries_avg" => round($departments_employees_salaries_avg),
                "employees_count_avg" => round($departments_employees_count_avg),
            ],
            "departments_employees_counts" => $departments_employees_counts,
        ];
        $context->getResponse()->setBody($result)->sendJSON();
    }

    public function getAllEmployeesAction(Context $context): void
    {
        $employees = (new Employee())->batch($context->getDatabase());
        $employees = array_map(function (Employee $employee) use ($context) {
            return $employee->loadDepartment($context->getDatabase());
        }, $employees);
        $context->getResponse()->setBody($employees)->sendJSON();
    }

    public function getEmployeeAction(Context $context): void
    {
        $emp_id = $context->getRequest()->getQuery("emp_id");
        if (empty($emp_id)) {
            $context->getResponse()->setStatus(400)->setBody(["message" => "bad request"])->sendJSON();
            return;
        }
        $employee = new Employee();
        $employee->load($context->getDatabase(), $emp_id);
        if ($employee->checkReady()) {
            $context->getResponse()->setBody($employee)->sendJSON();
        } else {
            $context->getResponse()->setStatus(404)->setBody(["message" => "not found"])->sendJSON();
        }
    }

    public function postEmployeeAction(Context $context): void
    {
        $form = $context->getRequest()->read();
        if (
            !isset($form["emp_name"]) ||
            !isset($form["job_title"]) ||
            !isset($form["dep_id"]) ||
            !isset($form["city"]) ||
            !isset($form["address"]) ||
            !isset($form["phone"]) ||
            !isset($form["zip_code"]) ||
            !isset($form["month_salary"]) ||
            !isset($form["annual_leave"])
        ) {
            $context->getResponse()->setStatus(400)->setBody(["message" => "bad request"])->sendJSON();
            return;
        }
        if (array_search("", $form)) {
            $context->getResponse()->setStatus(400)->setBody(["message" => "bad request"])->sendJSON();
            return;
        }
        if (!(new Department())->load($context->getDatabase(), $form["dep_id"])->checkReady()) {
            $context->getResponse()->setStatus(400)->setBody(["message" => "bad request"])->sendJSON();
            return;
        }
        $employee = new Employee();
        $employee
            ->setEmpId(Utils::randomUUID())
            ->setEmpName($form["emp_name"])
            ->setJobTitle($form["job_title"])
            ->setDepId($form["dep_id"])
            ->setCity($form["city"])
            ->setAddress($form["address"])
            ->setPhone($form["phone"])
            ->setZipCode($form["zip_code"])
            ->setMonthSalary($form["month_salary"])
            ->setAnnualLeave($form["annual_leave"]);
        $employee->create($context->getDatabase());
        $context->getResponse()->setStatus(201)->send();
    }

    public function putEmployeeAction(Context $context): void
    {
        $form = $context->getRequest()->read();
        if (
            !isset($form["emp_id"]) ||
            !isset($form["emp_name"]) ||
            !isset($form["job_title"]) ||
            !isset($form["dep_id"]) ||
            !isset($form["city"]) ||
            !isset($form["address"]) ||
            !isset($form["phone"]) ||
            !isset($form["zip_code"]) ||
            !isset($form["month_salary"]) ||
            !isset($form["annual_leave"])
        ) {
            $context->getResponse()->setStatus(400)->setBody(["message" => "bad request"])->sendJSON();
            return;
        }
        if (array_search("", $form)) {
            $context->getResponse()->setStatus(400)->setBody(["message" => "bad request"])->sendJSON();
            return;
        }
        if (!(new Department())->load($context->getDatabase(), $form["dep_id"])->checkReady()) {
            $context->getResponse()->setStatus(400)->setBody(["message" => "bad request"])->sendJSON();
            return;
        }
        $employee = new Employee();
        $employee->load($context->getDatabase(), $form["emp_id"]);
        if (!$employee->checkReady()) {
            $context->getResponse()->setStatus(404)->setBody(["message" => "not found"])->sendJSON();
            return;
        }
        $employee
            ->setEmpName($form["emp_name"])
            ->setJobTitle($form["job_title"])
            ->setDepId($form["dep_id"])
            ->setCity($form["city"])
            ->setAddress($form["address"])
            ->setPhone($form["phone"])
            ->setZipCode($form["zip_code"])
            ->setMonthSalary($form["month_salary"])
            ->setAnnualLeave($form["annual_leave"]);
        $employee->replace($context->getDatabase());
        $context->getResponse()->setStatus(204)->send();
    }

    public function deleteEmployeeAction(Context $context): void
    {
        $emp_id = $context->getRequest()->getQuery("emp_id");
        $user = new User();
        $user->load($context->getDatabase(), $emp_id);
        if ($user->checkReady()) {
            $context->getResponse()->setStatus(400)->setBody(["message" => "bad request"])->sendJSON();
            return;
        }
        $employee = new Employee();
        $employee->load($context->getDatabase(), $emp_id);
        if (!$employee->checkReady()) {
            $context->getResponse()->setStatus(404)->setBody(["message" => "not found"])->sendJSON();
            return;
        }
        $employee->destroy($context->getDatabase());
        $context->getResponse()->setStatus(200)->setBody(["message" => "success"])->sendJSON();
    }

    public function getAllDepartmentsAction(Context $context): void
    {
        $departments = (new Department())->batch($context->getDatabase());
        $departments = array_map(function (Department $department) use ($context) {
            $department->loadManager($context->getDatabase());
            $department->setManager($department->getManager()->loadDepartment($context->getDatabase()));
            return $department;
        }, $departments);
        $context->getResponse()->setBody($departments)->sendJSON();
    }

    public function getDepartmentAction(Context $context): void
    {
        $dep_id = $context->getRequest()->getQuery("dep_id");
        $department = new Department();
        $department->load($context->getDatabase(), $dep_id);
        if ($department->checkReady()) {
            $context->getResponse()->setBody($department)->sendJSON();
        } else {
            $context->getResponse()->setStatus(404)->setBody(["message" => "not found"])->sendJSON();
        }
    }

    public function getDepartmentEmployeesAction(Context $context): void
    {
        $dep_id = $context->getRequest()->getQuery("dep_id");
        $department = new Department();
        $department->load($context->getDatabase(), $dep_id);
        if ($department->checkReady()) {
            $department->loadEmployees($context->getDatabase());
            $employees = $department->getEmployees();
            $context->getResponse()->setBody($employees)->sendJSON();
        } else {
            $context->getResponse()->setStatus(404)->setBody(["message" => "not found"])->sendJSON();
        }
    }

    public function postDepartmentAction(Context $context): void
    {
        $form = $context->getRequest()->read();
        if (
            !isset($form["dep_name"]) ||
            !isset($form["manager_emp_id"])
        ) {
            $context->getResponse()->setStatus(400)->setBody(["message" => "bad request"])->sendJSON();
            return;
        }
        if (array_search("", $form)) {
            $context->getResponse()->setStatus(400)->setBody(["message" => "bad request"])->sendJSON();
            return;
        }
        $department = new Department();
        $department
            ->setUuid(Utils::randomUUID())
            ->setDepName($form["dep_name"])
            ->setManagerEmpId($form["manager_emp_id"]);
        $department->create($context->getDatabase());
        $context->getResponse()->setBody($department)->sendJSON();
    }

    public function putDepartmentAction(Context $context): void
    {
        $form = $context->getRequest()->read();
        if (
            !isset($form["dep_id"]) ||
            !isset($form["dep_name"]) ||
            !isset($form["manager_emp_id"])
        ) {
            $context->getResponse()->setStatus(400)->setBody(["message" => "bad request"])->sendJSON();
            return;
        }
        if (array_search("", $form)) {
            $context->getResponse()->setStatus(400)->setBody(["message" => "bad request"])->sendJSON();
            return;
        }
        $department = new Department();
        $department->load($context->getDatabase(), $form["dep_id"]);
        if (!$department->checkReady()) {
            $context->getResponse()->setStatus(404)->setBody(["message" => "not found"])->sendJSON();
            return;
        }
        $department
            ->setDepName($form["dep_name"])
            ->setManagerEmpId($form["manager_emp_id"]);
        $department->replace($context->getDatabase());
        $context->getResponse()->setBody($department)->sendJSON();
    }

    public function deleteDepartmentAction(Context $context): void
    {
        $dep_id = $context->getRequest()->getQuery("dep_id");
        $department = new Department();
        $department->load($context->getDatabase(), $dep_id);
        if (!$department->checkReady()) {
            $context->getResponse()->setStatus(404)->setBody(["message" => "not found"])->sendJSON();
            return;
        }
        $department->destroy($context->getDatabase());
        $context->getResponse()->setStatus(200)->setBody(["message" => "success"])->sendJSON();
    }

    public function patchDepartmentAction(Context $context): void
    {
        $emp_id = $context->getRequest()->getQuery("emp_id");
        $dep_id = $context->getRequest()->getQuery("dep_id");
        $employee = new Employee();
        $employee->load($context->getDatabase(), $emp_id);
        if (!$employee->checkReady()) {
            $context->getResponse()->setStatus(404)->setBody(["message" => "not found"])->sendJSON();
            return;
        }
        $department = new Department();
        $department->load($context->getDatabase(), $dep_id);
        if (!$department->checkReady()) {
            $context->getResponse()->setStatus(404)->setBody(["message" => "not found"])->sendJSON();
            return;
        }
        $employee->setDepId($department);
        $employee->replace($context->getDatabase());
        $context->getResponse()->setStatus(200)->setBody(["message" => "success"])->sendJSON();
    }
}
