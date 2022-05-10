<template>
  <v-card id="current-wrapper">
    <v-container class="py-8 px-6" fluid>
      <v-card>
        <v-card-title> 員工資料 </v-card-title>
        <item-list :empty="dataset.length < 1" :loaded="loaded">
          <template #data>
            <v-list-item
              v-for="(item, index) in exportData"
              :key="index"
              @click="show(item)"
            >
              <v-list-item-content>
                <v-list-item-title>
                  {{ item.emp_name }}
                </v-list-item-title>
                <v-list-item-subtitle>
                  {{ item.department.dep_name }}/{{ item.job_title }}
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
        <v-card v-if="mode === 5" light>
          <v-banner class="white" sticky>
            <v-card-title>{{ editing.target.emp_name }}</v-card-title>
            <v-card-subtitle>
              {{ editing.target.department.dep_name }}/{{
                editing.target.job_title
              }}
            </v-card-subtitle>
          </v-banner>
          <v-card-text>
            <ul>
              <li>縣市：{{ editing.target.city }}</li>
              <li>地址：{{ editing.target.address }}</li>
              <li>電話：{{ editing.target.phone }}</li>
              <li>郵遞區號：{{ editing.target.zip_code }}</li>
              <li>月薪資：{{ editing.target.month_salary }}</li>
              <li>年假天數：{{ editing.target.annual_leave }}</li>
            </ul>
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
            <v-card-title v-if="mode === 1">新增員工資料</v-card-title>
            <v-card-title v-else>編輯員工資料</v-card-title>
            <v-card-subtitle v-if="mode === 1">Append Employee</v-card-subtitle>
            <v-card-subtitle v-else>Modify Employee</v-card-subtitle>
            <v-card-subtitle
              class="red white--text"
              v-show="editing.message"
              v-text="editing.message"
            />
          </v-banner>
          <v-card-text>
            <v-form>
              <v-text-field
                v-model="editing.target.emp_name"
                label="姓名"
                type="name"
              />
              <v-text-field
                v-model="editing.target.job_title"
                label="現任職稱"
                type="text"
              />
              <v-select
                v-model="editing.target.department"
                :items="departments"
                item-text="dep_name"
                label="所屬部門"
              />
              <v-text-field
                v-model="editing.target.city"
                label="縣市"
                type="text"
              />
              <v-text-field
                v-model="editing.target.address"
                label="地址"
                type="address"
              />
              <v-text-field
                v-model="editing.target.phone"
                label="電話"
                type="tel"
              />
              <v-text-field
                v-model="editing.target.zip_code"
                label="郵遞區號"
                type="text"
              />
              <v-text-field
                v-model="editing.target.month_salary"
                label="月薪資"
                type="number"
              />
              <v-text-field
                v-model="editing.target.annual_leave"
                label="年假天數"
                type="number"
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
          <v-card-subtitle>Delete Employee</v-card-subtitle>
          <v-card-subtitle
            class="red white--text"
            v-show="editing.message"
            v-text="editing.message"
          />
          <v-card-text>
            {{ editing.target.emp_name }} 的資料將被刪除
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
  name: "Employee",
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
    departments: [],
  }),
  created() {
    this.load();
  },
  methods: {
    load() {
      this.loaded = false;
      this.$axios.get("/manage/employees").then((resp) => {
        this.loaded = true;
        resp.data.sort((a, b) => a.emp_name > b.emp_name);
        this.dataset = resp.data;
      });
    },
    loadDepartments() {
      return this.$axios.get("/manage/departments").then((resp) => {
        resp.data.sort((a, b) => a.dep_name > b.dep_name);
        this.departments = resp.data;
      });
    },
    show(item) {
      if (this.mode !== 0) return;
      this.editing.target = {};
      Object.assign(this.editing.target, item);
      this.mode = 5;
    },
    append() {
      this.editing.target = {};
      this.loadDepartments().then(() => (this.mode = 1));
    },
    modify(item) {
      this.editing.target = {};
      Object.assign(this.editing.target, item);
      this.loadDepartments().then(() => (this.mode = 3));
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
      if (form.has("department")) {
        const dep = this.departments.find(
          (item) => item.dep_name === this.editing.target.department
        );
        form.append("dep_id", dep.dep_id);
        form.delete("department");
      }
      return form;
    },
    async appendSubmit() {
      this.editing.loading = true;
      try {
        const form = this.getParams();
        const response = await this.$axios.post("/manage/employee", form);
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
        form.set("emp_id", this.editing.target.emp_id);
        const response = await this.$axios.put("/manage/employee", form);
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
