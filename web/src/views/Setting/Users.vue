<template>
  <v-card>
    <v-container class="py-8 px-6" fluid>
      <v-card>
        <v-card-title> 使用者管理 </v-card-title>
        <item-list :empty="dataset.length < 1" :loaded="loaded">
          <template #data>
            <v-list-item v-for="(item, index) in exportData" :key="index">
              <v-list-item-content>
                <v-list-item-title>
                  {{ item.display_name }} ({{ item.username }})
                </v-list-item-title>
                <v-list-item-subtitle>
                  {{ item.uuid }}
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
    <v-overlay :value="mode === 1 || mode === 3 || mode === 4">
      <v-container>
        <v-card v-if="mode === 1" light>
          <v-card-title>新增使用者</v-card-title>
          <v-card-subtitle>Append User</v-card-subtitle>
          <v-card-subtitle
            class="red white--text"
            v-show="editing.message"
            v-text="editing.message"
          />
          <v-card-text>
            <v-form>
              <v-select
                v-model="editing.target.manager_emp_name"
                :items="employees"
                item-text="display_name"
                label="員工檔案"
              />
              <v-text-field
                v-model="editing.target.username"
                label="帳號"
                type="username"
              />
              <v-text-field
                v-model="editing.target.password"
                label="密碼"
                type="password"
              />
            </v-form>
          </v-card-text>
          <v-card-actions>
            <v-btn :disabled="editing.loading" depressed @click="mode = 0"
              >取消</v-btn
            >
            <v-spacer />
            <v-btn
              :loading="editing.loading"
              class="secondary"
              @click="appendSubmit"
              depressed
              >送出</v-btn
            >
          </v-card-actions>
        </v-card>
        <v-card v-if="mode === 3" light>
          <v-card-title>編輯使用者</v-card-title>
          <v-card-subtitle>Modify User</v-card-subtitle>
          <v-card-subtitle
            class="red white--text"
            v-show="editing.message"
            v-text="editing.message"
          />
          <v-card-text>
            <v-form>
              <v-text-field
                v-model="editing.target.display_name"
                label="姓名"
                type="name"
                disabled
              />
              <v-text-field
                v-model="editing.target.username"
                label="帳號"
                type="username"
              />
              <v-text-field
                v-model="editing.target.password"
                label="密碼"
                type="password"
              />
            </v-form>
          </v-card-text>
          <v-card-actions>
            <v-btn :disabled="editing.loading" depressed @click="mode = 0"
              >取消</v-btn
            >
            <v-spacer />
            <v-btn
              :loading="editing.loading"
              class="secondary"
              @click="modifySubmit"
              depressed
              >送出</v-btn
            >
          </v-card-actions>
        </v-card>
        <v-card v-if="mode === 4" light>
          <v-card-title>刪除使用者</v-card-title>
          <v-card-subtitle>Delete User</v-card-subtitle>
          <v-card-subtitle
            class="red white--text"
            v-show="editing.message"
            v-text="editing.message"
          />
          <v-card-text>
            {{ editing.target.display_name }} 的資料將被刪除
          </v-card-text>
          <v-card-actions>
            <v-btn :disabled="editing.loading" depressed @click="mode = 0"
              >取消</v-btn
            >
            <v-spacer />
            <v-btn
              :loading="editing.loading"
              class="secondary"
              @click="removeSubmit"
              depressed
              >送出</v-btn
            >
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
  name: "Users",
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
      this.$axios.get("/setting/users").then((resp) => {
        this.loaded = true;
        this.dataset = resp.data;
      });
    },
    loadEmployees() {
      return this.$axios.get("/manage/employees").then((resp) => {
        const uuids = this.dataset.map((i) => i.uuid);
        resp.data = resp.data.filter((item) => !uuids.includes(item.emp_id));
        resp.data = resp.data.map((item) => {
          item.display_name = `${item.emp_name} (${item.department.dep_name} | ${item.emp_id})`;
          return item;
        });
        resp.data.sort((a, b) => a.emp_name > b.emp_name);
        this.employees = resp.data;
      });
    },
    append() {
      this.editing.target = {};
      this.loadEmployees().then(() => (this.mode = 1));
    },
    modify(item) {
      this.editing.target = {};
      Object.assign(this.editing.target, item);
      this.mode = 3;
    },
    remove(item) {
      this.editing.target = item;
      this.mode = 4;
    },
    getParams() {
      if (this.editing.target.manager_emp_name) {
        const emp = this.employees.find(
          (item) => item.display_name === this.editing.target.manager_emp_name
        );
        this.editing.target.uuid = emp.emp_id;
        delete this.editing.target.manager_emp_name;
      }
      const form = new URLSearchParams();
      for (const key in this.editing.target) {
        if (this.editing.target[key]) {
          form.append(key, this.editing.target[key]);
        }
      }
      return form;
    },
    async appendSubmit() {
      this.editing.loading = true;
      try {
        const form = this.getParams();
        const response = await this.$axios.post("/setting/user", form);
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
        form.set("uuid", this.editing.target.uuid);
        const response = await this.$axios.put("/setting/user", form);
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
        const response = await this.$axios.delete("/setting/user", {
          params: { uuid: this.editing.target.uuid },
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
  },
};
</script>
