<template>
  <v-navigation-drawer v-model="drawer">
    <v-list class="pl-14" shaped>
      <v-list-item-group v-model="current">
        <v-list-item v-for="i in list" :key="i.title" @click="action(i)">
          <v-list-item-content>
            <v-list-item-title>{{ i.title }}</v-list-item-title>
          </v-list-item-content>
        </v-list-item>
      </v-list-item-group>
    </v-list>
  </v-navigation-drawer>
</template>

<script>
export default {
  name: "Drawer",
  data: () => ({
    drawer: null,
    current: 0,
    lists: {
      manage: [
        {
          title: "概觀",
          path: "/manage",
        },
        {
          title: "員工資料",
          path: "/manage/employee",
        },
        {
          title: "部門管理",
          path: "/manage/department",
        },
      ],
      setting: [
        {
          title: "個人檔案",
          path: "/setting",
        },
        {
          title: "使用者列表",
          path: "/setting/users",
        },
      ],
    },
  }),
  methods: {
    action(i) {
      if (i.path) {
        if (this.$route.path !== i.path) {
          this.$router.push(i.path);
        }
      } else if (i.url) {
        location.assign(i.url);
      } else {
        console.error(i);
      }
    },
  },
  computed: {
    list() {
      const path = this.$route.path.split("/");
      if (path.length < 2 || !(path[1] in this.lists)) return [];
      return this.lists[path[1]];
    },
    currentRow() {
      return this.list.findIndex((i) => this.$route.path === i.path);
    },
  },
  watch: {
    currentRow(e) {
      this.current = e;
    },
  },
  created() {
    window.addEventListener("resize", () => {
      this.drawer = window.innerWidth > 1024;
    });
  },
};
</script>
