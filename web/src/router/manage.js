import Overview from "@/views/Manage/Overview.vue";
import Employee from "@/views/Manage/Employee";
import Department from "@/views/Manage/Department";

export default [
  {
    path: "",
    name: "Overview",
    component: Overview
  },
  {
    path: "employee",
    name: "Employee",
    component: Employee
  },
  {
    path: "department",
    name: "Department",
    component: Department
  },
];
