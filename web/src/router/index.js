import Vue from "vue";
import VueRouter from "vue-router";
import manage from "./manage";
import setting from "./setting";
import Index from "../views/Index";
import Manage from "../views/Manage";
import Setting from "../views/Setting";
import Logout from "../views/Logout";
import NotFound from "../views/NotFound";

Vue.use(VueRouter);

const routes = [
  {
    path: "/",
    name: "Index",
    component: Index
  },
  {
    path: "/manage",
    name: "Manage",
    component: Manage,
    children: manage
  },
  {
    path: "/setting",
    name: "Setting",
    component: Setting,
    children: setting
  },
  {
    path: "/logout",
    name: "Logout",
    component: Logout
  },
  {
    path: "*",
    name: "NotFound",
    component: NotFound
  }
];

const router = new VueRouter({
  routes
});

export default router;
