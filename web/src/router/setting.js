import Profile from "@/views/Setting/Profile";
import Users from "@/views/Setting/Users";

export default [
  {
    path: "/",
    name: "Profile",
    component: Profile
  },
  {
    path: "users",
    name: "Users",
    component: Users
  }
];
