<template>
  <v-card id="current-wrapper">
    <v-container class="py-8 px-6" fluid>
      <v-card>
        <v-card-title> 部門管理 </v-card-title>
        <item-list :empty="dataset.length < 1" :loaded="loaded">
          <template #data>
            <v-list-item
              v-for="(item, index) in exportData"
              :key="index"
              @click="show(item)"
            >
              <v-list-item-content>
                <v-list-item-title>
                  {{ item.dep_name }}
                </v-list-item-title>
                <v-list-item-subtitle>
                  主管：{{ item.manager ? item.manager.emp_name : "" }} /
                  電話：{{ item.manager ? item.manager.phone : "" }} / 住址：{{
                    item.manager ? item.manager.city : ""
                  }}{{ item.manager ? item.manager.address : "" }}
                </v-list-item-subtitle>
              </v-list-item-content>
              <v-list-item-action v-show="mode === 2">
                <v-btn
                  class="amber darken-1 white--text"
                  depressed
                  rounded
                  @click="modify(item)"
                >
                  <v-icon>mdi-pencil-outline</v-icon>
                </v-btn>
              </v-list-item-action>
              <v-list-item-action v-show="mode === 2">
                <v-btn
                  class="red darken-1 white--text"
                  depressed
                  rounded
                  @click="remove(item)"
                >
                  <v-icon>mdi-trash-can-outline</v-icon>
                </v-btn>
              </v-list-item-action>
            </v-list-item>
          </template>
        </item-list>
      </v-card>
    </v-container>
    <modify-drawer @append="append" @modify="mode = 2" @view="mode = 0" />
    <v-overlay :value="mode === 1 || mode === 3 || mode === 4 || mode === 5">
      <v-container>
        <v-card
          :width="wrapperSize.width"
          :max-height="wrapperSize.height"
          class="overflow-y-auto"
          v-if="mode === 5"
          light
        >
          <v-banner class="white" sticky>
            <v-card-title>{{ editing.target.dep_name }}</v-card-title>
            <v-card-subtitle>部門名冊</v-card-subtitle>
          </v-banner>
          <v-card-text>
            <item-list :empty="dataset.length < 1" :loaded="loaded">
              <template #data>
                <v-list-item v-for="(item, index) in employees" :key="index">
                  <v-list-item-content>
                    <v-list-item-title>
                      {{ item.emp_name }}
                    </v-list-item-title>
                    <v-list-item-subtitle>
                      {{ item.job_title }}
                    </v-list-item-subtitle>
                  </v-list-item-content>
                </v-list-item>
              </template>
            </item-list>
          </v-card-text>
          <v-card-actions>
            <v-spacer />
            <v-btn class="primary" depressed @click="mode = 0">確定</v-btn>
          </v-card-actions>
        </v-card>
        <v-card
          :width="wrapperSize.width"
          :max-height="wrapperSize.height"
          class="overflow-y-auto"
          v-if="mode === 1 || mode === 3"
          light
        >
          <v-banner class="white" sticky>
            <v-card-title v-if="mode === 1">新增部門</v-card-title>
            <v-card-title v-else>編輯部門</v-card-title>
            <v-card-subtitle v-if="mode === 1">
              Append Department
            </v-card-subtitle>
            <v-card-subtitle v-else>Modify Department</v-card-subtitle>
            <v-card-subtitle
              class="red white--text"
              v-show="editing.message"
              v-text="editing.message"
            />
          </v-banner>
          <v-card-text>
            <v-form>
              <v-text-field
                v-model="editing.target.dep_name"
                label="部門名稱"
                type="name"
              />
              <v-select
                v-model="editing.target.manager_emp_name"
                :items="employees"
                item-text="display_name"
                label="部門主管"
              />
            </v-form>
          </v-card-text>
          <v-card-actions>
            <v-btn :disabled="editing.loading" depressed @click="mode = 0">
              取消
            </v-btn>
            <v-spacer />
            <v-btn
              v-if="mode === 1"
              :loading="editing.loading"
              class="secondary"
              @click="appendSubmit"
              depressed
            >
              新增
            </v-btn>
            <v-btn
              v-else
              :loading="editing.loading"
              class="secondary"
              @click="modifySubmit"
              depressed
            >
              更新
            </v-btn>
          </v-card-actions>
        </v-card>
        <v-card v-if="mode === 4" light>
          <v-card-title>刪除員工資料</v-card-title>
          <v-card-subtitle>Delete Department</v-card-subtitle>
          <v-card-subtitle
            class="red white--text"
            v-show="editing.message"
            v-text="editing.message"
          />
          <v-card-text>
            {{ editing.target.display_name }} 的資料將被刪除
          </v-card-text>
          <v-card-actions>
            <v-btn :disabled="editing.loading" depressed @click="mode = 0">
              取消
            </v-btn>
            <v-spacer />
            <v-btn
              :loading="editing.loading"
              class="secondary"
              @click="removeSubmit"
              depressed
            >
              送出
            </v-btn>
          </v-card-actions>
        </v-card>
      </v-container>
    </v-overlay>
  </v-card>
</template>

<script>
import capitalize from "capitalize";

import ItemList from "@/components/ItemList";
import ModifyDrawer from "@/components/ModifyDrawer";

export default {
  name: "Department",
  components: { ModifyDrawer, ItemList },
  data: () => ({
    loaded: false,
    mode: 0,
    editing: {
      message: null,
      loading: false,
      target: null,
    },
    dataset: [],
    employees: [],
  }),
  created() {
    this.load();
  },
  methods: {
    load() {
      this.loaded = false;
      this.$axios.get("/manage/departments").then((resp) => {
        this.loaded = true;
        resp.data.map((item) => {
          item.manager_emp_name = `${item.manager.emp_name} (${item.manager.department.dep_name} | ${item.manager.emp_id})`;
          return item;
        });
        resp.data.sort((a, b) => a.dep_name > b.dep_name);
        this.dataset = resp.data;
      });
    },
    loadEmployees() {
      return this.$axios.get("/manage/employees").then((resp) => {
        resp.data = resp.data.map((item) => {
          item.display_name = `${item.emp_name} (${item.department.dep_name} | ${item.emp_id})`;
          return item;
        });
        resp.data.sort((a, b) => a.emp_name > b.emp_name);
        this.employees = resp.data;
      });
    },
    loadDepartmentEmployees(dep_id) {
      return this.$axios
        .get("/manage/department/employees", { params: { dep_id } })
        .then((resp) => {
          resp.data.sort((a, b) => a.emp_name > b.emp_name);
          this.employees = resp.data;
        });
    },
    show(item) {
      if (this.mode !== 0) return;
      this.editing.target = {};
      this.loadDepartmentEmployees(item.dep_id);
      Object.assign(this.editing.target, item);
      this.mode = 5;
    },
    append() {
      this.editing.target = {};
      this.loadEmployees().then(() => (this.mode = 1));
    },
    modify(item) {
      this.editing.target = {};
      Object.assign(this.editing.target, item);
      this.loadEmployees().then(() => (this.mode = 3));
    },
    remove(item) {
      this.editing.target = item;
      this.mode = 4;
    },
    getParams() {
      const form = new URLSearchParams();
      for (const key in this.editing.target) {
        if (this.editing.target[key]) {
          form.append(key, this.editing.target[key]);
        }
      }
      if (form.has("manager_emp_name")) {
        const manager = this.employees.find(
          (item) => item.display_name === this.editing.target.manager_emp_name
        );
        form.append("manager_emp_id", manager.emp_id);
        form.delete("manager_emp_name");
        form.delete("manager");
      }
      return form;
    },
    async appendSubmit() {
      this.editing.loading = true;
      try {
        const form = this.getParams();
        const response = await this.$axios.post("/manage/department", form);
        if (response.status === 201) {
          this.mode = 0;
          this.load();
        }
      } catch (e) {
        this.editing.message = e.response.data.message
          ? capitalize(e.response.data.message)
          : "Failed";
        console.warn(e);
      }
      this.editing.loading = false;
    },
    async modifySubmit() {
      this.editing.loading = true;
      try {
        const form = this.getParams();
        form.set("dep_id", this.editing.target.dep_id);
        const response = await this.$axios.put("/manage/department", form);
        if (response.status === 204) {
          this.mode = 0;
          this.load();
        }
      } catch (e) {
        this.editing.message = e.response.data.message
          ? capitalize(e.response.data.message)
          : "Failed";
        console.warn(e);
      }
      this.editing.loading = false;
    },
    async removeSubmit() {
      this.editing.loading = true;
      try {
        const response = await this.$axios.delete("/manage/employee", {
          params: { emp_id: this.editing.target.emp_id },
        });
        if (response.status === 204) {
          this.mode = 0;
          this.load();
        }
      } catch (e) {
        this.editing.message = e.response.data.message
          ? capitalize(e.response.data.message)
          : "Failed";
        console.warn(e);
      }
      this.editing.loading = false;
    },
  },
  computed: {
    exportData() {
      return this.dataset;
    },
    wrapperSize() {
      const wrapper = document.getElementById("current-wrapper");
      const style = window.getComputedStyle(wrapper);
      return {
        width: parseInt(style.width) - 30,
        height: window.innerHeight - 50,
      };
    },
  },
};
</script>
